<?php

require_once __DIR__ . '/Controller.class.php';

/** Páginas públicas (não exigem login). */
class SiteController extends Controller
{
    public function professores(): void
    {
        $professores = (new ProfessorDAO())->listarParaSite();

        $logado     = !empty($_SESSION['id_usuario']);
        $linkPainel = Permissao::paginaInicial();

        $this->render('site_professores', compact('professores', 'logado', 'linkPainel'));
    }
}
