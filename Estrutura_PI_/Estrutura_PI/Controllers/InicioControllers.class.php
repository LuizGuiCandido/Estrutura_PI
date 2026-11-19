<?php

require_once __DIR__ . '/Controller.class.php';

class InicioController extends Controller
{
    /** Rota padrão: manda cada perfil para a sua página inicial (ou para o login). */
    public function inicio(): void
    {
        header('Location: ' . Permissao::paginaInicial());
        exit;
    }
}
