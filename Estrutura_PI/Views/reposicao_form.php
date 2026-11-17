<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$repEdit = null;

if ($idEditar > 0) {
    $stm = $db->prepare("SELECT * FROM reposicao WHERE id_reposicao = ? AND deletado_em IS NULL");
    $stm->execute([$idEditar]);
    $repEdit = $stm->fetch(PDO::FETCH_OBJ);
    if (!$repEdit) {
        header('Location: reposicoes.php');
        exit;
    }
}

$matriculas = $db->query("
    SELECT m.id_matricula, u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula 
    FROM matriculas m
    INNER JOIN alunos a ON a.usuario_id = m.aluno_id
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
    LEFT JOIN turmas t ON t.id_turma = m.turma_id
    LEFT JOIN cursos c ON c.id_curso = t.curso_id
    WHERE m.status_matricula = 1 AND m.deletado_em IS NULL AND u.deletado_em IS NULL
    ORDER BY u.nome ASC
")->fetchAll(PDO::FETCH_OBJ);

$isEdicao = ($repEdit !== null);
$actionUrl = $isEdicao 
    ? '../index.php?controle=ReposicaoController&metodo=editar' 
    : '../index.php?controle=ReposicaoController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar reposição' : 'Nova reposição' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar reposição' : 'Nova reposição' ?></h1>
              <div class="text-muted small">Turmas e aulas › Reposições › <?= $isEdicao ? 'Editar' : 'Nova reposição' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_reposicao" value="<?= $repEdit->id_reposicao ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c54">Matrícula (Aluno e Turma)</label>
                  <select class="form-select" id="c54" name="matricula_id" required>
                    <option value="" disabled <?= !$isEdicao ? 'selected' : '' ?>>Selecione aluno e turma</option>
                    <?php foreach ($matriculas as $mat): ?>
                      <option value="<?= $mat->id_matricula ?>" <?= ($isEdicao && (int)$repEdit->matricula_id === (int)$mat->id_matricula) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mat->aluno_nome . ' — ' . $mat->curso_nome . ' (Raia ' . $mat->local_aula . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c55">Data</label>
                  <input type="date" class="form-control" id="c55" name="data_reposicao" value="<?= htmlspecialchars($repEdit->data_reposicao ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c56">Horário</label>
                  <input type="time" class="form-control" id="c56" name="horario" value="<?= htmlspecialchars($repEdit->horario ?? '') ?>" required>
                </div>
              </div>

              <span class="lb mb-0">Situação</span>
              <div class="d-flex flex-wrap gap-3">
                <?php $sitAtual = $repEdit->status_reposicao ?? 'marcada'; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_marcada" value="marcada" <?= $sitAtual === 'marcada' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_marcada">Agendada (Marcada)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_concluida" value="concluída" <?= $sitAtual === 'concluída' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_concluida">Concluída</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_nao" value="não realizada" <?= $sitAtual === 'não realizada' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_nao">Não realizada</label>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c58">Observações</label>
                  <input type="text" class="form-control" id="c58" name="observacao" value="<?= htmlspecialchars($repEdit->observacao ?? '') ?>">
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="reposicoes.php" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar reposição' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html><?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$repEdit = null;

if ($idEditar > 0) {
    $stm = $db->prepare("SELECT * FROM reposicao WHERE id_reposicao = ? AND deletado_em IS NULL");
    $stm->execute([$idEditar]);
    $repEdit = $stm->fetch(PDO::FETCH_OBJ);
    if (!$repEdit) {
        header('Location: reposicoes.php');
        exit;
    }
}

$matriculas = $db->query("
    SELECT m.id_matricula, u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula 
    FROM matriculas m
    INNER JOIN alunos a ON a.usuario_id = m.aluno_id
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
    LEFT JOIN turmas t ON t.id_turma = m.turma_id
    LEFT JOIN cursos c ON c.id_curso = t.curso_id
    WHERE m.status_matricula = 1 AND m.deletado_em IS NULL AND u.deletado_em IS NULL
    ORDER BY u.nome ASC
")->fetchAll(PDO::FETCH_OBJ);

$isEdicao = ($repEdit !== null);
$actionUrl = $isEdicao 
    ? '../index.php?controle=ReposicaoController&metodo=editar' 
    : '../index.php?controle=ReposicaoController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar reposição' : 'Nova reposição' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar reposição' : 'Nova reposição' ?></h1>
              <div class="text-muted small">Turmas e aulas › Reposições › <?= $isEdicao ? 'Editar' : 'Nova reposição' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_reposicao" value="<?= $repEdit->id_reposicao ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c54">Matrícula (Aluno e Turma)</label>
                  <select class="form-select" id="c54" name="matricula_id" required>
                    <option value="" disabled <?= !$isEdicao ? 'selected' : '' ?>>Selecione aluno e turma</option>
                    <?php foreach ($matriculas as $mat): ?>
                      <option value="<?= $mat->id_matricula ?>" <?= ($isEdicao && (int)$repEdit->matricula_id === (int)$mat->id_matricula) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mat->aluno_nome . ' — ' . $mat->curso_nome . ' (Raia ' . $mat->local_aula . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c55">Data</label>
                  <input type="date" class="form-control" id="c55" name="data_reposicao" value="<?= htmlspecialchars($repEdit->data_reposicao ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c56">Horário</label>
                  <input type="time" class="form-control" id="c56" name="horario" value="<?= htmlspecialchars($repEdit->horario ?? '') ?>" required>
                </div>
              </div>

              <span class="lb mb-0">Situação</span>
              <div class="d-flex flex-wrap gap-3">
                <?php $sitAtual = $repEdit->status_reposicao ?? 'marcada'; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_marcada" value="marcada" <?= $sitAtual === 'marcada' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_marcada">Agendada (Marcada)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_concluida" value="concluída" <?= $sitAtual === 'concluída' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_concluida">Concluída</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status_reposicao" id="sit_nao" value="não realizada" <?= $sitAtual === 'não realizada' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="sit_nao">Não realizada</label>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c58">Observações</label>
                  <input type="text" class="form-control" id="c58" name="observacao" value="<?= htmlspecialchars($repEdit->observacao ?? '') ?>">
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="reposicoes.php" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar reposição' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>