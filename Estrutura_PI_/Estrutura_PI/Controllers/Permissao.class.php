<?php

class Permissao
{
    private const URL_LOGIN = 'index.php?controle=LoginController&metodo=entrar';
    private const URL_SAIR  = 'index.php?controle=LoginController&metodo=sair';

    /** Página inicial de cada perfil (usada após o login e na rota padrão). */
    public static function paginaInicial(): string
    {
        if (empty($_SESSION['id_usuario'])) {
            return self::URL_LOGIN;
        }
        if ((int) ($_SESSION['gestor'] ?? 0) === 1) {
            return 'index.php?controle=DashboardController&metodo=index';
        }
        if (($_SESSION['perfil'] ?? '') === 'professor') {
            return 'index.php?controle=ProfessorController&metodo=meuDia';
        }
        if (($_SESSION['perfil'] ?? '') === 'aluno') {
            return 'index.php?controle=AlunoController&metodo=agenda';
        }
        return self::URL_LOGIN;
    }

    public static function exigirLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            self::ir(self::URL_LOGIN);
        }
    }

    public static function exigirGestor(): void
    {
        self::exigirLogin();

        if ((int) ($_SESSION['gestor'] ?? 0) !== 1) {
            $perfil = $_SESSION['perfil'] ?? '';

            if ($perfil === 'professor') {
                self::ir('index.php?controle=TurmaController&metodo=listar');
            } elseif ($perfil === 'aluno') {
                self::ir('index.php?controle=AlunoController&metodo=perfil');
            }
            self::ir(self::URL_SAIR);
        }
    }

    public static function exigirProfessor(): void
    {
        self::exigirLogin();

        $isGestor    = ((int) ($_SESSION['gestor'] ?? 0) === 1);
        $isProfessor = (($_SESSION['perfil'] ?? '') === 'professor');

        if (!$isGestor && !$isProfessor) {
            if (($_SESSION['perfil'] ?? '') === 'aluno') {
                self::ir('index.php?controle=AlunoController&metodo=perfil');
            }
            self::ir(self::URL_SAIR);
        }
    }

    public static function exigirAluno(): void
    {
        self::exigirLogin();

        $isGestor = ((int) ($_SESSION['gestor'] ?? 0) === 1);
        $isAluno  = (($_SESSION['perfil'] ?? '') === 'aluno');

        if (!$isGestor && !$isAluno) {
            self::ir(self::URL_SAIR);
        }
    }

    private static function ir(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
