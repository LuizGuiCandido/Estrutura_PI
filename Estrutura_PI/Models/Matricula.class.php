<?php

class Matricula
{
    public function __construct(
        private ?int $id_matricula = null,
        private int $aluno_id = 0,
        private int $turma_id = 0,
        private int $plano_id = 0,
        private string $data_matricula = '',
        private int $dia_vencimento = 10,
        private string $status = 'Ativa',
        private ?string $observacoes = null,
        private ?string $deletado_em = null
    ) {}

    public function getIdMatricula(): ?int { return $this->id_matricula; }
    public function setIdMatricula(?int $id): void { $this->id_matricula = $id; }

    public function getAlunoId(): int { return $this->aluno_id; }
    public function getTurmaId(): int { return $this->turma_id; }
    public function getPlanoId(): int { return $this->plano_id; }
    public function getDataMatricula(): string { return $this->data_matricula; }
    public function getDiaVencimento(): int { return $this->dia_vencimento; }
    public function getStatus(): string { return $this->status; }
    public function getObservacoes(): ?string { return $this->observacoes; }
}