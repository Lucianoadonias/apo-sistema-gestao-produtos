<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';

// Inicia ou recupera a sessão.
SessionManager::start();


if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
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

    < <nav class="navbar navbar-dark bg-primary">
        <div class="container">

            <span class="navbar-brand">
                Gestão de Produtos
            </span>

            <form action="logout.php" method="POST" class="mb-0">

                <!-- Token de proteção CSRF -->
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

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h4 mb-3">
                        Painel do sistema
                    </h1>

                    <div class="alert alert-success">
                        Usuário autenticado com sucesso!
                    </div>

                    <p>
                        Bem-vindo,
                        <strong>
                            <?= htmlspecialchars(
                            $usuario['nome'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                        </strong>!
                    </p>

                    <p class="text-muted">
                        Esta página está protegida por sessão PHP.
                    </p>

                </div>
            </div>

        </main>

</body>

</html>