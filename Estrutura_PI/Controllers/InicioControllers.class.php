<?php

class inicioController
{
    public function inicio(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['id_usuario'])) {
            if ((int) ($_SESSION['gestor'] ?? 0) === 1) {
                header('Location: Views/dashboard.php');
                exit;
            }
            if (($_SESSION['perfil'] ?? '') === 'professor') {
                header('Location: Views/turmas.php');
                exit;
            }
            if (($_SESSION['perfil'] ?? '') === 'aluno') {
                header('Location: Views/area_aluno.php');
                exit;
            }
        }

        header('Location: Views/PaginaLogin.php');
        exit;
    }
}