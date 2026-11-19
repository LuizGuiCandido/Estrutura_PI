<?php
/**
 * View: pagamentos — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $atrasados, $busca, $pagamentos, $qtdAtrasos, $situacao, $totalEmAberto, $totalFiltrado
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
    <title>Pagamentos · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Pagamentos</h1>
              <div class="text-muted small">Administrativo › Pagamentos</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="index.php?controle=PagamentoController&metodo=formulario" class="btn btn-primary">+ Registrar pagamento</a>
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

          <ul class="nav nav-pills gap-1" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-todos" data-bs-toggle="pill" data-bs-target="#aba-todos" type="button" role="tab">Todos os pagamentos</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-atraso" data-bs-toggle="pill" data-bs-target="#aba-atraso" type="button" role="tab">
                Em atraso <span class="badge rounded-pill b-er ms-1"><?= $qtdAtrasos ?></span>
              </button>
            </li>
          </ul>

          <div class="tab-content">
            <!-- ABA 1: TODOS OS PAGAMENTOS -->
            <div class="tab-pane fade show active" id="aba-todos" role="tabpanel">
              <div class="card">
                <div class="card-body d-flex flex-column gap-3">
                  <form method="GET" class="row g-2 mb-1">
                  <input type="hidden" name="controle" value="PagamentoController">
                  <input type="hidden" name="metodo" value="listar">
                    <div class="col-12 col-md-8">
                      <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Filtrar por aluno">
                    </div>
                    <div class="col-12 col-md-3">
                      <select name="situacao" class="form-select">
                        <option value="">Todas situações</option>
                        <option value="Pago" <?= $situacao === 'Pago' ? 'selected' : '' ?>>Pago</option>
                        <option value="Em atraso" <?= $situacao === 'Em atraso' ? 'selected' : '' ?>>Agendado / Futuro</option>
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
                          <th>Plano</th>
                          <th>Data</th>
                          <th>Meio de pagamento</th>
                          <th>Valor</th>
                          <th>Situação</th>
                          <th>Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($pagamentos)): ?>
                          <tr><td colspan="8" class="text-center text-muted py-4">Nenhum pagamento encontrado.</td></tr>
                        <?php else: ?>
                          <?php foreach ($pagamentos as $pag): ?>
                            <tr>
                              <td><b><?= htmlspecialchars($pag->aluno_nome) ?></b></td>
                              <td><?= htmlspecialchars($pag->curso_nome ? $pag->curso_nome . ' (Raia ' . $pag->local_aula . ')' : '—') ?></td>
                              <td><?= htmlspecialchars($pag->plano_nome ?? '—') ?></td>
                              <td><?= date('d/m/Y', strtotime($pag->data_pagamento)) ?></td>
                              <td><?= htmlspecialchars($pag->meio_pagamento ?? '—') ?></td>
                              <td>R$ <?= number_format((float) ($pag->valor_total ?? 0), 2, ',', '.') ?></td>
                              <td>
                                <span class="badge rounded-pill <?= $pag->situacao_calc === 'Pago' ? 'b-ok' : 'b-wa' ?>">
                                  <?= htmlspecialchars($pag->situacao_calc) ?>
                                </span>
                              </td>
                              <td class="text-nowrap">
                                <a href="index.php?controle=PagamentoController&metodo=formulario&id=<?= $pag->id_pagamento ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?controle=PagamentoController&metodo=excluir&id=<?= $pag->id_pagamento ?>" class="text-danger" title="Apagar" onclick="return confirm('Deseja excluir este registro de pagamento?')"><i class="bi bi-trash"></i></a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
                    <span>Mostrando <?= count($pagamentos) ?> registro(s) · Total filtrado: <b>R$ <?= number_format($totalFiltrado, 2, ',', '.') ?></b></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- ABA 2: EM ATRASO -->
            <div class="tab-pane fade" id="aba-atraso" role="tabpanel">
              <div class="card">
                <div class="card-body d-flex flex-column gap-3">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>Aluno</th>
                          <th>Turma</th>
                          <th>Plano</th>
                          <th>Vencimento</th>
                          <th>Valor</th>
                          <th>Atraso</th>
                          <th>Telefone</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($atrasados)): ?>
                          <tr><td colspan="7" class="text-center text-muted py-4">Nenhum pagamento em atraso!</td></tr>
                        <?php else: ?>
                          <?php foreach ($atrasados as $atr): ?>
                            <tr>
                              <td><b><?= htmlspecialchars($atr->aluno_nome) ?></b></td>
                              <td><?= htmlspecialchars($atr->curso_nome ? $atr->curso_nome . ' (Raia ' . $atr->local_aula . ')' : '—') ?></td>
                              <td><?= htmlspecialchars($atr->plano_nome ?? '—') ?></td>
                              <td><?= date('d/m/Y', strtotime($atr->vencimento)) ?></td>
                              <td>R$ <?= number_format((float) ($atr->valor_devido ?? 0), 2, ',', '.') ?></td>
                              <td><span class="badge rounded-pill b-er"><?= (int) $atr->dias_atraso ?> dias</span></td>
                              <td><a href="tel:<?= htmlspecialchars($atr->telefone) ?>" class="text-decoration-none"><?= htmlspecialchars($atr->telefone) ?></a></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
                    <span><?= $qtdAtrasos ?> inadimplente(s) · Valor em aberto: <b>R$ <?= number_format($totalEmAberto, 2, ',', '.') ?></b></span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      if (location.hash === '#atrasados') bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-atraso')).show();
    </script>
  </body>
</html>
