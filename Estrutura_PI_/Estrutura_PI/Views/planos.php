<?php
/**
 * View: planos — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $planos
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planos – Academia do Bill</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="Views/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar/sidebar.php'; ?>

  <div class="flex-grow-1 min-vh-100 d-flex flex-column">
    <!-- Topbar mobile -->
    <header class="topbar d-lg-none p-3 d-flex align-items-center justify-content-between text-white">
      <div class="d-flex align-items-center gap-2">
        <img src="Views/assets/img/logo.png" alt="Logo">
        <span class="fw-semibold">Academia do Bill</span>
      </div>
      <button class="btn btn-outline-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu">
        <i class="bi bi-list fs-5"></i>
      </button>
    </header>

    <main class="container-fluid p-4 flex-grow-1">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
          <h3 class="mb-1 fw-bold" style="color:#0405C0">Planos</h3>
          <p class="text-muted mb-0">Gerenciamento de períodos, valores por aula e meios de pagamento</p>
        </div>
        <a href="index.php?controle=PlanoController&metodo=formulario" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i> Novo Plano
        </a>
      </div>

      <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php
            echo match ($_GET['sucesso']) {
                '1' => 'Plano cadastrado com sucesso!',
                '2' => 'Plano atualizado com sucesso!',
                '3' => 'Plano excluído com sucesso!',
                default => 'Operação realizada com sucesso!'
            };
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
      <?php endif; ?>

      <div class="card p-4">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Período</th>
                <th>Valor da Aula</th>
                <th>Meio de Pagamento</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($planos)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    Nenhum plano cadastrado até o momento.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($planos as $p): ?>
                  <tr>
                    <td><?= (int)$p->id_plano ?></td>
                    <td class="fw-medium">
                      <?= htmlspecialchars(ucfirst((string)($p->periodo ?? 'Avulso'))) ?>
                    </td>
                    <td>
                      R$ <?= number_format((float)$p->valor_aula, 2, ',', '.') ?>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border">
                        <?= htmlspecialchars(ucfirst((string)$p->meio_pagamento)) ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <a href="index.php?controle=PlanoController&metodo=formulario&id=<?= (int)$p->id_plano ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <a href="index.php?controle=PlanoController&metodo=excluir&id=<?= (int)$p->id_plano ?>"
                         class="btn btn-sm btn-outline-danger"
                         title="Excluir"
                         onclick="return confirm('Deseja realmente excluir este plano?');">
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
