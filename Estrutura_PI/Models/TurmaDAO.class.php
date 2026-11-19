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

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    public function listar(array $filtros, bool $isGestor, int $usuarioId): array
    {
        $busca = (string) ($filtros['busca'] ?? '');
        $curso = (int) ($filtros['curso'] ?? 0);
        $prof  = (int) ($filtros['professor'] ?? 0);

        $sql = "SELECT t.id_turma, t.horario_inicio, t.horario_termino, t.local_aula,
                       c.nome AS curso_nome, u_prof.nome AS professor_nome, u_est.nome AS estagiario_nome,
                       (SELECT COUNT(*) FROM matriculas m
                        WHERE m.turma_id = t.id_turma AND m.status_matricula = 1 AND m.deletado_em IS NULL) AS total_alunos
                FROM turmas t
                INNER JOIN cursos c ON c.id_curso = t.curso_id
                INNER JOIN professores prof ON prof.usuario_id = t.professor_id
                INNER JOIN usuarios u_prof ON u_prof.id_usuario = prof.usuario_id
                LEFT JOIN professores est ON est.usuario_id = t.estagiario_id
                LEFT JOIN usuarios u_est ON u_est.id_usuario = est.usuario_id
                WHERE t.deletado_em IS NULL";
        $params = [];

        if (!$isGestor) {
            $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
            $params[] = $usuarioId;
            $params[] = $usuarioId;
        }
        if ($busca !== '') {
            $sql .= " AND (c.nome LIKE ? OR u_prof.nome LIKE ?)";
            $like = "%{$busca}%";
            array_push($params, $like, $like);
        }
        if ($curso > 0) {
            $sql .= " AND t.curso_id = ?";
            $params[] = $curso;
        }
        if ($isGestor && $prof > 0) {
            $sql .= " AND t.professor_id = ?";
            $params[] = $prof;
        }
        $sql .= " ORDER BY t.horario_inicio ASC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM turmas WHERE id_turma = ? AND deletado_em IS NULL");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function buscarDetalhe(int $id): ?object
    {
        $stm = $this->db->prepare("
            SELECT t.*, c.nome AS curso_nome, u_prof.nome AS professor_nome, u_est.nome AS estagiario_nome
            FROM turmas t
            INNER JOIN cursos c ON c.id_curso = t.curso_id
            INNER JOIN professores prof ON prof.usuario_id = t.professor_id
            INNER JOIN usuarios u_prof ON u_prof.id_usuario = prof.usuario_id
            LEFT JOIN professores est ON est.usuario_id = t.estagiario_id
            LEFT JOIN usuarios u_est ON u_est.id_usuario = est.usuario_id
            WHERE t.id_turma = ? AND t.deletado_em IS NULL
        ");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function listarAlunosMatriculados(int $turmaId): array
    {
        $stm = $this->db->prepare("
            SELECT m.*, u.id_usuario, u.nome AS aluno_nome, u.data_nasc, a.observacao
            FROM matriculas m
            INNER JOIN alunos a ON a.usuario_id = m.aluno_id
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            WHERE m.turma_id = ? AND m.status_matricula = 1 AND m.deletado_em IS NULL
            ORDER BY u.nome ASC
        ");
        $stm->execute([$turmaId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Turmas com nome do curso (para <select>). */
    public function listarParaSelect(): array
    {
        $sql = "SELECT t.id_turma, c.nome AS curso_nome, t.horario_inicio, t.horario_termino, t.local_aula
                FROM turmas t
                INNER JOIN cursos c ON c.id_curso = t.curso_id
                WHERE t.deletado_em IS NULL
                ORDER BY c.nome ASC, t.horario_inicio ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    /** Turmas do professor (ou todas, se gestor) — tela "Meu dia". */
    public function listarDoDia(int $usuarioId, bool $isGestor): array
    {
        $sql = "SELECT t.id_turma, t.horario_inicio, t.horario_termino, t.local_aula, c.nome AS curso_nome,
                       (SELECT COUNT(*) FROM matriculas m
                        WHERE m.turma_id = t.id_turma AND m.status_matricula = 1 AND m.deletado_em IS NULL) AS total_alunos
                FROM turmas t
                INNER JOIN cursos c ON c.id_curso = t.curso_id
                WHERE t.deletado_em IS NULL";
        $params = [];
        if (!$isGestor) {
            $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
            $params = [$usuarioId, $usuarioId];
        }
        $sql .= " ORDER BY t.horario_inicio ASC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Turmas ministradas por um professor (titular ou estagiário). */
    public function listarPorProfessor(int $professorId): array
    {
        $stm = $this->db->prepare("
            SELECT t.id_turma, t.horario_inicio, t.horario_termino, t.local_aula, t.professor_id,
                   c.nome AS curso_nome,
                   (SELECT COUNT(*) FROM matriculas m
                    WHERE m.turma_id = t.id_turma AND m.status_matricula = 1 AND m.deletado_em IS NULL) AS total_alunos
            FROM turmas t
            INNER JOIN cursos c ON c.id_curso = t.curso_id
            WHERE (t.professor_id = ? OR t.estagiario_id = ?) AND t.deletado_em IS NULL
            ORDER BY t.horario_inicio ASC
        ");
        $stm->execute([$professorId, $professorId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Grade resumida para o dashboard. */
    public function listarGrade(int $limite = 6): array
    {
        $stm = $this->db->prepare("
            SELECT t.horario_inicio, t.horario_termino, t.local_aula, c.nome AS curso_nome, u.nome AS professor_nome
            FROM turmas t
            INNER JOIN cursos c ON c.id_curso = t.curso_id
            INNER JOIN usuarios u ON u.id_usuario = t.professor_id
            WHERE t.deletado_em IS NULL
            ORDER BY t.horario_inicio ASC
            LIMIT :lim
        ");
        $stm->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}
