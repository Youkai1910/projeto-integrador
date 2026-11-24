<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\UsuarioServico;
use ProjetoIntegrador\Enum\TipoUsuario;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

try {
    $usuarioServico = new UsuarioServico(ConexaoBD::getConexao());
    $usuarioSessao = $_SESSION['usuario'] ?? null;
    $idAdministrador = is_array($usuarioSessao)
        ? filter_var($usuarioSessao['id'] ?? null, FILTER_VALIDATE_INT)
        : false;

    $tipoSessao = $usuarioServico->obterTipoUsuario((int) $idAdministrador);
    $ehPrivilegiado = $tipoSessao === TipoUsuario::ADMIN || $tipoSessao === TipoUsuario::EDITOR;

    if ($idAdministrador === false || $idAdministrador < 1 || !$ehPrivilegiado) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><title>Acesso restrito</title>'
            . '<body><h1>Acesso restrito</h1><p>Esta área exige uma sessão de administrador ou editor autenticada.</p>'
            . '<a href="../index.php">Voltar ao início</a></body></html>';
        exit;
    }

    $idAdministrador = (int) $idAdministrador;
    $ehAdministrador = $tipoSessao === TipoUsuario::ADMIN;
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="pt-BR"><meta charset="UTF-8"><title>Erro</title>'
        . '<body><h1>Não foi possível acessar a área administrativa</h1>'
        . '<p>Tente novamente mais tarde.</p></body></html>';
    exit;
}