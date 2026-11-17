<?php

class ContatoDAO extends Conexao
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
            $sql = "INSERT INTO contatos_emergencia (aluno_id, nome, numero, parentesco, tipo)
                    VALUES (?, ?, ?, ?, ?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['aluno_id'],
                $dados['nome'],
                $dados['numero'],
                $dados['parentesco'],
                $dados['tipo']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar contato: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(array $dados): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE contatos_emergencia 
                    SET nome = ?, numero = ?, parentesco = ?, tipo = ?
                    WHERE id_contato = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([
                $dados['nome'],
                $dados['numero'],
                $dados['parentesco'],
                $dados['tipo'],
                $dados['id_contato']
            ]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar contato: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE contatos_emergencia SET deletado_em = NOW() WHERE id_contato = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}