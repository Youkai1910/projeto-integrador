<?php
require_once __DIR__ . '/biblia_catolica/conexao.php';
$livros = $pdo->query('SELECT label FROM books ORDER BY id')->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bíblia</title>

</head>
<body>
<main>
    <h1>Livros da Bíblia</h1>
    <?php foreach ($livros as $livro): ?>
        <div>
            <?= htmlspecialchars($livro['label'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endforeach; ?>
</main>
</body>
</html>