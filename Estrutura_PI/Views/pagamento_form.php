<?php
/**
 * View: pagamento_form — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $dataHoje, $matriculaSelecionada, $matriculas, $pagamentoEdit, $planoSelecionado, $planos
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Pagamento – Academia do Bill</title>
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
          <h3 class="mb-1 fw-bold" style="color:#0405C0">Registrar Pagamento</h3>
          <p class="text-muted mb-0">Lançamento de mensalidade ou plano vinculado à matrícula</p>
        </div>
        <a href="index.php?controle=PagamentoController&metodo=listar" class="btn btn-outline-primary">
          <i class="bi bi-arrow-left me-1"></i> Voltar para Pagamentos
        </a>
      </div>

      <div class="card p-4">
        <form action="index.php?controle=PagamentoController&metodo=salvar" method="POST">
          <?php if (!empty($pagamentoEdit)): ?>
            <input type="hidden" name="id_pagamento" value="<?= (int) $pagamentoEdit->id_pagamento ?>">
          <?php endif; ?>
          <div class="row g-3">

            <!-- Matrícula / Aluno -->
            <div class="col-md-6">
              <label for="matricula_id" class="form-label">Aluno / Matrícula *</label>
              <select class="form-select" id="matricula_id" name="matricula_id" required>
                <option value="">Selecione a matrícula...</option>
                <?php foreach ($matriculas as $m): ?>
                  <?php
                    $infoTurma = !empty($m['nome_curso'])
                        ? $m['nome_curso'] . ' (' . substr((string)$m['horario_inicio'], 0, 5) . ' - ' . substr((string)$m['horario_termino'], 0, 5) . ')'
                        : 'Sem turma vinculada';
                  ?>
                  <option value="<?= (int)$m['id_matricula'] ?>" <?= (int)$m['id_matricula'] === (int)$matriculaSelecionada ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nome_aluno']) ?> — <?= htmlspecialchars($infoTurma) ?> (#<?= (int)$m['id_matricula'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Plano -->
            <div class="col-md-6">
              <label for="plano_id" class="form-label">Plano *</label>
              <select class="form-select" id="plano_id" name="plano_id" required>
                <option value="" data-valor="" data-meio="">Selecione o plano...</option>
                <?php foreach ($planos as $p): ?>
                  <?php
                    $descricaoPlano = ucfirst((string)($p['periodo'] ?? 'Avulso')) . ' — ' . ucfirst((string)$p['meio_pagamento']);
                  ?>
                  <option 
                    value="<?= (int)$p['id_plano'] ?>" <?= (int)$p['id_plano'] === (int)$planoSelecionado ? 'selected' : '' ?>
                    data-valor="<?= htmlspecialchars((string)$p['valor_aula']) ?>"
                    data-meio="<?= htmlspecialchars((string)$p['meio_pagamento']) ?>"
                  >
                    <?= htmlspecialchars($descricaoPlano) ?> (R$ <?= number_format((float)$p['valor_aula'], 2, ',', '.') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Valor Total (coluna valor_total da tabela pagamentos) -->
            <div class="col-md-4">
              <label for="valor_total" class="form-label">Valor Total (R$) *</label>
              <input type="number" step="0.01" min="0" class="form-control" id="valor_total" name="valor_total" placeholder="0,00" value="<?= isset($pagamentoEdit) ? htmlspecialchars((string) $pagamentoEdit->valor_total) : '' ?>" required>
            </div>

            <!-- Data do Pagamento (coluna data_pagamento da tabela pagamentos) -->
            <div class="col-md-4">
              <label for="data_pagamento" class="form-label">Data do Pagamento *</label>
              <input type="date" class="form-control" id="data_pagamento" name="data_pagamento" value="<?= $dataHoje ?>" required>
            </div>

            <!-- Meio de Pagamento (informativo vindo do plano selecionado) -->
            <div class="col-md-4">
              <label for="meio_info" class="form-label">Meio de Pagamento (do Plano)</label>
              <input type="text" class="form-control bg-light" id="meio_info" readonly placeholder="Selecione um plano">
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="index.php?controle=PagamentoController&metodo=listar" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i> <?= !empty($pagamentoEdit) ? 'Salvar alterações' : 'Salvar Pagamento' ?>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('plano_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const valor = opt.getAttribute('data-valor');
    const meio  = opt.getAttribute('data-meio');

    if (valor) {
      document.getElementById('valor_total').value = parseFloat(valor).toFixed(2);
    }
    document.getElementById('meio_info').value = meio || '';
  });

  // Edição/pré-seleção: preenche o meio de pagamento sem alterar o valor digitado
  (function () {
    const sel = document.getElementById('plano_id');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.getAttribute('data-meio')) {
      document.getElementById('meio_info').value = opt.getAttribute('data-meio');
    }
  })();
</script>
</body>
</html>
