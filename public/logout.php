<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';

SessionManager::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$tokenRecebido = $_POST['csrf_token'] ?? '';

$tokenSessao = $_SESSION['csrf_token'] ?? null;

if (
    !is_string($tokenRecebido) ||
    !is_string($tokenSessao) ||
    !hash_equals($tokenSessao, $tokenRecebido)
) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');

    exit('Operação não autorizada: token CSRF inválido.');
}

SessionManager::logout();

header('Location: index.php', true, 303);
exit;