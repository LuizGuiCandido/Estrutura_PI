<?php

require_once __DIR__ . '/../Models/Plano.class.php';
require_once __DIR__ . '/../Models/PlanoDAO.class.php';

class PlanoController
{
    public function listar(): array
    {
        $dao = new PlanoDAO();
        return $dao->buscarTodos();
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plano = new Plano(
                id_plano: 0,
                periodo: $_POST['periodo'] ?? 'mensal',
                valor_aula: (float)str_replace(',', '.', $_POST['valor_aula'] ?? '0'),
                meio_pagamento: $_POST['meio_pagamento'] ?? 'à vista'
            );

            $dao = new PlanoDAO();
            $dao->inserir($plano);

            header('Location: Views/planos.php?sucesso=1');
            exit;
        }

        header('Location: Views/plano_form.php');
        exit;
    }

    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plano = new Plano(
                id_plano: (int)($_POST['id_plano'] ?? 0),
                periodo: $_POST['periodo'] ?? 'mensal',
                valor_aula: (float)str_replace(',', '.', $_POST['valor_aula'] ?? '0'),
                meio_pagamento: $_POST['meio_pagamento'] ?? 'à vista'
            );

            $dao = new PlanoDAO();
            $dao->alterar($plano);

            header('Location: Views/planos.php?sucesso=2');
            exit;
        }

        header('Location: Views/planos.php');
        exit;
    }

    public function excluir(int $id = 0): void
    {
        $idPlano = $id > 0 ? $id : (int)($_GET['id'] ?? $_POST['id_plano'] ?? 0);

        if ($idPlano > 0) {
            $dao = new PlanoDAO();
            $dao->excluir($idPlano);
        }

        header('Location: Views/planos.php?sucesso=3');
        exit;
    }
}