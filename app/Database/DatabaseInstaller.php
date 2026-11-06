<?php

declare(strict_types=1);

class DatabaseInstaller
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function install(): void
    {
        $this->createDatabase();

        $connection = Connection::connect(
            $this->config
        );

        $this->createTables($connection);
    }

    private function createDatabase(): void
    {
        $database = $this->config['database'];

        // Um identificador SQL não pode ser passado
        // como parâmetro de uma consulta preparada.
        // Por isso, validamos o nome antes de usá-lo.
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $database)) {
            throw new InvalidArgumentException(
                'Nome de banco de dados inválido.'
            );
        }

        $connection = Connection::connect(
            $this->config,
            false
        );

        $sql = "CREATE DATABASE IF NOT EXISTS `{$database}`
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci";

        $connection->exec($sql);
    }

    private function createTables(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                senha CHAR(64) NOT NULL
            ) ENGINE=InnoDB"
        );

        $connection->exec(
            "CREATE TABLE IF NOT EXISTS fornecedores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                telefone VARCHAR(20) NOT NULL,
                endereco VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB"
        );

        $connection->exec(
            "CREATE TABLE IF NOT EXISTS produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                descricao TEXT NOT NULL,
                preco DECIMAL(10,2) NOT NULL,
                fornecedor_id INT NOT NULL,

                CONSTRAINT fk_produtos_fornecedores
                    FOREIGN KEY (fornecedor_id)
                    REFERENCES fornecedores(id)
                    ON DELETE RESTRICT
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB"
        );
    }
}