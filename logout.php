<?php
declare(strict_types=1);

use ProjetoIntegrador\Helpers\Seguranca;

require_once __DIR__ . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

try {
    Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
} catch (DomainException $e) {
    http_response_code(400);
    exit('A solicitação expirou. Atualize a página e tente novamente.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
}
session_destroy();

header('Location: index.php', true, 303);
exit;
