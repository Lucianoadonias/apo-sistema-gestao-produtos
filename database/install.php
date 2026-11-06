<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Database/Connection.php';

require_once __DIR__ . '/../app/Database/DatabaseInstaller.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Instalação permitida somente pelo terminal.');
}

$config = require __DIR__ . '/../config/database.php';

try {
    $installer = new DatabaseInstaller($config);

    $installer->install();

    echo "Banco de dados e tabelas criados com sucesso!\n";
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        "Falha na instalação. Verifique a configuração e o MySQL.\n"
    );

    exit(1);
}