<?php

require_once __DIR__ . '/Controller.class.php';

class PlanoController extends Controller
{
    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirGestor();

        $planos = (new PlanoDAO())->buscarTodos();

        $this->render('planos', compact('planos'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $idEditar  = $this->getInt('id');
        $planoEdit = null;

        if ($idEditar > 0) {
            $planoEdit = (new PlanoDAO())->buscarPorId($idEditar);
            if (!$planoEdit) {
                $this->redirecionar('PlanoController', 'listar');
            }
        }

        $isEdicao  = ($planoEdit !== null);
        $actionUrl = self::url('PlanoController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('plano_form', compact('planoEdit', 'isEdicao', 'actionUrl'));
    }

    // ---------------------------- AÇÕES (POST) ----------------------------

    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new PlanoDAO())->inserir($this->planoDoPost(0));
            $this->redirecionar('PlanoController', 'listar', ['sucesso' => 1]);
        }

        $this->redirecionar('PlanoController', 'formulario');
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new PlanoDAO())->alterar($this->planoDoPost((int) ($_POST['id_plano'] ?? 0)));
            $this->redirecionar('PlanoController', 'listar', ['sucesso' => 2]);
        }

        $this->redirecionar('PlanoController', 'listar');
    }

    public function excluir(int $id = 0): void
    {
        Permissao::exigirGestor();

        $idPlano = $id > 0 ? $id : (int) ($_GET['id'] ?? $_POST['id_plano'] ?? 0);

        if ($idPlano > 0) {
            (new PlanoDAO())->excluir($idPlano);
        }

        $this->redirecionar('PlanoController', 'listar', ['sucesso' => 3]);
    }

    private function planoDoPost(int $id): Plano
    {
        return new Plano(
            id_plano: $id,
            periodo: $_POST['periodo'] ?? 'mensal',
            valor_aula: (float) str_replace(',', '.', $_POST['valor_aula'] ?? '0'),
            meio_pagamento: $_POST['meio_pagamento'] ?? 'à vista'
        );
    }
}
