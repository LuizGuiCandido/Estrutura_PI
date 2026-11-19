<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/MatriculaDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class MatriculaController extends Controller
{
    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        $dados = [
            'status_matricula' => (int) ($_POST['status_matricula'] ?? 1),
            'segunda'          => isset($_POST['segunda']) ? 1 : 0,
            'terca'            => isset($_POST['terca']) ? 1 : 0,
            'quarta'           => isset($_POST['quarta']) ? 1 : 0,
            'quinta'           => isset($_POST['quinta']) ? 1 : 0,
            'sexta'            => isset($_POST['sexta']) ? 1 : 0,
            'aluno_id'         => (int) ($_POST['aluno_id'] ?? 0),
            'turma_id'         => (int) ($_POST['turma_id'] ?? 0),
            'data_matricula'   => (string) ($_POST['data_matricula'] ?? date('Y-m-d'))
        ];

        $dao = new MatriculaDAO();
        if ($dao->inserir($dados)) {
            $_SESSION['sucesso'] = 'Matrícula efetuada com sucesso!';
            header('Location: index.php?controle=MatriculaController&metodo=listar');
        } else {
            header('Location: index.php?controle=MatriculaController&metodo=formulario');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idMatricula = (int) ($_POST['id_matricula'] ?? 0);

        $dados = [
            'id_matricula'     => $idMatricula,
            'status_matricula' => (int) ($_POST['status_matricula'] ?? 1),
            'segunda'          => isset($_POST['segunda']) ? 1 : 0,
            'terca'            => isset($_POST['terca']) ? 1 : 0,
            'quarta'           => isset($_POST['quarta']) ? 1 : 0,
            'quinta'           => isset($_POST['quinta']) ? 1 : 0,
            'sexta'            => isset($_POST['sexta']) ? 1 : 0,
            'aluno_id'         => (int) ($_POST['aluno_id'] ?? 0),
            'turma_id'         => (int) ($_POST['turma_id'] ?? 0),
            'data_matricula'   => (string) ($_POST['data_matricula'] ?? date('Y-m-d'))
        ];

        $dao = new MatriculaDAO();
        if ($dao->atualizar($dados)) {
            $_SESSION['sucesso'] = 'Matrícula atualizada com sucesso!';
            header('Location: index.php?controle=MatriculaController&metodo=listar');
        } else {
            header("Location: index.php?controle=MatriculaController&metodo=formulario&id={$idMatricula}");
        }
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new MatriculaDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Matrícula removida com sucesso!';
            } else {
                $_SESSION['erro'] = 'Não foi possível remover esta matrícula.';
            }
        }

        header('Location: index.php?controle=MatriculaController&metodo=listar');
        exit;
    }

    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirProfessor();

        $isGestor  = $this->ehGestor();
        $busca     = $this->get('busca');
        $situacao  = $this->get('situacao');

        $matriculas = (new MatriculaDAO())->listar(
            ['busca' => $busca, 'situacao' => $situacao],
            $isGestor,
            $this->usuarioId()
        );

        $this->render('matriculas', compact('isGestor', 'busca', 'situacao', 'matriculas'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $idEditar            = $this->getInt('id');
        $alunoPreSelecionado = $this->getInt('aluno_id');
        $turmaPreSelecionada = $this->getInt('turma_id');
        $matEdit             = null;

        if ($idEditar > 0) {
            $matEdit = (new MatriculaDAO())->buscarPorId($idEditar);
            if (!$matEdit) {
                $this->redirecionar('MatriculaController', 'listar');
            }
        }

        $alunos    = (new AlunoDAO())->listarParaSelect();
        $turmas    = (new TurmaDAO())->listarParaSelect();
        $isEdicao  = ($matEdit !== null);
        $actionUrl = self::url('MatriculaController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('matricula_form', compact(
            'matEdit', 'alunoPreSelecionado', 'turmaPreSelecionada', 'alunos', 'turmas', 'isEdicao', 'actionUrl'
        ));
    }
}
