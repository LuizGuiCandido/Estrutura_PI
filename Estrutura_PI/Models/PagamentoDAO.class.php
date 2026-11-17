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
}