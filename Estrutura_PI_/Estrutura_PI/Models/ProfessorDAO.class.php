<?php

class ProfessorDAO extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    public function cadastrar(Professor $professor): bool
    {
        try {
            $this->db->beginTransaction();

            $usuario = $professor->getUsuario();

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

            $sqlProfessor = "INSERT INTO professores (usuario_id, creef, formacao, especialidade, mostrar) 
                             VALUES (?, ?, ?, ?, ?)";
            
            $stmProf = $this->db->prepare($sqlProfessor);
            $stmProf->bindValue(1, $usuario_id, PDO::PARAM_INT);
            
            if (empty($professor->getCref())) {
                $stmProf->bindValue(2, null, PDO::PARAM_NULL);
            } else {
                $stmProf->bindValue(2, $professor->getCref());
            }
            
            $stmProf->bindValue(3, $professor->getFormacao());
            $stmProf->bindValue(4, $professor->getEspecialidade());
            $stmProf->bindValue(5, $professor->getMostrar(), PDO::PARAM_INT);
            $stmProf->execute();

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['erro'] = "Erro ao inserir professor: " . $e->getMessage();
            return false;
        }
    }

    public function atualizar(Professor $professor, bool $alterarSenha = false): bool
    {
        try {
            $this->db->beginTransaction();

            $usuario = $professor->getUsuario();
            $idUsuario = (int) $usuario->getIdUsuario();

            if ($alterarSenha) {
                $sqlUsuario = "UPDATE usuarios 
                               SET nome = ?, email = ?, cpf = ?, senha = ?, telefone = ?, data_nasc = ?, endereco = ?
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
                    $idUsuario
                ]);
            } else {
                $sqlUsuario = "UPDATE usuarios 
                               SET nome = ?, email = ?, cpf = ?, telefone = ?, data_nasc = ?, endereco = ?
                               WHERE id_usuario = ?";
                $stm = $this->db->prepare($sqlUsuario);
                $stm->execute([
                    $usuario->getNome(),
                    $usuario->getEmail(),
                    $usuario->getCPF(),
                    $usuario->getTelefone(),
                    $usuario->getDataNascimento(),
                    $usuario->getEndereco(),
                    $idUsuario
                ]);
            }

            $sqlProf = "UPDATE professores 
                        SET creef = ?, formacao = ?, especialidade = ?, mostrar = ?
                        WHERE usuario_id = ?";
            $stmProf = $this->db->prepare($sqlProf);
            if (empty($professor->getCref())) {
                $stmProf->bindValue(1, null, PDO::PARAM_NULL);
            } else {
                $stmProf->bindValue(1, $professor->getCref());
            }
            $stmProf->bindValue(2, $professor->getFormacao());
            $stmProf->bindValue(3, $professor->getEspecialidade());
            $stmProf->bindValue(4, $professor->getMostrar(), PDO::PARAM_INT);
            $stmProf->bindValue(5, $idUsuario, PDO::PARAM_INT);
            $stmProf->execute();

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['erro'] = "Erro ao atualizar professor: " . $e->getMessage();
            return false;
        }
    }

    public function alternarMostrarSite(int $usuarioId): bool
    {
        $sql = "UPDATE professores SET mostrar = IF(mostrar = 1, 0, 1) WHERE usuario_id = ?";
        $stm = $this->db->prepare($sql);
        return $stm->execute([$usuarioId]);
    }

    public function excluir(int $usuarioId): bool
    {
        try {
            $this->db->beginTransaction();
            $stm1 = $this->db->prepare("UPDATE professores SET deletado_em = NOW() WHERE usuario_id = ?");
            $stm1->execute([$usuarioId]);

            $stm2 = $this->db->prepare("UPDATE usuarios SET deletado_em = NOW() WHERE id_usuario = ?");
            $stm2->execute([$usuarioId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->db = null;
            return false;
        }
    }

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    public function listar(array $filtros): array
    {
        $busca    = (string) ($filtros['busca'] ?? '');
        $formacao = (string) ($filtros['formacao'] ?? '');
        $mostrar  = (string) ($filtros['mostrar'] ?? '');

        $sql = "SELECT u.id_usuario, u.nome, u.email, u.telefone, p.creef, p.formacao, p.especialidade, p.mostrar
                FROM professores p
                INNER JOIN usuarios u ON u.id_usuario = p.usuario_id
                WHERE p.deletado_em IS NULL AND u.deletado_em IS NULL";
        $params = [];

        if ($busca !== '') {
            $sql .= " AND (u.nome LIKE ? OR p.creef LIKE ? OR p.especialidade LIKE ?)";
            $like = "%{$busca}%";
            array_push($params, $like, $like, $like);
        }
        if ($formacao !== '') {
            $sql .= " AND p.formacao = ?";
            $params[] = $formacao;
        }
        if ($mostrar !== '') {
            $sql .= " AND p.mostrar = ?";
            $params[] = (int) $mostrar;
        }
        $sql .= " ORDER BY u.nome ASC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("
            SELECT u.*, p.creef, p.formacao, p.especialidade, p.mostrar
            FROM professores p
            INNER JOIN usuarios u ON u.id_usuario = p.usuario_id
            WHERE p.usuario_id = ? AND p.deletado_em IS NULL AND u.deletado_em IS NULL
        ");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /** Professores para <select> (id + nome). */
    public function listarParaSelect(): array
    {
        $sql = "SELECT p.usuario_id, u.nome
                FROM professores p
                INNER JOIN usuarios u ON u.id_usuario = p.usuario_id
                WHERE p.deletado_em IS NULL AND u.deletado_em IS NULL
                ORDER BY u.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    /** Equipe exibida no site público (somente quem está marcado para aparecer). */
    public function listarParaSite(): array
    {
        $sql = "SELECT u.nome, p.formacao, p.especialidade,
                       p.creef AS cref,
                       (p.creef IS NULL OR p.creef = '') AS estagiario
                FROM professores p
                INNER JOIN usuarios u ON u.id_usuario = p.usuario_id
                WHERE p.deletado_em IS NULL AND u.deletado_em IS NULL AND p.mostrar = 1
                ORDER BY estagiario ASC, u.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }
}
