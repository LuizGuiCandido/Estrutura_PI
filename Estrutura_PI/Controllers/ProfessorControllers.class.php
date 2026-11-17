<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/../Models/Usuario.class.php';
require_once __DIR__ . '/../Models/Professor.class.php';
require_once __DIR__ . '/../Models/ProfessorDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class ProfessorController
{
    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        $cpf = mb_substr(trim((string) ($_POST['cpf'] ?? '')), 0, 14);
        $crefRaw = trim((string) ($_POST['cref'] ?? ''));
        $cref = $crefRaw !== '' ? mb_substr($crefRaw, 0, 10) : null;
        $mostrar = isset($_POST['exibir_site']) ? 1 : 0;

        $usuario = new Usuario(
            null,
            trim((string) ($_POST['nome'] ?? '')),
            trim((string) ($_POST['email'] ?? '')),
            $cpf,
            password_hash((string) ($_POST['senha'] ?? ''), PASSWORD_DEFAULT),
            mb_substr(trim((string) ($_POST['telefone'] ?? '')), 0, 15),
            trim((string) ($_POST['data_nasc'] ?? '')),
            $this->montarEndereco($_POST),
            0
        );

        $professor = new Professor(
            $usuario,
            $cref,
            trim((string) ($_POST['formacao'] ?? 'Educação Física')),
            trim((string) ($_POST['especialidade'] ?? '')),
            $mostrar
        );

        $professorDAO = new ProfessorDAO();
        if ($professorDAO->cadastrar($professor)) {
            $_SESSION['sucesso'] = 'Professor cadastrado com sucesso!';
            header('Location: Views/professores.php');
        } else {
            header('Location: Views/professor_form.php');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: Views/professores.php');
            exit;
        }

        $senha = (string) ($_POST['senha'] ?? '');
        $alterarSenha = ($senha !== '');

        $cpf = mb_substr(trim((string) ($_POST['cpf'] ?? '')), 0, 14);
        $crefRaw = trim((string) ($_POST['cref'] ?? ''));
        $cref = $crefRaw !== '' ? mb_substr($crefRaw, 0, 10) : null;
        $mostrar = isset($_POST['exibir_site']) ? 1 : 0;
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
            0
        );

        $professor = new Professor(
            $usuario,
            $cref,
            trim((string) ($_POST['formacao'] ?? 'Educação Física')),
            trim((string) ($_POST['especialidade'] ?? '')),
            $mostrar
        );

        $professorDAO = new ProfessorDAO();
        if ($professorDAO->atualizar($professor, $alterarSenha)) {
            $_SESSION['sucesso'] = 'Dados do professor atualizados com sucesso!';
            header('Location: Views/professores.php');
        } else {
            header("Location: Views/professor_form.php?id={$idUsuario}");
        }
        exit;
    }

    public function alternarSite(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new ProfessorDAO();
            $dao->alternarMostrarSite($id);
            $_SESSION['sucesso'] = 'Visibilidade no site atualizada!';
        }

        header('Location: Views/professores.php');
        exit;
    }

    public function excluir(): void
    {
        Permissao::exigirGestor();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $dao = new ProfessorDAO();
            if ($dao->excluir($id)) {
                $_SESSION['sucesso'] = 'Professor removido com sucesso!';
            } else {
                $_SESSION['erro'] = 'Não foi possível remover este professor (verifique se ele está vinculado a alguma turma ativa).';
            }
        }

        header('Location: Views/professores.php');
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