<?php

require_once __DIR__ . '/../Models/Pagamento.class.php';
require_once __DIR__ . '/../Models/PagamentoDAO.class.php';

class PagamentoController
{
    public function listar(): array
    {
        $dao = new PagamentoDAO();
        return $dao->buscarTodos();
    }

    public function salvar(): void
    {
        $this->cadastrar();
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPagamento = (int)($_POST['id_pagamento'] ?? 0);
            $valorTotal  = (float)str_replace(',', '.', $_POST['valor_total'] ?? '0');

            $pagamento = new Pagamento(
                id_pagamento: $idPagamento,
                data_pagamento: $_POST['data_pagamento'] ?? date('Y-m-d'),
                valor_total: $valorTotal,
                matricula_id: !empty($_POST['matricula_id']) ? (int)$_POST['matricula_id'] : null,
                plano_id: (int)($_POST['plano_id'] ?? 0)
            );

            $dao = new PagamentoDAO();

            if ($idPagamento > 0) {
                $dao->alterar($pagamento);
                header('Location: Views/pagamentos.php?sucesso=2');
            } else {
                $dao->inserir($pagamento);
                header('Location: Views/pagamentos.php?sucesso=1');
            }
            exit;
        }

        header('Location: Views/pagamento_form.php');
        exit;
    }

    public function editar(): void
    {
        $this->cadastrar();
    }

    public function excluir(int $id = 0): void
    {
        $idPagamento = $id > 0 ? $id : (int)($_GET['id'] ?? $_POST['id_pagamento'] ?? 0);

        if ($idPagamento > 0) {
            $dao = new PagamentoDAO();
            $dao->excluir($idPagamento);
        }

        header('Location: Views/pagamentos.php?sucesso=3');
        exit;
    }
}