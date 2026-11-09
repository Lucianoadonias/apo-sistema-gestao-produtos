<?php

declare(strict_types=1);

class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(
        string $nome,
        string $email,
        string $senha,
        string $confirmarSenha
    ): int {
        // Remove espaços desnecessários.
        $nome = trim($nome);
        $email = trim($email);

        // Valida os campos obrigatórios.
        if ($nome === '' || $email === '' || $senha === '') {
            throw new InvalidArgumentException(
                'Preencha todos os campos obrigatórios.'
            );
        }

        // Verifica o formato do e-mail.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Informe um e-mail válido.'
            );
        }

        // Compara a senha com sua confirmação.
        if ($senha !== $confirmarSenha) {
            throw new InvalidArgumentException(
                'As senhas não coincidem.'
            );
        }

        // Gera o hash SHA-256 exigido pela APO.
        $senhaHash = hash('sha256', $senha);

        // Envia os dados validados ao repositório.
        return $this->repository->create(
            $nome,
            $email,
            $senhaHash
        );
    }
}