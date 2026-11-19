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
            $sql = "INSERT INTO contatos (aluno_id, nome, numero, parentesco, tipo)
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
            $sql = "UPDATE contatos 
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
            $sql = "UPDATE contatos SET deletado_em = NOW() WHERE id_contato = ?";
            $stm = $this->db->prepare($sql);
            return $stm->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM contatos WHERE id_contato = ? AND deletado_em IS NULL");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function listarPorAluno(int $alunoId): array
    {
        $stm = $this->db->prepare("SELECT * FROM contatos WHERE aluno_id = ? AND deletado_em IS NULL ORDER BY id_contato ASC");
        $stm->execute([$alunoId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Aluno vinculado ao contato (id + nome), ou null se não existir. */
    public function buscarAlunoVinculado(int $alunoId): ?object
    {
        $stm = $this->db->prepare("
            SELECT u.id_usuario, u.nome
            FROM alunos a
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            WHERE a.usuario_id = ?
        ");
        $stm->execute([$alunoId]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }
}
