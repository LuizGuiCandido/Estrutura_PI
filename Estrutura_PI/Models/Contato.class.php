<?php

class Contato
{
    public function __construct(
        private ?int $id_contato = null,
        private string $nome = '',
        private string $email = '',
        private string $telefone = '',
        private ?string $mensagem = null,
        private string $status = 'Novo',
        private ?string $deletado_em = null
    ) {}

    public function getIdContato(): ?int { return $this->id_contato; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getTelefone(): string { return $this->telefone; }
    public function getMensagem(): ?string { return $this->mensagem; }
    public function getStatus(): string { return $this->status; }
}