<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';

// Inicia ou recupera a sessão.
SessionManager::start();

// Verifica se o usuário está autenticado.
if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
}

// Gera o token de proteção CSRF, caso não exista.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Recupera os dados do usuário autenticado.
$usuario = SessionManager::getUser();

// Evita armazenar conteúdo protegido em cache.
header('Cache-Control: no-store');

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel - Gestão de Produtos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Barra de navegação -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">

            <span class="navbar-brand">
                Gestão de Produtos
            </span>

            <!-- Logout com POST e proteção CSRF -->
            <form action="logout.php" method="POST" class="mb-0">

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>">

                <button type="submit" class="btn btn-outline-light btn-sm">
                    Sair
                </button>

            </form>

        </div>
    </nav>

    <main class="container py-5">

        <!-- Boas-vindas -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">

                <h1 class="h4 mb-3">
                    Painel do sistema
                </h1>

                <div class="alert alert-success">
                    Usuário autenticado com sucesso!
                </div>

                <p class="mb-2">
                    Bem-vindo,
                    <strong>
                        <?= htmlspecialchars(
                            $usuario['nome'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>!
                </p>

                <p class="text-muted mb-0">
                    Selecione uma funcionalidade para continuar.
                </p>

            </div>
        </div>

        <!-- Menu principal -->
        <h2 class="h5 mb-3">
            Funcionalidades do sistema
        </h2>

        <div class="row g-4">

            <!-- Cartão: fornecedores -->
            <div class="col-12 col-md-6">

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column p-4">

                        <h3 class="h5">
                            Fornecedores
                        </h3>

                        <p class="text-muted">
                            Cadastre, consulte, edite e exclua
                            fornecedores do sistema.
                        </p>

                        <a href="fornecedores.php" class="btn btn-primary mt-auto">
                            Gerenciar fornecedores
                        </a>

                    </div>
                </div>

            </div>

            <!-- Cartão: produtos -->
            <div class="col-12 col-md-6">

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column p-4">

                        <h3 class="h5">
                            Produtos
                        </h3>

                        <p class="text-muted">
                            Cadastre, consulte, edite e exclua
                            produtos vinculados aos fornecedores.
                        </p>

                        <a href="produtos.php" class="btn btn-primary mt-auto">
                            Gerenciar produtos
                        </a>

                    </div>
                </div>

            </div>

            <!-- Cartão: seleção -->
            <div class="col-12 col-md-6">

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column p-4">

                        <h3 class="h5">
                            Seleção de produtos
                        </h3>

                        <p class="text-muted">
                            Selecione produtos utilizando checkboxes
                            e adicione-os à cesta.
                        </p>

                        <a href="selecao-produtos.php" class="btn btn-primary mt-auto">
                            Selecionar produtos
                        </a>

                    </div>
                </div>

            </div>

            <!-- Cartão: cesta -->
            <div class="col-12 col-md-6">

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column p-4">

                        <h3 class="h5">
                            Minha cesta
                        </h3>

                        <p class="text-muted">
                            Visualize os produtos selecionados,
                            a quantidade de itens e o valor total.
                        </p>

                        <a href="cesta.php" class="btn btn-primary mt-auto">
                            Visualizar cesta
                        </a>

                    </div>
                </div>

            </div>

        </div>

    </main>

</body>

</html>