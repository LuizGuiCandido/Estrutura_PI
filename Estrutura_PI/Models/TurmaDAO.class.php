<?php

class TurmaDAO extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    private function verificarConexao(): void
    {
        if (!isset($this->db) || !($this->db instanceof PDO)) {
            parent::__construct();
        }
    }

    public function inserir(Turma $turma): bool
    {
        $this->verificarConexao();
        try {
            $sql = "INSERT INTO turmas (horario_inicio, horario_termino, local_aula, data_inicio, data_fim, curso_id, professor_id, estagiario_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $turma->getHorarioInicio(),
                $turma->getHorarioTermino(),
                $turma->getLocalAula(),
                $turma->getDataInicio(),
                $turma->getDataFim() ?: null,
                $turma->getCursoId(),
                $turma->getProfessorId(),
                $turma->getEstagiarioId() > 0 ? $turma->getEstagiarioId() : null
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar turma: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(Turma $turma): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE turmas
                    SET horario_inicio = ?, horario_termino = ?, local_aula = ?, data_inicio = ?, data_fim = ?,
                        curso_id = ?, professor_id = ?, estagiario_id = ?
                    WHERE id_turma = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $turma->getHorarioInicio(),
                $turma->getHorarioTermino(),
                $turma->getLocalAula(),
                $turma->getDataInicio(),
                $turma->getDataFim() ?: null,
                $turma->getCursoId(),
                $turma->getProfessorId(),
                $turma->getEstagiarioId() > 0 ? $turma->getEstagiarioId() : null,
                $turma->getIdTurma()
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar turma: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE turmas SET deletado_em = NOW() WHERE id_turma = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}