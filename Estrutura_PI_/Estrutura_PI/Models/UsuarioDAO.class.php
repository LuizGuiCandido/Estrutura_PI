<?php

class UsuarioDAO extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    public function buscarPorEmail(string $email): ?object
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nome,
                    u.email,
                    u.senha,
                    u.gestor,
                    a.usuario_id AS is_aluno,
                    p.usuario_id AS is_professor
                FROM usuarios u
                LEFT JOIN alunos a ON a.usuario_id = u.id_usuario AND a.deletado_em IS NULL
                LEFT JOIN professores p ON p.usuario_id = u.id_usuario AND p.deletado_em IS NULL
                WHERE u.email = ? AND u.deletado_em IS NULL
                LIMIT 1";

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $email);
            $stm->execute();
            $usuario = $stm->fetch(PDO::FETCH_OBJ);

            return $usuario ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function buscarSenhaHash(int $idUsuario): ?string
    {
        $stm = $this->db->prepare("SELECT senha FROM usuarios WHERE id_usuario = ? AND deletado_em IS NULL");
        $stm->execute([$idUsuario]);
        $hash = $stm->fetchColumn();
        return $hash !== false ? (string) $hash : null;
    }

    public function atualizarSenha(int $idUsuario, string $hash): bool
    {
        $stm = $this->db->prepare("UPDATE usuarios SET senha = ? WHERE id_usuario = ?");
        return $stm->execute([$hash, $idUsuario]);
    }
}
