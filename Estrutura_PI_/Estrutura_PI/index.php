<?php
// Front controller: toda página do sistema passa por aqui.
//   index.php?controle=NomeController&metodo=nomeDoMetodo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controle = preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($_GET['controle'] ?? ''));
$metodo   = preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($_GET['metodo'] ?? ''));

// Sem rota na URL: InicioController decide a página conforme o perfil (ou manda ao login).
if ($controle === '' || $metodo === '') {
    $controle = 'InicioController';
    $metodo   = 'inicio';
}

if ($controle === 'Controller' || !str_ends_with($controle, 'Controller')) {
    die("Erro de roteamento: '{$controle}' não é um controller válido.");
}

// Busca o arquivo no singular (LoginController.class.php) ou no plural (AlunoControllers.class.php)
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

// is_callable garante que só métodos públicos podem ser chamados pela URL
if (!is_callable([$obj, $metodo])) {
    die("Erro de roteamento: O método '{$metodo}' não existe (ou não é público) no controller '{$controle}'.");
}

$obj->$metodo();
