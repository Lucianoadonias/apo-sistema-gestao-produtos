<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../app/Repositories/ProdutoRepository.php';
require_once __DIR__ . '/../app/Services/ProdutoService.php';

SessionManager::start();

if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
}

header('Cache-Control: no-store');

$titulo = 'Cadastro de Produtos';
$erro = '';
$sucesso = $_SESSION['produto_sucesso'] ?? '';

unset($_SESSION['produto_sucesso']);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$fornecedores = [];
$produtos = [];

try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = Connection::connect($config);

    $fornecedorRepository = new FornecedorRepository($pdo);
    $fornecedores = $fornecedorRepository->findAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            throw new InvalidArgumentException(
                'Token CSRF inválido.'
            );
        }

        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $preco = $_POST['preco'] ?? '';
        $fornecedorId = filter_var(
            $_POST['fornecedor_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !is_string($nome) ||
            !is_string($descricao) ||
            !is_string($preco) ||
            $fornecedorId === false ||
            $fornecedorId === null
        ) {
            throw new InvalidArgumentException(
                'Dados do produto inválidos.'
            );
        }

        $repository = new ProdutoRepository($pdo);
        $service = new ProdutoService($repository);

        $service->register(
            $nome,
            $descricao,
            $preco,
            $fornecedorId
        );

        $_SESSION['produto_sucesso'] =
            'Produto cadastrado com sucesso!';

        header('Location: produtos.php', true, 303);
        exit;
    }
        // Carrega os produtos para a listagem.
    $produtoRepository = new ProdutoRepository($pdo);

    $produtos = $produtoRepository->findAll();

} catch (InvalidArgumentException $exception) {
    $erro = $exception->getMessage();

} catch (PDOException $exception) {
    $erro = 'Não foi possível acessar o banco de dados.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                Gestão de Produtos
            </span>

            <a href="painel.php" class="btn btn-outline-light btn-sm">
                Voltar ao painel
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                <div class="card shadow-sm">
                    <div class="card-body p-4">

                        <h1 class="h4 mb-4">
                            Cadastro de produtos
                        </h1>

                        <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $erro, ENT_QUOTES, 'UTF-8'
                            ) ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($sucesso !== ''): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars(
                                $sucesso, ENT_QUOTES, 'UTF-8'
                            ) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (empty($fornecedores)): ?>
                        <div class="alert alert-warning">
                            Cadastre um fornecedor antes
                            de cadastrar produtos.
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="produtos.php">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                $_SESSION['csrf_token'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">

                            <div class="mb-3">
                                <label for="nome" class="form-label">
                                    Nome
                                </label>

                                <input type="text" id="nome" name="nome" class="form-control" maxlength="150" required>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">
                                    Descrição
                                </label>

                                <textarea id="descricao" name="descricao" class="form-control" rows="3"
                                    required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="preco" class="form-label">
                                    Preço (R$)
                                </label>

                                <input type="number" id="preco" name="preco" class="form-control" min="0.01"
                                    max="99999999.99" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="fornecedor_id" class="form-label">
                                    Fornecedor
                                </label>

                                <select id="fornecedor_id" name="fornecedor_id" class="form-select" required>
                                    <option value="">
                                        Selecione um fornecedor
                                    </option>

                                    <?php foreach (
                                    $fornecedores as $fornecedor
                                ): ?>
                                    <option value="<?= (int) $fornecedor['id'] ?>">
                                        <?= htmlspecialchars(
                                            $fornecedor['nome'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" <?= empty($fornecedores) ? 'disabled' : '' ?>>
                                Cadastrar produto
                            </button>

                        </form>

                    </div>
                </div>

                <!-- Listagem de produtos -->
                <div class="card shadow-sm mt-4">

                    <div class="card-body p-4">

                        <h2 class="h5 mb-3">
                            Produtos cadastrados
                        </h2>

                        <?php if (empty($produtos)): ?>

                        <p class="text-muted mb-0">
                            Nenhum produto cadastrado.
                        </p>

                        <?php else: ?>

                        <div class="table-responsive">

                            <table class="table table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Preço</th>
                                        <th>Fornecedor</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($produtos as $produto): ?>

                                    <tr>
                                        <td>
                                            <?= htmlspecialchars(
                                        $produto['nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                        $produto['descricao'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>

                                        <td class="text-nowrap">
                                            R$ <?= number_format(
                                        (float) $produto['preco'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                        $produto['fornecedor_nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>
                                    </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>
        </div>
    </main>

</body>

</html>