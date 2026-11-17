<?php

class ReposicaoDAO extends Conexao
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
            $sql = "INSERT INTO reposicao (matricula_id, data_reposicao, horario, status_reposicao, observacao)
                    VALUES (?, ?, ?, ?, ?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['matricula_id'],
                $dados['data_reposicao'],
                $dados['horario'],
                $dados['status_reposicao'],
                $dados['observacao']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar reposição: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(array $dados): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE reposicao 
                    SET matricula_id = ?, data_reposicao = ?, horario = ?, status_reposicao = ?, observacao = ?
                    WHERE id_reposicao = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['matricula_id'],
                $dados['data_reposicao'],
                $dados['horario'],
                $dados['status_reposicao'],
                $dados['observacao'],
                $dados['id_reposicao']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar reposição: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE reposicao SET deletado_em = NOW() WHERE id_reposicao = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}