<?php

    class Usuario{

        public function __construct(
        protected ?int $id_usuario = null,
        protected string $nome = '',
        protected string $email = '',
        protected string $cpf = '',
        protected string $senha = '',
        protected string $telefone = '',
        protected string $data_nascimento = '',
        protected string $endereco = '',
        protected int $gestor = 0
    ) {}

        //Metodos getters
        public function getIdUsuario(){
            return $this->id_usuario;
        }
        public function getNome(){
            return $this->nome;
        }
        public function getEmail(){
            return $this->email;
        }
        public function getCPF(){
            return $this->cpf;
        }
        public function getSenha(){
            return $this->senha;
        }
        public function getTelefone(){
            return $this->telefone;
        }
        public function getDataNascimento(){
            return $this->data_nascimento;
        }
        public function getEndereco(){
            return $this->endereco;
        }
        public function getGestor(){
            return $this->gestor;;
        }
        //Metodos setters
        public function setIdUsuario($new){
            $this->id_usuario = $new;
        }
        public function setNome($new){
            $this->nome = $new;
        }
        public function setEmail($new){
            $this->email = $new;
        }
        public function setCPF($new){
            $this->cpf = $new;
        }
        public function setSenha($new){
            $this->senha = $new;
        }
        public function setTelefone($new){
            $this->telefone = $new;
        }
        public function setDataNascimento($new){
            $this->data_nascimento = $new;
        }
        public function setEndereco($new){
            $this->endereco = $new;
        }
        public function setGestor($new){
            $this->gestor = $new;
        }

    }


?>