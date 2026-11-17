<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/../Models/CategoriaDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class CategoriaController
{
    public function salvar(): void
    {
        Permissao::exigirGestor();

        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $nome        = trim((string) ($_POST['nome'] ?? ''));

        if ($nome === '') {
            $_SESSION['erro'] = 'O nome da categoria é obrigatório.';
            header('Location: Views/cursos_categorias.php#categorias');
            exit;
        }

        $dao = new CategoriaDAO();
        if ($idCategoria > 0) {
            if ($dao->atualizar($idCategoria, $nome)) {
                $_SESSION['sucesso'] = 'Categoria atualizada com sucesso!';
            }
        } else {
            if ($dao->inserir($nome)) {
                $_SESSION['sucesso'] = 'Categoria cadastrada com sucesso!';
            }
        }

        header('Location: Views/cursos_categorias.php#categorias');
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new CategoriaDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Categoria removida com sucesso!';
            }
        }

        header('Location: Views/cursos_categorias.php#categorias');
        exit;
    }
}