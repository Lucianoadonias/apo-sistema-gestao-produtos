<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/ProdutoRepository.php';

SessionManager::start();

if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
}

header('Cache-Control: no-store');

$titulo = 'Seleção de Produtos';
$erro = '';
$sucesso = $_SESSION['selecao_sucesso'] ?? '';

unset($_SESSION['selecao_sucesso']);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Recupera o usuário autenticado.
$usuario = SessionManager::getUser();
$usuarioId = (int) $usuario['id'];

// Mantém a cesta vinculada ao usuário atual.
if (
    !isset($_SESSION['cesta_produtos']) ||
    ($_SESSION['cesta_produtos']['usuario_id'] ?? null) !== $usuarioId
) {
    $_SESSION['cesta_produtos'] = [
        'usuario_id' => $usuarioId,
        'ids' => []
    ];
}

$produtos = [];

try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = Connection::connect($config);

    $repository = new ProdutoRepository($pdo);

    // Processa a seleção enviada pelo formulário.
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

        $selecionados = $_POST['produtos'] ?? [];

        if (
            !is_array($selecionados) ||
            empty($selecionados)
        ) {
            throw new InvalidArgumentException(
                'Selecione pelo menos um produto.'
            );
        }

        $ids = [];

        // Valida individualmente cada ID recebido.
        foreach ($selecionados as $valor) {

            if (!is_string($valor)) {
                throw new InvalidArgumentException(
                    'Seleção de produtos inválida.'
                );
            }

            $id = filter_var(
                $valor,
                FILTER_VALIDATE_INT
            );

            if ($id === false || $id <= 0) {
                throw new InvalidArgumentException(
                    'Seleção de produtos inválida.'
                );
            }

            // Verifica se o produto existe no banco.
            if ($repository->findById($id) === null) {
                throw new InvalidArgumentException(
                    'Um dos produtos selecionados não existe.'
                );
            }

            $ids[] = $id;
        }

        // Remove IDs repetidos da seleção recebida.
        $ids = array_values(array_unique($ids));

        // Recupera o conteúdo atual da cesta.
        $idsAtuais = $_SESSION['cesta_produtos']['ids'];

        // Adiciona novos produtos sem repetir os existentes.
        $_SESSION['cesta_produtos']['ids'] = array_values(
            array_unique(array_merge($idsAtuais, $ids))
        );

        $_SESSION['selecao_sucesso'] =
            'Produtos incluídos na cesta com sucesso!';

        // Evita reenviar o POST quando pressionar F5.
        header('Location: selecao-produtos.php', true, 303);
        exit;
    }

    // Carrega os produtos para exibir na página.
    $produtos = $repository->findAll();

} catch (InvalidArgumentException $exception) {
    $erro = $exception->getMessage();

} catch (PDOException $exception) {
    $erro = 'Não foi possível consultar os produtos.';
}

$quantidadeCesta = count($_SESSION['cesta_produtos']['ids']);

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
            <div class="col-12 col-lg-10">

                <div class="card shadow-sm">

                    <div class="card-body p-4">

                        <h1 class="h4 mb-3">
                            Seleção de produtos
                        </h1>

                        <p class="text-muted">
                            Selecione os produtos que deseja
                            adicionar à cesta.
                            Cada produto corresponde a uma unidade.
                        </p>

                        <p class="fw-semibold">
                            Produtos na cesta:
                            <span class="badge bg-primary">
                                <?= $quantidadeCesta ?>
                            </span>
                        </p>
                        <a href="cesta.php" class="btn btn-outline-primary mb-4">
                            Visualizar cesta
                        </a>

                        <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $erro,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($sucesso !== ''): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars(
                                $sucesso,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (empty($produtos)): ?>

                        <p class="text-muted">
                            Nenhum produto disponível.
                        </p>

                        <?php else: ?>

                        <form method="POST" action="selecao-produtos.php" id="form-selecao">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                            <div class="table-responsive">

                                <table class="table table-striped align-middle">

                                    <thead>
                                        <tr>
                                            <th>Selecionar</th>
                                            <th>Produto</th>
                                            <th>Preço</th>
                                            <th>Fornecedor</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($produtos as $produto): ?>

                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input checkbox-produto"
                                                    name="produtos[]" value="<?= (int) $produto['id'] ?>" aria-label="Selecionar <?= htmlspecialchars(
                                                        $produto['nome'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>" <?= in_array(
                                                        (int) $produto['id'],
                                                        $_SESSION['cesta_produtos']['ids'],
                                                        true
                                                    ) ? 'checked' : '' ?>>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $produto['nome'],
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

                            <p id="contador-selecao" class="text-muted" aria-live="polite"></p>

                            <button type="submit" id="botao-adicionar" class="btn btn-primary" disabled>
                                Adicionar à cesta
                            </button>

                        </form>

                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
    const formulario = document.getElementById('form-selecao');

    if (formulario) {
        const checkboxes = formulario.querySelectorAll(
            '.checkbox-produto'
        );

        const contador = document.getElementById(
            'contador-selecao'
        );

        const botaoAdicionar = document.getElementById(
            'botao-adicionar'
        );

        function atualizarSelecao() {
            const quantidade = formulario.querySelectorAll(
                '.checkbox-produto:checked'
            ).length;

            contador.textContent =
                `${quantidade} produto(s) selecionado(s).`;

            // Impede o envio sem nenhum produto selecionado.
            botaoAdicionar.disabled = quantidade === 0;
        }

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener(
                'change',
                atualizarSelecao
            );
        });

        atualizarSelecao();
    }
    </script>

</body>

</html>