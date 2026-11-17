<?php

class MatriculaDAO extends Conexao
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

    public function inserir(array $dados): bool
    {
        $this->verificarConexao();
        try {
            $sql = "INSERT INTO matriculas (status_matricula, segunda, terca, quarta, quinta, sexta, aluno_id, turma_id, data_matricula)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['status_matricula'],
                $dados['segunda'],
                $dados['terca'],
                $dados['quarta'],
                $dados['quinta'],
                $dados['sexta'],
                $dados['aluno_id'],
                $dados['turma_id'] > 0 ? $dados['turma_id'] : null,
                $dados['data_matricula']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar matrícula: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(array $dados): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE matriculas 
                    SET status_matricula = ?, segunda = ?, terca = ?, quarta = ?, quinta = ?, sexta = ?, aluno_id = ?, turma_id = ?, data_matricula = ?
                    WHERE id_matricula = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['status_matricula'],
                $dados['segunda'],
                $dados['terca'],
                $dados['quarta'],
                $dados['quinta'],
                $dados['sexta'],
                $dados['aluno_id'],
                $dados['turma_id'] > 0 ? $dados['turma_id'] : null,
                $dados['data_matricula'],
                $dados['id_matricula']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar matrícula: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE matriculas SET deletado_em = NOW() WHERE id_matricula = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}