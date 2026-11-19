<?php

require_once __DIR__ . '/Controller.class.php';

class TurmaController extends Controller
{
    // ---------------------------- PÁGINAS (GET) ----------------------------

    public function listar(): void
    {
        Permissao::exigirProfessor();   // gestores e professores

        $isGestor  = $this->ehGestor();
        $usuarioId = $this->usuarioId();
        $busca     = $this->get('busca');
        $cursoF    = $this->getInt('curso');
        $profF     = $this->getInt('professor');

        $cursosFiltro = (new CursoDAO())->listarTodos();
        $profFiltro   = (new ProfessorDAO())->listarParaSelect();
        $turmas       = (new TurmaDAO())->listar(
            ['busca' => $busca, 'curso' => $cursoF, 'professor' => $profF],
            $isGestor,
            $usuarioId
        );

        $this->render('turmas', compact('isGestor', 'busca', 'cursoF', 'profF', 'cursosFiltro', 'profFiltro', 'turmas'));
    }

    public function formulario(): void
    {
        Permissao::exigirGestor();

        $idEditar  = $this->getInt('id');
        $turmaEdit = null;

        if ($idEditar > 0) {
            $turmaEdit = (new TurmaDAO())->buscarPorId($idEditar);
            if (!$turmaEdit) {
                $this->redirecionar('TurmaController', 'listar');
            }
        }

        $cursos      = (new CursoDAO())->listarTodos();
        $professores = (new ProfessorDAO())->listarParaSelect();
        $isEdicao    = ($turmaEdit !== null);
        $actionUrl   = self::url('TurmaController', $isEdicao ? 'editar' : 'cadastrar');

        $this->render('turma_form', compact('turmaEdit', 'cursos', 'professores', 'isEdicao', 'actionUrl'));
    }

    public function detalhe(): void
    {
        Permissao::exigirProfessor();

        $idTurma = $this->getInt('id');
        if ($idTurma <= 0) {
            $this->redirecionar('TurmaController', 'listar');
        }

        $dao   = new TurmaDAO();
        $turma = $dao->buscarDetalhe($idTurma);
        if (!$turma) {
            $this->redirecionar('TurmaController', 'listar');
        }

        $alunosMatriculados = $dao->listarAlunosMatriculados($idTurma);
        $isGestor           = $this->ehGestor();

        $this->render('turma_detalhe', compact('turma', 'alunosMatriculados', 'isGestor'));
    }

    // ---------------------------- AÇÕES (POST) ----------------------------

    public function cadastrar(): void
    {
        Permissao::exigirGestor();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new TurmaDAO())->inserir($this->turmaDoPost(0));
            $this->redirecionar('TurmaController', 'listar', ['sucesso' => 1]);
        }

        $this->redirecionar('TurmaController', 'formulario');
    }

    public function editar(): void
    {
        Permissao::exigirGestor();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new TurmaDAO())->atualizar($this->turmaDoPost((int) ($_POST['id_turma'] ?? 0)));
            $this->redirecionar('TurmaController', 'listar', ['sucesso' => 2]);
        }

        $this->redirecionar('TurmaController', 'listar');
    }

    public function excluir(int $id = 0): void
    {
        Permissao::exigirGestor();

        $idTurma = $id > 0 ? $id : (int) ($_GET['id'] ?? $_POST['id_turma'] ?? 0);

        if ($idTurma > 0) {
            (new TurmaDAO())->excluir($idTurma);
        }

        $this->redirecionar('TurmaController', 'listar', ['sucesso' => 3]);
    }

    private function turmaDoPost(int $id): Turma
    {
        return new Turma(
            id_turma: $id,
            horario_inicio: $_POST['horario_inicio'] ?? '',
            horario_termino: $_POST['horario_termino'] ?? '',
            local_aula: (int) ($_POST['local_aula'] ?? 0),
            data_inicio: $_POST['data_inicio'] ?? date('Y-m-d'),
            data_fim: !empty($_POST['data_fim']) ? $_POST['data_fim'] : null,
            curso_id: (int) ($_POST['curso_id'] ?? 0),
            professor_id: (int) ($_POST['professor_id'] ?? 0),
            estagiario_id: !empty($_POST['estagiario_id']) ? (int) $_POST['estagiario_id'] : null
        );
    }
}
