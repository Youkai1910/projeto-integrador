<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\FavoritoBiblicoServico;
use ProjetoIntegrador\Services\LeituraBiblicaServico;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

function responderFavoritoErro(int $status, string $mensagem): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $mensagem;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responderFavoritoErro(405, 'Método não permitido.');
}

$usuarioSessao = $_SESSION['usuario'] ?? null;
$usuarioId = is_array($usuarioSessao)
    ? Validacoes::idPositivo($usuarioSessao['id'] ?? null)
    : null;
if ($usuarioId === null) {
    $_SESSION['auth_flash'] = [
        'mensagem' => 'Entre na sua conta para salvar favoritos bíblicos.',
        'tipo' => 'erro',
    ];
    header('Location: login.php', true, 303);
    exit;
}

try {
    Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);

    $tipo = $_POST['tipo'] ?? null;
    $livroId = Validacoes::idPositivo($_POST['livro_id'] ?? null);
    $capitulo = Validacoes::idPositivo($_POST['capitulo'] ?? null);
    $favoritoRecebido = $_POST['favorito'] ?? null;
    $origem = $_POST['origem'] ?? null;
    if (!is_string($tipo) || !in_array($tipo, ['capitulo', 'versiculo'], true)
        || $livroId === null || $capitulo === null
        || !in_array($favoritoRecebido, ['0', '1'], true)
        || !is_string($origem) || !in_array($origem, ['inicio', 'leitura', 'anotacoes'], true)) {
        responderFavoritoErro(400, 'Os dados do favorito são inválidos.');
    }

    $versiculo = 0;
    if ($tipo === 'versiculo') {
        $versiculo = Validacoes::idPositivo($_POST['versiculo'] ?? null);
        if ($versiculo === null) {
            responderFavoritoErro(400, 'O versículo informado é inválido.');
        }
    } elseif (($_POST['versiculo'] ?? null) !== '0') {
        responderFavoritoErro(400, 'O capítulo informado é inválido.');
    }

    $leituraBiblica = new LeituraBiblicaServico(ConexaoBD::getConexaoBiblia());
    $livro = $leituraBiblica->obterLivro($livroId);
    if ($livro === null || !in_array($capitulo, $leituraBiblica->listarCapitulos($livroId), true)) {
        responderFavoritoErro(400, 'O livro ou capítulo não foi encontrado.');
    }
    if ($tipo === 'versiculo') {
        $numerosVersiculos = array_map(
            'intval',
            array_column($leituraBiblica->listarVersiculos($livroId, $capitulo), 'verse')
        );
        if (!in_array($versiculo, $numerosVersiculos, true)) {
            responderFavoritoErro(400, 'O versículo não foi encontrado neste capítulo.');
        }
    }

    $favorito = $favoritoRecebido === '1';
    $servico = new FavoritoBiblicoServico(ConexaoBD::getConexao());
    $servico->definirFavorito($usuarioId, $tipo, $livroId, $capitulo, $versiculo, $favorito);
    $_SESSION['favorito_biblico_flash'] = $favorito
        ? 'Favorito bíblico salvo.'
        : 'Favorito bíblico removido.';

    if ($origem === 'anotacoes') {
        $destino = 'admin/anotacoes.php';
    } elseif ($origem === 'inicio') {
        $destino = 'index.php?' . http_build_query([
            'livro' => $livroId,
            'capitulo' => $capitulo,
        ]);
        $destino .= $tipo === 'versiculo'
            ? '#versiculo-' . $versiculo
            : '#capitulo-atual';
    } else {
        $destino = 'leitura.php?' . http_build_query([
            'livro' => $livroId,
            'capitulo' => $capitulo,
        ]);
        if ($tipo === 'versiculo') {
            $destino .= '#versiculo-' . $versiculo;
        } else {
            $destino .= '#texto';
        }
    }

    header('Location: ' . $destino, true, 303);
    exit;
} catch (DomainException $erro) {
    responderFavoritoErro(403, $erro->getMessage());
} catch (InvalidArgumentException $erro) {
    responderFavoritoErro(400, $erro->getMessage());
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    responderFavoritoErro(500, 'Não foi possível atualizar o favorito bíblico. Tente novamente.');
}
