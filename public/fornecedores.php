<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Auth/SessionManager.php';
require_once __DIR__ . '/../app/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../app/Services/FornecedorService.php';

SessionManager::start();

if (!SessionManager::isAuthenticated()) {
    header('Location: index.php', true, 303);
    exit;
}

header('Cache-Control: no-store');

$titulo = 'Fornecedores - Gestão de Produtos';
$erro = '';
$sucesso = $_SESSION['fornecedor_sucesso'] ?? '';

unset($_SESSION['fornecedor_sucesso']);

// Cria um token CSRF para o formulário.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Sessão inválida. Atualize a página.';

    } elseif (
        !is_string($nome) ||
        !is_string($email) ||
        !is_string($telefone) ||
        !is_string($endereco)
    ) {
        $erro = 'Dados inválidos.';

    } else {
        try {
         $config = require __DIR__ . '/../config/database.php';

            // Cria a conexão PDO.
            $pdo = Connection::connect($config);

            $repository = new FornecedorRepository($pdo);
            $service = new FornecedorService($repository);

            $service->register(
                $nome,
                $email,
                $telefone,
                $endereco
            );

            $_SESSION['fornecedor_sucesso'] =
                'Fornecedor cadastrado com sucesso!';

            header('Location: fornecedores.php', true, 303);
            exit;

        } catch (InvalidArgumentException $exception) {
            $erro = $exception->getMessage();

        } catch (PDOException $exception) {
            $erro = 'Não foi possível cadastrar o fornecedor.';
        }
    }
}


$fornecedores = [];

try {
    $config = require __DIR__ . '/../config/database.php';

    $pdo = Connection::connect($config);

    $repository = new FornecedorRepository($pdo);

    $fornecedores = $repository->findAll();

} catch (PDOException $exception) {
    $erro = 'Não foi possível carregar os fornecedores.';
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

            <a href="painel.php" class="btn btn-outline-light btn-sm">
                Voltar ao painel
            </a>
        </div>
    </nav>

    <main class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-lg-8">

                <div id="secao-cadastro" class="card shadow-sm">

                    <div class="card-body p-4">

                        <h1 class="h4 mb-4">
                            Cadastro de fornecedores
                        </h1>

                        <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($sucesso !== ''): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>

                        <form action="fornecedores.php" method="POST">
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
                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input type="email" id="email" name="email" class="form-control" maxlength="150"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="telefone" class="form-label">
                                    Telefone
                                </label>

                                <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="endereco" class="form-label">
                                    Endereço
                                </label>

                                <input type="text" id="endereco" name="endereco" class="form-control" maxlength="255"
                                    required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Cadastrar fornecedor
                            </button>

                        </form>

                    </div>

                </div>

                <div id="secao-edicao" class="card shadow-sm mt-4 d-none">

                    <div class="card-body p-4">

                        <h2 class="h5 mb-3">
                            Editar fornecedor
                        </h2>

                        <form id="form-editar-fornecedor">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                            <input type="hidden" id="editar-id" name="id">

                            <div class="mb-3">
                                <label for="editar-nome" class="form-label">
                                    Nome
                                </label>
                                <input type="text" id="editar-nome" name="nome" class="form-control" maxlength="150"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-email" class="form-label">
                                    E-mail
                                </label>
                                <input type="email" id="editar-email" name="email" class="form-control" maxlength="150"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-telefone" class="form-label">
                                    Telefone
                                </label>
                                <input type="tel" id="editar-telefone" name="telefone" class="form-control"
                                    maxlength="20" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-endereco" class="form-label">
                                    Endereço
                                </label>
                                <input type="text" id="editar-endereco" name="endereco" class="form-control"
                                    maxlength="255" required>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Salvar alterações
                                </button>

                                <button type="button" id="cancelar-edicao" class="btn btn-secondary">
                                    Cancelar
                                </button>
                            </div>

                            <div id="mensagem-edicao" class="mt-3" role="status" aria-live="polite"></div>

                        </form>

                    </div>

                </div>

                <div class="card shadow-sm mt-4">

                    <div class="card-body p-4">

                        <h2 class="h5 mb-3">
                            Fornecedores cadastrados
                        </h2>

                        <?php if (empty($fornecedores)): ?>

                        <p class="text-muted mb-0">
                            Nenhum fornecedor cadastrado.
                        </p>

                        <?php else: ?>

                        <div class="table-responsive">

                            <table class="table table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Telefone</th>
                                        <th>Endereço</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($fornecedores as $fornecedor): ?>

                                    <tr>
                                        <td>
                                            <?= htmlspecialchars(
                                        $fornecedor['nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                        $fornecedor['email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                        $fornecedor['telefone'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                        $fornecedor['endereco'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-editar"
                                                data-id="<?= (int) $fornecedor['id'] ?>"
                                                data-nome="<?= htmlspecialchars($fornecedor['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-email="<?= htmlspecialchars($fornecedor['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-telefone="<?= htmlspecialchars($fornecedor['telefone'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-endereco="<?= htmlspecialchars($fornecedor['endereco'], ENT_QUOTES, 'UTF-8') ?>">
                                                Editar
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger btn-excluir"
                                                data-id="<?= (int) $fornecedor['id'] ?>" data-nome="<?= htmlspecialchars(
                                                    $fornecedor['nome'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>">
                                                Excluir
                                            </button>
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

    <script>
    const secaoEdicao = document.getElementById('secao-edicao');
    const formularioEdicao = document.getElementById('form-editar-fornecedor');
    const botaoCancelar = document.getElementById('cancelar-edicao');
    const secaoCadastro = document.getElementById('secao-cadastro');

    const botoesEditar = document.querySelectorAll('.btn-editar');

    botoesEditar.forEach((botao) => {
        botao.addEventListener('click', () => {

            const dados = botao.dataset;

            document.getElementById('editar-id').value = dados.id;
            document.getElementById('editar-nome').value = dados.nome;
            document.getElementById('editar-email').value = dados.email;
            document.getElementById('editar-telefone').value = dados.telefone;
            document.getElementById('editar-endereco').value = dados.endereco;


            document.getElementById('mensagem-edicao').textContent = '';

            secaoCadastro.classList.add('d-none');

            secaoEdicao.classList.remove('d-none');

            secaoEdicao.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    botaoCancelar.addEventListener('click', () => {
        // Limpa os campos de edição.
        formularioEdicao.reset();

        // Esconde a edição.
        secaoEdicao.classList.add('d-none');

        // Mostra novamente o cadastro.
        secaoCadastro.classList.remove('d-none');
    });


    formularioEdicao.addEventListener('submit', async (evento) => {
        // Impede o envio tradicional do formulário.
        evento.preventDefault();

        const mensagem = document.getElementById('mensagem-edicao');
        const botaoSalvar = formularioEdicao.querySelector(
            'button[type="submit"]'
        );

        // Recupera os dados do formulário, incluindo o CSRF.
        const dados = new FormData(formularioEdicao);

        mensagem.textContent = '';
        mensagem.className = 'mt-3';

        // Evita envios repetidos enquanto a requisição acontece.
        botaoSalvar.disabled = true;

        try {
            const resposta = await fetch('atualizar-fornecedor.php', {
                method: 'POST',
                body: dados
            });

            const resultado = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    resultado.erro || 'Não foi possível atualizar.'
                );
            }

            // Exibe a mensagem retornada pelo PHP.
            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add('alert', 'alert-success');

            // Atualiza os dados exibidos sem recarregar a página.
            const id = dados.get('id');
            const botaoEditar = [...document.querySelectorAll('.btn-editar')]
                .find((botao) => botao.dataset.id === id);

            if (botaoEditar) {
                const linha = botaoEditar.closest('tr');

                const campos = ['nome', 'email', 'telefone', 'endereco'];
                const celulas = linha.querySelectorAll('td');

                campos.forEach((campo, indice) => {
                    const valor = String(dados.get(campo)).trim();

                    // Atualiza o texto na tabela com segurança.
                    celulas[indice].textContent = valor;

                    // Mantém os atributos do botão sincronizados.
                    botaoEditar.dataset[campo] = valor;
                });
            }

        } catch (erro) {
            mensagem.textContent = erro.message;
            mensagem.classList.add('alert', 'alert-danger');

        } finally {
            // Permite novas tentativas.
            botaoSalvar.disabled = false;
        }
    });


    const botoesExcluir = document.querySelectorAll('.btn-excluir');

    botoesExcluir.forEach((botao) => {
        botao.addEventListener('click', async () => {

            const id = botao.dataset.id;
            const nome = botao.dataset.nome;

            // Solicita confirmação antes de excluir.
            const confirmado = confirm(
                `Deseja realmente excluir o fornecedor "${nome}"?`
            );

            if (!confirmado) {
                return;
            }

            // Recupera o token CSRF do formulário de cadastro.
            const token = document.querySelector(
                'input[name="csrf_token"]'
            ).value;

            // Prepara os dados da requisição.
            const dados = new FormData();

            dados.append('id', id);
            dados.append('csrf_token', token);

            // Impede cliques repetidos.
            botao.disabled = true;

            try {
                const resposta = await fetch('excluir-fornecedor.php', {
                    method: 'POST',
                    body: dados
                });

                const resultado = await resposta.json();

                if (!resposta.ok) {
                    throw new Error(
                        resultado.erro || 'Erro ao excluir fornecedor.'
                    );
                }

                // Remove a linha da tabela sem recarregar a página.
                botao.closest('tr').remove();

                alert(resultado.mensagem);

            } catch (erro) {
                alert(erro.message);

            } finally {
                botao.disabled = false;
            }
        });
    });
    </script>

</body>

</html>