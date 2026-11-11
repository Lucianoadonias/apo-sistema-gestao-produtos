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

                <div id="secao-cadastro-produto" class="card shadow-sm">
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

                <!-- Formulário de edição: oculto inicialmente -->
                <div id="secao-edicao-produto" class="card shadow-sm mt-4 d-none">

                    <div class="card-body p-4">

                        <h2 class="h5 mb-3">Editar produto</h2>

                        <form id="form-editar-produto">

                            <input type="hidden" id="editar-produto-id" name="id">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">

                            <div class="mb-3">
                                <label for="editar-produto-nome" class="form-label">
                                    Nome
                                </label>

                                <input type="text" id="editar-produto-nome" name="nome" class="form-control"
                                    maxlength="150" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-produto-descricao" class="form-label">
                                    Descrição
                                </label>

                                <textarea id="editar-produto-descricao" name="descricao" class="form-control" rows="3"
                                    required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editar-produto-preco" class="form-label">
                                    Preço (R$)
                                </label>

                                <input type="number" id="editar-produto-preco" name="preco" class="form-control"
                                    min="0.01" max="99999999.99" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar-produto-fornecedor" class="form-label">
                                    Fornecedor
                                </label>

                                <select id="editar-produto-fornecedor" name="fornecedor_id" class="form-select"
                                    required>
                                    <option value="">Selecione um fornecedor</option>

                                    <?php foreach ($fornecedores as $fornecedor): ?>
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

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Salvar alterações
                                </button>

                                <button type="button" id="cancelar-edicao-produto" class="btn btn-secondary">
                                    Cancelar
                                </button>
                            </div>

                            <div id="mensagem-edicao-produto" class="mt-3" role="status" aria-live="polite"></div>

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
                                        <th>Ações</th>
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

                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary btn-editar-produto"
                                                data-id="<?= (int) $produto['id'] ?>"
                                                data-nome="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descricao="<?= htmlspecialchars($produto['descricao'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-preco="<?= htmlspecialchars((string) $produto['preco'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-fornecedor-id="<?= (int) $produto['fornecedor_id'] ?>">
                                                Editar
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-excluir-produto"
                                                data-id="<?= (int) $produto['id'] ?>" data-nome="<?= htmlspecialchars(
                                                    $produto['nome'],
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
    const secaoCadastro = document.getElementById(
        'secao-cadastro-produto'
    );

    const secaoEdicao = document.getElementById(
        'secao-edicao-produto'
    );

    const formularioEdicao = document.getElementById(
        'form-editar-produto'
    );

    const mensagem = document.getElementById(
        'mensagem-edicao-produto'
    );

    // Abre o formulário de edição com os dados selecionados.
    document.querySelectorAll('.btn-editar-produto')
        .forEach((botao) => {

            botao.addEventListener('click', () => {
                const dados = botao.dataset;

                document.getElementById('editar-produto-id').value =
                    dados.id;

                document.getElementById('editar-produto-nome').value =
                    dados.nome;

                document.getElementById('editar-produto-descricao').value =
                    dados.descricao;

                document.getElementById('editar-produto-preco').value =
                    dados.preco;

                document.getElementById('editar-produto-fornecedor').value =
                    dados.fornecedorId;

                mensagem.textContent = '';
                mensagem.className = 'mt-3';

                secaoCadastro.classList.add('d-none');
                secaoEdicao.classList.remove('d-none');

                secaoEdicao.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

    // Cancela a edição sem modificar o produto.
    document.getElementById('cancelar-edicao-produto')
        .addEventListener('click', () => {

            formularioEdicao.reset();
            secaoEdicao.classList.add('d-none');
            secaoCadastro.classList.remove('d-none');

            mensagem.textContent = '';
            mensagem.className = 'mt-3';
        });

    // Salva as alterações utilizando AJAX.
    formularioEdicao.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const dados = new FormData(formularioEdicao);
        const botaoSalvar = formularioEdicao.querySelector(
            'button[type="submit"]'
        );

        mensagem.textContent = '';
        mensagem.className = 'mt-3';
        botaoSalvar.disabled = true;

        try {
            const resposta = await fetch('atualizar-produto.php', {
                method: 'POST',
                body: dados
            });

            const resultado = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    resultado.erro || 'Erro ao atualizar produto.'
                );
            }

            // Encontra o botão do produto atualizado.
            const id = String(dados.get('id'));

            const botaoEditar = [
                ...document.querySelectorAll('.btn-editar-produto')
            ].find((botao) => botao.dataset.id === id);

            if (botaoEditar) {
                const linha = botaoEditar.closest('tr');
                const celulas = linha.querySelectorAll('td');

                const nome = String(dados.get('nome')).trim();
                const descricao = String(dados.get('descricao')).trim();
                const preco = Number(
                    String(dados.get('preco')).replace(',', '.')
                );

                const fornecedorSelect = document.getElementById(
                    'editar-produto-fornecedor'
                );

                const fornecedorId = fornecedorSelect.value;

                const fornecedorNome =
                    fornecedorSelect.selectedOptions[0].textContent.trim();

                // Atualiza a tabela sem recarregar a página.
                celulas[0].textContent = nome;
                celulas[1].textContent = descricao;

                celulas[2].textContent = preco.toLocaleString(
                    'pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }
                );

                celulas[3].textContent = fornecedorNome;

                // Mantém os dados do botão Editar atualizados.
                botaoEditar.dataset.nome = nome;
                botaoEditar.dataset.descricao = descricao;
                botaoEditar.dataset.preco = preco.toFixed(2);
                botaoEditar.dataset.fornecedorId = fornecedorId;
            }

            mensagem.textContent = resultado.mensagem;
            mensagem.classList.add('alert', 'alert-success');

        } catch (erro) {
            mensagem.textContent = erro.message;
            mensagem.classList.add('alert', 'alert-danger');

        } finally {
            botaoSalvar.disabled = false;
        }
    });


    // Localiza todos os botões Excluir da tabela.
    const botoesExcluirProduto = document.querySelectorAll(
        '.btn-excluir-produto'
    );

    botoesExcluirProduto.forEach((botao) => {
        botao.addEventListener('click', async () => {

            const id = botao.dataset.id;
            const nome = botao.dataset.nome;

            // Solicita confirmação antes de excluir.
            const confirmado = confirm(
                `Deseja realmente excluir o produto "${nome}"?`
            );

            // Cancelar encerra a operação sem acessar o servidor.
            if (!confirmado) {
                return;
            }

            // Recupera o token do formulário de edição.
            const token = document.querySelector(
                '#form-editar-produto input[name="csrf_token"]'
            ).value;

            // Prepara os dados que serão enviados ao PHP.
            const dados = new FormData();

            dados.append('id', id);
            dados.append('csrf_token', token);

            // Impede cliques repetidos durante a requisição.
            botao.disabled = true;

            try {
                const resposta = await fetch('excluir-produto.php', {
                    method: 'POST',
                    body: dados
                });

                const resultado = await resposta.json();

                if (!resposta.ok) {
                    throw new Error(
                        resultado.erro || 'Erro ao excluir produto.'
                    );
                }

                // Localiza a linha antes de removê-la.
                const linha = botao.closest('tr');

                // Remove somente a linha confirmada pelo servidor.
                linha.remove();

                // Se a edição estava aberta para esse produto,
                // fecha o formulário de edição.
                const idEmEdicao = document.getElementById(
                    'editar-produto-id'
                ).value;

                if (idEmEdicao === id) {
                    formularioEdicao.reset();

                    secaoEdicao.classList.add('d-none');
                    secaoCadastro.classList.remove('d-none');
                }

                // Exibe o resultado retornado pelo PHP.
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