<?php
require_once __DIR__ . '/../Controllers/Permissao.class.php';
Permissao::exigirGestor();

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$idEditar = (int) ($_GET['id'] ?? 0);
$alunoEdit = null;

if ($idEditar > 0) {
    $stm = $db->prepare("
        SELECT u.*, a.aula_experimental, a.responsavel_id, a.observacao
        FROM alunos a
        INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
        WHERE a.usuario_id = ? AND a.deletado_em IS NULL
    ");
    $stm->execute([$idEditar]);
    $alunoEdit = $stm->fetch(PDO::FETCH_OBJ);

    if (!$alunoEdit) {
        header('Location: Aluno_Lista.php');
        exit;
    }
}

$responsaveis = $db->query("
    SELECT id_usuario, nome, cpf 
    FROM usuarios 
    WHERE deletado_em IS NULL AND id_usuario != {$idEditar}
    ORDER BY nome ASC
")->fetchAll(PDO::FETCH_OBJ);

$isEdicao = ($alunoEdit !== null);
$actionUrl = $isEdicao
    ? '../index.php?controle=AlunoController&metodo=editar'
    : '../index.php?controle=AlunoController&metodo=cadastrar';
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
    <title><?= $isEdicao ? 'Editar aluno' : 'Novo aluno' ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= $isEdicao ? 'Editar aluno' : 'Novo aluno' ?></h1>
              <div class="text-muted small">Pessoas › Alunos › <?= $isEdicao ? htmlspecialchars($alunoEdit->nome) : 'Novo aluno' ?></div>
            </div>
          </div>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <form action="<?= $actionUrl ?>" method="POST" class="card">
            <?php if ($isEdicao): ?>
              <input type="hidden" name="id_usuario" value="<?= $alunoEdit->id_usuario ?>">
            <?php endif; ?>

            <div class="card-body d-flex flex-column gap-3">
              <div class="sec">Dados pessoais</div>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c1">Nome completo</label>
                  <input type="text" class="form-control" id="c1" name="nome" value="<?= htmlspecialchars($alunoEdit->nome ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c2">Data de nascimento</label>
                  <input type="date" class="form-control" id="c2" name="data_nasc" value="<?= htmlspecialchars($alunoEdit->data_nasc ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c3">CPF</label>
                  <input type="text" class="form-control" id="c3" name="cpf" value="<?= htmlspecialchars($alunoEdit->cpf ?? '') ?>" required>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="c4">E-mail</label>
                  <input type="email" class="form-control" id="c4" name="email" value="<?= htmlspecialchars($alunoEdit->email ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c5">Telefone</label>
                  <input type="text" class="form-control" id="c5" name="telefone" value="<?= htmlspecialchars($alunoEdit->telefone ?? '') ?>" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label" for="c6">Responsável</label>
                  <select class="form-select" id="c6" name="responsavel_id">
                    <option value="">Selecione (opcional)</option>
                    <?php foreach ($responsaveis as $resp): ?>
                      <option value="<?= $resp->id_usuario ?>" <?= ((int) ($alunoEdit->responsavel_id ?? 0) === (int) $resp->id_usuario) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($resp->nome) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              
              <div class="sec">Endereço</div>
              <?php if ($isEdicao): ?>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="c_end">Endereço completo</label>
                    <input type="text" class="form-control" id="c_end" name="endereco_completo" value="<?= htmlspecialchars($alunoEdit->endereco ?? '') ?>" required>
                  </div>
                </div>
              <?php else: ?>
                <div class="row g-3">
                  <div class="col-12 col-md-3">
                    <label class="form-label" for="c7">CEP</label>
                    <input type="text" class="form-control" id="c7" name="cep">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="c8">Logradouro</label>
                    <input type="text" class="form-control" id="c8" name="logradouro" required>
                  </div>
                  <div class="col-12 col-md-3">
                    <label class="form-label" for="c9">Nº</label>
                    <input type="text" class="form-control" id="c9" name="numero" required>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c10">Bairro</label>
                    <input type="text" class="form-control" id="c10" name="bairro" required>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c11">Cidade</label>
                    <input type="text" class="form-control" id="c11" name="cidade" required>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label" for="c12">Complemento</label>
                    <input type="text" class="form-control" id="c12" name="complemento">
                  </div>
                </div>
              <?php endif; ?>
              
              <div class="sec">Acesso e observações</div>
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c13"><?= $isEdicao ? 'Nova senha (opcional)' : 'Senha de acesso' ?></label>
                  <input type="password" class="form-control" id="c13" name="senha" <?= $isEdicao ? '' : 'required' ?>>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c14">Confirmar senha</label>
                  <input type="password" class="form-control" id="c14" name="confirmar_senha" <?= $isEdicao ? '' : 'required' ?>>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="c16">Tipo de usuário</label>
                  <select class="form-select" id="c16" name="gestor">
                    <option value="0" <?= ((int) ($alunoEdit->gestor ?? 0) === 0) ? 'selected' : '' ?>>Aluno</option>
                    <option value="1" <?= ((int) ($alunoEdit->gestor ?? 0) === 1) ? 'selected' : '' ?>>Gestor / Administrador</option>
                  </select>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label" for="c17">Observações</label>
                  <input type="text" class="form-control" id="c17" name="observacoes" value="<?= htmlspecialchars($alunoEdit->observacao ?? '') ?>">
                </div>
              </div>
              <div>
                Aula experimental realizada &nbsp;
                <div class="form-check form-switch d-inline-block m-0">
                  <input class="form-check-input" type="checkbox" name="aula_experimental" value="1" <?= ((int) ($alunoEdit->aula_experimental ?? 0) === 1) ? 'checked' : '' ?>>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <a href="alunos.php" class="btn btn-outline-primary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $isEdicao ? 'Salvar alterações' : 'Salvar aluno' ?></button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>