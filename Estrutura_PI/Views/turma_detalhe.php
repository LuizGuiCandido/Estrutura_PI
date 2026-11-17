<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirProfessor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$isGestor = ((int) ($_SESSION['gestor'] ?? 0) === 1);
$idTurma  = (int) ($_GET['id'] ?? 0);

if ($idTurma <= 0) {
    header('Location: turmas.php');
    exit;
}

// 1. Dados da turma
$stm = $db->prepare("
    SELECT 
        t.*,
        c.nome AS curso_nome,
        u_prof.nome AS professor_nome,
        u_est.nome AS estagiario_nome
    FROM turmas t
    INNER JOIN cursos c ON c.id_curso = t.curso_id
    INNER JOIN professores prof ON prof.usuario_id = t.professor_id
    INNER JOIN usuarios u_prof ON u_prof.id_usuario = prof.usuario_id
    LEFT JOIN professores est ON est.usuario_id = t.estagiario_id
    LEFT JOIN usuarios u_est ON u_est.id_usuario = est.usuario_id
    WHERE t.id_turma = ? AND t.deletado_em IS NULL
");
$stm->execute([$idTurma]);
$turma = $stm->fetch(PDO::FETCH_OBJ);

if (!$turma) {
    header('Location: turmas.php');
    exit;
}

// 2. Alunos matriculados nesta turma
$stmAlunos = $db->prepare("
    SELECT 
        m.*,
        u.id_usuario,
        u.nome AS aluno_nome,
        u.data_nasc,
        a.observacao
    FROM matriculas m
    INNER JOIN alunos a ON a.usuario_id = m.aluno_id
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
    WHERE m.turma_id = ? AND m.status_matricula = 1 AND m.deletado_em IS NULL
    ORDER BY u.nome ASC
");
$stmAlunos->execute([$idTurma]);
$alunosMatriculados = $stmAlunos->fetchAll(PDO::FETCH_OBJ);
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
    <title><?= htmlspecialchars($turma->curso_nome) ?> · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php
        if ((int) ($_SESSION['gestor'] ?? 0) === 1) {
          require_once __DIR__ . '/sidebar/sidebar.php';
        } else {
          require_once __DIR__ . '/sidebar/sidebar_professor.php';
        }
      ?>  

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($turma->curso_nome) ?></h1>
              <div class="text-muted small">Turmas e aulas › Turmas › Dados da turma</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="turmas.php" class="btn btn-outline-secondary">Voltar</a>
              <?php if ($isGestor): ?>
                <a href="turma_form.php?id=<?= $turma->id_turma ?>" class="btn btn-outline-primary">Editar turma</a>
              <?php endif; ?>
            </div>
          </div>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <h2 class="h6 fw-bold mb-0">Dados da turma</h2>
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                  <tbody>
                    <tr>
                      <th class="bg-light" style="width:16%">Curso</th>
                      <td class="text-wrap"><?= htmlspecialchars($turma->curso_nome) ?></td>
                      <th class="bg-light" style="width:16%">Horário</th>
                      <td class="text-wrap"><?= substr((string) $turma->horario_inicio, 0, 5) ?> – <?= substr((string) $turma->horario_termino, 0, 5) ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Local</th>
                      <td class="text-wrap">Raia <?= (int) $turma->local_aula ?></td>
                      <th class="bg-light" style="width:16%">Professor</th>
                      <td class="text-wrap"><?= htmlspecialchars($turma->professor_nome) ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Estagiário</th>
                      <td class="text-wrap"><?= htmlspecialchars($turma->estagiario_nome ?? 'Nenhum') ?></td>
                      <th class="bg-light" style="width:16%">Período</th>
                      <td class="text-wrap"><?= date('d/m/Y', strtotime($turma->data_inicio)) ?> – <?= $turma->data_fim ? date('d/m/Y', strtotime($turma->data_fim)) : 'em aberto' ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Alunos vinculados</h2>
                <?php if ($isGestor): ?>
                  <a href="matricula_form.php?turma_id=<?= $turma->id_turma ?>" class="btn btn-primary">+ Vincular aluno</a>
                <?php endif; ?>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Aluno</th>
                      <th>Nascimento</th>
                      <th>Observações</th>
                      <th>Seg</th>
                      <th>Ter</th>
                      <th>Qua</th>
                      <th>Qui</th>
                      <th>Sex</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($alunosMatriculados)): ?>
                      <tr><td colspan="9" class="text-center text-muted py-3">Nenhum aluno matriculado nesta turma.</td></tr>
                    <?php else: ?>
                      <?php foreach ($alunosMatriculados as $am): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($am->aluno_nome) ?></b></td>
                          <td><?= $am->data_nasc ? date('d/m/Y', strtotime($am->data_nasc)) : '-' ?></td>
                          <td><?= htmlspecialchars($am->observacao !== '' ? $am->observacao : '—') ?></td>
                          <td><?= (int) $am->segunda === 1 ? '✔' : '—' ?></td>
                          <td><?= (int) $am->terca === 1 ? '✔' : '—' ?></td>
                          <td><?= (int) $am->quarta === 1 ? '✔' : '—' ?></td>
                          <td><?= (int) $am->quinta === 1 ? '✔' : '—' ?></td>
                          <td><?= (int) $am->sexta === 1 ? '✔' : '—' ?></td>
                          <td class="text-nowrap">
                            <a href="aluno_detalhe.php?id=<?= $am->aluno_id ?>" class="text-primary me-2" title="Ver aluno"><i class="bi bi-eye"></i></a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($alunosMatriculados) ?> aluno(s) vinculado(s)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>