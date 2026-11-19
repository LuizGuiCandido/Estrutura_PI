<?php
/**
 * View: site_professores — somente exibição (sem SQL, sem regra de permissão).
 * Os dados chegam prontos do Controller via render().
 * Variáveis esperadas: $linkPainel, $logado, $professores
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
    <title>Nossa equipe · Academia do Bill</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-md navbar-dark px-3 px-lg-5" style="background:#0405C0">
      <a class="navbar-brand logo-box p-1" href="index.php"><img style="height:56px" src="Views/assets/img/logo.png" alt="Academia do Bill – Jaú"></a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#n"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse justify-content-end" id="n">
        <ul class="navbar-nav gap-md-3">
          <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#modalidades">Modalidades</a></li>
          <li class="nav-item"><a class="nav-link text-warning" href="index.php?controle=SiteController&metodo=professores">Professores</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $linkPainel ?>"><?= $logado ? 'Meu Painel' : 'Entrar' ?></a></li>
        </ul>
      </div>
    </nav>

    <section class="container py-5">
      <h1 class="display-6 fw-bold text-primary">Nossa equipe</h1>
      <p class="text-muted fs-5 mb-4">Profissionais qualificados para cada etapa da sua evolução na água.</p>
      
      <div class="card">
        <div class="card-body d-flex flex-column gap-3">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Professor</th>
                  <th>Formação</th>
                  <th>Especialidade</th>
                  <th>CREF</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($professores)): ?>
                  <tr><td colspan="4" class="text-center text-muted py-4">Nenhum profissional cadastrado no momento.</td></tr>
                <?php else: ?>
                  <?php foreach ($professores as $prof): ?>
                    <tr>
                      <td>
                        <b><?= htmlspecialchars($prof->nome) ?></b>
                        <?php if ((int) $prof->estagiario === 1): ?>
                          <span class="badge rounded-pill b-in ms-1">Estagiário(a)</span>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(!empty($prof->formacao) ? $prof->formacao : 'Educação Física') ?></td>
                      <td><?= htmlspecialchars(!empty($prof->especialidade) ? $prof->especialidade : '—') ?></td>
                      <td><?= htmlspecialchars(!empty($prof->cref) ? $prof->cref : 'Em formação') ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
