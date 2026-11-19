<?php

class Reposicao
{
    public function __construct(
        private ?int $id_reposicao = null,
        private int $matricula_id = 0,
        private int $turma_destino_id = 0,
        private string $data_falta = '',
        private string $data_reposicao = '',
        private string $status = 'Agendada',
        private ?string $observacoes = null,
        private ?string $deletado_em = null
    ) {}

    public function getIdReposicao(): ?int { return $this->id_reposicao; }
    public function setIdReposicao(?int $id): void { $this->id_reposicao = $id; }

    public function getMatriculaId(): int { return $this->matricula_id; }
    public function getTurmaDestinoId(): int { return $this->turma_destino_id; }
    public function getDataFalta(): string { return $this->data_falta; }
    public function getDataReposicao(): string { return $this->data_reposicao; }
    public function getStatus(): string { return $this->status; }
    public function getObservacoes(): ?string { return $this->observacoes; }
}