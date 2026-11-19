<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/ReposicaoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class ReposicaoController extends Controller
{
    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        $dados = [
            'matricula_id'     => (int) ($_POST['matricula_id'] ?? 0),
            'data_reposicao'   => (string) ($_POST['data_reposicao'] ?? date('Y-m-d')),
            'horario'          => (string) ($_POST['horario'] ?? ''),
            'status_reposicao' => trim((string) ($_POST['status_reposicao'] ?? 'marcada')),
            'observacao'       => trim((string) ($_POST['observacao'] ?? ''))
        ];

        $dao = new ReposicaoDAO();
        if ($dao->inserir($dados)) {
            $_SESSION['sucesso'] = 'Reposição agendada com sucesso!';
            header('Location: index.php?controle=ReposicaoController&metodo=listar');
        } else {
            header('Location: index.php?controle=ReposicaoController&metodo=formulario');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idReposicao = (int) ($_POST['id_reposicao'] ?? 0);

        $dados = [
            'id_reposicao'     => $idReposicao,
            'matricula_id'     => (int) ($_POST['matricula_id'] ?? 0),
            'data_reposicao'   => (string) ($_POST['data_reposicao'] ?? date('Y-m-d')),
            'horario'          => (string) ($_POST['horario'] ?? ''),
            'status_reposicao' => trim((string) ($_POST['status_reposicao'] ?? 'marcada')),
            'observacao'       => trim((string) ($_POST['observacao'] ?? ''))
        ];

        $dao = new ReposicaoDAO();
        if ($dao->atualizar($dados)) {
            $_SESSION['sucesso'] = 'Reposição atualizada com sucesso!';
            header('Location: index.php?controle=ReposicaoController&metodo=listar');
        } else {
            header("Location: index.php?controle=ReposicaoController&metodo=formulario&id={$idReposicao}");
        }
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new ReposicaoDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Reposição removida com sucesso!';
            } else {
                $_SESSION['erro'] = 'Não foi possível remover esta reposição.';
            }
        }

        header('Location: index.php?controle=ReposicaoController&metodo=listar');
        exit;
    }

    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirProfessor();

        $isGestor = $this->ehGestor();
        $busca    = $this->get('busca');
        $situacao = $this->get('situacao');

        $reposicoes = (new ReposicaoDAO())->listar(
            ['busca' => $busca, 'situacao' => $situacao],
            $isGestor,
            $this->usuarioId()
        );

        $this->render('reposicoes', compact('isGestor', 'busca', 'situacao', 'reposicoes'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $idEditar = $this->getInt('id');
        $repEdit  = null;

        if ($idEditar > 0) {
            $repEdit = (new ReposicaoDAO())->buscarPorId($idEditar);
            if (!$repEdit) {
                $this->redirecionar('ReposicaoController', 'listar');
            }
        }

        $matriculas = (new MatriculaDAO())->listarAtivasParaReposicao();
        $isEdicao   = ($repEdit !== null);
        $actionUrl  = self::url('ReposicaoController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('reposicao_form', compact('repEdit', 'matriculas', 'isEdicao', 'actionUrl'));
    }
}
