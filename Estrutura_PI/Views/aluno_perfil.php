<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../Models/Conexao.php';
$db = (new Conexao())->getConexao();

$usuarioId = (int) $_SESSION['id_usuario'];

// 1. Dados do usuário logado + perfil de aluno (se existir)
$stmUser = $db->prepare("
    SELECT 
        u.id_usuario,
        u.nome,
        u.cpf,
        u.email,
        u.telefone,
        u.data_nasc,
        u.endereco,
        a.aula_experimental,
        a.observacao
    FROM usuarios u
    LEFT JOIN alunos a ON a.usuario_id = u.id_usuario AND a.deletado_em IS NULL
    WHERE u.id_usuario = ? AND u.deletado_em IS NULL
");
$stmUser->execute([$usuarioId]);
$usuario = $stmUser->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
    header('Location: login.php');
    exit;
}

// Mascara CPF exibindo apenas os dois últimos dígitos
$cpfDigitos = preg_replace('/\D/', '', (string) $usuario->cpf);
$cpfMascarado = strlen($cpfDigitos) === 11
    ? '***.***.***-' . substr($cpfDigitos, -2)
    : htmlspecialchars((string) $usuario->cpf);

// 2. Matrícula(s) do próprio usuário logado
$stmMatPropria = $db->prepare("
    SELECT 
        m.status_matricula,
        m.segunda,
        m.terca,
        m.quarta,
        m.quinta,
        m.sexta,
        c.nome AS curso_nome,
        t.horario_inicio,
        t.horario_termino,
        u_prof.nome AS professor_nome
    FROM matriculas m
    INNER JOIN turmas t ON t.id_turma = m.turma_id
    INNER JOIN cursos c ON c.id_curso = t.curso_id
    LEFT JOIN professores prof ON prof.usuario_id = t.professor_id
    LEFT JOIN usuarios u_prof ON u_prof.id_usuario = prof.usuario_id
    WHERE m.aluno_id = ? AND m.deletado_em IS NULL AND t.deletado_em IS NULL
    ORDER BY m.status_matricula DESC, m.data_matricula DESC
");
$stmMatPropria->execute([$usuarioId]);
$minhasMatriculas = $stmMatPropria->fetchAll(PDO::FETCH_OBJ);

// 3. Dependentes (alunos onde responsavel_id = usuarioId)
$stmDep = $db->prepare("
    SELECT 
        u.id_usuario,
        u.nome,
        u.data_nasc,
        (SELECT c2.nome 
         FROM matriculas m2 
         INNER JOIN turmas t2 ON t2.id_turma = m2.turma_id 
         INNER JOIN cursos c2 ON c2.id_curso = t2.curso_id 
         WHERE m2.aluno_id = u.id_usuario AND m2.status_matricula = 1 AND m2.deletado_em IS NULL 
         LIMIT 1) AS turma_atual,
        (SELECT COUNT(*) 
         FROM matriculas m3 
         WHERE m3.aluno_id = u.id_usuario AND m3.status_matricula = 1 AND m3.deletado_em IS NULL) AS qtd_ativas
    FROM alunos a
    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
    WHERE a.responsavel_id = ? AND a.deletado_em IS NULL AND u.deletado_em IS NULL
    ORDER BY u.nome ASC
");
$stmDep->execute([$usuarioId]);
$dependentes = $stmDep->fetchAll(PDO::FETCH_OBJ);

// 4. Lista de alunos elegíveis para filtrar Contatos de Emergência (próprio usuário + dependentes)
$alunosContatos = [];
if ($usuario->aula_experimental !== null) {
    $alunosContatos[] = (object) ['id_usuario' => $usuario->id_usuario, 'nome' => $usuario->nome];
}
foreach ($dependentes as $dep) {
    $alunosContatos[] = (object) ['id_usuario' => $dep->id_usuario, 'nome' => $dep->nome];
}

$alunoContatoId = (int) ($_GET['contato_aluno_id'] ?? 0);
if ($alunoContatoId <= 0 && !empty($alunosContatos)) {
    $alunoContatoId = (int) $alunosContatos[0]->id_usuario;
}

// Detecta automaticamente o nome real da tabela de contatos no banco
$tabelaContato = null;
foreach (['contato_emergencia', 'contatos_emergencia', 'contatos'] as $candidata) {
    $chk = $db->query("SHOW TABLES LIKE '{$candidata}'");
    if ($chk && $chk->fetch()) {
        $tabelaContato = $candidata;
        break;
    }
}

$contatosEmergencia = [];
if ($alunoContatoId > 0 && $tabelaContato !== null) {
    try {
        $stmCont = $db->prepare("
            SELECT nome, parentesco, tipo, numero 
            FROM {$tabelaContato} 
            WHERE aluno_id = ? AND deletado_em IS NULL
        ");
        $stmCont->execute([$alunoContatoId]);
        $contatosEmergencia = $stmCont->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        $contatosEmergencia = [];
    }
}
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
    <title>Meu perfil · Academia do Bill</title>
  </head>
  <body>
    <div class="d-flex min-vh-100">
      <?php require_once __DIR__ . '/sidebar/sidebar_aluno.php'; ?>

      <main class="flex-grow-1" style="min-width:0">
        <header class="topbar d-lg-none d-flex align-items-center justify-content-between p-2 px-3">
          <button class="btn btn-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menu"><i class="bi bi-list fs-5"></i></button>
          <img src="assets/img/logo.png" alt="Academia do Bill – Jaú">
        </header>

        <div class="p-3 p-lg-4 d-flex flex-column gap-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h1 class="h3 fw-bold mb-0">Meu perfil</h1>
              <div class="text-muted small">Minha conta › Meu perfil</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSenha">Alterar senha</button>
            </div>
          </div>

          <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alert alert-success py-2 small mb-0"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
            <?php unset($_SESSION['sucesso']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert alert-danger py-2 small mb-0"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
          <?php endif; ?>

          <!-- CARD 1: MEUS DADOS -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <h2 class="h6 fw-bold mb-0">Meus dados</h2>
              <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                  <tbody>
                    <tr>
                      <th class="bg-light" style="width:16%">Nome</th>
                      <td class="text-wrap"><?= htmlspecialchars($usuario->nome) ?></td>
                      <th class="bg-light" style="width:16%">CPF</th>
                      <td class="text-wrap"><?= $cpfMascarado ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">E-mail</th>
                      <td class="text-wrap"><?= htmlspecialchars($usuario->email) ?></td>
                      <th class="bg-light" style="width:16%">Telefone</th>
                      <td class="text-wrap"><?= htmlspecialchars($usuario->telefone ?: '—') ?></td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Nascimento</th>
                      <td class="text-wrap"><?= $usuario->data_nasc ? date('d/m/Y', strtotime($usuario->data_nasc)) : '—' ?></td>
                      <th class="bg-light" style="width:16%">Aula experimental</th>
                      <td class="text-wrap">
                        <?php
                          if ($usuario->aula_experimental === null) {
                              echo '—';
                          } else {
                              echo ((int) $usuario->aula_experimental === 1) ? 'Realizada' : 'Não realizada';
                          }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light" style="width:16%">Endereço</th>
                      <td class="text-wrap"><?= htmlspecialchars($usuario->endereco ?: '—') ?></td>
                      <th class="bg-light" style="width:16%">Observações</th>
                      <td class="text-wrap"><?= htmlspecialchars(!empty($usuario->observacao) ? $usuario->observacao : '—') ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- CARD 2: MINHA MATRÍCULA -->
          <div class="card">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Minha matrícula</h2>
              </div>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Turma</th>
                      <th>Professor</th>
                      <th>Horário</th>
                      <th>Dias</th>
                      <th>Situação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($minhasMatriculas)): ?>
                      <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma matrícula vinculada ao seu usuário.</td></tr>
                    <?php else: ?>
                      <?php foreach ($minhasMatriculas as $mat): ?>
                        <?php
                          $dias = [];
                          if (!empty($mat->segunda)) $dias[] = 'Seg';
                          if (!empty($mat->terca))   $dias[] = 'Ter';
                          if (!empty($mat->quarta))  $dias[] = 'Qua';
                          if (!empty($mat->quinta))  $dias[] = 'Qui';
                          if (!empty($mat->sexta))   $dias[] = 'Sex';
                        ?>
                        <tr>
                          <td><b><?= htmlspecialchars($mat->curso_nome) ?></b></td>
                          <td><?= htmlspecialchars($mat->professor_nome ?? '—') ?></td>
                          <td><?= substr((string) $mat->horario_inicio, 0, 5) ?>–<?= substr((string) $mat->horario_termino, 0, 5) ?></td>
                          <td><?= !empty($dias) ? implode(', ', $dias) : '—' ?></td>
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
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span><?= count($minhasMatriculas) ?> registro(s)</span>
              </div>
            </div>
          </div>

          <!-- CARD 3: MEUS DEPENDENTES -->
          <?php if (!empty($dependentes)): ?>
            <div class="card">
              <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <h2 class="h6 fw-bold mb-0">Meus dependentes</h2>
                </div>
                <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Nome</th>
                        <th>Turma</th>
                        <th>Nascimento</th>
                        <th>Situação</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($dependentes as $dep): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($dep->nome) ?></b></td>
                          <td><?= htmlspecialchars($dep->turma_atual ?? 'Sem turma ativa') ?></td>
                          <td><?= $dep->data_nasc ? date('d/m/Y', strtotime($dep->data_nasc)) : '—' ?></td>
                          <td>
                            <span class="badge rounded-pill <?= (int) $dep->qtd_ativas > 0 ? 'b-ok' : 'b-er' ?>">
                              <?= (int) $dep->qtd_ativas > 0 ? 'Ativo' : 'Inativo' ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                  <span><?= count($dependentes) ?> registro(s)</span>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- CARD 4: CONTATOS DE EMERGÊNCIA -->
          <div class="card" id="contatos">
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0">Contatos de emergência</h2>
                <?php if (count($alunosContatos) > 1): ?>
                  <form method="GET" action="aluno_perfil.php#contatos">
                    <select name="contato_aluno_id" class="form-select w-auto" aria-label="Aluno" onchange="this.form.submit()">
                      <?php foreach ($alunosContatos as $ac): ?>
                        <option value="<?= $ac->id_usuario ?>" <?= (int) $ac->id_usuario === $alunoContatoId ? 'selected' : '' ?>>
                          Aluno: <?= htmlspecialchars($ac->nome) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                <?php elseif (count($alunosContatos) === 1): ?>
                  <span class="text-muted small">Aluno: <b><?= htmlspecialchars($alunosContatos[0]->nome) ?></b></span>
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
                    <?php if (empty($contatosEmergencia)): ?>
                      <tr><td colspan="4" class="text-center text-muted py-3">Nenhum contato de emergência cadastrado.</td></tr>
                    <?php else: ?>
                      <?php foreach ($contatosEmergencia as $ce): ?>
                        <tr>
                          <td><b><?= htmlspecialchars($ce->nome) ?></b></td>
                          <td><?= htmlspecialchars($ce->parentesco) ?></td>
                          <td><?= htmlspecialchars($ce->tipo) ?></td>
                          <td><?= htmlspecialchars($ce->numero) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
                <span>Somente leitura. Para alterar um contato, fale com a gerência.</span>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL: ALTERAR SENHA -->
        <div class="modal fade" id="modalSenha" tabindex="-1" aria-labelledby="modalSenhaTitulo" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="../index.php?controle=LoginController&metodo=alterarSenha" method="POST">
              <div class="modal-header">
                <h2 class="modal-title h5 fw-bold" id="modalSenhaTitulo">Alterar senha</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>
              <div class="modal-body d-flex flex-column gap-3">
                <div>
                  <label class="form-label" for="senhaAtual">Senha atual</label>
                  <input type="password" class="form-control" id="senhaAtual" name="senha_atual" required>
                </div>
                <div>
                  <label class="form-label" for="novaSenha">Nova senha</label>
                  <input type="password" class="form-control" id="novaSenha" name="nova_senha" minlength="6" required>
                </div>
                <div>
                  <label class="form-label" for="confirmarNovaSenha">Confirmar nova senha</label>
                  <input type="password" class="form-control" id="confirmarNovaSenha" name="confirmar_senha" minlength="6" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar nova senha</button>
              </div>
            </form>
          </div>
        </div>

      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>