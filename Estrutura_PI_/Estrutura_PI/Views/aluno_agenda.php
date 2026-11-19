<?php
/**
 * View: aluno_agenda — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $alunoIdSelecionado, $diasSemanaDatas, $gradeHorarios, $listaAlunos, $nomeAlunoAtual, $proximaSemana, $reposicoes, $reposicoesSemana, $segundaTs, $semanaAnterior, $tituloSemana
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
    <title>Minha agenda · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar_aluno.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Minha agenda</h1>
              <div class="text-muted small"><?= htmlspecialchars($tituloSemana) ?> · <?= htmlspecialchars($nomeAlunoAtual) ?></div>
            </div>
          </div>

          <!-- CARD 1: HORÁRIOS DA SEMANA -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="row g-2 mb-1">
                <?php if (count($listaAlunos) > 1): ?>
                  <div class="col-12 col-md-4">
                    <form method="GET">
                  <input type="hidden" name="controle" value="AlunoController">
                  <input type="hidden" name="metodo" value="agenda">
                      <input type="hidden" name="semana" value="<?= htmlspecialchars(date('Y-m-d', $segundaTs)) ?>">
                      <select name="aluno_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($listaAlunos as $al): ?>
                          <option value="<?= $al->id_usuario ?>" <?= (int) $al->id_usuario === $alunoIdSelecionado ? 'selected' : '' ?>>
                            Aluno: <?= htmlspecialchars($al->nome) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  </div>
                <?php elseif (count($listaAlunos) === 1): ?>
                  <div class="col-12 col-md-4">
                    <input type="text" class="form-control" value="Aluno: <?= htmlspecialchars($nomeAlunoAtual) ?>" readonly>
                  </div>
                <?php endif; ?>

                <div class="col-12 col-md-<?= !empty($listaAlunos) ? '8' : '12' ?>">
                  <div class="btn-group w-100">
                    <a class="btn btn-outline-primary" href="index.php?controle=AlunoController&metodo=agenda&aluno_id=<?= $alunoIdSelecionado ?>&semana=<?= $semanaAnterior ?>">‹ Semana anterior</a>
                    <a class="btn btn-outline-primary" href="index.php?controle=AlunoController&metodo=agenda&aluno_id=<?= $alunoIdSelecionado ?>&semana=<?= $proximaSemana ?>">Próxima semana ›</a>
                  </div>
                </div>
              </div>

              <h2 class="h6 fw-bold mb-0">Horários da semana</h2>
              <div class="tb">
                <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Horário</th>
                        <th><?= $diasSemanaDatas[1]['label'] ?></th>
                        <th><?= $diasSemanaDatas[2]['label'] ?></th>
                        <th><?= $diasSemanaDatas[3]['label'] ?></th>
                        <th><?= $diasSemanaDatas[4]['label'] ?></th>
                        <th><?= $diasSemanaDatas[5]['label'] ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($gradeHorarios) && empty($reposicoesSemana)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma aula agendada para este aluno.</td></tr>
                      <?php else: ?>
                        <?php foreach ($gradeHorarios as $gh): ?>
                          <?php $horaLinha = substr((string) $gh->horario_inicio, 0, 5); ?>
                          <tr>
                            <td><b><?= $horaLinha ?></b></td>
                            <?php
                              $colsDias = [1 => 'segunda', 2 => 'terca', 3 => 'quarta', 4 => 'quinta', 5 => 'sexta'];
                              foreach ($colsDias as $numDia => $campoDia):
                                  $dataCelula = $diasSemanaDatas[$numDia]['data'];
                                  if (!empty($reposicoesSemana[$dataCelula][$horaLinha])):
                            ?>
                                    <td><span class="badge rounded-pill b-wa">Reposição</span></td>
                            <?php elseif ((int) $gh->$campoDia === 1): ?>
                                    <td><?= htmlspecialchars($gh->curso_nome) ?></td>
                            <?php else: ?>
                                    <td>—</td>
                            <?php
                                  endif;
                              endforeach;
                            ?>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- CARD 2: REPOSIÇÕES -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Reposições</h2>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Data</th>
                      <th>Horário</th>
                      <th>Turma</th>
                      <th>Situação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($reposicoes)): ?>
                      <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma reposição registrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($reposicoes as $rep): ?>
                        <?php
                          $badgeClass = match ($rep->status_reposicao) {
                              'marcada'   => 'b-wa',
                              'concluída' => 'b-ok',
                              default     => 'b-er',
                          };
                          $labelStatus = match ($rep->status_reposicao) {
                              'marcada'   => 'Agendada',
                              'concluída' => 'Concluída',
                              default     => 'Não concluída',
                          };
                        ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($rep->data_reposicao)) ?></td>
                          <td><?= substr((string) $rep->horario, 0, 5) ?></td>
                          <td><?= htmlspecialchars($rep->curso_nome ?? '—') ?></td>
                          <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $labelStatus ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($reposicoes) ?> registro(s)</span>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
