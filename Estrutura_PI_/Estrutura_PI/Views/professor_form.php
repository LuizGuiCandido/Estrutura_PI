<?php
/**
 * View: professor_form — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $actionUrl, $isEdicao, $profEdit
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
    <title><?= $isEdicao ? 'Editar professor' : 'Novo professor' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar professor' : 'Novo professor' ?></h1>
              <div class="text-muted small">Pessoas › Professores › <?= $isEdicao ? htmlspecialchars($profEdit->nome) : 'Novo professor' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_usuario" value="<?= $profEdit->id_usuario ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="sec">Dados pessoais</div>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c23">Nome completo</label>
                  <input type="text" class="form-control" id="c23" name="nome" value="<?= htmlspecialchars($profEdit->nome ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c24">Data de nascimento</label>
                  <input type="date" class="form-control" id="c24" name="data_nasc" value="<?= htmlspecialchars($profEdit->data_nasc ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c25">CPF</label>
                  <input type="text" class="form-control" id="c25" name="cpf" value="<?= htmlspecialchars($profEdit->cpf ?? '') ?>" required>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c26">E-mail</label>
                  <input type="email" class="form-control" id="c26" name="email" value="<?= htmlspecialchars($profEdit->email ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c27">Telefone</label>
                  <input type="text" class="form-control" id="c27" name="telefone" value="<?= htmlspecialchars($profEdit->telefone ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c28"><?= $isEdicao ? 'Nova senha (opcional)' : 'Senha de acesso' ?></label>
                  <input type="password" class="form-control" id="c28" name="senha" <?= $isEdicao ? '' : 'required' ?>>
                </div>
              </div>

              <div class="sec">Endereço</div>
              <?php if ($isEdicao): ?>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="c_end">Endereço completo</label>
                    <input type="text" class="form-control" id="c_end" name="endereco_completo" value="<?= htmlspecialchars($profEdit->endereco ?? '') ?>" required>
                  </div>
                </div>
              <?php else: ?>
                <div class="row g-3">
                  <div class="col-12 col-md-3">
                    <label class="form-label" for="c29">CEP</label>
                    <input type="text" class="form-control" id="c29" name="cep">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="c30">Logradouro</label>
                    <input type="text" class="form-control" id="c30" name="logradouro" required>
                  </div>
                  <div class="col-12 col-md-3">
                    <label class="form-label" for="c31">Nº</label>
                    <input type="text" class="form-control" id="c31" name="numero" required>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c32">Bairro</label>
                    <input type="text" class="form-control" id="c32" name="bairro" required>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c33">Cidade</label>
                    <input type="text" class="form-control" id="c33" name="cidade" required>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c34">Complemento</label>
                    <input type="text" class="form-control" id="c34" name="complemento">
                  </div>
                </div>
              <?php endif; ?>

              <div class="sec">Dados profissionais</div>
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c35">CREF (máx. 10 caracteres)</label>
                  <input type="text" class="form-control" id="c35" name="cref" maxlength="10" value="<?= htmlspecialchars($profEdit->creef ?? '') ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c36">Formação</label>
                  <select class="form-select" id="c36" name="formacao" required>
                    <option value="Educação Física" <?= (($profEdit->formacao ?? '') === 'Educação Física') ? 'selected' : '' ?>>Educação Física</option>
                    <option value="Tecnólogo em Natação" <?= (($profEdit->formacao ?? '') === 'Tecnólogo em Natação') ? 'selected' : '' ?>>Tecnólogo em Natação</option>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c38">Especialidade</label>
                  <input type="text" class="form-control" id="c38" name="especialidade" value="<?= htmlspecialchars($profEdit->especialidade ?? '') ?>" required>
                </div>
              </div>
              <div>
                Exibir no site institucional &nbsp;
                <div class="form-check form-switch d-inline-block m-0">
                  <input class="form-check-input" type="checkbox" name="exibir_site" value="1" <?= ((int) ($profEdit->mostrar ?? 1) === 1) ? 'checked' : '' ?>>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <a href="index.php?controle=ProfessorController&metodo=listar" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar professor' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
