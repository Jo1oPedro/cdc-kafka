<?php

namespace Cdc\Kafka;

use Exception;
use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private ?PDO $connection = null;

    /**
     * Construtor privado impede criação direta da instância
     */
    private function __construct() {}

    public static function connect(): PDO
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
            try {
                switch ($_ENV['driver']) {
                    case 'mysql':
                        $dsn = "mysql:host={$_ENV['host']};dbname={$_ENV['dbname']};charset=utf8;port={$_ENV['port']}";
                        break;

                    case 'pgsql':
                    case 'postgres':
                    case 'postgresql':
                        $dsn = "pgsql:host={$_ENV['host']};dbname={$_ENV['dbname']};port={$_ENV['port']}";
                        break;

                    default:
                        throw new Exception("Driver de banco de dados não suportado: {$_ENV['driver']}");
                }

                self::$instance->connection = new PDO($dsn, $_ENV['username'], $_ENV['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true,
                ]);
            } catch (PDOException $e) {
                throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
            }
        }

        return self::$instance->connection;
    }
}
