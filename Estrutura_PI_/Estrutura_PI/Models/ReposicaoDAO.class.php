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

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    public function listar(array $filtros, bool $isGestor, int $usuarioId): array
    {
        $busca    = (string) ($filtros['busca'] ?? '');
        $situacao = (string) ($filtros['situacao'] ?? '');

        $sql = "SELECT r.id_reposicao, r.data_reposicao, r.horario, r.status_reposicao, r.observacao,
                       u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula
                FROM reposicao r
                INNER JOIN matriculas m ON m.id_matricula = r.matricula_id
                INNER JOIN alunos a ON a.usuario_id = m.aluno_id
                INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                LEFT JOIN cursos c ON c.id_curso = t.curso_id
                WHERE r.deletado_em IS NULL AND u.deletado_em IS NULL";
        $params = [];

        if (!$isGestor) {
            $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
            $params[] = $usuarioId;
            $params[] = $usuarioId;
        }
        if ($busca !== '') {
            $sql .= " AND u.nome LIKE ?";
            $params[] = "%{$busca}%";
        }
        if ($situacao !== '') {
            $sql .= " AND r.status_reposicao = ?";
            $params[] = $situacao;
        }
        $sql .= " ORDER BY r.data_reposicao DESC, r.horario DESC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM reposicao WHERE id_reposicao = ? AND deletado_em IS NULL");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function listarPorAluno(int $alunoId): array
    {
        $stm = $this->db->prepare("
            SELECT r.*, c.nome AS curso_nome
            FROM reposicao r
            INNER JOIN matriculas m ON m.id_matricula = r.matricula_id
            LEFT JOIN turmas t ON t.id_turma = m.turma_id
            LEFT JOIN cursos c ON c.id_curso = t.curso_id
            WHERE m.aluno_id = ? AND r.deletado_em IS NULL
            ORDER BY r.data_reposicao DESC, r.horario DESC
        ");
        $stm->execute([$alunoId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}
