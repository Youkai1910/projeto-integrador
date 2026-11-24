<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\Utils;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\BibliaServico;

$biblia = new BibliaServico();
$erros = [];
$termo = '';

if (array_key_exists('q', $_GET)) {
    try {
        $termo = Validacoes::termoBusca($_GET['q']);
    } catch (InvalidArgumentException $erro) {
        $erros[] = $erro->getMessage();
    }
}

$testamentos = $biblia->listarTestamentos();
$todosLivros = $biblia->listarLivros();
$livros = $termo === '' ? $todosLivros : $biblia->listarLivros(null, $termo);
$totalCapitulos = array_sum(array_map(function ($livro) {
    return $livro->totalCapitulos;
}, $todosLivros));
$totalVersiculos = array_sum(array_map(function ($livro) {
    return $livro->totalVersiculos;
}, $todosLivros));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta da Bíblia</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="admin-biblia-page">
    <section class="admin-biblia-painel">
        <a href="../index.php">Voltar à página inicial</a>
        <h1>Consulta da Bíblia</h1>
        <p>Esta página é somente para consulta. Ela não altera livros, capítulos ou versículos.</p>

        <div class="admin-biblia-resumo">
            <div><strong><?= count($testamentos) ?></strong><span>testamentos</span></div>
            <div><strong><?= count($todosLivros) ?></strong><span>livros</span></div>
            <div><strong><?= $totalCapitulos ?></strong><span>capítulos</span></div>
            <div><strong><?= $totalVersiculos ?></strong><span>versículos</span></div>
        </div>

        <form class="biblia-filtros" action="biblia.php" method="GET">
            <label for="busca-admin">Buscar livro</label>
            <input id="busca-admin" type="search" name="q" maxlength="200" value="<?= Utils::escaparHtml($termo) ?>">
            <button type="submit">Buscar</button>
        </form>

        <?php if ($erros): ?>
            <div class="biblia-mensagem biblia-erro" role="alert">
                <?php foreach ($erros as $erro): ?>
                    <p><?= Utils::escaparHtml($erro) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-biblia-tabela-wrap">
            <table class="admin-biblia-tabela">
                <thead>
                    <tr>
                        <th>Livro</th>
                        <th>Testamento</th>
                        <th>Capítulos com dados</th>
                        <th>Versículos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros as $livro): ?>
                        <tr>
                            <td><?= Utils::escaparHtml($livro->nome) ?></td>
                            <td><?= Utils::escaparHtml($livro->testamentoNome) ?></td>
                            <td><?= $livro->totalCapitulos ?></td>
                            <td><?= $livro->totalVersiculos ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$livros): ?>
                        <tr><td colspan="4">Nenhum livro corresponde à busca.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
