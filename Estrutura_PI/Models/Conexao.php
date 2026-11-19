<?php

class Conexao
{
    protected PDO $db;

    /** Conexão única compartilhada por todos os DAOs da requisição. */
    private static ?PDO $compartilhada = null;

    public function __construct(?PDO $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
            return;
        }

        if (self::$compartilhada === null) {
            $host   = 'localhost';
            $dbname = 'academia_bill';
            $user   = 'root';
            $pass   = '';

            try {
                self::$compartilhada = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
            }
        }

        $this->db = self::$compartilhada;
    }

    public function getConexao(): PDO
    {
        return $this->db;
    }
}
