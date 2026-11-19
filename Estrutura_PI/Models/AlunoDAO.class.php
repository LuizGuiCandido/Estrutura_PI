<?php

class AlunoDAO extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    private function verificarConexao(): void
    {
        if (!isset($this->db) || !($this->db instanceof PDO)) {
            // Garante re-instanciação caso o PDO tenha sido resetado
            parent::__construct();
        }
    }

    public function cadastrar(Aluno $aluno): bool
    {
        $this->verificarConexao();

        try {
            $this->db->beginTransaction();

            $usuario = $aluno->getUsuario();

            $sqlUsuario = "INSERT INTO usuarios (nome, email, cpf, senha, telefone, data_nasc, endereco, gestor) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stm = $this->db->prepare($sqlUsuario);
            $stm->execute([
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getCPF(),
                $usuario->getSenha(),
                $usuario->getTelefone(),
                $usuario->getDataNascimento(),
                $usuario->getEndereco(),
                $usuario->getGestor()
            ]);

            $usuario_id = (int) $this->db->lastInsertId();

            $sqlAluno = "INSERT INTO alunos (usuario_id, aula_experimental, responsavel_id, observacao) 
                         VALUES (?, ?, ?, ?)";
            
            $stmAluno = $this->db->prepare($sqlAluno);
            $stmAluno->bindValue(1, $usuario_id, PDO::PARAM_INT);
            $stmAluno->bindValue(2, $aluno->getAulaExperimental(), PDO::PARAM_INT);
            
            if ($aluno->getResponsavelId() === null) {
                $stmAluno->bindValue(3, null, PDO::PARAM_NULL);
            } else {
                $stmAluno->bindValue(3, $aluno->getResponsavelId(), PDO::PARAM_INT);
            }
            
            $stmAluno->bindValue(4, $aluno->getObservacao());
            $stmAluno->execute();

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if (isset($this->db) && $this->db instanceof PDO && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['erro'] = "Erro ao inserir aluno: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(Aluno $aluno, bool $alterarSenha = false): bool
    {
        $this->verificarConexao();

        try {
            $this->db->beginTransaction();

            $usuario = $aluno->getUsuario();
            $idUsuario = (int) $usuario->getIdUsuario();

            if ($alterarSenha) {
                $sqlUsuario = "UPDATE usuarios 
                               SET nome = ?, email = ?, cpf = ?, senha = ?, telefone = ?, data_nasc = ?, endereco = ?, gestor = ?
                               WHERE id_usuario = ?";
                $stm = $this->db->prepare($sqlUsuario);
                $stm->execute([
                    $usuario->getNome(),
                    $usuario->getEmail(),
                    $usuario->getCPF(),
                    $usuario->getSenha(),
                    $usuario->getTelefone(),
                    $usuario->getDataNascimento(),
                    $usuario->getEndereco(),
                    $usuario->getGestor(),
                    $idUsuario
                ]);
            } else {
                $sqlUsuario = "UPDATE usuarios 
                               SET nome = ?, email = ?, cpf = ?, telefone = ?, data_nasc = ?, endereco = ?, gestor = ?
                               WHERE id_usuario = ?";
                $stm = $this->db->prepare($sqlUsuario);
                $stm->execute([
                    $usuario->getNome(),
                    $usuario->getEmail(),
                    $usuario->getCPF(),
                    $usuario->getTelefone(),
                    $usuario->getDataNascimento(),
                    $usuario->getEndereco(),
                    $usuario->getGestor(),
                    $idUsuario
                ]);
            }

            $sqlAluno = "UPDATE alunos 
                         SET aula_experimental = ?, responsavel_id = ?, observacao = ?
                         WHERE usuario_id = ?";
            $stmAluno = $this->db->prepare($sqlAluno);
            $stmAluno->bindValue(1, $aluno->getAulaExperimental(), PDO::PARAM_INT);
            if ($aluno->getResponsavelId() === null) {
                $stmAluno->bindValue(2, null, PDO::PARAM_NULL);
            } else {
                $stmAluno->bindValue(2, $aluno->getResponsavelId(), PDO::PARAM_INT);
            }
            $stmAluno->bindValue(3, $aluno->getObservacao());
            $stmAluno->bindValue(4, $idUsuario, PDO::PARAM_INT);
            $stmAluno->execute();

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if (isset($this->db) && $this->db instanceof PDO && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['erro'] = "Erro ao atualizar aluno: " . $e->getMessage();
            return false;
        }
    }

    public function excluir(int $usuarioId): bool
    {
        $this->verificarConexao();

        try {
            $this->db->beginTransaction();
            $stm1 = $this->db->prepare("UPDATE alunos SET deletado_em = NOW() WHERE usuario_id = ?");
            $stm1->execute([$usuarioId]);

            $stm2 = $this->db->prepare("UPDATE usuarios SET deletado_em = NOW() WHERE id_usuario = ?");
            $stm2->execute([$usuarioId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($this->db) && $this->db instanceof PDO && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function BuscarResponsavel(int $responsavel_id)
    {
        $this->verificarConexao();
        $sql = "SELECT id_usuario, nome, cpf, email FROM usuarios WHERE id_usuario = ? AND deletado_em IS NULL";
        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $responsavel_id, PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return null;
        }
    }

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    /** Lista de alunos com filtros (busca, turma, situacao). Professor só vê os seus alunos. */
    public function listar(array $filtros, bool $isGestor, int $usuarioId): array
    {
        $busca    = (string) ($filtros['busca'] ?? '');
        $turma    = (int) ($filtros['turma'] ?? 0);
        $situacao = (string) ($filtros['situacao'] ?? '');

        $sql = "SELECT DISTINCT
                    u.id_usuario, u.nome, u.cpf, u.email, u.telefone, u.data_nasc,
                    (SELECT c2.nome FROM matriculas m2
                     INNER JOIN turmas t2 ON t2.id_turma = m2.turma_id
                     INNER JOIN cursos c2 ON c2.id_curso = t2.curso_id
                     WHERE m2.aluno_id = u.id_usuario AND m2.status_matricula = 1 AND m2.deletado_em IS NULL
                     LIMIT 1) AS turma_atual,
                    (SELECT COUNT(*) FROM matriculas m3
                     WHERE m3.aluno_id = u.id_usuario AND m3.status_matricula = 1 AND m3.deletado_em IS NULL) AS qtd_ativas
                FROM alunos a
                INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                LEFT JOIN matriculas m ON m.aluno_id = u.id_usuario AND m.deletado_em IS NULL
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                WHERE a.deletado_em IS NULL AND u.deletado_em IS NULL AND u.gestor = 0";
        $params = [];

        if (!$isGestor) {
            $sql .= " AND (t.professor_id = ? OR t.estagiario_id = ?)";
            $params[] = $usuarioId;
            $params[] = $usuarioId;
        }
        if ($busca !== '') {
            $sql .= " AND (u.nome LIKE ? OR u.cpf LIKE ? OR u.email LIKE ?)";
            $like = "%{$busca}%";
            array_push($params, $like, $like, $like);
        }
        if ($turma > 0) {
            $sql .= " AND m.turma_id = ?";
            $params[] = $turma;
        }
        if ($situacao === 'ativo') {
            $sql .= " HAVING qtd_ativas > 0";
        } elseif ($situacao === 'inativo') {
            $sql .= " HAVING qtd_ativas = 0";
        }
        $sql .= " ORDER BY u.nome ASC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Dados completos do aluno para o formulário de edição. */
    public function buscarParaEdicao(int $id): ?object
    {
        $stm = $this->db->prepare("
            SELECT u.*, a.aula_experimental, a.responsavel_id, a.observacao
            FROM alunos a
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            WHERE a.usuario_id = ? AND a.deletado_em IS NULL
        ");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /** Usuários que podem ser escolhidos como responsável (todos, menos o próprio aluno). */
    public function listarResponsaveis(int $excluirId = 0): array
    {
        $stm = $this->db->prepare("
            SELECT id_usuario, nome, cpf
            FROM usuarios
            WHERE deletado_em IS NULL AND id_usuario != ?
            ORDER BY nome ASC
        ");
        $stm->execute([$excluirId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Aluno + nome do responsável (tela de detalhe). */
    public function buscarDetalhe(int $id): ?object
    {
        $stm = $this->db->prepare("
            SELECT u.*, a.aula_experimental, a.observacao, u_resp.nome AS responsavel_nome
            FROM alunos a
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            LEFT JOIN usuarios u_resp ON u_resp.id_usuario = a.responsavel_id
            WHERE a.usuario_id = ? AND a.deletado_em IS NULL AND u.deletado_em IS NULL
        ");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /** Alunos de gestão em ordem alfabética (para <select> de matrícula). */
    public function listarParaSelect(): array
    {
        $sql = "SELECT u.id_usuario, u.nome, u.cpf
                FROM alunos a
                INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                WHERE a.deletado_em IS NULL AND u.deletado_em IS NULL AND u.gestor = 0
                ORDER BY u.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    /** Alunos que o usuário logado pode ver na agenda (gestor: todos; demais: ele + dependentes). */
    public function listarVisiveis(bool $isGestor, int $usuarioId): array
    {
        if ($isGestor) {
            $sql = "SELECT u.id_usuario, u.nome
                    FROM alunos a
                    INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
                    WHERE a.deletado_em IS NULL AND u.deletado_em IS NULL
                    ORDER BY u.nome ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
        }

        $stm = $this->db->prepare("
            SELECT u.id_usuario, u.nome
            FROM alunos a
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            WHERE (a.usuario_id = ? OR a.responsavel_id = ?)
              AND a.deletado_em IS NULL AND u.deletado_em IS NULL
            ORDER BY u.nome ASC
        ");
        $stm->execute([$usuarioId, $usuarioId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Usuário logado + dados de aluno (se existir). */
    public function buscarPerfil(int $usuarioId): ?object
    {
        $stm = $this->db->prepare("
            SELECT u.id_usuario, u.nome, u.cpf, u.email, u.telefone, u.data_nasc, u.endereco,
                   a.aula_experimental, a.observacao
            FROM usuarios u
            LEFT JOIN alunos a ON a.usuario_id = u.id_usuario AND a.deletado_em IS NULL
            WHERE u.id_usuario = ? AND u.deletado_em IS NULL
        ");
        $stm->execute([$usuarioId]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /** Alunos dos quais o usuário é responsável. */
    public function listarDependentes(int $responsavelId): array
    {
        $stm = $this->db->prepare("
            SELECT u.id_usuario, u.nome, u.data_nasc,
                   (SELECT c2.nome FROM matriculas m2
                    INNER JOIN turmas t2 ON t2.id_turma = m2.turma_id
                    INNER JOIN cursos c2 ON c2.id_curso = t2.curso_id
                    WHERE m2.aluno_id = u.id_usuario AND m2.status_matricula = 1 AND m2.deletado_em IS NULL
                    LIMIT 1) AS turma_atual,
                   (SELECT COUNT(*) FROM matriculas m3
                    WHERE m3.aluno_id = u.id_usuario AND m3.status_matricula = 1 AND m3.deletado_em IS NULL) AS qtd_ativas
            FROM alunos a
            INNER JOIN usuarios u ON u.id_usuario = a.usuario_id
            WHERE a.responsavel_id = ? AND a.deletado_em IS NULL AND u.deletado_em IS NULL
            ORDER BY u.nome ASC
        ");
        $stm->execute([$responsavelId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}
