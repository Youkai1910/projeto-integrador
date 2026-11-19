<?php
namespace ProjetoIntegrador\Database;

use PDO;
use PDOException;
use RuntimeException;

final class ConexaoBD
{
    private static ?PDO $conexao = null;
    private static ?PDO $conexaoBiblia = null;

    private function __construct()
    {
    }

    public static function getConexao(): PDO
    {
        if (self::$conexao === null) {
            $database = getenv('DB_NAME') ?: 'biblia_catolica';
            self::$conexao = self::criarConexao($database);
        }

        return self::$conexao;
    }

    public static function getConexaoBiblia(): PDO
    {
        if (self::$conexaoBiblia === null) {
            $database = getenv('BIBLIA_DB_NAME') ?: (getenv('DB_NAME') ?: 'biblia_catolica');
            self::$conexaoBiblia = self::criarConexao($database);
        }

        return self::$conexaoBiblia;
    }

    private static function criarConexao(string $database): PDO
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'senacpenha';
        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                'Não foi possível conectar ao banco de dados. Contate o suporte.',
                0,
                $e
            );
        }
    }
}
