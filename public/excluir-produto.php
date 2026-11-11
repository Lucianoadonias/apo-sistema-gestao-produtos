<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/ProdutoRepository.php';
require_once __DIR__ . '/../app/Services/ProdutoService.php';

SessionManager::start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Verifica se existe usuário autenticado.
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);

    echo json_encode([
        'erro' => 'Usuário não autenticado.'
    ]);
    exit;
}

// A exclusão aceita somente POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');

    echo json_encode([
        'erro' => 'Método não permitido.'
    ]);
    exit;
}

// Verifica o token CSRF.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);

    echo json_encode([
        'erro' => 'Token CSRF inválido.'
    ]);
    exit;
}

// Valida o ID enviado pelo JavaScript.
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);

    echo json_encode([
        'erro' => 'ID do produto inválido.'
    ]);
    exit;
}

try {
    $config = require __DIR__ . '/../config/database.php';

    $pdo = Connection::connect($config);

    $repository = new ProdutoRepository($pdo);
    $service = new ProdutoService($repository);

    $excluido = $service->delete($id);

    if (!$excluido) {
        http_response_code(404);

        echo json_encode([
            'erro' => 'Produto não encontrado.'
        ]);
        exit;
    }

    echo json_encode([
        'mensagem' => 'Produto excluído com sucesso!'
    ]);

} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível excluir o produto.'
    ]);
}