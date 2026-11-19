<?php
/**
 * View: professores — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $busca, $formacao, $mostrarF, $professores
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
    <title>Professores · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex  min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Professores</h1>
              <div class="text-muted small">Pessoas › Professores</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="index.php?controle=ProfessorController&metodo=formulario" class="btn btn-primary">+ Novo professor</a>
            </div>
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
                  <input type="hidden" name="controle" value="ProfessorController">
                  <input type="hidden" name="metodo" value="listar">
                <div class="col-12 col-md-5">
                  <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Buscar por nome, CREF ou especialidade">
                </div>
                <div class="col-12 col-md-3">
                  <select name="formacao" class="form-select">
                    <option value="">Todas formações</option>
                    <option value="Educação Física" <?= $formacao === 'Educação Física' ? 'selected' : '' ?>>Educação Física</option>
                    <option value="Tecnólogo em Natação" <?= $formacao === 'Tecnólogo em Natação' ? 'selected' : '' ?>>Tecnólogo em Natação</option>
                  </select>
                </div>
                <div class="col-12 col-md-2">
                  <select name="mostrar" class="form-select">
                    <option value="">Site (todos)</option>
                    <option value="1" <?= $mostrarF === '1' ? 'selected' : '' ?>>Visível no site</option>
                    <option value="0" <?= $mostrarF === '0' ? 'selected' : '' ?>>Oculto no site</option>
                  </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                  <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Nome</th>
                      <th>CREF</th>
                      <th>Formação</th>
                      <th>Especialidade</th>
                      <th>Telefone</th>
                      <th>Exibir no site</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($professores)): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">Nenhum professor encontrado.</td></tr>
                    <?php else: ?>
                      <?php foreach ($professores as $prof): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($prof->nome) ?></b></td>
                          <td><?= htmlspecialchars($prof->creef ?: '—') ?></td>
                          <td><?= htmlspecialchars($prof->formacao) ?></td>
                          <td><?= htmlspecialchars($prof->especialidade ?: '—') ?></td>
                          <td><?= htmlspecialchars($prof->telefone ?? '—') ?></td>
                          <td>
                            <a href="index.php?controle=ProfessorController&metodo=alternarSite&id=<?= $prof->id_usuario ?>" 
                               class="text-decoration-none" 
                               title="Clique para alternar visibilidade no site">
                              <div class="form-check form-switch d-inline-block m-0">
                                <input class="form-check-input" type="checkbox" <?= (int) $prof->mostrar === 1 ? 'checked' : '' ?> style="pointer-events:none;">
                              </div>
                            </a>
                          </td>
                          <td class="text-nowrap">
                            <a href="index.php?controle=ProfessorController&metodo=detalhe&id=<?= $prof->id_usuario ?>" class="text-primary me-2" title="Visualizar">
                              <i class="bi bi-eye"></i>
                            </a>
                            <a href="index.php?controle=ProfessorController&metodo=formulario&id=<?= $prof->id_usuario ?>" class="text-primary me-2" title="Editar">
                              <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?controle=ProfessorController&metodo=excluir&id=<?= $prof->id_usuario ?>" 
                               class="text-danger" 
                               title="Apagar" 
                               onclick="return confirm('Tem certeza que deseja remover o(a) professor(a) <?= htmlspecialchars(addslashes($prof->nome)) ?>?')">
                              <i class="bi bi-trash"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Mostrando <?= count($professores) ?> professor(es)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
