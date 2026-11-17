<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirProfessor(); // Permitido para professores e gestores

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$usuarioId = (int) $_SESSION['id_usuario'];
$isGestor  = ((int) ($_SESSION['gestor'] ?? 0) === 1);

// Nomes dos dias para o cabeçalho
$diasPt = [
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
    7 => 'Domingo'
];
$diaNum = (int) date('N');
$nomeDiaHoje = ($diasPt[$diaNum] ?? 'Hoje') . ', ' . date('d \d\e F \d\e Y');

// Consulta SQL limpa e validada para as turmas do dia/professor
$sql = "SELECT 
            t.id_turma,
            t.horario_inicio,
            t.horario_termino,
            t.local_aula,
            c.nome AS curso_nome,
            (SELECT COUNT(*) FROM matriculas m 
             WHERE m.turma_id = t.id_turma AND m.status_matricula = 1 AND m.deletado_em IS NULL) AS total_alunos
        FROM turmas t
        INNER JOIN cursos c ON c.id_curso = t.curso_id
        WHERE t.deletado_em IS NULL";

$params = [];

if (!$isGestor) {
    $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
    $params[] = $usuarioId;
    $params[] = $usuarioId;
}

$sql .= " ORDER BY t.horario_inicio ASC";

$stm = $db->prepare($sql);
$stm->execute($params);
$aulasHoje = $stm->fetchAll(PDO::FETCH_OBJ);

$horaAtualStr = date('H:i:s');
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
    <title>Meu dia · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar_professor.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Meu dia</h1>
              <div class="text-muted small"><?= htmlspecialchars($nomeDiaHoje) ?> · <?= count($aulasHoje) ?> aula(s)</div>
            </div>
          </div>

          <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success py-2 small mb-0"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
            <?php unset($_SESSION['sucesso']); ?>
          <?php endif; ?>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Horário</th>
                      <th>Turma</th>
                      <th>Local</th>
                      <th>Alunos</th>
                      <th>Situação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($aulasHoje)): ?>
                      <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma aula agendada para hoje.</td></tr>
                    <?php else: ?>
                      <?php foreach ($aulasHoje as $aula): ?>
                        <?php 
                          $hInicio = $aula->horario_inicio;
                          $hTermino = $aula->horario_termino;

                          // Define a situação dinamicamente com base no horário atual
                          if ($horaAtualStr > $hTermino) {
                              $situacao = 'Concluída';
                              $badgeClass = 'b-ok';
                          } elseif ($horaAtualStr >= $hInicio && $horaAtualStr <= $hTermino) {
                              $situacao = 'Em andamento';
                              $badgeClass = 'b-in';
                          } else {
                              $situacao = 'A seguir';
                              $badgeClass = 'b-wa';
                          }

                          $nomeTurma = trim(($aula->curso_nome ?? '') . ' ' . ($aula->categoria_nome ?? ''));
                        ?>
                        <tr>
                          <td><b><?= substr($hInicio, 0, 5) ?> – <?= substr($hTermino, 0, 5) ?></b></td>
                          <td><?= htmlspecialchars($nomeTurma !== '' ? $nomeTurma : 'Turma #' . $aula->id_turma) ?></td>
                          <td>Raia <?= htmlspecialchars($aula->local_aula) ?></td>
                          <td><?= (int) $aula->total_alunos ?></td>
                          <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $situacao ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($aulasHoje) ?> aula(s) hoje</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>