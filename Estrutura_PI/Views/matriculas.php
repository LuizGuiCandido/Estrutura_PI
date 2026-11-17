<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirProfessor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$isGestor  = ((int) ($_SESSION['gestor'] ?? 0) === 1);
$usuarioId = (int) $_SESSION['id_usuario'];

$busca    = trim((string) ($_GET['busca'] ?? ''));
$situacao = trim((string) ($_GET['situacao'] ?? ''));

$sql = "SELECT 
            m.id_matricula,
            m.status_matricula,
            m.segunda,
            m.terca,
            m.quarta,
            m.quinta,
            m.sexta,
            m.data_matricula,
            u.nome AS aluno_nome,
            c.nome AS curso_nome,
            t.local_aula,
            t.horario_inicio
        FROM matriculas m
        INNER JOIN alunos a ON a.usuario_id = m.aluno_id
        INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
        LEFT JOIN turmas t ON t.id_turma = m.turma_id
        LEFT JOIN cursos c ON c.id_curso = t.curso_id
        WHERE m.deletado_em IS NULL AND u.deletado_em IS NULL";

$params = [];

if (!$isGestor) {
    $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
    $params[] = $usuarioId;
    $params[] = $usuarioId;
}

if ($busca !== '') {
    $sql .= " AND (u.nome LIKE ? OR c.nome LIKE ?)";
    $like = "%{$busca}%";
    $params[] = $like;
    $params[] = $like;
}

if ($situacao !== '') {
    $sql .= " AND m.status_matricula = ?";
    $params[] = (int) $situacao;
}

$sql .= " ORDER BY m.data_matricula DESC";

$stm = $db->prepare($sql);
$stm->execute($params);
$matriculas = $stm->fetchAll(PDO::FETCH_OBJ);
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
    <title>Matrículas · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Matrículas</h1>
              <div class="text-muted small">Turmas e aulas › Matrículas</div>
            </div>
            <?php if ($isGestor): ?>
              <div class="d-flex gap-2 flex-wrap">
                <a href="matricula_form.php" class="btn btn-primary">+ Nova matrícula</a>
              </div>
            <?php endif; ?>
          </div>

          <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success py-2 small mb-0"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
            <?php unset($_SESSION['sucesso']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <form method="GET" class="row g-2 mb-1">
                <div class="col-12 col-md-8">
                  <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Buscar por aluno ou turma">
                </div>
                <div class="col-12 col-md-3">
                  <select name="situacao" class="form-select">
                    <option value="">Todas situações</option>
                    <option value="1" <?= $situacao === '1' ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $situacao === '0' ? 'selected' : '' ?>>Desativado</option>
                  </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                  <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Aluno</th>
                      <th>Turma</th>
                      <th>Data da matrícula</th>
                      <th>Dias de frequência</th>
                      <th>Situação</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($matriculas)): ?>
                      <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma matrícula encontrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($matriculas as $mat): ?>
                        <?php
                          $dias = [];
                          if (!empty($mat->segunda)) $dias[] = 'Seg';
                          if (!empty($mat->terca))   $dias[] = 'Ter';
                          if (!empty($mat->quarta))  $dias[] = 'Qua';
                          if (!empty($mat->quinta))  $dias[] = 'Qui';
                          if (!empty($mat->sexta))   $dias[] = 'Sex';
                        ?>
                        <tr>
                          <td><b><?= htmlspecialchars($mat->aluno_nome) ?></b></td>
                          <td><?= htmlspecialchars($mat->curso_nome ? $mat->curso_nome . ' (Raia ' . $mat->local_aula . ')' : 'Turma desvinculada') ?></td>
                          <td><?= date('d/m/Y', strtotime($mat->data_matricula)) ?></td>
                          <td><?= !empty($dias) ? implode(', ', $dias) : 'Nenhum' ?></td>
                          <td>
                            <span class="badge rounded-pill <?= (int) $mat->status_matricula === 1 ? 'b-ok' : 'b-er' ?>">
                              <?= (int) $mat->status_matricula === 1 ? 'Ativo' : 'Desativado' ?>
                            </span>
                          </td>
                          <td class="text-nowrap">
                            <a href="aluno_detalhe.php?id=<?= $mat->id_matricula ?>" class="text-primary me-2" title="Ver perfil"><i class="bi bi-eye"></i></a>
                            <?php if ($isGestor): ?>
                              <a href="matricula_form.php?id=<?= $mat->id_matricula ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil"></i></a>
                              <a href="../index.php?controle=MatriculaController&metodo=excluir&id=<?= $mat->id_matricula ?>" class="text-danger" title="Apagar" onclick="return confirm('Deseja remover esta matrícula?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Mostrando <?= count($matriculas) ?> matrícula(s)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>