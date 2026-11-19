<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = $viewAtual ?? '';  // definido por Controller::render()

$isMeuDia     = ($paginaAtual === 'prof_meu_dia.php');
$isTurmas     = in_array($paginaAtual, ['turmas.php', 'turma_detalhe.php'], true);
$isAlunos     = in_array($paginaAtual, ['alunos.php', 'aluno_detalhe.php'], true);
$isReposicoes = ($paginaAtual === 'reposicoes.php');

$nomeLogado  = $_SESSION['nome'] ?? 'Professor(a)';
$cargoLogado = 'Professor(a)';

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

      <a class="nav-x <?= $isMeuDia ? 'active' : '' ?>" href="index.php?controle=ProfessorController&metodo=meuDia">
        <i class="bi bi-calendar-check"></i>Meu dia
      </a>
      <a class="nav-x <?= $isTurmas ? 'active' : '' ?>" href="index.php?controle=TurmaController&metodo=listar">
        <i class="bi bi-water"></i>Minhas turmas
      </a>
      <a class="nav-x <?= $isAlunos ? 'active' : '' ?>" href="index.php?controle=AlunoController&metodo=listar">
        <i class="bi bi-people"></i>Alunos
      </a>
      <a class="nav-x <?= $isReposicoes ? 'active' : '' ?>" href="index.php?controle=ReposicaoController&metodo=listar">
        <i class="bi bi-arrow-repeat"></i>Reposições
      </a>

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