<?php

class CursoDAO extends Conexao
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

    public function inserir(string $nome): bool
    {
        $this->verificarConexao();
        try {
            $sql = "INSERT INTO cursos (nome) VALUES (?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$nome]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar curso: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(int $idCurso, string $nome): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE cursos SET nome = ? WHERE id_curso = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$nome, $idCurso]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar curso: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $idCurso): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE cursos SET deletado_em = NOW() WHERE id_curso = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$idCurso]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao excluir curso: " . $e->getMessage();
            return false;
        }
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id_curso, nome FROM cursos WHERE deletado_em IS NULL ORDER BY nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function listarComTotalTurmas(): array
    {
        $sql = "SELECT c.id_curso, c.nome, COUNT(t.id_turma) AS total_turmas
                FROM cursos c
                LEFT JOIN turmas t ON t.curso_id = c.id_curso AND t.deletado_em IS NULL
                WHERE c.deletado_em IS NULL
                GROUP BY c.id_curso, c.nome
                ORDER BY c.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }
}
