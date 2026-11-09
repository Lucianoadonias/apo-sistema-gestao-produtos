<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Services/UserService.php';

$titulo = 'Cadastro de Usuário';
$erro = '';
$sucesso = $_SESSION['cadastro_sucesso'] ?? '';
unset($_SESSION['cadastro_sucesso']);

// Cria um token CSRF para esta sessão.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Processa somente requisições POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Formulário inválido. Atualize a página.';

    } else {

        // Recebe apenas valores do tipo string.
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmarSenha'] ?? '';

        if (
            !is_string($nome) ||
            !is_string($email) ||
            !is_string($senha) ||
            !is_string($confirmarSenha)
        ) {
            $erro = 'Dados inválidos.';

        } else {

            try {
                $config = require __DIR__
                    . '/../config/database.php';

                $pdo = Connection::connect($config);

                $repository = new UserRepository($pdo);
                $service = new UserService($repository);

                $service->register(
                    $nome,
                    $email,
                    $senha,
                    $confirmarSenha
                );

                // Mensagem que aparecerá após o redirecionamento.
                $_SESSION['cadastro_sucesso'] =
                    'Usuário cadastrado com sucesso!';

                // Renova o token após o cadastro.
                $_SESSION['csrf_token'] =
                    bin2hex(random_bytes(32));

                header('Location: cadastro.php', true, 303);
                exit;

            } catch (InvalidArgumentException $exception) {
                $erro = $exception->getMessage();

            } catch (PDOException $exception) {
                // Código MySQL 1062: valor duplicado em chave UNIQUE.
                $codigoMysql = $exception->errorInfo[1] ?? null;

                if ((int) $codigoMysql === 1062) {
                    $erro = 'Este e-mail já está cadastrado.';
                } else {
                    $erro = 'Erro ao realizar o cadastro.';
                }
            }
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
            <span class="navbar-brand">
                Gestão de Produtos
            </span>

            <a href="index.php" class="btn btn-light btn-sm">
                Entrar
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        <h1 class="h4 text-center mb-2">
                            Cadastro de Usuário
                        </h1>

                        <p class="text-muted text-center mb-4">
                            Preencha os dados abaixo para criar sua conta.
                        </p>

                        <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars(
                                    $erro,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($sucesso !== ''): ?>
                        <div class="alert alert-success" role="status">
                            <?= htmlspecialchars(
                                    $sucesso,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="cadastro.php">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                            <div class="mb-3">
                                <label for="nome" class="form-label">
                                    Nome
                                </label>

                                <input type="text" id="nome" name="nome" class="form-control"
                                    placeholder="Digite seu nome completo" autocomplete="name" maxlength="150" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Digite seu e-mail" autocomplete="email" maxlength="150" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <input type="password" id="senha" name="senha" class="form-control"
                                    placeholder="Digite sua senha" autocomplete="new-password" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirmarSenha" class="form-label">
                                    Confirmar Senha
                                </label>

                                <input type="password" id="confirmarSenha" name="confirmarSenha" class="form-control"
                                    placeholder="Confirme sua senha" autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Cadastrar
                            </button>

                        </form>

                        <p class="text-center mt-4 mb-0">
                            Já tem uma conta?
                            <a href="index.php">Faça login</a>
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>