<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $pdo = \ProjetoIntegrador\Database\ConexaoBD::getConexao();
    echo 'app=' . $pdo->query('SELECT DATABASE()')->fetchColumn() . PHP_EOL;

    $biblia = \ProjetoIntegrador\Database\ConexaoBD::getConexaoBiblia();
    echo 'biblia=' . $biblia->query('SELECT DATABASE()')->fetchColumn() . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
