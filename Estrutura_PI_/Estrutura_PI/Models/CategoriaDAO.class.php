<?php

class CategoriaDAO extends Conexao
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
            $sql = "INSERT INTO categorias (nome) VALUES (?)";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$nome]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar categoria: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(int $idCategoria, string $nome): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE categorias SET nome = ? WHERE id_categoria = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$nome, $idCategoria]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao atualizar categoria: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $idCategoria): bool
    {
        $this->verificarConexao();
        try {
            $sql = "UPDATE categorias SET deletado_em = NOW() WHERE id_categoria = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$idCategoria]);
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao excluir categoria: " . $e->getMessage();
            return false;
        }
    }

    public function listarTodos(): array
    {
        $this->verificarConexao();
        $sql = "SELECT cat.id_categoria, cat.nome, 0 AS total_turmas
                FROM categorias cat
                WHERE cat.deletado_em IS NULL
                ORDER BY cat.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }
}
