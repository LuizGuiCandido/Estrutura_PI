<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$usuarioIdLogado = (int) $_SESSION['id_usuario'];
$isGestor        = ((int) ($_SESSION['gestor'] ?? 0) === 1);
$perfil          = $_SESSION['perfil'] ?? 'aluno';

// 1. Determina quais alunos o usuário logado pode visualizar
$listaAlunos = [];
if ($isGestor) {
    $listaAlunos = $db->query("
        SELECT u.id_usuario, u.nome 
        FROM alunos a 
        INNER JOIN usuarios u ON u.id_usuario = a.usuario_id 
        WHERE a.deletado_em IS NULL AND u.deletado_em IS NULL 
        ORDER BY u.nome ASC
    ")->fetchAll(PDO::FETCH_OBJ);
} else {
    // Próprio usuário (caso também seja aluno) + dependentes vinculados a ele
    $stmAlunos = $db->prepare("
        SELECT u.id_usuario, u.nome 
        FROM alunos a 
        INNER JOIN usuarios u ON u.id_usuario = a.usuario_id 
        WHERE (a.usuario_id = ? OR a.responsavel_id = ?) 
          AND a.deletado_em IS NULL AND u.deletado_em IS NULL 
        ORDER BY u.nome ASC
    ");
    $stmAlunos->execute([$usuarioIdLogado, $usuarioIdLogado]);
    $listaAlunos = $stmAlunos->fetchAll(PDO::FETCH_OBJ);
}

$alunoIdSelecionado = (int) ($_GET['aluno_id'] ?? 0);
$idsPermitidos = array_map(fn($al) => (int) $al->id_usuario, $listaAlunos);

if (!in_array($alunoIdSelecionado, $idsPermitidos, true)) {
    $alunoIdSelecionado = !empty($idsPermitidos) ? $idsPermitidos[0] : $usuarioIdLogado;
}

// Nome do aluno selecionado
$nomeAlunoAtual = $_SESSION['nome'] ?? 'Aluno';
foreach ($listaAlunos as $al) {
    if ((int) $al->id_usuario === $alunoIdSelecionado) {
        $nomeAlunoAtual = $al->nome;
        break;
    }
}

// 2. Controle da semana selecionada (segunda a sexta)
$dataBaseStr = trim((string) ($_GET['semana'] ?? date('Y-m-d')));
$timestampBase = strtotime($dataBaseStr) ?: time();
// Ajusta para a segunda-feira da semana escolhida
$diaSemanaNum = (int) date('N', $timestampBase); // 1 (Seg) a 7 (Dom)
$segundaTs = strtotime('-' . ($diaSemanaNum - 1) . ' days', $timestampBase);
$sextaTs   = strtotime('+4 days', $segundaTs);

$semanaAnterior = date('Y-m-d', strtotime('-7 days', $segundaTs));
$proximaSemana  = date('Y-m-d', strtotime('+7 days', $segundaTs));

$diasSemanaDatas = [
    1 => ['label' => 'Seg ' . date('d', $segundaTs), 'data' => date('Y-m-d', $segundaTs)],
    2 => ['label' => 'Ter ' . date('d', strtotime('+1 day', $segundaTs)), 'data' => date('Y-m-d', strtotime('+1 day', $segundaTs))],
    3 => ['label' => 'Qua ' . date('d', strtotime('+2 days', $segundaTs)), 'data' => date('Y-m-d', strtotime('+2 days', $segundaTs))],
    4 => ['label' => 'Qui ' . date('d', strtotime('+3 days', $segundaTs)), 'data' => date('Y-m-d', strtotime('+3 days', $segundaTs))],
    5 => ['label' => 'Sex ' . date('d', $sextaTs), 'data' => date('Y-m-d', $sextaTs)],
];

$mesesPt = [
    1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
    5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
    9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
];
$tituloSemana = 'Semana de ' . date('d', $segundaTs) . ' a ' . date('d', $sextaTs) . ' de ' . $mesesPt[(int) date('n', $sextaTs)];

// 3. Busca matrículas ativas e turmas do aluno selecionado
$stmMat = $db->prepare("
    SELECT 
        m.segunda,
        m.terca,
        m.quarta,
        m.quinta,
        m.sexta,
        t.horario_inicio,
        t.horario_termino,
        c.nome AS curso_nome
    FROM matriculas m
    INNER JOIN turmas t ON t.id_turma = m.turma_id
    INNER JOIN cursos c ON c.id_curso = t.curso_id
    WHERE m.aluno_id = ? AND m.status_matricula = 1 AND m.deletado_em IS NULL AND t.deletado_em IS NULL
    ORDER BY t.horario_inicio ASC
");
$stmMat->execute([$alunoIdSelecionado]);
$gradeHorarios = $stmMat->fetchAll(PDO::FETCH_OBJ);

// 4. Busca histórico completo de reposições do aluno (tabela `reposicao`)
$stmRep = $db->prepare("
    SELECT 
        r.data_reposicao,
        r.horario,
        r.status_reposicao,
        c.nome AS curso_nome
    FROM reposicao r
    INNER JOIN matriculas m ON m.id_matricula = r.matricula_id
    LEFT JOIN turmas t ON t.id_turma = m.turma_id
    LEFT JOIN cursos c ON c.id_curso = t.curso_id
    WHERE m.aluno_id = ? AND r.deletado_em IS NULL
    ORDER BY r.data_reposicao DESC, r.horario DESC
");
$stmRep->execute([$alunoIdSelecionado]);
$reposicoes = $stmRep->fetchAll(PDO::FETCH_OBJ);

// Mapeia reposições marcadas nesta semana para exibir badge na grade
$reposicoesSemana = [];
foreach ($reposicoes as $rep) {
    if ($rep->status_reposicao === 'marcada') {
        $horaCurta = substr((string) $rep->horario, 0, 5);
        $reposicoesSemana[$rep->data_reposicao][$horaCurta] = $rep->curso_nome ?? 'Reposição';
    }
}
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <title>Minha agenda · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar_aluno.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Minha agenda</h1>
              <div class="text-muted small"><?= htmlspecialchars($tituloSemana) ?> · <?= htmlspecialchars($nomeAlunoAtual) ?></div>
            </div>
          </div>

          <!-- CARD 1: HORÁRIOS DA SEMANA -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-2 mb-1">
                <?php if (count($listaAlunos) > 1): ?>
                  <div class="col-12 col-md-4">
                    <form method="GET">
                      <input type="hidden" name="semana" value="<?= htmlspecialchars(date('Y-m-d', $segundaTs)) ?>">
                      <select name="aluno_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($listaAlunos as $al): ?>
                          <option value="<?= $al->id_usuario ?>" <?= (int) $al->id_usuario === $alunoIdSelecionado ? 'selected' : '' ?>>
                            Aluno: <?= htmlspecialchars($al->nome) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  </div>
                <?php elseif (count($listaAlunos) === 1): ?>
                  <div class="col-12 col-md-4">
                    <input type="text" class="form-control" value="Aluno: <?= htmlspecialchars($nomeAlunoAtual) ?>" readonly>
                  </div>
                <?php endif; ?>

                <div class="col-12 col-md-<?= !empty($listaAlunos) ? '8' : '12' ?>">
                  <div class="btn-group w-100">
                    <a class="btn btn-outline-primary" href="aluno_agenda.php?aluno_id=<?= $alunoIdSelecionado ?>&semana=<?= $semanaAnterior ?>">‹ Semana anterior</a>
                    <a class="btn btn-outline-primary" href="aluno_agenda.php?aluno_id=<?= $alunoIdSelecionado ?>&semana=<?= $proximaSemana ?>">Próxima semana ›</a>
                  </div>
                </div>
              </div>

              <h2 class="h6 fw-bold mb-0">Horários da semana</h2>
              <div class="tb">
                <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Horário</th>
                        <th><?= $diasSemanaDatas[1]['label'] ?></th>
                        <th><?= $diasSemanaDatas[2]['label'] ?></th>
                        <th><?= $diasSemanaDatas[3]['label'] ?></th>
                        <th><?= $diasSemanaDatas[4]['label'] ?></th>
                        <th><?= $diasSemanaDatas[5]['label'] ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($gradeHorarios) && empty($reposicoesSemana)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma aula agendada para este aluno.</td></tr>
                      <?php else: ?>
                        <?php foreach ($gradeHorarios as $gh): ?>
                          <?php $horaLinha = substr((string) $gh->horario_inicio, 0, 5); ?>
                          <tr>
                            <td><b><?= $horaLinha ?></b></td>
                            <?php
                              $colsDias = [1 => 'segunda', 2 => 'terca', 3 => 'quarta', 4 => 'quinta', 5 => 'sexta'];
                              foreach ($colsDias as $numDia => $campoDia):
                                  $dataCelula = $diasSemanaDatas[$numDia]['data'];
                                  if (!empty($reposicoesSemana[$dataCelula][$horaLinha])):
                            ?>
                                    <td><span class="badge rounded-pill b-wa">Reposição</span></td>
                            <?php elseif ((int) $gh->$campoDia === 1): ?>
                                    <td><?= htmlspecialchars($gh->curso_nome) ?></td>
                            <?php else: ?>
                                    <td>—</td>
                            <?php
                                  endif;
                              endforeach;
                            ?>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- CARD 2: REPOSIÇÕES -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Reposições</h2>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Data</th>
                      <th>Horário</th>
                      <th>Turma</th>
                      <th>Situação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($reposicoes)): ?>
                      <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma reposição registrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($reposicoes as $rep): ?>
                        <?php
                          $badgeClass = match ($rep->status_reposicao) {
                              'marcada'   => 'b-wa',
                              'concluída' => 'b-ok',
                              default     => 'b-er',
                          };
                          $labelStatus = match ($rep->status_reposicao) {
                              'marcada'   => 'Agendada',
                              'concluída' => 'Concluída',
                              default     => 'Não concluída',
                          };
                        ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($rep->data_reposicao)) ?></td>
                          <td><?= substr((string) $rep->horario, 0, 5) ?></td>
                          <td><?= htmlspecialchars($rep->curso_nome ?? '—') ?></td>
                          <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $labelStatus ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($reposicoes) ?> registro(s)</span>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>