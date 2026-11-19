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

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    public function listar(array $filtros, bool $isGestor, int $usuarioId): array
    {
        $busca    = (string) ($filtros['busca'] ?? '');
        $situacao = (string) ($filtros['situacao'] ?? '');

        $sql = "SELECT m.id_matricula, m.status_matricula, m.segunda, m.terca, m.quarta, m.quinta, m.sexta,
                       m.data_matricula, u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula, t.horario_inicio
                FROM matriculas m
                INNER JOIN alunos a ON a.usuario_id = m.aluno_id
                INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                LEFT JOIN cursos c ON c.id_curso = t.curso_id
                WHERE m.deletado_em IS NULL AND u.deletado_em IS NULL";
        $params = [];

        if (!$isGestor) {
            $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
            $params[] = $usuarioId;
            $params[] = $usuarioId;
        }
        if ($busca !== '') {
            $sql .= " AND (u.nome LIKE ? OR c.nome LIKE ?)";
            $like = "%{$busca}%";
            array_push($params, $like, $like);
        }
        if ($situacao !== '') {
            $sql .= " AND m.status_matricula = ?";
            $params[] = (int) $situacao;
        }
        $sql .= " ORDER BY m.data_matricula DESC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM matriculas WHERE id_matricula = ? AND deletado_em IS NULL");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /** Matrículas de um aluno com dados da turma (tela de detalhe do aluno). */
    public function listarPorAluno(int $alunoId): array
    {
        $stm = $this->db->prepare("
            SELECT m.*, c.nome AS curso_nome, u_prof.nome AS professor_nome,
                   t.horario_inicio, t.horario_termino, t.local_aula
            FROM matriculas m
            LEFT JOIN turmas t ON t.id_turma = m.turma_id
            LEFT JOIN cursos c ON c.id_curso = t.curso_id
            LEFT JOIN usuarios u_prof ON u_prof.id_usuario = t.professor_id
            WHERE m.aluno_id = ? AND m.deletado_em IS NULL
            ORDER BY m.data_matricula DESC
        ");
        $stm->execute([$alunoId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Grade semanal: matrículas ativas do aluno. */
    public function listarGradeAtiva(int $alunoId): array
    {
        $stm = $this->db->prepare("
            SELECT m.segunda, m.terca, m.quarta, m.quinta, m.sexta,
                   t.horario_inicio, t.horario_termino, c.nome AS curso_nome
            FROM matriculas m
            INNER JOIN turmas t ON t.id_turma = m.turma_id
            INNER JOIN cursos c ON c.id_curso = t.curso_id
            WHERE m.aluno_id = ? AND m.status_matricula = 1 AND m.deletado_em IS NULL AND t.deletado_em IS NULL
            ORDER BY t.horario_inicio ASC
        ");
        $stm->execute([$alunoId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Matrículas do próprio usuário (tela "Meu perfil"). */
    public function listarMinhas(int $usuarioId): array
    {
        $stm = $this->db->prepare("
            SELECT m.status_matricula, m.segunda, m.terca, m.quarta, m.quinta, m.sexta,
                   c.nome AS curso_nome, t.horario_inicio, t.horario_termino, u_prof.nome AS professor_nome
            FROM matriculas m
            INNER JOIN turmas t ON t.id_turma = m.turma_id
            INNER JOIN cursos c ON c.id_curso = t.curso_id
            LEFT JOIN professores prof ON prof.usuario_id = t.professor_id
            LEFT JOIN usuarios u_prof ON u_prof.id_usuario = prof.usuario_id
            WHERE m.aluno_id = ? AND m.deletado_em IS NULL AND t.deletado_em IS NULL
            ORDER BY m.status_matricula DESC, m.data_matricula DESC
        ");
        $stm->execute([$usuarioId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Opções do <select> de matrícula no formulário de pagamento (array associativo). */
    public function listarParaPagamento(): array
    {
        $sql = "SELECT m.id_matricula, m.aluno_id, u.nome AS nome_aluno, c.nome AS nome_curso,
                       t.horario_inicio, t.horario_termino
                FROM matriculas m
                INNER JOIN alunos a ON m.aluno_id = a.usuario_id
                INNER JOIN usuarios u ON a.usuario_id = u.id_usuario
                LEFT JOIN turmas t ON m.turma_id = t.id_turma
                LEFT JOIN cursos c ON t.curso_id = c.id_curso
                WHERE m.deletado_em IS NULL AND a.deletado_em IS NULL AND u.deletado_em IS NULL
                ORDER BY u.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Opções do <select> de matrícula no formulário de reposição. */
    public function listarAtivasParaReposicao(): array
    {
        $sql = "SELECT m.id_matricula, u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula
                FROM matriculas m
                INNER JOIN alunos a ON a.usuario_id = m.aluno_id
                INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                LEFT JOIN cursos c ON c.id_curso = t.curso_id
                WHERE m.status_matricula = 1 AND m.deletado_em IS NULL AND u.deletado_em IS NULL
                ORDER BY u.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }
}
