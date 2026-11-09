<?php

declare(strict_types=1);

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $usuario): void
    {
        self::start();

        // Renova o identificador da sessão após o login.
        session_regenerate_id(true);

        // Guarda apenas os dados necessários do usuário.
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email']
        ];
    }

    public static function isAuthenticated(): bool
    {
        self::start();

        return isset($_SESSION['usuario']['id']);
    }

    public static function getUser(): ?array
    {
        self::start();

        return self::isAuthenticated()
            ? $_SESSION['usuario']
            : null;
    }

    public static function logout(): void
    {
        self::start();

        // Remove os dados de autenticação.
        unset($_SESSION['usuario']);

        // Renova o identificador para não reutilizá-lo.
        session_regenerate_id(true);
    }
}