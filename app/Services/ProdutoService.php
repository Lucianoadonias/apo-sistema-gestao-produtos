<?php

declare(strict_types=1);

class ProdutoService
{
    private ProdutoRepository $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(
        string $nome,
        string $descricao,
        string $preco,
        int $fornecedorId
    ): int {
        $nome = trim($nome);
        $descricao = trim($descricao);
        $preco = trim($preco);

        // Valida os campos obrigatórios.
        if (
            $nome === '' ||
            $descricao === '' ||
            $preco === ''
        ) {
            throw new InvalidArgumentException(
                'Preencha todos os campos obrigatórios.'
            );
        }

        // Respeita o tamanho definido no banco.
        if (mb_strlen($nome, 'UTF-8') > 150) {
            throw new InvalidArgumentException(
                'O nome deve ter no máximo 150 caracteres.'
            );
        }

        // Aceita valores como 150.50 ou 150,50.
        if (!preg_match('/^\d{1,8}([.,]\d{1,2})?$/', $preco)) {
            throw new InvalidArgumentException(
                'Informe um preço válido.'
            );
        }

        // Padroniza o separador decimal.
        $preco = str_replace(',', '.', $preco);

        if ((float) $preco <= 0) {
            throw new InvalidArgumentException(
                'O preço deve ser maior que zero.'
            );
        }

        // Verifica a associação com o fornecedor.
        if (
            $fornecedorId <= 0 ||
            !$this->repository->supplierExists($fornecedorId)
        ) {
            throw new InvalidArgumentException(
                'Selecione um fornecedor válido.'
            );
        }

        return $this->repository->create(
            $nome,
            $descricao,
            $preco,
            $fornecedorId
        );
    }
}
