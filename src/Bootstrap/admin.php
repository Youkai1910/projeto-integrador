<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\UsuarioServico;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

$idAdministradorConfigurado = getenv('ADMIN_USER_ID');
if ($idAdministradorConfigurado === false || trim($idAdministradorConfigurado) === '') {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><title>Administração indisponível</title>'
        . '<body><h1>Administração indisponível</h1><p>Configure ADMIN_USER_ID no ambiente do servidor.</p>'
        . '<a href="../index.php">Voltar ao início</a></body></html>';
    exit;
}

try {
    $usuarioServico = new UsuarioServico(ConexaoBD::getConexao());
    $usuarioSessao = $_SESSION['usuario'] ?? null;
    $idAdministrador = is_array($usuarioSessao)
        ? filter_var($usuarioSessao['id'] ?? null, FILTER_VALIDATE_INT)
        : false;

    $ehAdministrador = $idAdministrador !== false
        && $idAdministrador > 0
        && $usuarioServico->ehAdministrador((int) $idAdministrador);

    if (!$ehAdministrador) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><title>Acesso restrito</title>'
            . '<body><h1>Acesso restrito</h1><p>Esta área está disponível somente para a conta configurada pela equipe.</p>'
            . '<a href="../index.php">Voltar ao início</a></body></html>';
        exit;
    }

    $idAdministrador = (int) $idAdministrador;
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><title>Erro</title>'
        . '<body><h1>Não foi possível acessar a área administrativa</h1>'
        . '<p>Tente novamente mais tarde.</p></body></html>';
    exit;
}