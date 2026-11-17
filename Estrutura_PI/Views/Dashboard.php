<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

// 1. Indicadores do resumo do dia
$totalAlunos      = (int) $db->query("SELECT COUNT(*) FROM alunos WHERE deletado_em IS NULL")->fetchColumn();
$totalTurmas      = (int) $db->query("SELECT COUNT(*) FROM turmas WHERE deletado_em IS NULL AND (data_fim IS NULL OR data_fim >= CURDATE())")->fetchColumn();
$reposicoesSemana = (int) $db->query("SELECT COUNT(*) FROM reposicao WHERE status_reposicao = 'marcada' AND deletado_em IS NULL")->fetchColumn();

$resumoPg = $db->query("
    SELECT COUNT(*) AS qtd, COALESCE(SUM(valor_total), 0) AS total
    FROM pagamentos
    WHERE deletado_em IS NULL
")->fetch(PDO::FETCH_OBJ);

// 2. Últimos pagamentos registrados
$ultimosPagamentos = $db->query("
    SELECT 
        pg.valor_total,
        pg.data_pagamento,
        u.nome AS aluno_nome,
        c.nome AS curso_nome,
        pl.periodo,
        pl.meio_pagamento
    FROM pagamentos pg
    INNER JOIN matriculas m ON m.id_matricula = pg.matricula_id
    INNER JOIN usuarios u ON u.id_usuario = m.aluno_id
    LEFT JOIN turmas t ON t.id_turma = m.turma_id
    LEFT JOIN cursos c ON c.id_curso = t.curso_id
    INNER JOIN planos pl ON pl.id_plano = pg.plano_id
    WHERE pg.deletado_em IS NULL
    ORDER BY pg.data_pagamento DESC, pg.id_pagamento DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_OBJ);

// 3. Grade de turmas
$aulasHoje = $db->query("
    SELECT 
        t.horario_inicio,
        t.horario_termino,
        t.local_aula,
        c.nome AS curso_nome,
        u.nome AS professor_nome
    FROM turmas t
    INNER JOIN cursos c ON c.id_curso = t.curso_id
    INNER JOIN usuarios u ON u.id_usuario = t.professor_id
    WHERE t.deletado_em IS NULL
    ORDER BY t.horario_inicio ASC
    LIMIT 6
")->fetchAll(PDO::FETCH_OBJ);

$primeiroNome = explode(' ', trim($_SESSION['nome'] ?? 'Gestor'))[0];
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
    <title>Olá, <?= htmlspecialchars($primeiroNome) ?> 👋 · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0">Olá, <?= htmlspecialchars($primeiroNome) ?> 👋</h1>
              <div class="text-muted small"><?= date('d/m/Y') ?> · Painel Geral</div>
            </div>
          </div>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <h2 class="h6 fw-bold mb-0">Resumo do dia</h2>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Alunos cadastrados</th>
                      <th>Turmas ativas</th>
                      <th>Reposições marcadas</th>
                      <th>Pagamentos registrados</th>
                      <th>Total recebido</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><b class="fs-5"><?= $totalAlunos ?></b></td>
                      <td><b class="fs-5"><?= $totalTurmas ?></b></td>
                      <td><b class="fs-5"><?= $reposicoesSemana ?></b></td>
                      <td><b class="fs-5"><?= (int) $resumoPg->qtd ?></b></td>
                      <td><b class="fs-5">R$ <?= number_format((float) $resumoPg->total, 2, ',', '.') ?></b></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-xl-8">
              <div class="card">
                <div class="card-body d-flex flex-column gap-3">
                  <h2 class="h6 fw-bold mb-0">Últimos pagamentos</h2>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>Aluno</th>
                          <th>Turma</th>
                          <th>Data</th>
                          <th>Plano</th>
                          <th>Valor</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($ultimosPagamentos)): ?>
                          <tr><td colspan="5" class="text-center text-muted py-3">Nenhum pagamento registrado.</td></tr>
                        <?php else: ?>
                          <?php foreach ($ultimosPagamentos as $item): ?>
                            <tr>
                              <td><b><?= htmlspecialchars($item->aluno_nome) ?></b></td>
                              <td><?= htmlspecialchars($item->curso_nome ?? 'Sem turma') ?></td>
                              <td><?= date('d/m/Y', strtotime($item->data_pagamento)) ?></td>
                              <td><?= htmlspecialchars(ucfirst((string) $item->periodo) . ' · ' . $item->meio_pagamento) ?></td>
                              <td>R$ <?= number_format((float) $item->valor_total, 2, ',', '.') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                  <a href="pagamentos.php" class="small fw-semibold">Ver todos →</a>
                </div>
              </div>
            </div>

            <div class="col-xl-4">
              <div class="card">
                <div class="card-body d-flex flex-column gap-3">
                  <h2 class="h6 fw-bold mb-0">Turmas e horários</h2>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>Horário</th>
                          <th>Curso</th>
                          <th>Professor</th>
                          <th>Raia</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($aulasHoje)): ?>
                          <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma turma cadastrada.</td></tr>
                        <?php else: ?>
                          <?php foreach ($aulasHoje as $aula): ?>
                            <tr>
                              <td><b><?= substr((string) $aula->horario_inicio, 0, 5) ?>–<?= substr((string) $aula->horario_termino, 0, 5) ?></b></td>
                              <td><?= htmlspecialchars($aula->curso_nome) ?></td>
                              <td><?= htmlspecialchars(explode(' ', $aula->professor_nome)[0]) ?></td>
                              <td>Raia <?= (int) $aula->local_aula ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>