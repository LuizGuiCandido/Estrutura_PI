<?php
/**
 * View: professor_detalhe — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $idProf, $prof, $turmas
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
    <title><?= htmlspecialchars($prof->nome) ?> · Academia do Bill</title>
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
              <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($prof->nome) ?></h1>
              <div class="text-muted small">Pessoas › Professores › Dados</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="index.php?controle=ProfessorController&metodo=listar" class="btn btn-outline-secondary">Voltar</a>
              <a href="index.php?controle=ProfessorController&metodo=formulario&id=<?= $prof->id_usuario ?>" class="btn btn-outline-primary">Editar professor</a>
            </div>
          </div>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <h2 class="h6 fw-bold mb-0">Dados do professor</h2>
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                  <tbody>
                    <tr>
                      <th class="bg-light" style="width:16%">Nome</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->nome) ?></td>
                      <th class="bg-light" style="width:16%">CPF</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->cpf) ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">E-mail</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->email) ?></td>
                      <th class="bg-light" style="width:16%">Telefone</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->telefone ?? '-') ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">CREF</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->creef ?: 'Não informado') ?></td>
                      <th class="bg-light" style="width:16%">Formação</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->formacao) ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Especialidade</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->especialidade ?: '-') ?></td>
                      <th class="bg-light" style="width:16%">Exibir no site</th>
                      <td class="text-wrap">
                        <?= (int) $prof->mostrar === 1 ? '<span class="badge rounded-pill b-ok">Sim</span>' : '<span class="badge rounded-pill b-wa">Não</span>' ?>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Nascimento</th>
                      <td class="text-wrap"><?= $prof->data_nasc ? date('d/m/Y', strtotime($prof->data_nasc)) : '-' ?></td>
                      <th class="bg-light" style="width:16%">Endereço</th>
                      <td class="text-wrap"><?= htmlspecialchars($prof->endereco) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Turmas ministradas</h2>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Curso / Turma</th>
                      <th>Função</th>
                      <th>Horário</th>
                      <th>Local</th>
                      <th>Alunos ativos</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($turmas)): ?>
                      <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma turma vinculada a este professor.</td></tr>
                    <?php else: ?>
                      <?php foreach ($turmas as $tm): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($tm->curso_nome) ?></b></td>
                          <td><?= (int) $tm->professor_id === $idProf ? 'Titular' : 'Estagiário' ?></td>
                          <td><?= substr((string) $tm->horario_inicio, 0, 5) ?>–<?= substr((string) $tm->horario_termino, 0, 5) ?></td>
                          <td>Raia <?= (int) $tm->local_aula ?></td>
                          <td><?= (int) $tm->total_alunos ?> aluno(s)</td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($turmas) ?> registro(s)</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
