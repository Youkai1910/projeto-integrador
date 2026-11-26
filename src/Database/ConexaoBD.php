<?php
namespace ProjetoIntegrador\Database;

use PDO;
use PDOException;
use RuntimeException;
use ProjetoIntegrador\Helpers\Env;

final class ConexaoBD
{
    private static ?PDO $conexao = null;

    private function __construct()
    {
    }

    public static function getConexao(): PDO
    {
        if (self::$conexao === null) {
            Env::carregar();
            $host = Env::obter('DB_HOST', 'localhost');
            $database = Env::obter('BIBLIA_DB_NAME', Env::obter('DB_NAME', 'biblia_catolica'));
            $username = Env::obter('DB_USER');
            $password = Env::obter('DB_PASSWORD');

            if ($username === null || $username === '' || $password === null) {
                throw new RuntimeException('Configure DB_USER e DB_PASSWORD antes de conectar ao banco de dados.');
            }

            $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$conexao = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new RuntimeException(
                    'Não foi possível conectar ao banco de dados. Contate o suporte.',
                    0,
                    $e
                );
            }
        }

        return self::$conexao;
    }

    public static function getConexaoBiblia(): PDO
    {
        return self::getConexao();
    }
}
