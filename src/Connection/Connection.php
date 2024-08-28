<?php

namespace App\Connection;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private static ?Connection $instance = null;
    private PDO $pdo;

    private function __construct(string $dsn, string $username, string $password)
    {
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            $config = require CONFIG_PATH;
            $dbConfig = $config['database'];

            $dsn = $dbConfig['dsn'] ?? null;
            if (!$dsn) {
                $driver = $dbConfig['driver'] ?? 'mysql';
                $host = $dbConfig['host'] ?? 'localhost';
                $dbname = $dbConfig['name'] ?? 'test';
                $path = $dbConfig['path'] ?? null;

                $dsn = match ($driver) {
                    'pgsql' => "pgsql:host=$host;dbname=$dbname",
                    'sqlite' => "sqlite:$path",
                    default => "mysql:host=$host;dbname=$dbname",
                };
            }

            self::$instance = new self(
                $dsn,
                $dbConfig['username'] ?? '',
                $dbConfig['password'] ?? ''
            );
        }

        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
