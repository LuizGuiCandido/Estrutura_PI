<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/../Models/Usuario.class.php';
require_once __DIR__ . '/../Models/Aluno.class.php';
require_once __DIR__ . '/../Models/AlunoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class AlunoController
{
    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        if ($senha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A senha e a confirmação de senha não conferem.';
            header('Location: Views/aluno_form.php');
            exit;
        }

        $cpf = mb_substr(trim((string) ($_POST['cpf'] ?? '')), 0, 14);
        $gestor = (int) ($_POST['gestor'] ?? 0);
        $responsavelId = (int) ($_POST['responsavel_id'] ?? 0);
        $responsavelId = $responsavelId > 0 ? $responsavelId : null;
        $aulaExperimental = isset($_POST['aula_experimental']) ? 1 : 0;
        $observacao = trim((string) ($_POST['observacoes'] ?? ''));

        $usuario = new Usuario(
            null,
            trim((string) ($_POST['nome'] ?? '')),
            trim((string) ($_POST['email'] ?? '')),
            $cpf,
            password_hash($senha, PASSWORD_DEFAULT),
            mb_substr(trim((string) ($_POST['telefone'] ?? '')), 0, 15),
            trim((string) ($_POST['data_nasc'] ?? '')),
            $this->montarEndereco($_POST),
            $gestor
        );

        $aluno = new Aluno($usuario, $aulaExperimental, $responsavelId, $observacao);
        $alunoDAO = new AlunoDAO();

        if ($alunoDAO->cadastrar($aluno)) {
            $_SESSION['sucesso'] = 'Aluno cadastrado com sucesso!';
            header('Location: Views/alunos.php');
        } else {
            header('Location: Views/aluno_form.php');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: Views/Aluno_Lista.php');
            exit;
        }

        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
        $alterarSenha = ($senha !== '');

        if ($alterarSenha && $senha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A senha e a confirmação de senha não conferem.';
            header("Location: Views/aluno_form.php?id={$idUsuario}");
            exit;
        }

        $cpf = mb_substr(trim((string) ($_POST['cpf'] ?? '')), 0, 14);
        $gestor = (int) ($_POST['gestor'] ?? 0);
        $responsavelId = (int) ($_POST['responsavel_id'] ?? 0);
        $responsavelId = $responsavelId > 0 ? $responsavelId : null;
        $aulaExperimental = isset($_POST['aula_experimental']) ? 1 : 0;
        $observacao = trim((string) ($_POST['observacoes'] ?? ''));
        $endereco = mb_substr(trim((string) ($_POST['endereco_completo'] ?? '')), 0, 255);

        $usuario = new Usuario(
            $idUsuario,
            trim((string) ($_POST['nome'] ?? '')),
            trim((string) ($_POST['email'] ?? '')),
            $cpf,
            $alterarSenha ? password_hash($senha, PASSWORD_DEFAULT) : '',
            mb_substr(trim((string) ($_POST['telefone'] ?? '')), 0, 15),
            trim((string) ($_POST['data_nasc'] ?? '')),
            $endereco,
            $gestor
        );

        $aluno = new Aluno($usuario, $aulaExperimental, $responsavelId, $observacao);
        $alunoDAO = new AlunoDAO();

        if ($alunoDAO->atualizar($aluno, $alterarSenha)) {
            $_SESSION['sucesso'] = 'Dados do aluno atualizados com sucesso!';
            header('Location: Views/Aluno_Lista.php');
        } else {
            header("Location: Views/aluno_form.php?id={$idUsuario}");
        }
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new AlunoDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Aluno removido com sucesso!';
            } else {
                $_SESSION['erro'] = 'Não foi possível remover este aluno.';
            }
        }

        header('Location: Views/Aluno_Lista.php');
        exit;
    }

    private function montarEndereco(array $post): string
    {
        $logradouro  = trim((string) ($post['logradouro'] ?? ''));
        $numero      = trim((string) ($post['numero'] ?? ''));
        $complemento = trim((string) ($post['complemento'] ?? ''));
        $bairro      = trim((string) ($post['bairro'] ?? ''));
        $cidade      = trim((string) ($post['cidade'] ?? ''));
        $cep         = trim((string) ($post['cep'] ?? ''));

        $endereco = "{$logradouro}, {$numero}";
        if ($complemento !== '') $endereco .= " ({$complemento})";
        $endereco .= " - {$bairro}, {$cidade}";
        if ($cep !== '') $endereco .= " - CEP: {$cep}";

        return mb_substr($endereco, 0, 255);
    }
}