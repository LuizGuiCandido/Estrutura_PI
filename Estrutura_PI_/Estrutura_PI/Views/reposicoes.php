<?php
/**
 * View: reposicoes — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $busca, $isGestor, $rep, $reposicoes, $situacao
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
    <title>Reposições · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0">Reposições</h1>
              <div class="text-muted small">Turmas e aulas › Reposições</div>
            </div>
            <?php if ($isGestor): ?>
              <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?controle=ReposicaoController&metodo=formulario" class="btn btn-primary">+ Nova reposição</a>
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
                  <input type="hidden" name="controle" value="ReposicaoController">
                  <input type="hidden" name="metodo" value="listar">
                <div class="col-12 col-md-7">
                  <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Buscar por aluno">
                </div>
                <div class="col-12 col-md-4">
                  <select name="situacao" class="form-select">
                    <option value="">Todas situações</option>
                    <option value="marcada" <?= $situacao === 'marcada' ? 'selected' : '' ?>>Marcada / Agendada</option>
                    <option value="concluída" <?= $situacao === 'concluída' ? 'selected' : '' ?>>Concluída</option>
                    <option value="não realizada" <?= $situacao === 'não realizada' ? 'selected' : '' ?>>Não realizada</option>
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
                      <th>Data</th>
                      <th>Horário</th>
                      <th>Situação</th>
                      <th>Observações</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($reposicoes)): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma reposição encontrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($reposicoes as$rep): ?>
                        <?php
                          $badgeClass = match($rep->status_reposicao) {
                              'marcada' => 'b-wa',
                              'concluída' => 'b-ok',
                              default => 'b-er'
                          };
                          $labelStatus = match($rep->status_reposicao) {
                              'marcada' => 'Agendada',
                              'concluída' => 'Concluída',
                              default => 'Não realizada'
                          };
                        ?>
                        <tr>
                          <td><b><?= htmlspecialchars($rep->aluno_nome) ?></b></td>
                          <td><?= htmlspecialchars($rep->curso_nome ? $rep->curso_nome . ' (Raia ' .$rep->local_aula . ')' : '—') ?></td>
                          <td><?= date('d/m/Y', strtotime($rep->data_reposicao)) ?></td>
                          <td><?= substr((string) $rep->horario, 0, 5) ?></td>
                          <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $labelStatus ?></span></td>
                          <td><?= htmlspecialchars($rep->observacao !== '' ?$rep->observacao : '—') ?></td>
                          <td class="text-nowrap">
                            <?php if ($isGestor): ?>
                              <a href="index.php?controle=ReposicaoController&metodo=formulario&id=<?= $rep->id_reposicao ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil"></i></a>
                              <a href="index.php?controle=ReposicaoController&metodo=excluir&id=<?= $rep->id_reposicao ?>" class="text-danger" title="Apagar" onclick="return confirm('Deseja remover esta reposição?')"><i class="bi bi-trash"></i></a>
                            <?php else: ?>
                              <span class="text-muted small">—</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Mostrando <?= count($reposicoes) ?> reposição(ões)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
