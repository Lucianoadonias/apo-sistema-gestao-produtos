<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/ProdutoRepository.php';

SessionManager::start();

// Somente usuários autenticados acessam a cesta.
if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
}

header('Cache-Control: no-store');

$titulo = 'Cesta de Produtos';
$erro = '';
$produtosCesta = [];
$totalCentavos = 0;

// Identifica o usuário autenticado.
$usuario = SessionManager::getUser();
$usuarioId = (int) $usuario['id'];

// Evita reaproveitar a cesta de outro usuário.
if (
    !isset($_SESSION['cesta_produtos']) ||
    ($_SESSION['cesta_produtos']['usuario_id'] ?? null) !== $usuarioId
) {
    $_SESSION['cesta_produtos'] = [
        'usuario_id' => $usuarioId,
        'ids' => []
    ];
}

// Cada produto deverá aparecer uma única vez.
$idsCesta = array_values(array_unique(
    $_SESSION['cesta_produtos']['ids']
));

try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = Connection::connect($config);

$repository = new ProdutoRepository($pdo);

// findAll() já inclui o nome do fornecedor via INNER JOIN.
$todosProdutos = $repository->findAll();

$produtosPorId = [];

foreach ($todosProdutos as $produto) {
    $produtosPorId[(int) $produto['id']] = $produto;
}

$idsValidos = [];

foreach ($idsCesta as $id) {
    $id = (int) $id;

    if (!isset($produtosPorId[$id])) {
        continue;
    }

    $produto = $produtosPorId[$id];

    $idsValidos[] = $id;
    $produtosCesta[] = $produto;

    $partesPreco = explode(
        '.',
        (string) $produto['preco']
    );

    $reais = (int) $partesPreco[0];

    $centavos = (int) str_pad(
        $partesPreco[1] ?? '0',
        2,
        '0'
    );

    $totalCentavos += ($reais * 100) + $centavos;
}


    // Mantém somente produtos existentes na sessão.
    $_SESSION['cesta_produtos']['ids'] = $idsValidos;

} catch (PDOException $exception) {
    $erro = 'Não foi possível carregar a cesta.';
    $produtosCesta = [];
    $totalCentavos = 0;
}

$quantidadeProdutos = count($produtosCesta);

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

            <a href="selecao-produtos.php" class="btn btn-outline-light btn-sm">
                Voltar à seleção
            </a>
        </div>
    </nav>

    <main class="container py-5">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">

                <div class="card shadow-sm">

                    <div class="card-body p-4">

                        <h1 class="h4 mb-4">
                            Minha cesta de produtos
                        </h1>

                        <?php if ($erro !== ''): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $erro,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                        <?php elseif (empty($produtosCesta)): ?>

                        <div class="alert alert-info">
                            Sua cesta está vazia.
                        </div>

                        <a href="selecao-produtos.php" class="btn btn-primary">
                            Selecionar produtos
                        </a>

                        <?php else: ?>

                        <div class="table-responsive">

                            <table class="table table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Fornecedor</th>
                                        <th>Preço unitário</th>
                                        <th>Quantidade</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach (
                                    $produtosCesta as $produto
                                ): ?>

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
                                            $produto['fornecedor_nome'],
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
                                            1
                                        </td>

                                        <td class="text-nowrap">
                                            R$ <?= number_format(
                                                (float) $produto['preco'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>
                                    </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                        <div class="border-top pt-3 mt-3">

                            <p class="mb-2">
                                <strong>Quantidade de produtos:</strong>
                                <?= $quantidadeProdutos ?>
                            </p>

                            <p class="fs-5 fw-bold mb-0">
                                Total: R$
                                <?= number_format(
                                    $totalCentavos / 100,
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </p>

                        </div>

                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </main>

</body>

</html>