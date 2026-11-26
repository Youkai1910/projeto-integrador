<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
use ProjetoIntegrador\Helpers\Utils;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\AnotacaoServico;
use ProjetoIntegrador\Services\FavoritoBiblicoServico;
use ProjetoIntegrador\Helpers\Seguranca;

Seguranca::iniciarSessao();

$usuarioSessao = $_SESSION['usuario'] ?? null;
$usuarioId = is_array($usuarioSessao)
    ? Validacoes::idPositivo($usuarioSessao['id'] ?? null)
    : null;
if ($usuarioId === null) {
    http_response_code(401);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acesso necessário</title>
    </head>
    <body>
        <main>
            <h1>Acesso necessário</h1>
            <p>Entre na sua conta para acessar suas anotações.</p>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if (!isset($_SESSION['anotacoes_csrf'])) {
    $_SESSION['anotacoes_csrf'] = bin2hex(random_bytes(32));
}
$tokenCsrf = $_SESSION['anotacoes_csrf'];
$tokenFavoritosCsrf = Seguranca::obterTokenCsrf();
$servico = new AnotacaoServico();
$servicoFavoritosBiblicos = new FavoritoBiblicoServico();
$erros = [];
$mensagem = '';
$mensagemFavoritoBiblico = $_SESSION['favorito_biblico_flash'] ?? '';
unset($_SESSION['favorito_biblico_flash']);
$mensagens = [
    'criada' => 'Anotação criada.',
    'atualizada' => 'Anotação atualizada.',
    'favorito' => 'Favorito atualizado.',
    'removida' => 'Anotação removida.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? null;
    if (!is_string($tokenEnviado) || !hash_equals($tokenCsrf, $tokenEnviado)) {
        http_response_code(403);
        $erros[] = 'A solicitação expirou. Atualize a página e tente novamente.';
    } else {
        $acao = $_POST['acao'] ?? '';
        try {
            switch ($acao) {
                case 'criar':
                    $texto = Validacoes::textoAnotacao($_POST['anotacao'] ?? null);
                    $servico->criar($usuarioId, $texto);
                    header('Location: anotacoes.php?status=criada', true, 303);
                    exit;

                case 'atualizar':
                    $id = Validacoes::idPositivo($_POST['id'] ?? null);
                    $texto = Validacoes::textoAnotacao($_POST['anotacao'] ?? null);
                    if ($id === null || !$servico->atualizar($id, $usuarioId, $texto)) {
                        $erros[] = 'Anotação não encontrada.';
                        break;
                    }
                    header('Location: anotacoes.php?status=atualizada', true, 303);
                    exit;

                case 'favorito':
                    $id = Validacoes::idPositivo($_POST['id'] ?? null);
                    $favorito = $_POST['favorito'] ?? null;
                    if ($id === null || !in_array($favorito, ['0', '1'], true)
                        || !$servico->definirFavorito($id, $usuarioId, $favorito === '1')) {
                        $erros[] = 'Não foi possível atualizar o favorito.';
                        break;
                    }
                    header('Location: anotacoes.php?status=favorito', true, 303);
                    exit;

                case 'remover':
                    $id = Validacoes::idPositivo($_POST['id'] ?? null);
                    if ($id === null || !$servico->remover($id, $usuarioId)) {
                        $erros[] = 'Anotação não encontrada.';
                        break;
                    }
                    header('Location: anotacoes.php?status=removida', true, 303);
                    exit;

                default:
                    $erros[] = 'Ação inválida.';
            }
        } catch (InvalidArgumentException $erro) {
            $erros[] = $erro->getMessage();
        }
    }
}

if (isset($_GET['status']) && is_string($_GET['status'])) {
    $mensagem = $mensagens[$_GET['status']] ?? '';
}

$anotacoes = $servico->listarDoUsuario($usuarioId);
$favoritosBiblicos = $servicoFavoritosBiblicos->listarDoUsuario($usuarioId);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas anotações</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="admin-biblia-page">
    <section class="admin-biblia-painel">
        <a href="../index.php">Voltar à página inicial</a>
        <a href="../conta.php">Minha conta</a>
        <h1>Minhas anotações</h1>

        <?php if ($mensagem !== ''): ?>
            <div role="status"><p><?= Utils::escaparHtml($mensagem) ?></p></div>
        <?php endif; ?>
        <?php if (is_string($mensagemFavoritoBiblico) && $mensagemFavoritoBiblico !== ''): ?>
            <div role="status"><p><?= Utils::escaparHtml($mensagemFavoritoBiblico) ?></p></div>
        <?php endif; ?>
        <?php if ($erros): ?>
            <div role="alert">
                <?php foreach ($erros as $erro): ?>
                    <p><?= Utils::escaparHtml($erro) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="anotacoes-criar" action="anotacoes.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
            <input type="hidden" name="acao" value="criar">
            <label for="nova-anotacao">Nova anotação</label>
            <textarea id="nova-anotacao" name="anotacao" maxlength="65535" required></textarea>
            <button class="anotacoes-botao" type="submit">Salvar anotação</button>
        </form>

        <?php if (!$anotacoes): ?>
            <p class="anotacoes-vazio">Você ainda não tem anotações. Seus favoritos bíblicos aparecem abaixo.</p>
        <?php else: ?>
            <div class="anotacoes-filtros" id="filtros-anotacoes" hidden>
                <div>
                    <label for="busca-anotacoes">Buscar nas anotações</label>
                    <input id="busca-anotacoes" type="search" autocomplete="off">
                </div>
                <div>
                    <label for="filtro-anotacoes">Mostrar</label>
                    <select id="filtro-anotacoes">
                        <option value="todas">Todas as anotações</option>
                        <option value="favoritos">Somente favoritas</option>
                    </select>
                </div>
                <p id="contagem-anotacoes" class="anotacoes-contagem" role="status" aria-live="polite"></p>
                <p id="nenhuma-anotacao-encontrada" class="anotacoes-sem-resultados" hidden>
                    Nenhuma anotação corresponde à busca e ao filtro selecionados.
                </p>
            </div>

            <section class="anotacoes-lista" aria-label="Suas anotações">
                <?php foreach ($anotacoes as $anotacao): ?>
                    <article class="anotacao-card" data-favorito="<?= $anotacao->favorito ? '1' : '0' ?>">
                        <p class="anotacao-texto">
                            <?= nl2br(Utils::escaparHtml($anotacao->texto)) ?>
                        </p>
                        <small class="anotacao-datas">
                            Criada em <?= Utils::escaparHtml($anotacao->criadoEm) ?>.
                            Atualizada em <?= Utils::escaparHtml($anotacao->atualizadoEm) ?>.
                        </small>

                        <div class="anotacao-acoes">
                            <details class="anotacao-edicao">
                                <summary>Editar anotação</summary>
                                <form action="anotacoes.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                                    <input type="hidden" name="acao" value="atualizar">
                                    <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                                    <label for="anotacao-<?= $anotacao->id ?>">Texto da anotação</label>
                                    <textarea id="anotacao-<?= $anotacao->id ?>" name="anotacao" maxlength="65535" required><?= Utils::escaparHtml($anotacao->texto) ?></textarea>
                                    <button class="anotacoes-botao" type="submit">Salvar alterações</button>
                                </form>
                            </details>

                            <form action="anotacoes.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                                <input type="hidden" name="acao" value="favorito">
                                <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                                <input type="hidden" name="favorito" value="<?= $anotacao->favorito ? '0' : '1' ?>">
                                <button class="anotacoes-botao anotacoes-botao-secundario" type="submit">
                                    <?= $anotacao->favorito ? 'Remover dos favoritos' : 'Marcar como favorita' ?>
                                </button>
                            </form>

                            <form action="anotacoes.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                                <input type="hidden" name="acao" value="remover">
                                <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                                <button class="anotacoes-botao anotacoes-botao-remover" type="submit">Remover</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
            <script src="../js/anotacoes.js" defer></script>
        <?php endif; ?>

        <section class="favoritos-biblicos" aria-labelledby="titulo-favoritos-biblicos">
            <h2 id="titulo-favoritos-biblicos">Favoritos bíblicos</h2>
            <?php if (!$favoritosBiblicos): ?>
                <p class="anotacoes-vazio">Você ainda não salvou versículos ou capítulos favoritos.</p>
            <?php else: ?>
                <div class="favoritos-biblicos-lista">
                    <?php foreach ($favoritosBiblicos as $favoritoBiblico): ?>
                        <?php
                        $livroFavorito = (string) $favoritoBiblico['livro_nome'];
                        $livroFavoritoId = (int) $favoritoBiblico['livro_id'];
                        $capituloFavorito = (int) $favoritoBiblico['capitulo'];
                        $ehVersiculoFavorito = $favoritoBiblico['tipo'] === 'versiculo';
                        $versiculoFavorito = (int) $favoritoBiblico['versiculo'];
                        ?>
                        <article class="favorito-biblico-card">
                            <h3><?= Utils::escaparHtml(
                                $livroFavorito . ' ' . $capituloFavorito
                                . ($ehVersiculoFavorito ? ':' . $versiculoFavorito : '')
                            ) ?></h3>
                            <?php if ($ehVersiculoFavorito): ?>
                                <?php if (is_string($favoritoBiblico['texto'])): ?>
                                    <blockquote><?= Utils::escaparHtml($favoritoBiblico['texto']) ?></blockquote>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>Capítulo salvo nos seus favoritos.</p>
                            <?php endif; ?>
                            <small>Salvo em <?= Utils::escaparHtml((string) $favoritoBiblico['criado_em']) ?></small>
                            <div class="favorito-biblico-acoes">
                                <a href="../leitura.php?livro=<?= $livroFavoritoId ?>&amp;capitulo=<?= $capituloFavorito ?><?= $ehVersiculoFavorito ? '#versiculo-' . $versiculoFavorito : '#texto' ?>">
                                    <?= $ehVersiculoFavorito ? 'Abrir versículo' : 'Ler capítulo' ?>
                                </a>
                                <form action="../favorito-biblico.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenFavoritosCsrf) ?>">
                                    <input type="hidden" name="tipo" value="<?= $ehVersiculoFavorito ? 'versiculo' : 'capitulo' ?>">
                                    <input type="hidden" name="livro_id" value="<?= $livroFavoritoId ?>">
                                    <input type="hidden" name="capitulo" value="<?= $capituloFavorito ?>">
                                    <input type="hidden" name="versiculo" value="<?= $ehVersiculoFavorito ? $versiculoFavorito : '0' ?>">
                                    <input type="hidden" name="favorito" value="0">
                                    <input type="hidden" name="origem" value="anotacoes">
                                    <button class="anotacoes-botao anotacoes-botao-secundario" type="submit">★ Remover dos favoritos</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </section>
</main>
</body>
</html>
