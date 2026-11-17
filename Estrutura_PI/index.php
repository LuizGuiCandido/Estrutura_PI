<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não houver parâmetros de rota na URL, redireciona para a tela inicial ou login
if (empty($_GET['controle']) || empty($_GET['metodo'])) {
    if (!empty($_SESSION['id_usuario'])) {
        if ((int) ($_SESSION['gestor'] ?? 0) === 1) {
            header('Location: Views/dashboard.php');
        } elseif (($_SESSION['perfil'] ?? '') === 'professor') {
            header('Location: Views/prof_meu_dia.php');
        } else {
            header('Location: Views/aluno_agenda.php');
        }
    } else {
        header('Location: Views/PaginaLogin.php');
    }
    exit;
}

$controle = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['controle']);
$metodo   = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['metodo']);

// Busca o arquivo no singular (ex: LoginController.class.php) ou no plural (ex: LoginControllers.class.php)
$caminhoSingular = __DIR__ . "/Controllers/{$controle}.class.php";
$caminhoPlural   = __DIR__ . "/Controllers/{$controle}s.class.php";

if (file_exists($caminhoSingular)) {
    require_once $caminhoSingular;
} elseif (file_exists($caminhoPlural)) {
    require_once $caminhoPlural;
} else {
    die("Erro de roteamento: Arquivo do controller '{$controle}' não encontrado na pasta Controllers.");
}

if (!class_exists($controle)) {
    die("Erro de roteamento: A classe '{$controle}' não foi definida dentro do arquivo.");
}

$obj = new $controle();

if (!method_exists($obj, $metodo)) {
    die("Erro de roteamento: O método '{$metodo}' não existe no controller '{$controle}'.");
}

$obj->$metodo();