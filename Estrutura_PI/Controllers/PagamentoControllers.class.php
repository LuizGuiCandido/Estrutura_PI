<?php

require_once __DIR__ . '/Controller.class.php';

class PagamentoController extends Controller
{
    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirGestor();

        $busca    = $this->get('busca');
        $situacao = $this->get('situacao');
        $dao      = new PagamentoDAO();

        $pagamentos = $dao->listar($busca, $situacao);
        $atrasados  = $dao->listarInadimplentes();

        $totalFiltrado = 0.0;
        foreach ($pagamentos as $pag) {
            $totalFiltrado += (float) ($pag->valor_total ?? 0);
        }

        $totalEmAberto = 0.0;
        foreach ($atrasados as $atr) {
            $totalEmAberto += (float) ($atr->valor_devido ?? 0);
        }
        $qtdAtrasos = count($atrasados);

        $this->render('pagamentos', compact(
            'busca', 'situacao', 'pagamentos', 'totalFiltrado', 'atrasados', 'totalEmAberto', 'qtdAtrasos'
        ));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $matriculas = (new MatriculaDAO())->listarParaPagamento();
        $planos     = (new PlanoDAO())->buscarTodosAssoc();

        $pagamentoEdit        = null;
        $matriculaSelecionada = 0;
        $planoSelecionado     = 0;
        $dataHoje             = date('Y-m-d');

        $idEditar = $this->getInt('id');
        if ($idEditar > 0) {
            $pagamentoEdit = (new PagamentoDAO())->buscarPorId($idEditar);
            if (!$pagamentoEdit) {
                $this->redirecionar('PagamentoController', 'listar');
            }
            $matriculaSelecionada = (int) $pagamentoEdit->matricula_id;
            $planoSelecionado     = (int) $pagamentoEdit->plano_id;
            $dataHoje             = $pagamentoEdit->data_pagamento;
        } else {
            // Vindo da tela do aluno: pré-seleciona a matrícula dele
            $alunoPre = $this->getInt('aluno_id');
            foreach ($matriculas as $m) {
                if ($alunoPre > 0 && (int) $m['aluno_id'] === $alunoPre) {
                    $matriculaSelecionada = (int) $m['id_matricula'];
                    break;
                }
            }
        }

        $this->render('pagamento_form', compact(
            'matriculas', 'planos', 'dataHoje', 'pagamentoEdit', 'matriculaSelecionada', 'planoSelecionado'
        ));
    }

    // ---------------------------- AÇÕES (POST) ----------------------------

    public function salvar(): void
    {
        $this->cadastrar();
    }

    public function editar(): void
    {
        $this->cadastrar();
    }

    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('PagamentoController', 'formulario');
        }

        $idPagamento = (int) ($_POST['id_pagamento'] ?? 0);

        $pagamento = new Pagamento(
            id_pagamento: $idPagamento,
            data_pagamento: $_POST['data_pagamento'] ?? date('Y-m-d'),
            valor_total: (float) str_replace(',', '.', $_POST['valor_total'] ?? '0'),
            matricula_id: !empty($_POST['matricula_id']) ? (int) $_POST['matricula_id'] : null,
            plano_id: (int) ($_POST['plano_id'] ?? 0)
        );

        $dao = new PagamentoDAO();

        if ($idPagamento > 0) {
            $ok = $dao->alterar($pagamento);
            $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Pagamento atualizado com sucesso!' : 'Não foi possível atualizar o pagamento.';
        } else {
            $ok = $dao->inserir($pagamento);
            $_SESSION[$ok ? 'sucesso' : 'erro'] = $ok ? 'Pagamento registrado com sucesso!' : 'Não foi possível registrar o pagamento.';
        }

        $this->redirecionar('PagamentoController', 'listar');
    }

    public function excluir(int $id = 0): void
    {
        Permissao::exigirGestor();

        $idPagamento = $id > 0 ? $id : (int) ($_GET['id'] ?? $_POST['id_pagamento'] ?? 0);

        if ($idPagamento > 0 && (new PagamentoDAO())->excluir($idPagamento)) {
            $_SESSION['sucesso'] = 'Pagamento excluído com sucesso!';
        }

        $this->redirecionar('PagamentoController', 'listar');
    }
}
