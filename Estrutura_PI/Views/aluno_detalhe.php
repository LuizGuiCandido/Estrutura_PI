<?php
/**
 * View: aluno_detalhe — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $aluno, $contatos, $idadeTexto, $isGestor, $matriculas, $pagamentos, $reposicoes
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
    <title><?= htmlspecialchars($aluno->nome) ?> · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php
        if ((int) ($_SESSION['gestor'] ?? 0) === 1) {
          require_once __DIR__ . '/sidebar/sidebar.php';
        } else {
          require_once __DIR__ . '/sidebar/sidebar_professor.php';
        }
      ?>  

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($aluno->nome) ?></h1>
              <div class="text-muted small">Pessoas › Alunos › Perfil</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="index.php?controle=AlunoController&metodo=listar" class="btn btn-outline-secondary">Voltar</a>
              <?php if ($isGestor): ?>
                <a href="index.php?controle=AlunoController&metodo=formulario&id=<?= $aluno->id_usuario ?>" class="btn btn-outline-primary">Editar aluno</a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Dados do Aluno -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <h2 class="h6 fw-bold mb-0">Dados do aluno</h2>
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                  <tbody>
                    <tr>
                      <th class="bg-light" style="width:16%">Nome</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->nome) ?></td>
                      <th class="bg-light" style="width:16%">CPF</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->cpf) ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Nascimento</th>
                      <td class="text-wrap"><?= htmlspecialchars($idadeTexto ?: '-') ?></td>
                      <th class="bg-light" style="width:16%">Telefone</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->telefone ?? '-') ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">E-mail</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->email) ?></td>
                      <th class="bg-light" style="width:16%">Responsável</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->responsavel_nome ?? 'Próprio aluno') ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Endereço</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->endereco) ?></td>
                      <th class="bg-light" style="width:16%">Aula experimental</th>
                      <td class="text-wrap"><?= (int) $aluno->aula_experimental === 1 ? 'Realizada' : 'Não realizada' ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Situação</th>
                      <td class="text-wrap">
                        <?= count($matriculas) > 0 ? '<span class="badge rounded-pill b-ok">Matrícula ativa</span>' : '<span class="badge rounded-pill b-wa">Sem matrícula</span>' ?>
                      </td>
                      <th class="bg-light" style="width:16%">Observações</th>
                      <td class="text-wrap"><?= htmlspecialchars($aluno->observacao !== '' ? $aluno->observacao : 'Nenhuma') ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Matrículas -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Matrículas</h2>
                <?php if ($isGestor): ?>
                  <a href="index.php?controle=MatriculaController&metodo=formulario&aluno_id=<?= $aluno->id_usuario ?>" class="btn btn-primary">+ Nova matrícula</a>
                <?php endif; ?>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Curso / Turma</th>
                      <th>Professor</th>
                      <th>Horário</th>
                      <th>Dias</th>
                      <th>Situação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($matriculas)): ?>
                      <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma matrícula encontrada.</td></tr>
                    <?php else: ?>
                      <?php foreach ($matriculas as $mat): ?>
                        <?php
                          $dias = [];
                          if (!empty($mat->segunda)) $dias[] = 'Seg';
                          if (!empty($mat->terca))   $dias[] = 'Ter';
                          if (!empty($mat->quarta))  $dias[] = 'Qua';
                          if (!empty($mat->quinta))  $dias[] = 'Qui';
                          if (!empty($mat->sexta))   $dias[] = 'Sex';
                        ?>
                        <tr>
                          <td><b><?= htmlspecialchars($mat->curso_nome ?? 'Turma removida') ?></b></td>
                          <td><?= htmlspecialchars($mat->professor_nome ?? '-') ?></td>
                          <td><?= $mat->horario_inicio ? substr($mat->horario_inicio, 0, 5) . '–' . substr((string) $mat->horario_termino, 0, 5) : '-' ?></td>
                          <td><?= !empty($dias) ? implode(', ', $dias) : '-' ?></td>
                          <td>
                            <span class="badge rounded-pill <?= (int) $mat->status_matricula === 1 ? 'b-ok' : 'b-er' ?>">
                              <?= (int) $mat->status_matricula === 1 ? 'Ativo' : 'Desativado' ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Contatos de Emergência -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Contatos de emergência</h2>
                <?php if ($isGestor): ?>
                  <a href="index.php?controle=ContatoController&metodo=formulario&aluno_id=<?= $aluno->id_usuario ?>" class="btn btn-primary">+ Novo contato</a>
                <?php endif; ?>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Nome</th>
                      <th>Parentesco</th>
                      <th>Tipo</th>
                      <th>Número</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($contatos)): ?>
                      <tr><td colspan="4" class="text-center text-muted py-3">Nenhum contato de emergência cadastrado.</td></tr>
                    <?php else: ?>
                      <?php foreach ($contatos as $ct): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($ct->nome) ?></b></td>
                          <td><?= htmlspecialchars($ct->parentesco) ?></td>
                          <td><?= htmlspecialchars($ct->tipo) ?></td>
                          <td><?= htmlspecialchars($ct->numero ?? '-') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Reposições -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Reposições</h2>
                <a href="index.php?controle=ReposicaoController&metodo=formulario&aluno_id=<?= $aluno->id_usuario ?>" class="btn btn-primary">+ Nova reposição</a>
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
                        <tr>
                          <td><?= date('d/m/Y', strtotime($rep->data_reposicao)) ?></td>
                          <td><?= substr((string) $rep->horario, 0, 5) ?></td>
                          <td><?= htmlspecialchars($rep->curso_nome ?? '-') ?></td>
                          <td>
                            <span class="badge rounded-pill <?= $rep->status_reposicao === 'concluída' ? 'b-ok' : ($rep->status_reposicao === 'marcada' ? 'b-wa' : 'b-er') ?>">
                              <?= htmlspecialchars(ucfirst($rep->status_reposicao)) ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Pagamentos (visível apenas para Gestor) -->
          <?php if ($isGestor): ?>
            <div class="card">
              <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <h2 class="h6 fw-bold mb-0">Pagamentos</h2>
                  <a href="index.php?controle=PagamentoController&metodo=formulario&aluno_id=<?= $aluno->id_usuario ?>" class="btn btn-primary">+ Registrar pagamento</a>
                </div>
                <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Data</th>
                        <th>Plano</th>
                        <th>Meio</th>
                        <th>Valor</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($pagamentos)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhum pagamento registrado.</td></tr>
                      <?php else: ?>
                        <?php foreach ($pagamentos as $pg): ?>
                          <tr>
                            <td><?= date('d/m/Y', strtotime($pg->data_pagamento)) ?></td>
                            <td><?= htmlspecialchars(ucfirst((string) $pg->periodo)) ?></td>
                            <td><?= htmlspecialchars($pg->meio_pagamento) ?></td>
                            <td>R$ <?= number_format((float) $pg->valor_total, 2, ',', '.') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
