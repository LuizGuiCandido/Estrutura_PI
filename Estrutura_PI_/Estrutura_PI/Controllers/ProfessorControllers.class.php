<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/Usuario.class.php';
require_once __DIR__ . '/../Models/Professor.class.php';
require_once __DIR__ . '/../Models/ProfessorDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class ProfessorController extends Controller
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
            header('Location: index.php?controle=ProfessorController&metodo=listar');
        } else {
            header('Location: index.php?controle=ProfessorController&metodo=formulario');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: index.php?controle=ProfessorController&metodo=listar');
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
            header('Location: index.php?controle=ProfessorController&metodo=listar');
        } else {
            header("Location: index.php?controle=ProfessorController&metodo=formulario&id={$idUsuario}");
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

        header('Location: index.php?controle=ProfessorController&metodo=listar');
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

        header('Location: index.php?controle=ProfessorController&metodo=listar');
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

    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirGestor();

        $busca    = $this->get('busca');
        $formacao = $this->get('formacao');
        $mostrarF = $this->get('mostrar');

        $professores = (new ProfessorDAO())->listar(['busca' => $busca, 'formacao' => $formacao, 'mostrar' => $mostrarF]);

        $this->render('professores', compact('busca', 'formacao', 'mostrarF', 'professores'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $idEditar = $this->getInt('id');
        $profEdit = null;

        if ($idEditar > 0) {
            $profEdit = (new ProfessorDAO())->buscarPorId($idEditar);
            if (!$profEdit) {
                $this->redirecionar('ProfessorController', 'listar');
            }
        }

        $isEdicao  = ($profEdit !== null);
        $actionUrl = self::url('ProfessorController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('professor_form', compact('profEdit', 'isEdicao', 'actionUrl'));
    }

    public function detalhe(): void
    {
        Permissao::exigirGestor();

        $idProf = $this->getInt('id');
        if ($idProf <= 0) {
            $this->redirecionar('ProfessorController', 'listar');
        }

        $prof = (new ProfessorDAO())->buscarPorId($idProf);
        if (!$prof) {
            $this->redirecionar('ProfessorController', 'listar');
        }

        $turmas = (new TurmaDAO())->listarPorProfessor($idProf);

        $this->render('professor_detalhe', compact('idProf', 'prof', 'turmas'));
    }

    /** "Meu dia": turmas do professor logado (gestor vê todas). */
    public function meuDia(): void
    {
        Permissao::exigirProfessor();

        $diasPt = [
            1 => 'Segunda-feira', 2 => 'Terça-feira', 3 => 'Quarta-feira', 4 => 'Quinta-feira',
            5 => 'Sexta-feira', 6 => 'Sábado', 7 => 'Domingo',
        ];
        $mesesPt = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho',
            7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
        $nomeDiaHoje = $diasPt[(int) date('N')] . ', ' . date('d') . ' de ' . $mesesPt[(int) date('n')] . ' de ' . date('Y');

        $aulasHoje    = (new TurmaDAO())->listarDoDia($this->usuarioId(), $this->ehGestor());
        $horaAtualStr = date('H:i:s');

        $this->render('prof_meu_dia', compact('nomeDiaHoje', 'aulasHoje', 'horaAtualStr'));
    }
}
