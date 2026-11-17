<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirProfessor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$alunoIdPre = (int) ($_GET['aluno_id'] ?? 0);
$contatoEdit = null;

if ($idEditar > 0) {
    $stm = $db->prepare("SELECT * FROM contatos_emergencia WHERE id_contato = ? AND deletado_em IS NULL");
    $stm->execute([$idEditar]);
    $contatoEdit = $stm->fetch(PDO::FETCH_OBJ);
    if (!$contatoEdit) {
        header('Location: alunos.php');
        exit;
    }
    $alunoIdPre = (int) $contatoEdit->aluno_id;
}

if ($alunoIdPre <= 0) {
    header('Location: alunos.php');
    exit;
}

// Busca dados do aluno vinculado
$stmAluno = $db->prepare("
    SELECT u.id_usuario, u.nome 
    FROM alunos a 
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id 
    WHERE a.usuario_id = ?
");
$stmAluno->execute([$alunoIdPre]);
$alunoVinculado = $stmAluno->fetch(PDO::FETCH_OBJ);

if (!$alunoVinculado) {
    header('Location: alunos.php');
    exit;
}

$isEdicao = ($contatoEdit !== null);
$actionUrl = $isEdicao 
    ? '../index.php?controle=ContatoController&metodo=editar' 
    : '../index.php?controle=ContatoController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar contato' : 'Novo contato de emergência' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar contato' : 'Novo contato de emergência' ?></h1>
              <div class="text-muted small">Pessoas › Alunos › <?= htmlspecialchars($alunoVinculado->nome) ?> › <?= $isEdicao ? 'Editar contato' : 'Novo contato' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <input type="hidden" name="aluno_id" value="<?= $alunoVinculado->id_usuario ?>">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_contato" value="<?= $contatoEdit->id_contato ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c18">Aluno vinculado</label>
                  <input type="text" class="form-control" id="c18" value="<?= htmlspecialchars($alunoVinculado->nome) ?>" readonly>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c19">Nome do contato</label>
                  <input type="text" class="form-control" id="c19" name="nome" value="<?= htmlspecialchars($contatoEdit->nome ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c20">Número</label>
                  <input type="text" class="form-control" id="c20" name="numero" value="<?= htmlspecialchars($contatoEdit->numero ?? '') ?>" required>
                </div>
              </div>

              <span class="lb mb-0">Parentesco</span>
              <div class="d-flex flex-wrap gap-3">
                <?php $parentescoAtual = $contatoEdit->parentesco ?? 'Mãe'; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="parentesco" id="par_guardiao" value="Guardião legal" <?= $parentescoAtual === 'Guardião legal' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="par_guardiao">Guardião legal</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="parentesco" id="par_mae" value="Mãe" <?= $parentescoAtual === 'Mãe' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="par_mae">Mãe</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="parentesco" id="par_pai" value="Pai" <?= $parentescoAtual === 'Pai' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="par_pai">Pai</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="parentesco" id="par_outro" value="Outro" <?= $parentescoAtual === 'Outro' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="par_outro">Outro</label>
                </div>
              </div>

              <span class="lb mb-0">Tipo de contato</span>
              <div class="d-flex flex-wrap gap-3">
                <?php $tipoAtual = $contatoEdit->tipo ?? 'Whatsapp'; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo" id="tp_tel" value="Telefone" <?= $tipoAtual === 'Telefone' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="tp_tel">Telefone</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo" id="tp_cel" value="Celular" <?= $tipoAtual === 'Celular' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="tp_cel">Celular</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipo" id="tp_zap" value="Whatsapp" <?= $tipoAtual === 'Whatsapp' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="tp_zap">Whatsapp</label>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="aluno_detalhe.php?id=<?= $alunoVinculado->id_usuario ?>" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar contato</button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>