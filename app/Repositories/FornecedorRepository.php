<?php

declare(strict_types=1);

class FornecedorRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(
        string $nome,
        string $email,
        string $telefone,
        string $endereco
    ): int {
        $sql = "INSERT INTO fornecedores
                    (nome, email, telefone, endereco)
                VALUES
                    (:nome, :email, :telefone, :endereco)";

        $statement = $this->connection->prepare($sql);

        $statement->execute([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'endereco' => $endereco
        ]);       

 return (int) $this->connection->lastInsertId();
    }

public function findAll(): array
{
    $sql = "SELECT
                id,
                nome,
                email,
                telefone,
                endereco
            FROM fornecedores
            ORDER BY nome ASC";

    $statement = $this->connection->query($sql);

    return $statement->fetchAll();
}


public function update(
    int $id,
    string $nome,
    string $email,
    string $telefone,
    string $endereco
): bool {
    $sql = "UPDATE fornecedores
            SET nome = :nome,
                email = :email,
                telefone = :telefone,
                endereco = :endereco
            WHERE id = :id";

    $statement = $this->connection->prepare($sql);

    $statement->execute([
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'endereco' => $endereco
    ]);

    return $statement->rowCount() > 0;
}
public function delete(int $id): bool
{
    $sql = "DELETE FROM fornecedores
            WHERE id = :id";

    $statement = $this->connection->prepare($sql);

    $statement->execute([
        'id' => $id
    ]);

    return $statement->rowCount() > 0;
}

}