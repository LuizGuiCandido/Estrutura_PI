<?php

class Permissao
{
    public static function exigirLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            header('Location: PaginaLogin.php');
            exit;
        }
    }

    public static function exigirGestor(): void
    {
        self::exigirLogin();

        if ((int) ($_SESSION['gestor'] ?? 0) !== 1) {
            $perfil = $_SESSION['perfil'] ?? '';

            if ($perfil === 'professor') {
                header('Location: turmas.php');
            } elseif ($perfil === 'aluno') {
                header('Location: aluno_perfil.php');
            } else {
                header('Location: ../index.php?controle=LoginController&metodo=sair');
            }
            exit;
        }
    }

    public static function exigirProfessor(): void
    {
        self::exigirLogin();

        $isGestor    = ((int) ($_SESSION['gestor'] ?? 0) === 1);
        $isProfessor = (($_SESSION['perfil'] ?? '') === 'professor');

        if (!$isGestor && !$isProfessor) {
            if (($_SESSION['perfil'] ?? '') === 'aluno') {
                header('Location: aluno_perfil.php');
            } else {
                header('Location: ../index.php?controle=LoginController&metodo=sair');
            }
            exit;
        }
    }

    public static function exigirAluno(): void
    {
        self::exigirLogin();

        $isGestor = ((int) ($_SESSION['gestor'] ?? 0) === 1);
        $isAluno  = (($_SESSION['perfil'] ?? '') === 'aluno');

        if (!$isGestor && !$isAluno) {
            header('Location: ../index.php?controle=LoginController&metodo=sair');
            exit;
        }
    }
}