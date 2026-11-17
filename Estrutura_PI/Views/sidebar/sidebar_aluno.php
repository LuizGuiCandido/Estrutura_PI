<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = basename($_SERVER['PHP_SELF']);

$isAgenda = ($paginaAtual === 'aluno_agenda.php');
$isPerfil = ($paginaAtual === 'aluno_perfil.php');

$nomeLogado  = $_SESSION['nome'] ?? 'Aluno(a)';
$cargoLogado = 'Aluno / Responsável';

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
      <div class="logo-box mb-3"><img src="assets/img/logo.png" alt="Academia do Bill – Jaú"></div>

      <a class="nav-x <?= $isAgenda ? 'active' : '' ?>" href="aluno_agenda.php">
        <i class="bi bi-calendar3"></i>Minha agenda
      </a>
      <a class="nav-x <?= $isPerfil ? 'active' : '' ?>" href="aluno_perfil.php">
        <i class="bi bi-person-circle"></i>Meu perfil
      </a>

      <div class="who mt-auto d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="av"><?= htmlspecialchars($iniciais) ?></span>
          <div><b><?= htmlspecialchars($nomeLogado) ?></b><br><?= htmlspecialchars($cargoLogado) ?></div>
        </div>
        <a href="../index.php?controle=LoginController&metodo=logout" class="btn btn-sm btn-outline-light" title="Sair">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </aside>
</div>