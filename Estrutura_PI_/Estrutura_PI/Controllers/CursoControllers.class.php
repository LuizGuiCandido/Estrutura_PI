<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/CursoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class CursoController extends Controller
{
    public function salvar(): void
    {
        Permissao::exigirGestor();

        $idCurso = (int) ($_POST['id_curso'] ?? 0);
        $nome    = trim((string) ($_POST['nome'] ?? ''));

        if ($nome === '') {
            $_SESSION['erro'] = 'O nome do curso é obrigatório.';
            header('Location: index.php?controle=CursoController&metodo=listar');
            exit;
        }

        $dao = new CursoDAO();
        if ($idCurso > 0) {
            if ($dao->atualizar($idCurso, $nome)) {
                $_SESSION['sucesso'] = 'Curso atualizado com sucesso!';
            }
        } else {
            if ($dao->inserir($nome)) {
                $_SESSION['sucesso'] = 'Curso cadastrado com sucesso!';
            }
        }

        header('Location: index.php?controle=CursoController&metodo=listar');
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new CursoDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Curso removido com sucesso!';
            }
        }

        header('Location: index.php?controle=CursoController&metodo=listar');
        exit;
    }

    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirGestor();

        $cursos     = (new CursoDAO())->listarComTotalTurmas();
        $categorias = (new CategoriaDAO())->listarTodos();

        $this->render('cursos_categorias', compact('cursos', 'categorias'));
    }
}
