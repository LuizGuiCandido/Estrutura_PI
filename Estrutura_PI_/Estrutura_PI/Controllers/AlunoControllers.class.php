<?php

require_once __DIR__ . '/Controller.class.php';
require_once __DIR__ . '/../Models/Usuario.class.php';
require_once __DIR__ . '/../Models/Aluno.class.php';
require_once __DIR__ . '/../Models/AlunoDAO.class.php';
require_once __DIR__ . '/Permissao.class.php';

class AlunoController extends Controller
{
    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        if ($senha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A senha e a confirmação de senha não conferem.';
            header('Location: index.php?controle=AlunoController&metodo=formulario');
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
            header('Location: index.php?controle=AlunoController&metodo=listar');
        } else {
            header('Location: index.php?controle=AlunoController&metodo=formulario');
        }
        exit;
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            header('Location: index.php?controle=AlunoController&metodo=listar');
            exit;
        }

        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
        $alterarSenha = ($senha !== '');

        if ($alterarSenha && $senha !== $confirmarSenha) {
            $_SESSION['erro'] = 'A senha e a confirmação de senha não conferem.';
            header("Location: index.php?controle=AlunoController&metodo=formulario&id={$idUsuario}");
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
            header('Location: index.php?controle=AlunoController&metodo=listar');
        } else {
            header("Location: index.php?controle=AlunoController&metodo=formulario&id={$idUsuario}");
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

        header('Location: index.php?controle=AlunoController&metodo=listar');
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
        Permissao::exigirProfessor();

        $isGestor  = $this->ehGestor();
        $usuarioId = $this->usuarioId();
        $busca     = $this->get('busca');
        $turmaF    = $this->getInt('turma');
        $situacao  = $this->get('situacao');

        $turmasFiltro = (new TurmaDAO())->listarParaSelect();
        $alunos       = (new AlunoDAO())->listar(
            ['busca' => $busca, 'turma' => $turmaF, 'situacao' => $situacao],
            $isGestor,
            $usuarioId
        );

        $this->render('alunos', compact('isGestor', 'busca', 'turmaF', 'situacao', 'turmasFiltro', 'alunos'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $dao       = new AlunoDAO();
        $idEditar  = $this->getInt('id');
        $alunoEdit = null;

        if ($idEditar > 0) {
            $alunoEdit = $dao->buscarParaEdicao($idEditar);
            if (!$alunoEdit) {
                $this->redirecionar('AlunoController', 'listar');
            }
        }

        $responsaveis = $dao->listarResponsaveis($idEditar);
        $isEdicao     = ($alunoEdit !== null);
        $actionUrl    = self::url('AlunoController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('aluno_form', compact('alunoEdit', 'responsaveis', 'isEdicao', 'actionUrl'));
    }

    public function detalhe(): void
    {
        Permissao::exigirProfessor();

        $idAluno  = $this->getInt('id');
        $isGestor = $this->ehGestor();

        if ($idAluno <= 0) {
            $this->redirecionar('AlunoController', 'listar');
        }

        $aluno = (new AlunoDAO())->buscarDetalhe($idAluno);
        if (!$aluno) {
            $this->redirecionar('AlunoController', 'listar');
        }

        $idadeTexto = '';
        if (!empty($aluno->data_nasc)) {
            $nasc       = new DateTime($aluno->data_nasc);
            $idade      = $nasc->diff(new DateTime())->y;
            $idadeTexto = $nasc->format('d/m/Y') . " ({$idade} anos)";
        }

        $matriculas = (new MatriculaDAO())->listarPorAluno($idAluno);
        $contatos   = (new ContatoDAO())->listarPorAluno($idAluno);
        $reposicoes = (new ReposicaoDAO())->listarPorAluno($idAluno);
        $pagamentos = $isGestor ? (new PagamentoDAO())->listarPorAluno($idAluno) : [];   // só o gestor vê pagamentos

        $this->render('aluno_detalhe', compact(
            'aluno', 'idadeTexto', 'isGestor', 'matriculas', 'contatos', 'reposicoes', 'pagamentos'
        ));
    }

    /** Agenda semanal (grade de aulas + reposições) do aluno, responsável ou gestor. */
    public function agenda(): void
    {
        Permissao::exigirLogin();

        $usuarioIdLogado = $this->usuarioId();
        $isGestor        = $this->ehGestor();

        // 1. Quais alunos o usuário logado pode visualizar
        $listaAlunos   = (new AlunoDAO())->listarVisiveis($isGestor, $usuarioIdLogado);
        $idsPermitidos = array_map(fn($al) => (int) $al->id_usuario, $listaAlunos);

        $alunoIdSelecionado = $this->getInt('aluno_id');
        if (!in_array($alunoIdSelecionado, $idsPermitidos, true)) {
            $alunoIdSelecionado = !empty($idsPermitidos) ? $idsPermitidos[0] : $usuarioIdLogado;
        }

        $nomeAlunoAtual = $_SESSION['nome'] ?? 'Aluno';
        foreach ($listaAlunos as $al) {
            if ((int) $al->id_usuario === $alunoIdSelecionado) {
                $nomeAlunoAtual = $al->nome;
                break;
            }
        }

        // 2. Semana selecionada (segunda a sexta)
        $dataBaseStr   = $this->get('semana', date('Y-m-d'));
        $timestampBase = strtotime($dataBaseStr) ?: time();
        $diaSemanaNum  = (int) date('N', $timestampBase);
        $segundaTs     = strtotime('-' . ($diaSemanaNum - 1) . ' days', $timestampBase);
        $sextaTs       = strtotime('+4 days', $segundaTs);

        $semanaAnterior = date('Y-m-d', strtotime('-7 days', $segundaTs));
        $proximaSemana  = date('Y-m-d', strtotime('+7 days', $segundaTs));

        $diasSemanaDatas = [
            1 => ['label' => 'Seg ' . date('d', $segundaTs), 'data' => date('Y-m-d', $segundaTs)],
            2 => ['label' => 'Ter ' . date('d', strtotime('+1 day', $segundaTs)), 'data' => date('Y-m-d', strtotime('+1 day', $segundaTs))],
            3 => ['label' => 'Qua ' . date('d', strtotime('+2 days', $segundaTs)), 'data' => date('Y-m-d', strtotime('+2 days', $segundaTs))],
            4 => ['label' => 'Qui ' . date('d', strtotime('+3 days', $segundaTs)), 'data' => date('Y-m-d', strtotime('+3 days', $segundaTs))],
            5 => ['label' => 'Sex ' . date('d', $sextaTs), 'data' => date('Y-m-d', $sextaTs)],
        ];

        $mesesPt = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
        $tituloSemana = 'Semana de ' . date('d', $segundaTs) . ' a ' . date('d', $sextaTs) . ' de ' . $mesesPt[(int) date('n', $sextaTs)];

        // 3. Grade e reposições do aluno selecionado
        $gradeHorarios = (new MatriculaDAO())->listarGradeAtiva($alunoIdSelecionado);
        $reposicoes    = (new ReposicaoDAO())->listarPorAluno($alunoIdSelecionado);

        // Reposições marcadas -> badge na grade: [data][hora] = curso
        $reposicoesSemana = [];
        foreach ($reposicoes as $rep) {
            if ($rep->status_reposicao === 'marcada') {
                $horaCurta = substr((string) $rep->horario, 0, 5);
                $reposicoesSemana[$rep->data_reposicao][$horaCurta] = $rep->curso_nome ?? 'Reposição';
            }
        }

        $this->render('aluno_agenda', compact(
            'listaAlunos', 'alunoIdSelecionado', 'nomeAlunoAtual', 'segundaTs', 'semanaAnterior', 'proximaSemana',
            'diasSemanaDatas', 'tituloSemana', 'gradeHorarios', 'reposicoes', 'reposicoesSemana'
        ));
    }

    /** "Meu perfil": dados do usuário logado, matrículas, dependentes e contatos de emergência. */
    public function perfil(): void
    {
        Permissao::exigirLogin();

        $usuarioId = $this->usuarioId();
        $dao       = new AlunoDAO();

        $usuario = $dao->buscarPerfil($usuarioId);
        if (!$usuario) {
            $this->redirecionar('LoginController', 'sair');
        }

        // CPF mascarado: só os dois últimos dígitos aparecem
        $cpfDigitos   = preg_replace('/\D/', '', (string) $usuario->cpf);
        $cpfMascarado = strlen($cpfDigitos) === 11
            ? '***.***.***-' . substr($cpfDigitos, -2)
            : htmlspecialchars((string) $usuario->cpf);

        $minhasMatriculas = (new MatriculaDAO())->listarMinhas($usuarioId);
        $dependentes      = $dao->listarDependentes($usuarioId);

        // Alunos elegíveis para o filtro de contatos: o próprio usuário (se for aluno) + dependentes
        $alunosContatos = [];
        if ($usuario->aula_experimental !== null) {
            $alunosContatos[] = (object) ['id_usuario' => $usuario->id_usuario, 'nome' => $usuario->nome];
        }
        foreach ($dependentes as $dep) {
            $alunosContatos[] = (object) ['id_usuario' => $dep->id_usuario, 'nome' => $dep->nome];
        }

        $alunoContatoId = $this->getInt('contato_aluno_id');
        if ($alunoContatoId <= 0 && !empty($alunosContatos)) {
            $alunoContatoId = (int) $alunosContatos[0]->id_usuario;
        }

        // Só lista contatos de alunos que realmente pertencem a este usuário
        $idsElegiveis       = array_map(fn($a) => (int) $a->id_usuario, $alunosContatos);
        $contatosEmergencia = (in_array($alunoContatoId, $idsElegiveis, true))
            ? (new ContatoDAO())->listarPorAluno($alunoContatoId)
            : [];

        $this->render('aluno_perfil', compact(
            'usuario', 'cpfMascarado', 'minhasMatriculas', 'dependentes',
            'alunosContatos', 'alunoContatoId', 'contatosEmergencia'
        ));
    }
}
