<?php

require_once __DIR__ . '/../Models/Conexao.php';
require_once __DIR__ . '/../Models/Turma.class.php';
require_once __DIR__ . '/../Models/TurmaDAO.class.php';

class TurmaController
{
    public function listar(): array
    {
        $dao = new TurmaDAO();
        return $dao->buscarTodos();
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turma = new Turma(
                id_turma: 0,
                horario_inicio: $_POST['horario_inicio'] ?? '',
                horario_termino: $_POST['horario_termino'] ?? '',
                local_aula: (int)($_POST['local_aula'] ?? 0),
                data_inicio: $_POST['data_inicio'] ?? date('Y-m-d'),
                data_fim: !empty($_POST['data_fim']) ? $_POST['data_fim'] : null,
                curso_id: (int)($_POST['curso_id'] ?? 0),
                professor_id: (int)($_POST['professor_id'] ?? 0),
                estagiario_id: !empty($_POST['estagiario_id']) ? (int)$_POST['estagiario_id'] : null
            );

            $dao = new TurmaDAO();
            $dao->inserir($turma);

            header('Location: Views/turmas.php?sucesso=1');
            exit;
        }

        header('Location: Views/turma_form.php');
        exit;
    }

    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turma = new Turma(
                id_turma: (int)($_POST['id_turma'] ?? 0),
                horario_inicio: $_POST['horario_inicio'] ?? '',
                horario_termino: $_POST['horario_termino'] ?? '',
                local_aula: (int)($_POST['local_aula'] ?? 0),
                data_inicio: $_POST['data_inicio'] ?? date('Y-m-d'),
                data_fim: !empty($_POST['data_fim']) ? $_POST['data_fim'] : null,
                curso_id: (int)($_POST['curso_id'] ?? 0),
                professor_id: (int)($_POST['professor_id'] ?? 0),
                estagiario_id: !empty($_POST['estagiario_id']) ? (int)$_POST['estagiario_id'] : null
            );

            $dao = new TurmaDAO();
            $dao->alterar($turma);

            header('Location: Views/turmas.php?sucesso=2');
            exit;
        }

        header('Location: Views/turmas.php');
        exit;
    }

    public function excluir(int $id = 0): void
    {
        $idTurma = $id > 0 ? $id : (int)($_GET['id'] ?? $_POST['id_turma'] ?? 0);

        if ($idTurma > 0) {
            $dao = new TurmaDAO();
            $dao->excluir($idTurma);
        }

        header('Location: Views/turmas.php?sucesso=3');
        exit;
    }
}