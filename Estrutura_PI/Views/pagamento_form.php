<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

foreach ([
    __DIR__ . '/../Models/Conexao.php',

] as $caminho) {
    if (is_file($caminho)) {
        require_once $caminho;
        break;
    }
}

if (class_exists('Conexao') && method_exists('Conexao', 'getConexao')) {
    $pdo = (new Conexao())->getConexao();
} elseif (!isset($pdo) || !($pdo instanceof PDO)) {
    $pdo = new PDO('mysql:host=localhost;dbname=academia_bill;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Busca matrículas ativas ligando: matriculas -> alunos(usuario_id) -> usuarios -> turmas -> cursos
$matriculas = $pdo->query("
    SELECT 
        m.id_matricula,
        u.nome AS nome_aluno,
        c.nome AS nome_curso,
        t.horario_inicio,
        t.horario_termino
    FROM matriculas m
    INNER JOIN alunos a ON m.aluno_id = a.usuario_id
    INNER JOIN usuarios u ON a.usuario_id = u.id_usuario
    LEFT JOIN turmas t ON m.turma_id = t.id_turma
    LEFT JOIN cursos c ON t.curso_id = c.id_curso
    WHERE m.deletado_em IS NULL
      AND a.deletado_em IS NULL
      AND u.deletado_em IS NULL
    ORDER BY u.nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Busca planos usando as colunas reais: id_plano, periodo, valor_aula, meio_pagamento
$planos = $pdo->query("
    SELECT 
        id_plano,
        periodo,
        valor_aula,
        meio_pagamento
    FROM planos
    WHERE deletado_em IS NULL
    ORDER BY periodo ASC, meio_pagamento ASC
")->fetchAll(PDO::FETCH_ASSOC);

$dataHoje = date('Y-m-d');
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
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar/sidebar.php'; ?>

  <div class="flex-grow-1 min-vh-100 d-flex flex-column">
    <!-- Topbar mobile -->
    <header class="topbar d-lg-none p-3 d-flex align-items-center justify-content-between text-white">
      <div class="d-flex align-items-center gap-2">
        <img src="assets/img/logo.png" alt="Logo">
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
        <a href="pagamentos.php" class="btn btn-outline-primary">
          <i class="bi bi-arrow-left me-1"></i> Voltar para Pagamentos
        </a>
      </div>

      <div class="card p-4">
        <form action="../index.php?controle=PagamentoController&metodo=salvar" method="POST">
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
                  <option value="<?= (int)$m['id_matricula'] ?>">
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
                    value="<?= (int)$p['id_plano'] ?>" 
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
              <input type="number" step="0.01" min="0" class="form-control" id="valor_total" name="valor_total" placeholder="0,00" required>
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
            <a href="pagamentos.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i> Salvar Pagamento
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
</script>
</body>
</html>