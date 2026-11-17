<?php

class Plano
{
    public function __construct(
        private int $id_plano = 0,
        private ?string $periodo = 'mensal',
        private float $valor_aula = 0.0,
        private string $meio_pagamento = 'à vista'
    ) {}

    public function getIdPlano(): int
    {
        return $this->id_plano;
    }

    public function setIdPlano(int $id_plano): void
    {
        $this->id_plano = $id_plano;
    }

    public function getPeriodo(): ?string
    {
        return $this->periodo;
    }

    public function setPeriodo(?string $periodo): void
    {
        $this->periodo = $periodo;
    }

    public function getValorAula(): float
    {
        return $this->valor_aula;
    }

    public function setValorAula(float $valor_aula): void
    {
        $this->valor_aula = $valor_aula;
    }

    public function getMeioPagamento(): string
    {
        return $this->meio_pagamento;
    }

    public function setMeioPagamento(string $meio_pagamento): void
    {
        $this->meio_pagamento = $meio_pagamento;
    }
}