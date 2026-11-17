<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/../Models/ContatoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class ContatoController
{
    public function cadastrar(): void
    {
        Permissao::exigirProfessor();

        $alunoId = (int) ($_POST['aluno_id'] ?? 0);

        $dados = [
            'aluno_id'   => $alunoId,
            'nome'       => trim((string) ($_POST['nome'] ?? '')),
            'numero'     => trim((string) ($_POST['numero'] ?? '')),
            'parentesco' => trim((string) ($_POST['parentesco'] ?? 'Mãe')),
            'tipo'       => trim((string) ($_POST['tipo'] ?? 'Whatsapp'))
        ];

        $dao = new ContatoDAO();
        if ($dao->inserir($dados)) {
            $_SESSION['sucesso'] = 'Contato de emergência adicionado com sucesso!';
        }
        
        header("Location: Views/aluno_detalhe.php?id={$alunoId}");
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirProfessor();

        $idContato = (int) ($_POST['id_contato'] ?? 0);
        $alunoId = (int) ($_POST['aluno_id'] ?? 0);

        $dados = [
            'id_contato' => $idContato,
            'nome'       => trim((string) ($_POST['nome'] ?? '')),
            'numero'     => trim((string) ($_POST['numero'] ?? '')),
            'parentesco' => trim((string) ($_POST['parentesco'] ?? 'Mãe')),
            'tipo'       => trim((string) ($_POST['tipo'] ?? 'Whatsapp'))
        ];

        $dao = new ContatoDAO();
        if ($dao->atualizar($dados)) {
            $_SESSION['sucesso'] = 'Contato atualizado com sucesso!';
        }

        header("Location: Views/aluno_detalhe.php?id={$alunoId}");
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirProfessor();

        $id = (int) ($_GET['id'] ?? 0);
        $alunoId = (int) ($_GET['aluno_id'] ?? 0);

        if ($id > 0) {
            $dao = new ContatoDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Contato removido com sucesso!';
            } else {
                $_SESSION['erro'] = 'Não foi possível remover este contato.';
            }
        }

        if ($alunoId > 0) {
            header("Location: Views/aluno_detalhe.php?id={$alunoId}");
        } else {
            header("Location: Views/alunos.php");
        }
        exit;
    }
}