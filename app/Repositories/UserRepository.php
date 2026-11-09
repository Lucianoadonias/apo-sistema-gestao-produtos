<?php

declare(strict_types=1);

class UserRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(
        string $nome,
        string $email,
        string $senhaHash
    ): int {
        $sql = "INSERT INTO usuarios (nome, email, senha)
                VALUES (:nome, :email, :senha)";

        $statement = $this->connection->prepare($sql);

        $statement->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senhaHash
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, nome, email, senha
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

        $statement = $this->connection->prepare($sql);

        $statement->execute([
        'email' => $email
        ]);

        $usuario = $statement->fetch();

        return $usuario === false ? null : $usuario;
    }
}