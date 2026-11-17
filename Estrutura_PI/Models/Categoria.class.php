<?php


    class CategoriaDAO extends Conexao {
    public function __construct($db = null) { parent::__construct($db); }
    private function verificarConexao(): void { if (!isset($this->db) || !($this->db instanceof PDO)) parent::__construct(); }

    public function salvar(array $dados): bool {
        $this->verificarConexao();
        try {
            if (!empty($dados['id_categoria'])) {
                $stm = $this->db->prepare("UPDATE categorias SET nome = ? WHERE id_categoria = ?");
                return $stm->execute([$dados['nome'], $dados['id_categoria']]);
            } else {
                $stm = $this->db->prepare("INSERT INTO categorias (nome) VALUES (?)");
                return $stm->execute([$dados['nome']]);
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao salvar categoria: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $id): bool {
        $this->verificarConexao();
        try {
            $stm = $this->db->prepare("UPDATE categorias SET deletado_em = NOW() WHERE id_categoria = ?");
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}