<?php

declare(strict_types=1);

class AuthService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function authenticate(
        string $email,
        string $senha
    ): ?array {
        // Normaliza o e-mail recebido.
        $email = trim($email);

        // Rejeita campos vazios.
        if ($email === '' || $senha === '') {
            return null;
        }

        // Procura o usuário no banco de dados.
        $usuario = $this->repository->findByEmail($email);

        // Se o usuário não existe, o login falha.
        if ($usuario === null) {
            return null;
        }

        // Gera o SHA-256 da senha informada.
        $senhaHash = hash('sha256', $senha);

        // Compara o hash gerado com o armazenado.
        if (!hash_equals($usuario['senha'], $senhaHash)) {
            return null;
        }

        // Retorna somente os dados necessários.
        return [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email']
        ];
    }
}