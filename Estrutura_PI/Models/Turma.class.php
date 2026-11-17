<?php

class Turma
{
    public function __construct(
        private int $id_turma = 0,
        private string $horario_inicio = '',
        private string $horario_termino = '',
        private int $local_aula = 0,
        private string $data_inicio = '',
        private ?string $data_fim = null,
        private int $curso_id = 0,
        private int $professor_id = 0,
        private ?int $estagiario_id = null
    ) {}

    public function getIdTurma(): int
    {
        return $this->id_turma;
    }

    public function setIdTurma(int $id_turma): void
    {
        $this->id_turma = $id_turma;
    }

    public function getHorarioInicio(): string
    {
        return $this->horario_inicio;
    }

    public function setHorarioInicio(string $horario_inicio): void
    {
        $this->horario_inicio = $horario_inicio;
    }

    public function getHorarioTermino(): string
    {
        return $this->horario_termino;
    }

    public function setHorarioTermino(string $horario_termino): void
    {
        $this->horario_termino = $horario_termino;
    }

    public function getLocalAula(): int
    {
        return $this->local_aula;
    }

    public function setLocalAula(int $local_aula): void
    {
        $this->local_aula = $local_aula;
    }

    public function getDataInicio(): string
    {
        return $this->data_inicio;
    }

    public function setDataInicio(string $data_inicio): void
    {
        $this->data_inicio = $data_inicio;
    }

    public function getDataFim(): ?string
    {
        return $this->data_fim;
    }

    public function setDataFim(?string $data_fim): void
    {
        $this->data_fim = $data_fim;
    }

    public function getCursoId(): int
    {
        return $this->curso_id;
    }

    public function setCursoId(int $curso_id): void
    {
        $this->curso_id = $curso_id;
    }

    public function getProfessorId(): int
    {
        return $this->professor_id;
    }

    public function setProfessorId(int $professor_id): void
    {
        $this->professor_id = $professor_id;
    }

    public function getEstagiarioId(): ?int
    {
        return $this->estagiario_id;
    }

    public function setEstagiarioId(?int $estagiario_id): void
    {
        $this->estagiario_id = $estagiario_id;
    }
}