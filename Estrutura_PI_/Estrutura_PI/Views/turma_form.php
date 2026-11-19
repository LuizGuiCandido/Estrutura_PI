<?php
/**
 * View: turma_form — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $actionUrl, $cursos, $isEdicao, $professores, $turmaEdit
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
    <title><?= $isEdicao ? 'Editar turma' : 'Nova turma' ?> · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar turma' : 'Nova turma' ?></h1>
              <div class="text-muted small">Turmas e aulas › Turmas › <?= $isEdicao ? 'Editar' : 'Nova turma' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_turma" value="<?= $turmaEdit->id_turma ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="sec">Dados da turma</div>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c40">Curso</label>
                  <select class="form-select" id="c40" name="curso_id" required>
                    <option value="" disabled <?= !$isEdicao ? 'selected' : '' ?>>Selecione</option>
                    <?php foreach ($cursos as $cur): ?>
                      <option value="<?= $cur->id_curso ?>" <?= ($isEdicao && (int)$turmaEdit->curso_id === (int)$cur->id_curso) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cur->nome) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c42">Raia / local</label>
                  <select class="form-select" id="c42" name="local_aula" required>
                    <option value="" disabled <?= !$isEdicao ? 'selected' : '' ?>>Selecione a raia</option>
                    <?php for ($r = 1; $r <= 6; $r++): ?>
                      <option value="<?= $r ?>" <?= ($isEdicao && (int)$turmaEdit->local_aula === $r) ? 'selected' : '' ?>>Raia <?= $r ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c43">Horário de início</label>
                  <input type="time" class="form-control" id="c43" name="horario_inicio" value="<?= htmlspecialchars($turmaEdit->horario_inicio ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c44">Horário de término</label>
                  <input type="time" class="form-control" id="c44" name="horario_termino" value="<?= htmlspecialchars($turmaEdit->horario_termino ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c45">Data de início</label>
                  <input type="date" class="form-control" id="c45" name="data_inicio" value="<?= htmlspecialchars($turmaEdit->data_inicio ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c46">Data de término</label>
                  <input type="date" class="form-control" id="c46" name="data_fim" value="<?= htmlspecialchars($turmaEdit->data_fim ?? '') ?>">
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c47">Professor responsável</label>
                  <select class="form-select" id="c47" name="professor_id" required>
                    <option value="" disabled <?= !$isEdicao ? 'selected' : '' ?>>Selecione</option>
                    <?php foreach ($professores as $prof): ?>
                      <option value="<?= $prof->usuario_id ?>" <?= ($isEdicao && (int)$turmaEdit->professor_id === (int)$prof->usuario_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prof->nome) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c48">Estagiário (opcional)</label>
                  <select class="form-select" id="c48" name="estagiario_id">
                    <option value="">Nenhum</option>
                    <?php foreach ($professores as $prof): ?>
                      <option value="<?= $prof->usuario_id ?>" <?= ($isEdicao && (int)$turmaEdit->estagiario_id === (int)$prof->usuario_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prof->nome) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <a href="index.php?controle=TurmaController&metodo=listar" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar turma' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
