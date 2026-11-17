<?php

class Pagamento
{
    public function __construct(
        private int $id_pagamento = 0,
        private string $data_pagamento = '',
        private float $valor_total = 0.0,
        private ?int $matricula_id = null,
        private int $plano_id = 0
    ) {}

    public function getIdPagamento(): int { return $this->id_pagamento; }
    public function setIdPagamento(int $id): void { $this->id_pagamento = $id; }

    public function getDataPagamento(): string { return $this->data_pagamento; }
    public function setDataPagamento(string $data): void { $this->data_pagamento = $data; }

    public function getValorTotal(): float { return $this->valor_total; }
    public function setValorTotal(float $valor): void { $this->valor_total = $valor; }

    public function getMatriculaId(): ?int { return $this->matricula_id; }
    public function setMatriculaId(?int $id): void { $this->matricula_id = $id; }

    public function getPlanoId(): int { return $this->plano_id; }
    public function setPlanoId(int $id): void { $this->plano_id = $id; }
}