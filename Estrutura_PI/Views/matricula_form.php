<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$alunoPreSelecionado = (int) ($_GET['aluno_id'] ?? 0);
$turmaPreSelecionada = (int) ($_GET['turma_id'] ?? 0);

$matEdit = null;
if ($idEditar > 0) {
    $stm = $db->prepare("SELECT * FROM matriculas WHERE id_matricula = ? AND deletado_em IS NULL");
    $stm->execute([$idEditar]);
    $matEdit = $stm->fetch(PDO::FETCH_OBJ);
    if (!$matEdit) {
        header('Location: matriculas.php');
        exit;
    }
}

// Carrega alunos e turmas
$alunos = $db->query("
    SELECT u.id_usuario, u.nome, u.cpf 
    FROM alunos a 
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id 
    WHERE a.deletado_em IS NULL AND u.deletado_em IS NULL AND u.gestor = 0 
    ORDER BY u.nome ASC
")->fetchAll(PDO::FETCH_OBJ);

$turmas = $db->query("
    SELECT t.id_turma, c.nome AS curso_nome, t.horario_inicio, t.horario_termino, t.local_aula 
    FROM turmas t 
    INNER JOIN cursos c ON c.id_curso = t.curso_id 
    WHERE t.deletado_em IS NULL 
    ORDER BY c.nome ASC
")->fetchAll(PDO::FETCH_OBJ);

$isEdicao = ($matEdit !== null);
$actionUrl = $isEdicao 
    ? '../index.php?controle=MatriculaController&metodo=editar' 
    : '../index.php?controle=MatriculaController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar matrícula' : 'Nova matrícula' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar matrícula' : 'Nova matrícula' ?></h1>
              <div class="text-muted small">Turmas e aulas › Matrículas › <?= $isEdicao ? 'Editar' : 'Nova matrícula' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_matricula" value="<?= $matEdit->id_matricula ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="form-label" for="c49">Aluno</label>
                  <select class="form-select" id="c49" name="aluno_id" required>
                    <option value="" disabled <?= (!$isEdicao && !$alunoPreSelecionado) ? 'selected' : '' ?>>Selecione o aluno</option>
                    <?php foreach ($alunos as $al): ?>
                      <?php 
                        $sel = ($isEdicao && (int)$matEdit->aluno_id === (int)$al->id_usuario) || (!$isEdicao && $alunoPreSelecionado === (int)$al->id_usuario);
                      ?>
                      <option value="<?= $al->id_usuario ?>" <?= $sel ? 'selected' : '' ?>><?= htmlspecialchars($al->nome . ' (CPF: ' . $al->cpf . ')') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c50">Data da matrícula</label>
                  <input type="date" class="form-control" id="c50" name="data_matricula" value="<?= htmlspecialchars($matEdit->data_matricula ?? date('Y-m-d')) ?>" required>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="form-label" for="c51">Turma</label>
                  <select class="form-select" id="c51" name="turma_id" required>
                    <option value="" disabled <?= (!$isEdicao && !$turmaPreSelecionada) ? 'selected' : '' ?>>Selecione a turma</option>
                    <?php foreach ($turmas as $t): ?>
                      <?php 
                        $sel = ($isEdicao && (int)$matEdit->turma_id === (int)$t->id_turma) || (!$isEdicao && $turmaPreSelecionada === (int)$t->id_turma);
                      ?>
                      <option value="<?= $t->id_turma ?>" <?= $sel ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t->curso_nome . ' (' . substr($t->horario_inicio, 0, 5) . ' · Raia ' . $t->local_aula . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c52">Situação</label>
                  <select class="form-select" id="c52" name="status_matricula">
                    <option value="1" <?= (!$isEdicao || (int)$matEdit->status_matricula === 1) ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= ($isEdicao && (int)$matEdit->status_matricula === 0) ? 'selected' : '' ?>>Desativado</option>
                  </select>
                </div>
              </div>

              <span class="lb mb-0">Dias de frequência (Check-box)</span>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="segunda" id="d_seg" value="1" <?= ($isEdicao && (int)$matEdit->segunda === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="d_seg">Segunda</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="terca" id="d_ter" value="1" <?= ($isEdicao && (int)$matEdit->terca === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="d_ter">Terça</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="quarta" id="d_qua" value="1" <?= ($isEdicao && (int)$matEdit->quarta === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="d_qua">Quarta</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="quinta" id="d_qui" value="1" <?= ($isEdicao && (int)$matEdit->quinta === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="d_qui">Quinta</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="sexta" id="d_sex" value="1" <?= ($isEdicao && (int)$matEdit->sexta === 1) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="d_sex">Sexta</label>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="matriculas.php" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar matrícula' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>