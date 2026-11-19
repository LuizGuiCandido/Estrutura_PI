<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = $viewAtual ?? '';  // definido por Controller::render()

$isDashboard   = ($paginaAtual === 'dashboard.php');
$isAlunos      = in_array($paginaAtual, ['alunos.php', 'aluno_form.php', 'aluno_detalhe.php', 'contato_form.php'], true);
$isProfessores = in_array($paginaAtual, ['professores.php', 'professor_form.php'], true);
$isTurmas      = in_array($paginaAtual, ['turmas.php', 'turma_form.php', 'turma_detalhe.php'], true);
$isMatriculas  = in_array($paginaAtual, ['matriculas.php', 'matricula_form.php'], true);
$isReposicoes  = in_array($paginaAtual, ['reposicoes.php', 'reposicao_form.php'], true);
$isPagamentos  = in_array($paginaAtual, ['pagamentos.php', 'pagamento_form.php'], true);
$isPlanos      = in_array($paginaAtual, ['planos.php', 'plano_form.php'], true);
$isCursos      = ($paginaAtual === 'cursos_categorias.php');

$openPessoas = ($isAlunos || $isProfessores);
$openTurmas  = ($isTurmas || $isMatriculas || $isReposicoes);
$openAdmin   = ($isPagamentos || $isPlanos);
$openConfig  = $isCursos;

$nomeLogado  = $_SESSION['nome'] ?? 'Gestor(a)';
$cargoLogado = 'Gestor(a)';

$partesNome = explode(' ', trim($nomeLogado));
$iniciais = mb_strtoupper(
    mb_substr($partesNome[0], 0, 1) .
    (isset($partesNome[1]) ? mb_substr(end($partesNome), 0, 1) : mb_substr($partesNome[0], 1, 1))
);
?>
<div class="side">
  <aside class="offcanvas-lg offcanvas-start sidebar-oc h-100" tabindex="-1" id="menu">
    <div class="offcanvas-header d-lg-none">
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menu"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-1 p-3 pt-lg-4">
      <div class="logo-box mb-3"><img src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú"></div>

      <a class="nav-x <?= $isDashboard ? 'active' : '' ?>" href="index.php?controle=DashboardController&metodo=index">
        <i class="bi bi-grid"></i>Dashboard
      </a>

      <button class="gp <?= $openPessoas ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#g1">
        <span><i class="bi bi-people me-2"></i>Pessoas</span><i class="bi bi-chevron-right ch"></i>
      </button>
      <div class="collapse <?= $openPessoas ? 'show' : '' ?>" id="g1">
        <a class="si <?= $isAlunos ? 'active' : '' ?>" href="index.php?controle=AlunoController&metodo=listar">Alunos</a>
        <a class="si <?= $isProfessores ? 'active' : '' ?>" href="index.php?controle=ProfessorController&metodo=listar">Professores</a>
      </div>

      <button class="gp <?= $openTurmas ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#g2">
        <span><i class="bi bi-water me-2"></i>Turmas e aulas</span><i class="bi bi-chevron-right ch"></i>
      </button>
      <div class="collapse <?= $openTurmas ? 'show' : '' ?>" id="g2">
        <a class="si <?= $isTurmas ? 'active' : '' ?>" href="index.php?controle=TurmaController&metodo=listar">Turmas</a>
        <a class="si <?= $isMatriculas ? 'active' : '' ?>" href="index.php?controle=MatriculaController&metodo=listar">Matrículas</a>
        <a class="si <?= $isReposicoes ? 'active' : '' ?>" href="index.php?controle=ReposicaoController&metodo=listar">Reposições</a>
      </div>

      <button class="gp <?= $openAdmin ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#g3">
        <span><i class="bi bi-cash-coin me-2"></i>Administrativo</span><i class="bi bi-chevron-right ch"></i>
      </button>
      <div class="collapse <?= $openAdmin ? 'show' : '' ?>" id="g3">
        <a class="si <?= $isPagamentos ? 'active' : '' ?>" href="index.php?controle=PagamentoController&metodo=listar">Pagamentos</a>
        <a class="si <?= $isPlanos ? 'active' : '' ?>" href="index.php?controle=PlanoController&metodo=listar">Planos</a>
      </div>

      <button class="gp <?= $openConfig ? '' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#g4">
        <span><i class="bi bi-gear me-2"></i>Configurações</span><i class="bi bi-chevron-right ch"></i>
      </button>
      <div class="collapse <?= $openConfig ? 'show' : '' ?>" id="g4">
        <a class="si <?= $isCursos ? 'active' : '' ?>" href="index.php?controle=CursoController&metodo=listar">Cursos e categorias</a>
      </div>

      <div class="who mt-auto d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="av"><?= htmlspecialchars($iniciais) ?></span>
          <div><b><?= htmlspecialchars($nomeLogado) ?></b><br><?= htmlspecialchars($cargoLogado) ?></div>
        </div>
        <a href="index.php?controle=LoginController&metodo=logout" class="btn btn-sm btn-outline-light" title="Sair">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </aside>
</div>