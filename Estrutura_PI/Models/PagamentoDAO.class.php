<?php

if (!class_exists('Conexao')) {
    foreach ([
        __DIR__ . '/Conexao.class.php',
        __DIR__ . '/Conexao.php',
        __DIR__ . '/../Config/Conexao.class.php',
        __DIR__ . '/../Config/Conexao.php',
        __DIR__ . '/../config/conexao.php'
    ] as $caminhoConexao) {
        if (is_file($caminhoConexao)) {
            require_once $caminhoConexao;
            break;
        }
    }
}

class PagamentoDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function inserir(Pagamento $pagamento): bool
    {
        $sql = "INSERT INTO pagamentos (data_pagamento, valor_total, matricula_id, plano_id)
                VALUES (:data_pagamento, :valor_total, :matricula_id, :plano_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':data_pagamento', $pagamento->getDataPagamento(), PDO::PARAM_STR);
        $stmt->bindValue(':valor_total', $pagamento->getValorTotal());
        $stmt->bindValue(':matricula_id', $pagamento->getMatriculaId(), $pagamento->getMatriculaId() !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':plano_id', $pagamento->getPlanoId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function alterar(Pagamento $pagamento): bool
    {
        $sql = "UPDATE pagamentos
                SET data_pagamento = :data_pagamento,
                    valor_total = :valor_total,
                    matricula_id = :matricula_id,
                    plano_id = :plano_id
                WHERE id_pagamento = :id_pagamento
                  AND deletado_em IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':data_pagamento', $pagamento->getDataPagamento(), PDO::PARAM_STR);
        $stmt->bindValue(':valor_total', $pagamento->getValorTotal());
        $stmt->bindValue(':matricula_id', $pagamento->getMatriculaId(), $pagamento->getMatriculaId() !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':plano_id', $pagamento->getPlanoId(), PDO::PARAM_INT);
        $stmt->bindValue(':id_pagamento', $pagamento->getIdPagamento(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $sql = "UPDATE pagamentos SET deletado_em = NOW() WHERE id_pagamento = :id_pagamento";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_pagamento', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ===================== CONSULTAS (usadas pelos Controllers) =====================

    public function listar(string $busca = '', string $situacao = ''): array
    {
        $sql = "SELECT p.id_pagamento, p.data_pagamento, p.valor_total,
                       u.nome AS aluno_nome, c.nome AS curso_nome, t.local_aula,
                       COALESCE(pl.periodo, CONCAT('Plano #', pl.id_plano)) AS plano_nome,
                       pl.meio_pagamento,
                       CASE WHEN p.data_pagamento > CURDATE() THEN 'Em aberto' ELSE 'Pago' END AS situacao_calc
                FROM pagamentos p
                INNER JOIN matriculas m ON m.id_matricula = p.matricula_id
                INNER JOIN alunos al ON al.usuario_id = m.aluno_id
                INNER JOIN usuarios u ON u.id_usuario = al.usuario_id
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                LEFT JOIN cursos c ON c.id_curso = t.curso_id
                LEFT JOIN planos pl ON pl.id_plano = p.plano_id
                WHERE p.deletado_em IS NULL AND u.deletado_em IS NULL";
        $params = [];

        if ($busca !== '') {
            $sql .= " AND u.nome LIKE ?";
            $params[] = "%{$busca}%";
        }
        if ($situacao === 'Pago') {
            $sql .= " AND p.data_pagamento <= CURDATE()";
        } elseif ($situacao === 'Em atraso') {
            $sql .= " AND p.data_pagamento > CURDATE()";
        }
        $sql .= " ORDER BY p.data_pagamento DESC";

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    /** Matrículas ativas cujo último pagamento venceu há mais de 30 dias. */
    public function listarInadimplentes(): array
    {
        $sql = "SELECT m.id_matricula, u.nome AS aluno_nome, u.telefone, c.nome AS curso_nome, t.local_aula,
                       COALESCE(pl.periodo, CONCAT('Plano #', pl.id_plano)) AS plano_nome,
                       COALESCE(ult.valor_total, 0) AS valor_devido,
                       DATE_ADD(COALESCE(ult.data_pagamento, m.data_matricula), INTERVAL 30 DAY) AS vencimento,
                       DATEDIFF(CURDATE(), DATE_ADD(COALESCE(ult.data_pagamento, m.data_matricula), INTERVAL 30 DAY)) AS dias_atraso
                FROM matriculas m
                INNER JOIN alunos al ON al.usuario_id = m.aluno_id
                INNER JOIN usuarios u ON u.id_usuario = al.usuario_id
                LEFT JOIN turmas t ON t.id_turma = m.turma_id
                LEFT JOIN cursos c ON c.id_curso = t.curso_id
                LEFT JOIN (
                    SELECT p1.matricula_id, p1.plano_id, p1.data_pagamento, p1.valor_total
                    FROM pagamentos p1
                    WHERE p1.deletado_em IS NULL
                      AND p1.id_pagamento = (
                          SELECT MAX(p2.id_pagamento) FROM pagamentos p2
                          WHERE p2.matricula_id = p1.matricula_id AND p2.deletado_em IS NULL
                      )
                ) ult ON ult.matricula_id = m.id_matricula
                LEFT JOIN planos pl ON pl.id_plano = ult.plano_id
                WHERE m.status_matricula = 1
                  AND m.deletado_em IS NULL
                  AND u.deletado_em IS NULL
                  AND DATEDIFF(CURDATE(), COALESCE(ult.data_pagamento, m.data_matricula)) > 30
                ORDER BY dias_atraso DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM pagamentos WHERE id_pagamento = ? AND deletado_em IS NULL");
        $stm->execute([$id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function listarPorAluno(int $alunoId): array
    {
        $stm = $this->db->prepare("
            SELECT pg.*, pl.periodo, pl.meio_pagamento
            FROM pagamentos pg
            INNER JOIN matriculas m ON m.id_matricula = pg.matricula_id
            INNER JOIN planos pl ON pl.id_plano = pg.plano_id
            WHERE m.aluno_id = ? AND pg.deletado_em IS NULL
            ORDER BY pg.data_pagamento DESC
        ");
        $stm->execute([$alunoId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}
