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

class PlanoDAO extends Conexao
{
    public function __construct()
    {
        parent::__construct();
    }

    public function buscarTodos(): array
    {
        $sql = "SELECT id_plano, periodo, valor_aula, meio_pagamento, criado_em, atualizado_em
                FROM planos
                WHERE deletado_em IS NULL
                ORDER BY periodo ASC, meio_pagamento ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorId(int $id): ?object
    {
        $sql = "SELECT id_plano, periodo, valor_aula, meio_pagamento, criado_em, atualizado_em
                FROM planos
                WHERE id_plano = :id_plano
                  AND deletado_em IS NULL
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_plano', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_OBJ);
        return $resultado ?: null;
    }

    public function inserir(Plano $plano): bool
    {
        $sql = "INSERT INTO planos (periodo, valor_aula, meio_pagamento)
                VALUES (:periodo, :valor_aula, :meio_pagamento)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':periodo', $plano->getPeriodo(), PDO::PARAM_STR);
        $stmt->bindValue(':valor_aula', $plano->getValorAula());
        $stmt->bindValue(':meio_pagamento', $plano->getMeioPagamento(), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function alterar(Plano $plano): bool
    {
        $sql = "UPDATE planos
                SET periodo = :periodo,
                    valor_aula = :valor_aula,
                    meio_pagamento = :meio_pagamento
                WHERE id_plano = :id_plano
                  AND deletado_em IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':periodo', $plano->getPeriodo(), PDO::PARAM_STR);
        $stmt->bindValue(':valor_aula', $plano->getValorAula());
        $stmt->bindValue(':meio_pagamento', $plano->getMeioPagamento(), PDO::PARAM_STR);
        $stmt->bindValue(':id_plano', $plano->getIdPlano(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        // Soft delete respeitando a coluna 'deletado_em' da tabela planos
        $sql = "UPDATE planos
                SET deletado_em = NOW()
                WHERE id_plano = :id_plano";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_plano', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /** Planos como array associativo (usado no <select> do formulário de pagamento). */
    public function buscarTodosAssoc(): array
    {
        $sql = "SELECT id_plano, periodo, valor_aula, meio_pagamento
                FROM planos
                WHERE deletado_em IS NULL
                ORDER BY periodo ASC, meio_pagamento ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
