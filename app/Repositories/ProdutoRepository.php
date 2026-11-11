<?php

declare(strict_types=1);

class ProdutoRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    // Verifica se o fornecedor selecionado existe.
    public function supplierExists(int $fornecedorId): bool
    {
        $sql = "SELECT id
                FROM fornecedores
                WHERE id = :id
                LIMIT 1";

        $statement = $this->connection->prepare($sql);

        $statement->execute([
            'id' => $fornecedorId
        ]);

        return $statement->fetch() !== false;
    }

    // Insere um produto e devolve o ID criado.
    public function create(
        string $nome,
        string $descricao,
        string $preco,
        int $fornecedorId
    ): int {
        $sql = "INSERT INTO produtos
                    (nome, descricao, preco, fornecedor_id)
                VALUES
                    (:nome, :descricao, :preco, :fornecedor_id)";

        $statement = $this->connection->prepare($sql);

        $statement->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'preco' => $preco,
            'fornecedor_id' => $fornecedorId
        ]);

        return (int) $this->connection->lastInsertId();
    }

public function findAll(): array
{
    $sql = "SELECT
                p.id,
                p.nome,
                p.descricao,
                p.preco,
                p.fornecedor_id,
                f.nome AS fornecedor_nome
            FROM produtos AS p
            INNER JOIN fornecedores AS f
                ON p.fornecedor_id = f.id
            ORDER BY p.nome ASC";

    $statement = $this->connection->query($sql);

    return $statement->fetchAll();
}

public function findById(int $id): ?array
{
    $sql = "SELECT id, nome, descricao, preco, fornecedor_id
            FROM produtos
            WHERE id = :id
            LIMIT 1";

    $statement = $this->connection->prepare($sql);
    $statement->execute(['id' => $id]);

    $produto = $statement->fetch();

    return $produto === false ? null : $produto;
}

public function update(
    int $id,
    string $nome,
    string $descricao,
    string $preco,
    int $fornecedorId
): void {
    $sql = "UPDATE produtos
            SET nome = :nome,
                descricao = :descricao,
                preco = :preco,
                fornecedor_id = :fornecedor_id
            WHERE id = :id";

    $statement = $this->connection->prepare($sql);

    $statement->execute([
        'id' => $id,
        'nome' => $nome,
        'descricao' => $descricao,
        'preco' => $preco,
        'fornecedor_id' => $fornecedorId
    ]);
}

public function delete(int $id): bool
{
    $sql = "DELETE FROM produtos
            WHERE id = :id";

    $statement = $this->connection->prepare($sql);

    $statement->execute([
        'id' => $id
    ]);

    return $statement->rowCount() > 0;
}

}