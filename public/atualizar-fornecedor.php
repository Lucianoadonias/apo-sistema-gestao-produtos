<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../app/Services/FornecedorService.php';

SessionManager::start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Permite apenas usuários autenticados.
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit;
}

// Permite somente requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['erro' => 'Método não permitido.']);
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
    echo json_encode(['erro' => 'Token CSRF inválido.']);
    exit;
}

// Recebe os campos enviados pelo JavaScript.
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$endereco = $_POST['endereco'] ?? '';

// Valida os tipos recebidos.
if (
    $id === false ||
    $id === null ||
    !is_string($nome) ||
    !is_string($email) ||
    !is_string($telefone) ||
    !is_string($endereco)
) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.']);
    exit;
}

try {
    $config = require __DIR__ . '/../config/database.php';

    $pdo = Connection::connect($config);

    $repository = new FornecedorRepository($pdo);
    $service = new FornecedorService($repository);

    $atualizado = $service->update(
        $id,
        $nome,
        $email,
        $telefone,
        $endereco
    );

    if (!$atualizado) {
        echo json_encode([
            'mensagem' => 'Nenhuma alteração foi necessária ou o fornecedor não foi encontrado.'
        ]);
        exit;
    }

    echo json_encode([
        'mensagem' => 'Fornecedor atualizado com sucesso!'
    ]);

} catch (InvalidArgumentException $exception) {
    http_response_code(400);

    echo json_encode([
        'erro' => $exception->getMessage()
    ]);

} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível atualizar o fornecedor.'
    ]);
}