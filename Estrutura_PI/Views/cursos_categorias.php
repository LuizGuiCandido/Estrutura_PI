<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor(); // Módulo restrito a gestores

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

// Busca Cursos com contagem de turmas vinculadas
$cursos = $db->query("
    SELECT c.id_curso, c.nome, COUNT(t.id_turma) AS total_turmas
    FROM cursos c
    LEFT JOIN turmas t ON t.curso_id = c.id_curso AND t.deletado_em IS NULL
    WHERE c.deletado_em IS NULL
    GROUP BY c.id_curso, c.nome
    ORDER BY c.nome ASC
")->fetchAll(PDO::FETCH_OBJ);

// Busca Categorias (sem depender da coluna t.categoria_id na tabela turmas)
$categorias = $db->query("
    SELECT cat.id_categoria, cat.nome, 0 AS total_turmas
    FROM categorias cat
    WHERE cat.deletado_em IS NULL
    ORDER BY cat.nome ASC
")->fetchAll(PDO::FETCH_OBJ);
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
    <title>Cursos e categorias · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0">Cursos e categorias</h1>
              <div class="text-muted small">Configurações › Cursos e categorias</div>
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
              <button class="nav-link active" id="tab-cursos" data-bs-toggle="pill" data-bs-target="#aba-cursos" type="button" role="tab">Cursos</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-categorias" data-bs-toggle="pill" data-bs-target="#aba-categorias" type="button" role="tab">Categorias</button>
            </li>
          </ul>

          <div class="tab-content">
            <!-- ABA 1: CURSOS -->
            <div class="tab-pane fade show active" id="aba-cursos" role="tabpanel">
              <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-end">
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCurso" data-id="" data-nome="">+ Novo curso</button>
                </div>
                <div class="card">
                  <div class="card-body d-flex flex-column gap-3">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                          <tr>
                            <th>Curso</th>
                            <th>Turmas vinculadas</th>
                            <th>Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($cursos)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Nenhum curso cadastrado.</td></tr>
                          <?php else: ?>
                            <?php foreach ($cursos as $cur): ?>
                              <tr>
                                <td><b><?= htmlspecialchars($cur->nome) ?></b></td>
                                <td><?= (int) $cur->total_turmas ?></td>
                                <td class="text-nowrap">
                                  <a href="#" class="text-primary me-2" data-bs-toggle="modal" data-bs-target="#modalCurso" data-id="<?= $cur->id_curso ?>" data-nome="<?= htmlspecialchars($cur->nome, ENT_QUOTES) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                                  <a href="../index.php?controle=CursoController&metodo=excluir&id=<?= $cur->id_curso ?>" class="text-danger" title="Apagar" onclick="return confirm('Deseja excluir este curso?')"><i class="bi bi-trash"></i></a>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
                      <span><?= count($cursos) ?> curso(s) cadastrado(s)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ABA 2: CATEGORIAS -->
            <div class="tab-pane fade" id="aba-categorias" role="tabpanel">
              <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-end">
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria" data-id="" data-nome="">+ Nova categoria</button>
                </div>
                <div class="card">
                  <div class="card-body d-flex flex-column gap-3">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                          <tr>
                            <th>Categoria</th>
                            <th>Turmas vinculadas</th>
                            <th>Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (empty($categorias)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>
                          <?php else: ?>
                            <?php foreach ($categorias as $cat): ?>
                              <tr>
                                <td><b><?= htmlspecialchars($cat->nome) ?></b></td>
                                <td class="text-nowrap"><?= (int) $cat->total_turmas ?></td>
                                <td class="text-nowrap">
                                  <a href="#" class="text-primary me-2" data-bs-toggle="modal" data-bs-target="#modalCategoria" data-id="<?= $cat->id_categoria ?>" data-nome="<?= htmlspecialchars($cat->nome, ENT_QUOTES) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                                  <a href="../index.php?controle=CategoriaController&metodo=excluir&id=<?= $cat->id_categoria ?>" class="text-danger" title="Apagar" onclick="return confirm('Deseja excluir esta categoria?')"><i class="bi bi-trash"></i></a>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
                      <span><?= count($categorias) ?> categoria(s) cadastrada(s)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- MODAL CURSO -->
        <div class="modal fade" id="modalCurso" tabindex="-1" aria-labelledby="modalCursoTitulo" aria-hidden="true" data-novo="Novo curso" data-editar="Editar curso">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="../index.php?controle=CursoController&metodo=salvar" method="POST">
              <input type="hidden" name="id_curso" id="modalCursoId" value="">
              <div class="modal-header">
                <h2 class="modal-title h5 fw-bold" id="modalCursoTitulo">Novo curso</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>
              <div class="modal-body">
                <label class="form-label" for="modalCursoNome">Nome do curso</label>
                <input type="text" class="form-control" id="modalCursoNome" name="nome" value="" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar curso</button>
              </div>
            </form>
          </div>
        </div>

        <!-- MODAL CATEGORIA -->
        <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaTitulo" aria-hidden="true" data-novo="Nova categoria" data-editar="Editar categoria">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="../index.php?controle=CategoriaController&metodo=salvar" method="POST">
              <input type="hidden" name="id_categoria" id="modalCategoriaId" value="">
              <div class="modal-header">
                <h2 class="modal-title h5 fw-bold" id="modalCategoriaTitulo">Nova categoria</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>
              <div class="modal-body">
                <label class="form-label" for="modalCategoriaNome">Nome da categoria</label>
                <input type="text" class="form-control" id="modalCategoriaNome" name="nome" value="" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar categoria</button>
              </div>
            </form>
          </div>
        </div>

      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      if (location.hash === '#categorias') bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-categorias')).show();
      
      // Preenchimento dinâmico dos modais para Cadastro e Edição
      document.querySelectorAll('.modal').forEach(function (m) {
        m.addEventListener('show.bs.modal', function (e) {
          var btn = e.relatedTarget;
          var id = btn ? btn.dataset.id : '';
          var nome = btn ? btn.dataset.nome : '';
          
          m.querySelector('input[type="hidden"]').value = id;
          m.querySelector('input[type="text"]').value = nome;
          m.querySelector('.modal-title').textContent = id ? m.dataset.editar : m.dataset.novo;
        });
      });
    </script>
  </body>
</html>