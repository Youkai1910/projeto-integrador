<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\Utils;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\AnotacaoServico;
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
$servico = new AnotacaoServico();
$erros = [];
$mensagem = '';
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
        <h1>Minhas anotações</h1>

        <?php if ($mensagem !== ''): ?>
            <div role="status"><p><?= Utils::escaparHtml($mensagem) ?></p></div>
        <?php endif; ?>
        <?php if ($erros): ?>
            <div role="alert">
                <?php foreach ($erros as $erro): ?>
                    <p><?= Utils::escaparHtml($erro) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="anotacoes.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
            <input type="hidden" name="acao" value="criar">
            <label for="nova-anotacao">Nova anotação</label>
            <textarea id="nova-anotacao" name="anotacao" maxlength="65535" required></textarea>
            <button type="submit">Salvar anotação</button>
        </form>

        <?php if (!$anotacoes): ?>
            <p>Você ainda não tem anotações.</p>
        <?php else: ?>
            <?php foreach ($anotacoes as $anotacao): ?>
                <article>
                    <p>
                        <?= nl2br(Utils::escaparHtml($anotacao->texto)) ?>
                    </p>
                    <small>
                        Criada em <?= Utils::escaparHtml($anotacao->criadoEm) ?>.
                        Atualizada em <?= Utils::escaparHtml($anotacao->atualizadoEm) ?>.
                    </small>

                    <form action="anotacoes.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                        <input type="hidden" name="acao" value="atualizar">
                        <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                        <label for="anotacao-<?= $anotacao->id ?>">Editar anotação</label>
                        <textarea id="anotacao-<?= $anotacao->id ?>" name="anotacao" maxlength="65535" required><?= Utils::escaparHtml($anotacao->texto) ?></textarea>
                        <button type="submit">Atualizar</button>
                    </form>

                    <form action="anotacoes.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                        <input type="hidden" name="acao" value="favorito">
                        <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                        <input type="hidden" name="favorito" value="<?= $anotacao->favorito ? '0' : '1' ?>">
                        <button type="submit">
                            <?= $anotacao->favorito ? 'Remover dos favoritos' : 'Marcar como favorito' ?>
                        </button>
                    </form>

                    <form action="anotacoes.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Utils::escaparHtml($tokenCsrf) ?>">
                        <input type="hidden" name="acao" value="remover">
                        <input type="hidden" name="id" value="<?= $anotacao->id ?>">
                        <button type="submit">Remover</button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
