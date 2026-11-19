<?php

/**
 * Consultas de leitura do painel do gestor.
 */
class DashboardDAO extends Conexao
{
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    public function totalAlunos(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM alunos WHERE deletado_em IS NULL")->fetchColumn();
    }

    public function totalTurmasAtivas(): int
    {
        $sql = "SELECT COUNT(*) FROM turmas
                WHERE deletado_em IS NULL AND (data_fim IS NULL OR data_fim >= CURDATE())";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function totalReposicoesMarcadas(): int
    {
        $sql = "SELECT COUNT(*) FROM reposicao WHERE status_reposicao = 'marcada' AND deletado_em IS NULL";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    /** Quantidade e soma de todos os pagamentos. */
    public function resumoPagamentos(): object
    {
        $sql = "SELECT COUNT(*) AS qtd, COALESCE(SUM(valor_total), 0) AS total
                FROM pagamentos
                WHERE deletado_em IS NULL";
        return $this->db->query($sql)->fetch(PDO::FETCH_OBJ);
    }

    public function ultimosPagamentos(int $limite = 5): array
    {
        $stm = $this->db->prepare("
            SELECT pg.valor_total, pg.data_pagamento, u.nome AS aluno_nome, c.nome AS curso_nome,
                   pl.periodo, pl.meio_pagamento
            FROM pagamentos pg
            INNER JOIN matriculas m ON m.id_matricula = pg.matricula_id
            INNER JOIN usuarios u ON u.id_usuario = m.aluno_id
            LEFT JOIN turmas t ON t.id_turma = m.turma_id
            LEFT JOIN cursos c ON c.id_curso = t.curso_id
            INNER JOIN planos pl ON pl.id_plano = pg.plano_id
            WHERE pg.deletado_em IS NULL
            ORDER BY pg.data_pagamento DESC, pg.id_pagamento DESC
            LIMIT :lim
        ");
        $stm->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}
