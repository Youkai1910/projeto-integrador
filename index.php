<?php
require_once __DIR__ . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\Utils;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\BibliaServico;

$biblia = new BibliaServico();
$erros = [];
$termo = '';
$testamentoId = null;
$livroId = null;
$capitulo = null;
$testamentos = $biblia->listarTestamentos();

if (array_key_exists('q', $_GET)) {
    try {
        $termo = Validacoes::termoBusca($_GET['q']);
    } catch (InvalidArgumentException $erro) {
        $erros[] = $erro->getMessage();
    }
}

if (array_key_exists('testamento', $_GET)) {
    $testamentoId = Validacoes::idPositivo($_GET['testamento']);
    if ($testamentoId === null) {
        $erros[] = 'Selecione um testamento válido.';
    } elseif (!in_array($testamentoId, array_map(function ($item) {
        return $item->id;
    }, $testamentos), true)) {
        $erros[] = 'O testamento selecionado não foi encontrado.';
        $testamentoId = null;
    }
}

if (array_key_exists('livro', $_GET)) {
    $livroId = Validacoes::idPositivo($_GET['livro']);
    if ($livroId === null) {
        $erros[] = 'Selecione um livro válido.';
    }
}

if (array_key_exists('capitulo', $_GET)) {
    $capitulo = Validacoes::idPositivo($_GET['capitulo']);
    if ($capitulo === null) {
        $erros[] = 'Selecione um capítulo válido.';
    } elseif ($livroId === null) {
        $erros[] = 'Selecione um livro antes de escolher um capítulo.';
        $capitulo = null;
    }
}

$livros = $biblia->listarLivros($testamentoId, $termo);
$livroSelecionado = $livroId === null ? null : $biblia->buscarLivro($livroId);
$capitulos = [];
$versiculos = [];

if ($livroId !== null && $livroSelecionado === null) {
    $erros[] = 'O livro selecionado não foi encontrado.';
    $livroId = null;
    $capitulo = null;
} elseif ($livroSelecionado !== null) {
    if ($testamentoId !== null && $livroSelecionado->testamentoId !== $testamentoId) {
        $erros[] = 'O livro selecionado não pertence ao testamento escolhido.';
        $livroSelecionado = null;
        $livroId = null;
        $capitulo = null;
    } else {
        $capitulos = $biblia->listarCapitulos($livroSelecionado->id);
        if ($capitulo !== null) {
            if (!in_array($capitulo, $capitulos, true)) {
                $erros[] = 'O capítulo não foi encontrado para este livro.';
                $capitulo = null;
            } else {
                $versiculos = $biblia->listarVersiculos($livroSelecionado->id, $capitulo);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Diário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <header>
        <div class="logo-area">
            <img class="logo" src="images/logo.png" alt="Portal Diário">
        </div>
        <div class="menu-area">
            <nav class="banner" aria-label="Navegação principal">
                <form class="search-box" action="#" method="GET">
                    <input type="text" placeholder="Pesquisar..." name="q" aria-label="Pesquisar" value="<?= Utils::escaparHtml($termo) ?>">
                    <button type="submit" aria-label="Enviar pesquisa">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <a href="#"><i class="fa-solid fa-house"></i> HOME</a>
                <a href="#"><i class="fa-solid fa-users"></i> QUEM SOMOS</a>
                <a href="#"><i class="fa-solid fa-envelope"></i> CONTATO</a>
                <a href="#"><i class="fa-solid fa-user-plus"></i> ÁREA DE CADASTRO</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="principal">
            <div>
                <h3>LEITURA DIÁRIA</h3>
                <div class="card-principal card-leitura">Clique para ver a leitura diária</div>
            </div>
            <div>
                <h3>NOVO TESTAMENTO</h3>
                <div class="card-principal card-novo-testamento">Clique para ver o novo testamento</div>
            </div>
            <div>
                <h3>ANTIGO TESTAMENTO</h3>
                <div class="card-principal card-antigo-testamento">Clique para ver o antigo testamento</div>
            </div>
        </section>

        <section class="inferior">
            <div>
                <h3>SALMOS</h3>
                <div class="card-inferior card-salmos">Ver Salmo</div>
            </div>
            <div>
                <h3>VERSÍCULOS</h3>
                <div class="card-inferior card-versiculos">Ver Versículo</div>
            </div>
            <div>
                <h3>EVANGELHO</h3>
                <div class="card-inferior card-evangelho">Ver Evangelho</div>
            </div>
            <div>
                <h3>AUTO AJUDA</h3>
                <div class="card-inferior card-auto-ajuda">Ver Conteúdo</div>
            </div>
        </section>

        <section class="livros-biblia">
            <h3>Livros da Bíblia</h3>
            <form class="biblia-filtros" action="index.php" method="GET">
                <label for="busca-livro">Buscar livro</label>
                <input id="busca-livro" type="search" name="q" maxlength="200" value="<?= Utils::escaparHtml($termo) ?>">
                <label for="filtro-testamento">Testamento</label>
                <select id="filtro-testamento" name="testamento">
                    <option value="">Todos</option>
                    <?php foreach ($testamentos as $testamento): ?>
                        <option value="<?= $testamento->id ?>" <?= $testamentoId === $testamento->id ? 'selected' : '' ?>>
                            <?= Utils::escaparHtml($testamento->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Buscar</button>
            </form>

            <?php if ($erros): ?>
                <div class="biblia-mensagem biblia-erro" role="alert">
                    <?php foreach ($erros as $erro): ?>
                        <p><?= Utils::escaparHtml($erro) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!$livros): ?>
                <p>Nenhum livro corresponde à busca.</p>
            <?php endif; ?>

            <?php foreach ($testamentos as $testamento): ?>
                <?php
                $livrosDoTestamento = array_filter($livros, function ($livro) use ($testamento) {
                    return $livro->testamentoId === $testamento->id;
                });
                ?>
                <?php if ($livrosDoTestamento): ?>
                    <div class="grupo-livros">
                        <h4><?= Utils::escaparHtml($testamento->nome) ?></h4>
                        <div class="lista-livros">
                            <?php foreach ($livrosDoTestamento as $livro): ?>
                                <?php
                                $parametrosLivro = ['livro' => $livro->id];
                                if ($testamentoId !== null) {
                                    $parametrosLivro['testamento'] = $testamentoId;
                                }
                                if ($termo !== '') {
                                    $parametrosLivro['q'] = $termo;
                                }
                                ?>
                                <a href="?<?= Utils::escaparHtml(http_build_query($parametrosLivro)) ?>">
                                    <?= Utils::escaparHtml($livro->nome) ?>
                                    <small>(<?= $livro->totalCapitulos ?> capítulos)</small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($livroSelecionado !== null): ?>
                <section class="selecao-biblia" aria-labelledby="titulo-leitura">
                    <h4 id="titulo-leitura"><?= Utils::escaparHtml($livroSelecionado->nome) ?></h4>
                    <?php if (!$capitulos): ?>
                        <p>Ainda não há capítulos com versículos cadastrados para este livro.</p>
                    <?php else: ?>
                        <form class="biblia-filtros" action="index.php" method="GET">
                            <input type="hidden" name="livro" value="<?= $livroSelecionado->id ?>">
                            <?php if ($testamentoId !== null): ?>
                                <input type="hidden" name="testamento" value="<?= $testamentoId ?>">
                            <?php endif; ?>
                            <?php if ($termo !== ''): ?>
                                <input type="hidden" name="q" value="<?= Utils::escaparHtml($termo) ?>">
                            <?php endif; ?>
                            <label for="capitulo">Capítulo</label>
                            <select id="capitulo" name="capitulo">
                                <?php foreach ($capitulos as $numeroCapitulo): ?>
                                    <option value="<?= $numeroCapitulo ?>" <?= $capitulo === $numeroCapitulo ? 'selected' : '' ?>>
                                        <?= $numeroCapitulo ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Ler capítulo</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($capitulo !== null): ?>
                        <h5>Capítulo <?= $capitulo ?></h5>
                        <div class="lista-versiculos">
                            <?php foreach ($versiculos as $versiculo): ?>
                                <p>
                                    <strong><?= $versiculo->capitulo ?>:<?= $versiculo->numero ?></strong>
                                    <?= Utils::escaparHtml($versiculo->texto) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-col">
            <h4>Portal Diário</h4>
            <p>Um espaço dedicado a nutrir a sua espiritualidade todos os dias com conteúdos atualizados, leituras, salmos e reflexões.</p>
        </div>
        <div class="footer-col">
            <h4>Links Rápidos</h4>
            <a href="#">Política de Privacidade</a>
            <a href="#">Termos de Uso</a>
            <a href="#">Seja um Colaborador</a>
        </div>
        <div class="footer-col">
            <h4>Fale Conosco</h4>
            <p><i class="fa-solid fa-envelope"></i> contato@portaldiario.com</p>
            <p><i class="fa-solid fa-phone"></i> (00) 0000-0000</p>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Portal Diário. Todos os direitos reservados.
        </div>
    </footer>
</div>
</body>
</html>