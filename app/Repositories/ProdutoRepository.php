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

}