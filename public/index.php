<?php

declare(strict_types=1);

$titulo = 'Sistema de Gestão de Produtos';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <main class="container py-5">
        <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>

        <p class="text-muted">
            Ambiente PHP configurado com sucesso!
        </p>
    </main>
</body>

</html>