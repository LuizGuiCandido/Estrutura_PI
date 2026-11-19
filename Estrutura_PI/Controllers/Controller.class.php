<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/Permissao.class.php';

// Carrega automaticamente qualquer classe da pasta Models (Aluno, AlunoDAO, Turma...).
spl_autoload_register(function (string $classe): void {
    foreach (["{$classe}.class.php", "{$classe}.php"] as $arquivo) {
        $caminho = __DIR__ . '/../Models/' . $arquivo;
        if (is_file($caminho)) {
            require_once $caminho;
            return;
        }
    }
});

/**
 * Classe base de todos os Controllers.
 *
 * Fluxo de uma página:  index.php -> Controller::metodo() -> DAO (Model) -> render(View)
 * A View só exibe os dados que o Controller entrega; nunca acessa o banco.
 */
abstract class Controller
{
    /** Carrega uma View (Views/<nome>.php) entregando $dados como variáveis. */
    protected function render(string $view, array $dados = []): void
    {
        $this->incluirView(__DIR__ . '/../Views/' . $view . '.php', $dados, $view . '.php');
    }

    // Escopo isolado: a View só enxerga $dados (+ $viewAtual, usado pelo menu lateral).
    private function incluirView(string $__arquivo, array $__dados, string $viewAtual): void
    {
        extract($__dados);
        require $__arquivo;
    }

    /** Monta a URL de uma rota: index.php?controle=X&metodo=Y&... */
    protected static function url(string $controle, string $metodo, array $params = []): string
    {
        $query = ['controle' => $controle, 'metodo' => $metodo] + $params;
        return 'index.php?' . http_build_query($query);
    }

    /** Redireciona para outra rota e encerra a requisição. */
    protected function redirecionar(string $controle, string $metodo, array $params = []): void
    {
        header('Location: ' . self::url($controle, $metodo, $params));
        exit;
    }

    protected function ehGestor(): bool
    {
        return ((int) ($_SESSION['gestor'] ?? 0) === 1);
    }

    protected function usuarioId(): int
    {
        return (int) ($_SESSION['id_usuario'] ?? 0);
    }

    /** Parâmetro GET como texto (já com trim). */
    protected function get(string $chave, string $padrao = ''): string
    {
        return trim((string) ($_GET[$chave] ?? $padrao));
    }

    /** Parâmetro GET como inteiro. */
    protected function getInt(string $chave): int
    {
        return (int) ($_GET[$chave] ?? 0);
    }
}
