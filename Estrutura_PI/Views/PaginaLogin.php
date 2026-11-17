<?php
session_start();
$erro = $_SESSION['erro_login'] ?? null;
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academia do Bill</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Cores e gradientes específicos do design original */
        body {
            background: linear-gradient(135deg, #11144c 0%, #0047ff 100%);
        }
        
        .painel-esquerdo {
            background: linear-gradient(180deg, #181d4a 0%, #090a2a 100%);
        }
        
        .texto-destaque {
            color: #0066ff;
        }
        
        .btn-entrar {
            background-color: #0c11bd;
            border: none;
        }
        
        .btn-entrar:hover {
            background-color: #090c96;
        }

        .texto-link {
            color: #0c11bd;
        }

        /* Container ajustado para a Imagem da Logo */
        .logo-box {
            width: 200px;
            height: 200px;
            background-color: #ffffff;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px; /* Evita que a imagem encoste nas bordas da caixa */
        }

        /* Regras para a Imagem caber perfeitamente sem distorcer */
        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        /* Ajuste fino para a altura do cartão principal */
        .card-login {
            min-height: 650px;
            max-width: 1050px;
        }
    </style>
</head>
<body class="vh-100 d-flex align-items-center justify-content-center m-0">

    <div class="container d-flex justify-content-center px-4">
        <div class="card card-login w-100 border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="row g-0 h-100 flex-grow-1">
                
                <!-- Coluna Esquerda (Branding) -->
                <div class="col-md-6 painel-esquerdo p-5 d-flex flex-column justify-content-between">
                    <!-- Área do Logo Centralizada -->
                    <div class="w-100 d-flex justify-content-center mt-3 mt-md-4">
                        <div class="logo-box shadow-sm">
                            <img src="./assets/img/logo.png" alt="Logo Academia do Bill">
                        </div>
                    </div>
                    
                    <!-- Área do Texto (Fundo) -->
                    <div class="mb-3 mt-5">
                        <h1 class="fw-bold text-white mb-3" style="font-size: 2.8rem; line-height: 1.2;">
                            Gestão da sua escola de natação, <span class="texto-destaque">sem complicação.</span>
                        </h1>
                        <p class="text-white opacity-75 mb-0" style="font-size: 1.05rem;">
                            Turmas, matrículas, reposições e pagamentos em um só lugar.
                        </p>
                    </div>
                </div>

                <!-- Coluna Direita (Formulário) -->
                <div class="col-md-6 bg-white p-5 d-flex flex-column justify-content-center">
                    <div class="px-lg-4">
                        <h2 class="fw-bold text-dark mb-1" style="font-size: 2rem;">Bem-vindo de volta</h2>
                        <p class="text-secondary mb-4">Acesse com seu e-mail e senha</p>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger py-2 small rounded-3 mb-4" role="alert">
                                <?= htmlspecialchars($erro) ?>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" action="../index.php?controle=LoginController&metodo=autenticar" method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold small text-dark">E-mail</label>
                                <input type="email" class="form-control py-3 rounded-3" id="email" name="email" placeholder="maria.souza@email.com" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="senha" class="form-label fw-bold small text-dark">Senha</label>
                                <input type="password" class="form-control py-3 rounded-3" id="senha" name="senha" placeholder="••••••••••" required>
                            </div>

                            <!-- Alinhamento da Checkbox e do Link usando Flexbox -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" id="manterConectado" name="manter_conectado" value="1">
                                    <label class="form-check-label text-secondary small pt-1" for="manterConectado">
                                        Manter conectado
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none fw-bold small texto-link">Esqueci minha senha</a>
                            </div>

                            <input type="submit" class="btn btn-entrar text-white w-100 py-3 rounded-3 fw-bold mt-2" value="Entrar">
        
                            </input>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

   

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>