<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/ProdutoRepository.php';
require_once __DIR__ . '/../app/Services/ProdutoService.php';

SessionManager::start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    echo json_encode(['erro' => 'Token CSRF inválido.']);
    exit;
}

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

$nome = $_POST['nome'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$preco = $_POST['preco'] ?? '';

$fornecedorId = filter_var(
    $_POST['fornecedor_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $id === false ||
    $id === null ||
    $fornecedorId === false ||
    $fornecedorId === null ||
    !is_string($nome) ||
    !is_string($descricao) ||
    !is_string($preco)
) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.']);
    exit;
}

try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = Connection::connect($config);

    $repository = new ProdutoRepository($pdo);
    $service = new ProdutoService($repository);

    $service->update(
        $id,
        $nome,
        $descricao,
        $preco,
        $fornecedorId
    );

    echo json_encode([
        'mensagem' => 'Produto atualizado com sucesso!'
    ]);

} catch (InvalidArgumentException $exception) {
    http_response_code(400);

    echo json_encode([
        'erro' => $exception->getMessage()
    ]);

} catch (OutOfBoundsException $exception) {
    http_response_code(404);

    echo json_encode([
        'erro' => $exception->getMessage()
    ]);

} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível atualizar o produto.'
    ]);
}