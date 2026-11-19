<?php
/**
 * View: alunos — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $alunos, $busca, $isGestor, $situacao, $turmaF, $turmasFiltro
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
    <title>Alunos · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0">Alunos</h1>
              <div class="text-muted small">Pessoas › Alunos</div>
            </div>
            <?php if ($isGestor): ?>
              <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?controle=AlunoController&metodo=formulario" class="btn btn-primary">+ Novo aluno</a>
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
                  <input type="hidden" name="controle" value="AlunoController">
                  <input type="hidden" name="metodo" value="listar">
                <div class="col-12 col-md-5">
                  <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" class="form-control" placeholder="Buscar por nome, CPF ou e-mail">
                </div>
                <div class="col-12 col-md-3">
                  <select name="turma" class="form-select">
                    <option value="0">Todas as turmas</option>
                    <?php foreach ($turmasFiltro as $tf): ?>
                      <option value="<?= $tf->id_turma ?>" <?= $turmaF === (int) $tf->id_turma ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tf->curso_nome . ' (' . substr((string) $tf->horario_inicio, 0, 5) . '–' . substr((string) $tf->horario_termino, 0, 5) . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-2">
                  <select name="situacao" class="form-select">
                    <option value="">Todas situações</option>
                    <option value="ativo" <?= $situacao === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $situacao === 'inativo' ? 'selected' : '' ?>>Inativo</option>
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
                      <th>CPF</th>
                      <th>E-mail</th>
                      <th>Telefone</th>
                      <th>Nascimento</th>
                      <th>Turma</th>
                      <th>Situação</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($alunos)): ?>
                      <tr><td colspan="8" class="text-center text-muted py-4">Nenhum aluno encontrado.</td></tr>
                    <?php else: ?>
                      <?php foreach ($alunos as $al): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($al->nome) ?></b></td>
                          <td><?= htmlspecialchars($al->cpf) ?></td>
                          <td><?= htmlspecialchars($al->email) ?></td>
                          <td><?= htmlspecialchars($al->telefone ?? '-') ?></td>
                          <td><?= $al->data_nasc ? date('d/m/Y', strtotime($al->data_nasc)) : '-' ?></td>
                          <td><?= htmlspecialchars($al->turma_atual ?? 'Sem turma') ?></td>
                          <td>
                            <?php if ((int) $al->qtd_ativas > 0): ?>
                              <span class="badge rounded-pill b-ok">Ativo</span>
                            <?php else: ?>
                              <span class="badge rounded-pill b-wa">Inativo</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-nowrap">
                            <!-- 1. VISUALIZAR -->
                            <a href="index.php?controle=AlunoController&metodo=detalhe&id=<?= $al->id_usuario ?>" class="text-primary me-2" title="Visualizar perfil">
                              <i class="bi bi-eye"></i>
                            </a>

                            <?php if ($isGestor): ?>
                              <!-- 2. EDITAR -->
                              <a href="index.php?controle=AlunoController&metodo=formulario&id=<?= $al->id_usuario ?>" class="text-primary me-2" title="Editar aluno">
                                <i class="bi bi-pencil"></i>
                              </a>

                              <!-- 3. APAGAR (Soft Delete via index.php) -->
                              <a href="index.php?controle=AlunoController&metodo=excluir&id=<?= $al->id_usuario ?>" 
                                 class="text-danger" 
                                 title="Apagar aluno" 
                                 onclick="return confirm('Tem certeza que deseja apagar o cadastro de <?= htmlspecialchars(addslashes($al->nome)) ?>?')">
                                <i class="bi bi-trash"></i>
                              </a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Mostrando <?= count($alunos) ?> aluno(s)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
