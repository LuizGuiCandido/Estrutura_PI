<?php

class Curso
{
    public function __construct(
        private ?int $id_curso = null,
        private string $nome = '',
        private ?string $descricao = null,
        private string $faixa_etaria = '',
        private int $categoria_id = 0,
        private int $mostrar = 1,
        private ?string $deletado_em = null
    ) {}

    public function getIdCurso(): ?int { return $this->id_curso; }
    public function setIdCurso(?int $id): void { $this->id_curso = $id; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = $nome; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }

    public function getFaixaEtaria(): string { return $this->faixa_etaria; }
    public function setFaixaEtaria(string $faixa): void { $this->faixa_etaria = $faixa; }

    public function getCategoriaId(): int { return $this->categoria_id; }
    public function setCategoriaId(int $id): void { $this->categoria_id = $id; }

    public function getMostrar(): int { return $this->mostrar; }
    public function setMostrar(int $mostrar): void { $this->mostrar = $mostrar; }
}