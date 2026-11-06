<?php

declare(strict_types=1);

class Connection
{
    public static function connect(
        array $config,
        bool $withDatabase = true
    ): PDO {
        $host = $config['host'];
        $port = $config['port'];

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

        if ($withDatabase) {
            $database = $config['database'];
            $dsn .= ";dbname={$database}";
        }

        return new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}