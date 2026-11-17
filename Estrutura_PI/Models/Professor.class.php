<?php

class Professor
{
    public function __construct(
        private Usuario $usuario_id,
        private ?string $cref = null,
        private string $formacao = '',
        private string $especialidade = '',
        private int $mostrar = 1
    ) {}

    public function getUsuario(): Usuario { 
        return $this->usuario_id;
    }
    public function getCref(): ?string { 
        return $this->cref;
    }
    public function getCreef(): ?string { 
        return $this->cref;
    }
    public function getFormacao(): string { 
        return $this->formacao;
    }
    public function getEspecialidade(): string { 
        return $this->especialidade;
    }
    public function getMostrar(): int { 
        return $this->mostrar;
    }

    public function setUsuario(Usuario $new): void {
        $this->usuario_id =$new;
    }
    public function setCref(?string $new): void { 
        $this->cref =$new;
    }
    public function setFormacao(string $new): void {
        $this->formacao =$new;
    }
    public function setEspecialidade(string $new): void {
        $this->especialidade =$new;
    }
    public function setMostrar(int $new): void { 
        $this->mostrar =$new;
    }
}