<?php
/**
 * View: turmas — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $busca, $cursoF, $cursosFiltro, $isGestor, $profF, $profFiltro, $turmas
 */
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="Views/assets/style.css" rel="stylesheet">
    <title>Turmas · Academia do Bill</title>
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
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0"><?= $isGestor ? 'Turmas' : 'Minhas turmas' ?></h1>
              <div class="text-muted small">Turmas e aulas › Turmas</div>
            </div>
            <?php if ($isGestor): ?>
              <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?controle=TurmaController&metodo=formulario" class="btn btn-primary">+ Nova turma</a>
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
                  <input type="hidden" name="controle" value="TurmaController">
                  <input type="hidden" name="metodo" value="listar">
                <div class="col-12 col-md-<?= $isGestor ? '5' : '7' ?>">
                  <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Buscar por curso ou professor">
                </div>
                <div class="col-12 col-md-3">
                  <select name="curso" class="form-select">
                    <option value="0">Todos os cursos</option>
                    <?php foreach ($cursosFiltro as $cur): ?>
                      <option value="<?= $cur->id_curso ?>" <?= $cursoF === (int) $cur->id_curso ? 'selected' : '' ?>><?= htmlspecialchars($cur->nome) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if ($isGestor): ?>
                  <div class="col-12 col-md-2">
                    <select name="professor" class="form-select">
                      <option value="0">Professor</option>
                      <?php foreach ($profFiltro as $pf): ?>
                        <option value="<?= $pf->usuario_id ?>" <?= $profF === (int) $pf->usuario_id ? 'selected' : '' ?>><?= htmlspecialchars($pf->nome) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                <?php endif; ?>
                <div class="col-12 col-md-2 d-grid">
                  <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Curso</th>
                      <th>Horário</th>
                      <th>Local</th>
                      <th>Professor</th>
                      <th>Estagiário</th>
                      <th>Alunos ativos</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($turmas)): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma turma encontrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($turmas as $t): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($t->curso_nome) ?></b></td>
                          <td><?= substr((string) $t->horario_inicio, 0, 5) ?>–<?= substr((string) $t->horario_termino, 0, 5) ?></td>
                          <td>Raia <?= (int) $t->local_aula ?></td>
                          <td><?= htmlspecialchars($t->professor_nome) ?></td>
                          <td><?= htmlspecialchars($t->estagiario_nome ?? '—') ?></td>
                          <td><span class="badge rounded-pill b-in"><?= (int) $t->total_alunos ?></span></td>
                          <td class="text-nowrap">
                            <a href="index.php?controle=TurmaController&metodo=detalhe&id=<?= $t->id_turma ?>" class="text-primary me-2" title="Visualizar"><i class="bi bi-eye"></i></a>
                            <?php if ($isGestor): ?>
                              <a href="index.php?controle=TurmaController&metodo=formulario&id=<?= $t->id_turma ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil"></i></a>
                              <a href="index.php?controle=TurmaController&metodo=excluir&id=<?= $t->id_turma ?>" class="text-danger" title="Apagar" onclick="return confirm('Tem certeza que deseja apagar esta turma?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Mostrando <?= count($turmas) ?> turma(s)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
