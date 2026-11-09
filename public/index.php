<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Services/AuthService.php';
require_once __DIR__ . '/../app/Auth/SessionManager.php';

SessionManager::start();

$titulo = 'Login - Gestão de Produtos';

$erro = '';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

     $tokenRecebido = $_POST['csrf_token'] ?? '';

  if (
        !is_string($tokenRecebido) ||
        !hash_equals($_SESSION['csrf_token'], $tokenRecebido)
    ) {
        $erro = 'Sessão inválida. Atualize a página e tente novamente.';
    } elseif (!is_string($email) || !is_string($senha)) {
        $erro = 'E-mail ou senha inválidos.';
    } else {
        try {
            $config = require __DIR__ . '/../config/database.php';

            $pdo = Connection::connect($config);
            $repository = new UserRepository($pdo);
            $authService = new AuthService($repository);

            $usuario = $authService->authenticate($email, $senha);

            if ($usuario === null) {
                $erro = 'E-mail ou senha inválidos.';
            } else {
                SessionManager::login($usuario);

                header('Location: painel.php', true, 303);
                exit;
            }
        } catch (PDOException $exception) {
                $erro = 'Não foi possível realizar o login.';
            }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand mb-0 h1">
                Gestão de Produtos
            </span>
        </div>
    </nav>

    <main class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-md-6 col-lg-5">

                <div class="card shadow-sm">

                    <div class="card-body p-4">

                        <h1 class="h4 text-center mb-4">
                            Entrar no sistema
                        </h1>
                        <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>

                        <form action="index.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input type="email" class="form-control" id="email" name="email" autocomplete="username"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <input type="password" class="form-control" id="senha" name="senha"
                                    autocomplete="current-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Entrar
                            </button>

                        </form>

                        <p class="text-center mt-3 mb-0">
                            Não possui uma conta?
                            <a href="cadastro.php">
                                Cadastre-se
                            </a>
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </main>

</body>

</html>