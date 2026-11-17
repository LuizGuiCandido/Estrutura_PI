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
}