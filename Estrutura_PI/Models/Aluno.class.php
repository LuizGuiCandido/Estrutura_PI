<?php

    class Aluno{

        public function __construct(
            private Usuario $usuario_id,
            private int $aula_experimental,
            private ?int $responsavel_id = null,
            private string $observacoes = '',
        ){}

        //Metodos getters
        public function getUsuario(){
            return $this->usuario_id;
        }
        public function getAulaExperimental(){
            return $this->aula_experimental;
        }
        public function getResponsavelId(){
            return $this->responsavel_id;
        }
        public function getObservacao(): string{
            return $this->observacoes;
        }
        //Metodos setters
        public function setUsuario(Usuario $new){
            $this->usuario_id = $new;
        }
        public function setAulaExperimental($new){
            $this->aula_experimental = $new;
        }
        public function setResponsavelId(Usuario $new){
            $this->responsavel = $new;
        }
        public function setObservacoes($new){
            $this->observacoes = $new;
        }


    }
    




?>