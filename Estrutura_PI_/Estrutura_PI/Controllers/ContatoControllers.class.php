<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/ContatoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class ContatoController extends Controller
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
        
        header("Location: index.php?controle=AlunoController&metodo=detalhe&id={$alunoId}");
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

        header("Location: index.php?controle=AlunoController&metodo=detalhe&id={$alunoId}");
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
            header("Location: index.php?controle=AlunoController&metodo=detalhe&id={$alunoId}");
        } else {
            header("Location: index.php?controle=AlunoController&metodo=listar");
        }
        exit;
    }

    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function formulario(): void
    {
        Permissao::exigirProfessor();

        $dao         = new ContatoDAO();
        $idEditar    = $this->getInt('id');
        $alunoIdPre  = $this->getInt('aluno_id');
        $contatoEdit = null;

        if ($idEditar > 0) {
            $contatoEdit = $dao->buscarPorId($idEditar);
            if (!$contatoEdit) {
                $this->redirecionar('AlunoController', 'listar');
            }
            $alunoIdPre = (int) $contatoEdit->aluno_id;
        }

        if ($alunoIdPre <= 0) {
            $this->redirecionar('AlunoController', 'listar');
        }

        $alunoVinculado = $dao->buscarAlunoVinculado($alunoIdPre);
        if (!$alunoVinculado) {
            $this->redirecionar('AlunoController', 'listar');
        }

        $isEdicao  = ($contatoEdit !== null);
        $actionUrl = self::url('ContatoController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('contato_form', compact('contatoEdit', 'alunoVinculado', 'isEdicao', 'actionUrl'));
    }
}
