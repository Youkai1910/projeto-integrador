<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\LogServico;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\FavoritoBiblicoServico;
use ProjetoIntegrador\Services\LeituraBiblicaServico;
use ProjetoIntegrador\Services\UsuarioServico;

require_once dirname(__DIR__) . '/vendor/autoload.php';

LogServico::configurar();
Seguranca::iniciarSessao();

$usuarioSessao = $_SESSION['usuario'] ?? null;
$idUsuarioSessao = is_array($usuarioSessao)
    ? filter_var($usuarioSessao['id'] ?? null, FILTER_VALIDATE_INT)
    : false;
$estaLogado = $idUsuarioSessao !== false && $idUsuarioSessao > 0;
$ehAdministrador = false;
$ehEditor = false;

if ($estaLogado) {
    try {
        $servicoUsuario = new UsuarioServico(ConexaoBD::getConexao());
        $ehAdministrador = $servicoUsuario->ehAdministrador((int) $idUsuarioSessao);
        $ehEditor = $servicoUsuario->ehEditor((int) $idUsuarioSessao);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

$servico = null;
$usuarioSessao = $_SESSION['usuario'] ?? null;
$usuarioId = is_array($usuarioSessao)
    ? Validacoes::idPositivo($usuarioSessao['id'] ?? null)
    : null;
$mensagemFavoritoBiblico = $_SESSION['favorito_biblico_flash'] ?? '';
unset($_SESSION['favorito_biblico_flash']);
$estadoFavoritosBiblicos = ['capitulo' => false, 'versiculos' => []];
$tokenFavoritosBiblicos = null;
$idLivroRecebido = filter_input(INPUT_GET, 'livro', FILTER_VALIDATE_INT);
$capituloRecebido = filter_input(INPUT_GET, 'capitulo', FILTER_VALIDATE_INT);
$idTestamentoRecebido = filter_input(INPUT_GET, 'testamento', FILTER_VALIDATE_INT);
$aleatoria = isset($_GET['aleatoria']) && $_GET['aleatoria'] === '1';
$buscaRecebida = $_GET['q'] ?? '';
$busca = is_string($buscaRecebida) ? substr(trim($buscaRecebida), 0, 100) : '';
$erroLeitura = '';
$livro = null;
$capitulos = [];
$versiculos = [];
$testamentoSelecionado = null;
$referenciaAleatoria = null;
$numeroCapitulo = $capituloRecebido !== false && $capituloRecebido !== null && $capituloRecebido > 0
    ? (int) $capituloRecebido
    : null;

try {
    $servico = new LeituraBiblicaServico(ConexaoBD::getConexaoBiblia());
    if ($aleatoria) {
        $referenciaAleatoria = $servico->obterVersiculoAleatorio();
        if ($referenciaAleatoria !== null) {
            $idLivroRecebido = (int) $referenciaAleatoria['book'];
            $numeroCapitulo = (int) $referenciaAleatoria['chapter'];
        }
    }

    if ($idLivroRecebido !== false && $idLivroRecebido !== null && $idLivroRecebido > 0) {
        $livro = $servico->obterLivro((int) $idLivroRecebido);
        if ($livro === null) {
            http_response_code(404);
        } else {
            $capitulos = $servico->listarCapitulos((int) $livro['id']);
            if ($numeroCapitulo !== null) {
                if (!in_array($numeroCapitulo, $capitulos, true)) {
                    http_response_code(404);
                    $numeroCapitulo = null;
                } else {
                    $versiculos = $servico->listarVersiculos((int) $livro['id'], $numeroCapitulo);
                }
            }
        }
    }

    if ($idTestamentoRecebido !== false && $idTestamentoRecebido !== null) {
        $testamentoSelecionado = in_array((int) $idTestamentoRecebido, [1, 2], true)
            ? (int) $idTestamentoRecebido
            : null;
    }

    $livros = $servico->listarLivros($busca, $testamentoSelecionado);
    $nomeTestamento = $testamentoSelecionado !== null
        ? $servico->obterTestamento($testamentoSelecionado)
        : null;
    if ($usuarioId !== null && $livro !== null && $numeroCapitulo !== null) {
        $servicoFavoritosBiblicos = new FavoritoBiblicoServico(ConexaoBD::getConexao());
        $estadoFavoritosBiblicos = $servicoFavoritosBiblicos->estadoNoTrecho(
            $usuarioId,
            (int) $livro['id'],
            $numeroCapitulo
        );
        $tokenFavoritosBiblicos = Seguranca::obterTokenCsrf();
    }
} catch (\Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $erroLeitura = 'Não foi possível carregar a leitura. Verifique a conexão e tente novamente.';
    $livros = [];
    $nomeTestamento = null;
}

$livroReferencia = $referenciaAleatoria !== null
    ? $servico->obterLivro((int) $referenciaAleatoria['book'])
    : null;
$livroAnterior = null;
$livroSeguinte = null;
if ($livro !== null) {
    foreach ($livros as $livroListado) {
        if ((int) $livroListado['id'] === (int) $livro['id'] - 1) {
            $livroAnterior = $livroListado;
        }
        if ((int) $livroListado['id'] === (int) $livro['id'] + 1) {
            $livroSeguinte = $livroListado;
        }
    }
}

function escaparLeitura(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Leia os livros, capítulos e versículos da Bíblia Católica no Portal Bíblico.">
    <title><?= $livro !== null ? escaparLeitura((string) $livro['label']) . ($numeroCapitulo !== null ? ' ' . $numeroCapitulo : '') . ' | ' : '' ?>Leitura bíblica | Portal Bíblico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
    <link rel="stylesheet" href="css/leitura.css?v=<?= filemtime(__DIR__ . '/css/leitura.css') ?>">
</head>
<body>
<a class="skip-link" href="#conteudo-principal">Pular para o conteúdo principal</a>
<div class="container">
    <header class="leitura-topo">
        <a class="leitura-marca" href="index.php"><img src="images/logo-pequena.png" alt="Portal Bíblico"></a>
        <button class="leitura-menu-toggle" type="button" aria-expanded="false" aria-controls="leitura-menu" id="leitura-menu-toggle" aria-label="Abrir ou fechar o menu">
            <span aria-hidden="true">☰</span>
        </button>
        <nav class="leitura-nav" id="leitura-menu" aria-label="Navegação da leitura">
            <a href="index.php"><i class="fa-solid fa-house"></i> HOME</a>
            <a href="leitura.php?testamento=1"><i class="fa-solid fa-scroll"></i> Antigo Testamento</a>
            <a href="leitura.php?testamento=2"><i class="fa-solid fa-book-open"></i> Novo Testamento</a>
            <?php if ($ehAdministrador || $ehEditor): ?>
                <a href="admin/usuarios.php"><i class="fa-solid fa-user-gear"></i> ADMINISTRAÇÃO</a>
            <?php endif; ?>
            <?php if ($estaLogado): ?>
                <a href="conta.php"><i class="fa-solid fa-user"></i> MINHA CONTA</a>
                <a href="admin/anotacoes.php"><i class="fa-solid fa-bookmark"></i> ANOTAÇÕES</a>
            <?php endif; ?>
            <?php if (!$estaLogado): ?>
                <a href="cadastro.php"><i class="fa-solid fa-user-plus"></i> ÁREA DE CADASTRO</a>
                <a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> ENTRAR</a>
            <?php else: ?>
                <form class="logout-form" method="post" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar(Seguranca::obterTokenCsrf()) ?>">
                    <button type="submit">SAIR</button>
                </form>
            <?php endif; ?>
        </nav>
        <script>
            (function () {
                var botao = document.getElementById('leitura-menu-toggle');
                var menu = document.getElementById('leitura-menu');
                if (!botao || !menu) return;
                botao.addEventListener('click', function () {
                    var aberto = botao.getAttribute('aria-expanded') === 'true';
                    botao.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                    menu.classList.toggle('aberto', !aberto);
                });
            }());
        </script>
    </header>

    <main class="leitura-main" id="conteudo-principal" tabindex="-1">
        <div class="leitura-cabecalho">
            <div>
                <p class="sobretitulo">BÍBLIA</p>
                <h1><?= $livro !== null ? escaparLeitura((string) $livro['label']) : ($nomeTestamento !== null ? escaparLeitura($nomeTestamento) : 'Escolha um livro') ?></h1>
                <p>Selecione um livro e capítulo para abrir a leitura completa.</p>
            </div>
            <a class="voltar-inicio" href="index.php">Voltar à página inicial</a>
        </div>

        <?php if (is_string($mensagemFavoritoBiblico) && $mensagemFavoritoBiblico !== ''): ?>
            <p class="favorito-biblico-mensagem" role="status"><?= escaparLeitura($mensagemFavoritoBiblico) ?></p>
        <?php endif; ?>

        <?php if ($erroLeitura !== ''): ?>
            <p class="leitura-alerta" role="alert"><?= escaparLeitura($erroLeitura) ?></p>
        <?php endif; ?>

        <?php if ($aleatoria && $referenciaAleatoria !== null && $livroReferencia !== null): ?>
            <?php
            $numeroVersiculoAleatorio = (int) $referenciaAleatoria['verse'];
            $versiculoAleatorioFavorito = isset($estadoFavoritosBiblicos['versiculos'][$numeroVersiculoAleatorio]);
            ?>
            <aside class="versiculo-dia">
                <span>UMA LEITURA PARA HOJE</span>
                <blockquote>
                    “<?= escaparLeitura((string) $referenciaAleatoria['text']) ?>”
                    <?php if ($usuarioId !== null): ?>
                        <button
                            class="botao-favorito-biblico"
                            type="submit"
                            form="favorito-versiculo-dia"
                            aria-label="<?= $versiculoAleatorioFavorito ? 'Remover versículo do dia dos favoritos' : 'Salvar versículo do dia nos favoritos' ?>"
                            title="<?= $versiculoAleatorioFavorito ? 'Remover dos favoritos' : 'Salvar nos favoritos' ?>"
                        ><?= $versiculoAleatorioFavorito ? '★' : '☆' ?></button>
                        <form id="favorito-versiculo-dia" action="favorito-biblico.php" method="POST" hidden>
                            <input type="hidden" name="csrf_token" value="<?= escaparLeitura((string) $tokenFavoritosBiblicos) ?>">
                            <input type="hidden" name="tipo" value="versiculo">
                            <input type="hidden" name="livro_id" value="<?= (int) $referenciaAleatoria['book'] ?>">
                            <input type="hidden" name="capitulo" value="<?= (int) $referenciaAleatoria['chapter'] ?>">
                            <input type="hidden" name="versiculo" value="<?= $numeroVersiculoAleatorio ?>">
                            <input type="hidden" name="favorito" value="<?= $versiculoAleatorioFavorito ? '0' : '1' ?>">
                            <input type="hidden" name="origem" value="leitura">
                        </form>
                    <?php endif; ?>
                </blockquote>
                <a href="leitura.php?livro=<?= (int) $referenciaAleatoria['book'] ?>&amp;capitulo=<?= (int) $referenciaAleatoria['chapter'] ?>#versiculo-<?= (int) $referenciaAleatoria['verse'] ?>">
                    <?= escaparLeitura((string) $livroReferencia['label']) ?> <?= (int) $referenciaAleatoria['chapter'] ?>:<?= (int) $referenciaAleatoria['verse'] ?> · Ler capítulo
                </a>
            </aside>
        <?php endif; ?>

        <?php if ($livro !== null): ?>
            <section class="capitulos" aria-labelledby="titulo-capitulos">
                <div class="secao-leitura-titulo"><h2 id="titulo-capitulos">Capítulos</h2><span><?= count($capitulos) ?> disponíveis</span></div>
                <button class="capitulos-toggle" type="button" aria-expanded="false" aria-controls="grade-capitulos" id="capitulos-toggle">
                    <span>Escolher capítulo</span>
                    <span class="capitulos-toggle-atual"><?= $numeroCapitulo !== null ? 'Capítulo ' . $numeroCapitulo : 'Selecionar' ?></span>
                    <span class="capitulos-toggle-icone" aria-hidden="true">☰</span>
                </button>
                <nav class="grade-capitulos" id="grade-capitulos" aria-label="Capítulos de <?= escaparLeitura((string) $livro['label']) ?>">
                    <?php foreach ($capitulos as $capitulo): ?>
                        <a class="numero-capitulo <?= $numeroCapitulo === $capitulo ? 'selecionado' : '' ?>" href="leitura.php?livro=<?= (int) $livro['id'] ?>&amp;capitulo=<?= $capitulo ?>#texto" <?= $numeroCapitulo === $capitulo ? 'aria-current="page"' : '' ?>><?= $capitulo ?></a>
                    <?php endforeach; ?>
                </nav>
                <script>
                    (function () {
                        var botao = document.getElementById('capitulos-toggle');
                        var grade = document.getElementById('grade-capitulos');
                        if (!botao || !grade) return;
                        botao.addEventListener('click', function () {
                            var aberto = botao.getAttribute('aria-expanded') === 'true';
                            botao.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                            grade.classList.toggle('aberta', !aberto);
                        });
                    }());
                </script>
            </section>

            <?php if ($numeroCapitulo !== null): ?>
                <article class="texto-capitulo" id="texto">
                    <header class="texto-capitulo-cabecalho">
                        <div><p class="sobretitulo">LEITURA</p><h2><?= escaparLeitura((string) $livro['label']) ?> <?= $numeroCapitulo ?></h2></div>
                        <div class="navegacao-capitulo">
                            <?php $indiceCapitulo = array_search($numeroCapitulo, $capitulos, true); ?>
                            <?php if ($indiceCapitulo !== false && $indiceCapitulo > 0): ?>
                                <a href="leitura.php?livro=<?= (int) $livro['id'] ?>&amp;capitulo=<?= $capitulos[$indiceCapitulo - 1] ?>#texto" aria-label="Capítulo anterior">← Anterior</a>
                            <?php endif; ?>
                            <?php if ($indiceCapitulo !== false && isset($capitulos[$indiceCapitulo + 1])): ?>
                                <a href="leitura.php?livro=<?= (int) $livro['id'] ?>&amp;capitulo=<?= $capitulos[$indiceCapitulo + 1] ?>#texto" aria-label="Próximo capítulo">Próximo →</a>
                            <?php endif; ?>
                        </div>
                    </header>
                    <?php if ($versiculos === []): ?>
                        <p>Este capítulo não possui versículos disponíveis.</p>
                    <?php else: ?>
                    <?php if ($usuarioId === null): ?>
                        <p class="favorito-biblico-login"><a href="login.php">Entre na sua conta</a> para salvar versículos e capítulos favoritos.</p>
                    <?php endif; ?>
                    <div class="leitura-audio-controles" role="group" aria-label="Controles de leitura em voz alta">
                        <button type="button" id="ouvir-capitulo">Ouvir capítulo</button>
                        <button type="button" id="pausar-leitura" disabled>Pausar</button>
                        <button type="button" id="continuar-leitura" disabled>Continuar</button>
                        <button type="button" id="parar-leitura" disabled>Parar</button>
                        <p id="status-leitura-audio" class="leitura-audio-status" role="status" aria-live="polite"></p>
                    </div>
                        <ol class="lista-versiculos">
                            <?php foreach ($versiculos as $versiculo): ?>
                                <?php
                                $numeroVersiculo = (int) $versiculo['verse'];
                                $versiculoFavorito = isset($estadoFavoritosBiblicos['versiculos'][$numeroVersiculo]);
                                $formFavoritoVersiculo = 'favorito-versiculo-' . $numeroCapitulo . '-' . $numeroVersiculo;
                                ?>
                                <li id="versiculo-<?= $numeroVersiculo ?>">
                                    <span class="numero-versiculo"><?= (int) $versiculo['verse'] ?></span>
                                    <p>
                                        <?= escaparLeitura((string) $versiculo['text']) ?>
                                        <?php if ($usuarioId !== null): ?>
                                            <button
                                                class="botao-favorito-biblico"
                                                type="submit"
                                                form="<?= $formFavoritoVersiculo ?>"
                                                aria-label="<?= $versiculoFavorito ? 'Remover versículo dos favoritos' : 'Salvar versículo nos favoritos' ?>"
                                                title="<?= $versiculoFavorito ? 'Remover dos favoritos' : 'Salvar nos favoritos' ?>"
                                            ><?= $versiculoFavorito ? '★' : '☆' ?></button>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($usuarioId !== null): ?>
                                        <form id="<?= $formFavoritoVersiculo ?>" action="favorito-biblico.php" method="POST" hidden>
                                            <input type="hidden" name="csrf_token" value="<?= escaparLeitura((string) $tokenFavoritosBiblicos) ?>">
                                            <input type="hidden" name="tipo" value="versiculo">
                                            <input type="hidden" name="livro_id" value="<?= (int) $livro['id'] ?>">
                                            <input type="hidden" name="capitulo" value="<?= $numeroCapitulo ?>">
                                            <input type="hidden" name="versiculo" value="<?= $numeroVersiculo ?>">
                                            <input type="hidden" name="favorito" value="<?= $versiculoFavorito ? '0' : '1' ?>">
                                            <input type="hidden" name="origem" value="leitura">
                                        </form>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                        <?php if ($usuarioId !== null): ?>
                            <form class="favorito-biblico-capitulo" action="favorito-biblico.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= escaparLeitura((string) $tokenFavoritosBiblicos) ?>">
                                <input type="hidden" name="tipo" value="capitulo">
                                <input type="hidden" name="livro_id" value="<?= (int) $livro['id'] ?>">
                                <input type="hidden" name="capitulo" value="<?= $numeroCapitulo ?>">
                                <input type="hidden" name="versiculo" value="0">
                                <input type="hidden" name="favorito" value="<?= $estadoFavoritosBiblicos['capitulo'] ? '0' : '1' ?>">
                                <input type="hidden" name="origem" value="leitura">
                                <button
                                    class="botao-favorito-biblico"
                                    type="submit"
                                    aria-label="<?= $estadoFavoritosBiblicos['capitulo'] ? 'Remover capítulo dos favoritos' : 'Salvar capítulo nos favoritos' ?>"
                                ><?= $estadoFavoritosBiblicos['capitulo'] ? 'Remover capítulo dos favoritos ★' : 'Salvar capítulo nos favoritos ☆' ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <nav class="troca-livro" aria-label="Navegar entre livros">
                        <?php if ($livroAnterior !== null): ?><a href="leitura.php?livro=<?= (int) $livroAnterior['id'] ?>">← <?= escaparLeitura((string) $livroAnterior['label']) ?></a><?php endif; ?>
                        <?php if ($livroSeguinte !== null): ?><a href="leitura.php?livro=<?= (int) $livroSeguinte['id'] ?>"><?= escaparLeitura((string) $livroSeguinte['label']) ?> →</a><?php endif; ?>
                    </nav>
                </article>
            <?php endif; ?>
        <?php elseif ($erroLeitura === ''): ?>
            <form class="busca-leitura" method="get" action="leitura.php">
                <?php if ($testamentoSelecionado !== null): ?><input type="hidden" name="testamento" value="<?= $testamentoSelecionado ?>"><?php endif; ?>
                <label for="busca-livro">Encontrar livro</label>
                <div><input id="busca-livro" type="search" name="q" value="<?= escaparLeitura($busca) ?>" placeholder="Ex.: Gênesis, Salmos, Mateus"><button type="submit">Pesquisar</button></div>
            </form>
            <?php if ($livros === []): ?>
                <p class="sem-livros">Nenhum livro encontrado para essa pesquisa.</p>
            <?php else: ?>
                <div class="grade-livros">
                    <?php foreach ($livros as $livroItem): ?>
                        <a class="cartao-livro" href="leitura.php?livro=<?= (int) $livroItem['id'] ?>">
                            <span class="livro-testamento"><?= escaparLeitura((string) ($livroItem['testament'] === 1 ? 'Antigo Testamento' : 'Novo Testamento')) ?></span>
                            <strong><?= escaparLeitura((string) $livroItem['label']) ?></strong>
                            <span class="abrir-livro">Abrir leitura <span aria-hidden="true">→</span></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="leitura-rodape"><a href="index.php">Portal Bíblico</a><span>Leia capítulo por capítulo e acompanhe cada versículo.</span></footer>
</div>
<?php if ($numeroCapitulo !== null && $versiculos !== []): ?>
<script>
    (function () {
        var botaoOuvir = document.getElementById('ouvir-capitulo');
        var botaoPausar = document.getElementById('pausar-leitura');
        var botaoContinuar = document.getElementById('continuar-leitura');
        var botaoParar = document.getElementById('parar-leitura');
        var status = document.getElementById('status-leitura-audio');
        var textos = Array.prototype.map.call(
            document.querySelectorAll('#texto .lista-versiculos li p'),
            function (paragrafo) {
                return paragrafo.textContent.trim();
            }
        ).filter(Boolean);

        if (!('speechSynthesis' in window) || typeof window.SpeechSynthesisUtterance !== 'function') {
            botaoOuvir.disabled = true;
            status.textContent = 'A leitura em voz alta não é compatível com este navegador.';
            return;
        }

        var sintetizador = window.speechSynthesis;
        var indiceVersiculo = 0;
        var sessao = 0;
        var leituraAtiva = false;
        var leituraPausada = false;

        function atualizarControles() {
            botaoOuvir.disabled = leituraAtiva;
            botaoPausar.disabled = !leituraAtiva || leituraPausada;
            botaoContinuar.disabled = !leituraAtiva || !leituraPausada;
            botaoParar.disabled = !leituraAtiva;
        }

        function falarProximoVersiculo(sessaoAtual) {
            if (sessaoAtual !== sessao || !leituraAtiva) {
                return;
            }

            if (indiceVersiculo >= textos.length) {
                leituraAtiva = false;
                leituraPausada = false;
                atualizarControles();
                status.textContent = 'Leitura concluída.';
                return;
            }

            var fala = new SpeechSynthesisUtterance(textos[indiceVersiculo]);
            fala.lang = 'pt-BR';
            var vozPortugues = sintetizador.getVoices().find(function (voz) {
                return voz.lang.toLowerCase() === 'pt-br';
            }) || sintetizador.getVoices().find(function (voz) {
                return voz.lang.toLowerCase().indexOf('pt-') === 0;
            });

            if (vozPortugues) {
                fala.voice = vozPortugues;
            }

            fala.onend = function () {
                if (sessaoAtual !== sessao || !leituraAtiva) {
                    return;
                }

                indiceVersiculo += 1;
                falarProximoVersiculo(sessaoAtual);
            };

            fala.onerror = function () {
                if (sessaoAtual !== sessao) {
                    return;
                }

                sessao += 1;
                leituraAtiva = false;
                leituraPausada = false;
                sintetizador.cancel();
                atualizarControles();
                status.textContent = 'Não foi possível reproduzir a leitura em voz alta.';
            };

            sintetizador.speak(fala);
        }

        botaoOuvir.addEventListener('click', function () {
            sessao += 1;
            sintetizador.cancel();
            indiceVersiculo = 0;
            leituraAtiva = true;
            leituraPausada = false;
            atualizarControles();
            status.textContent = 'Leitura em voz alta iniciada.';
            falarProximoVersiculo(sessao);
        });

        botaoPausar.addEventListener('click', function () {
            if (!leituraAtiva || leituraPausada) {
                return;
            }

            sintetizador.pause();
            leituraPausada = true;
            atualizarControles();
            status.textContent = 'Leitura pausada.';
        });

        botaoContinuar.addEventListener('click', function () {
            if (!leituraAtiva || !leituraPausada) {
                return;
            }

            sintetizador.resume();
            leituraPausada = false;
            atualizarControles();
            status.textContent = 'Leitura continuando.';
        });

        botaoParar.addEventListener('click', function () {
            sessao += 1;
            leituraAtiva = false;
            leituraPausada = false;
            sintetizador.cancel();
            indiceVersiculo = 0;
            atualizarControles();
            status.textContent = 'Leitura interrompida.';
        });

        window.addEventListener('pagehide', function () {
            sessao += 1;
            sintetizador.cancel();
        });
    }());
</script>
<?php endif; ?>
</body>
</html>
