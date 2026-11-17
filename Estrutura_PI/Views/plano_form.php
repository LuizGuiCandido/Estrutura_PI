<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$planoEdit = null;

if ($idEditar > 0) {
    $stm = $db->prepare("SELECT * FROM planos WHERE id_plano = ? AND deletado_em IS NULL");
    $stm->execute([$idEditar]);
    $planoEdit = $stm->fetch(PDO::FETCH_OBJ);
    if (!$planoEdit) {
        header('Location: planos.php');
        exit;
    }
}

$isEdicao = ($planoEdit !== null);
$actionUrl = $isEdicao 
    ? '../index.php?controle=PlanoController&metodo=editar' 
    : '../index.php?controle=PlanoController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar plano' : 'Novo plano' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar plano' : 'Novo plano' ?></h1>
              <div class="text-muted small">Administrativo › Planos › <?= $isEdicao ? 'Editar' : 'Novo plano' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_plano" value="<?= $planoEdit->id_plano ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c_nome">Nome do Plano</label>
                  <input type="text" class="form-control" id="c_nome" name="nome" value="<?= htmlspecialchars($planoEdit->nome ?? '') ?>" placeholder="Ex: Mensal – 8 aulas" required>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c64">Período</label>
                  <select class="form-select" id="c64" name="periodo" required>
                    <?php $periodoAtual = $planoEdit->periodo ?? 'Mensal'; ?>
                    <option value="Mensal" <?= $periodoAtual === 'Mensal' ? 'selected' : '' ?>>Mensal</option>
                    <option value="Semestral" <?= $periodoAtual === 'Semestral' ? 'selected' : '' ?>>Semestral</option>
                    <option value="Anual" <?= $periodoAtual === 'Anual' ? 'selected' : '' ?>>Anual</option>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c65">Quantidade de aulas</label>
                  <input type="number" min="1" class="form-control" id="c65" name="aulas" value="<?= htmlspecialchars($planoEdit->aulas ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c66">Valor por aula (R$)</label>
                  <input type="number" step="0.01" min="0" class="form-control" id="c66" name="valor_aula" value="<?= htmlspecialchars($planoEdit->valor_aula ?? '') ?>" required>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-12">
                  <label class="form-label" for="c67">Valor total do plano (R$)</label>
                  <input type="number" step="0.01" class="form-control" id="c67" name="valor" value="<?= htmlspecialchars($planoEdit->valor ?? '') ?>" readonly required>
                </div>
              </div>

              <span class="lb mb-0">Meio de pagamento</span>
              <div class="d-flex flex-wrap gap-3">
                <?php $meioAtual = $planoEdit->meio_pagamento ?? 'À vista'; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="meio_pagamento" id="mp_vista" value="À vista" <?= $meioAtual === 'À vista' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="mp_vista">À vista</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="meio_pagamento" id="mp_cheque" value="Cheque pré-datado" <?= $meioAtual === 'Cheque pré-datado' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="mp_cheque">Cheque pré-datado</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="meio_pagamento" id="mp_cartao" value="Cartão de crédito" <?= $meioAtual === 'Cartão de crédito' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="mp_cartao">Cartão de crédito</label>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="planos.php" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar plano' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Cálculo automático: Valor Total = Quantidade de Aulas × Valor por Aula
      const inputAulas = document.getElementById('c65');
      const inputValorAula = document.getElementById('c66');
      const inputValorTotal = document.getElementById('c67');

      function calcularTotal() {
        let aulas = parseFloat(inputAulas.value) || 0;
        let valorAula = parseFloat(inputValorAula.value) || 0;
        let total = aulas * valorAula;
        inputValorTotal.value = total.toFixed(2);
      }

      inputAulas.addEventListener('input', calcularTotal);
      inputValorAula.addEventListener('input', calcularTotal);
    </script>
  </body>
</html>