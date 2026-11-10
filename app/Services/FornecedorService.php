<?php

declare(strict_types=1);

class FornecedorService
{
    private FornecedorRepository $repository;

    public function __construct(FornecedorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(
        string $nome,
        string $email,
        string $telefone,
        string $endereco
    ): int {

        $nome = trim($nome);
        $email = trim($email);
        $telefone = trim($telefone);
        $endereco = trim($endereco);

        if (
            $nome === '' ||
            $email === '' ||
            $telefone === '' ||
            $endereco === ''
        ) {
            throw new InvalidArgumentException(
                'Preencha todos os campos obrigatórios.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Informe um e-mail válido.'
            );
        }

        return $this->repository->create(
            $nome,
            $email,
            $telefone,
            $endereco
        );
    }

public function update(
    int $id,
    string $nome,
    string $email,
    string $telefone,
    string $endereco
): bool {
    if ($id <= 0) {
        throw new InvalidArgumentException(
            'ID do fornecedor inválido.'
        );
    }

    $nome = trim($nome);
    $email = trim($email);
    $telefone = trim($telefone);
    $endereco = trim($endereco);

    if (
        $nome === '' ||
        $email === '' ||
        $telefone === '' ||
        $endereco === ''
    ) {
        throw new InvalidArgumentException(
            'Preencha todos os campos obrigatórios.'
        );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException(
            'Informe um e-mail válido.'
        );
    }

    return $this->repository->update(
        $id,
        $nome,
        $email,
        $telefone,
        $endereco
    );
}

public function delete(int $id): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException(
            'ID do fornecedor inválido.'
        );
    }

    return $this->repository->delete($id);
}

}