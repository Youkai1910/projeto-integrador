<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

use Throwable;

final class LogServico
{
    private function __construct()
    {
    }

    public static function configurar(): void
    {
        $diretorioLogs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($diretorioLogs)) {
            mkdir($diretorioLogs, 0775, true);
        }

        $arquivoLog = $diretorioLogs . DIRECTORY_SEPARATOR . 'erros.log';
        ini_set('log_errors', '1');
        ini_set('error_log', $arquivoLog);

        register_shutdown_function(static function (): void {
            $erro = error_get_last();
            if ($erro === null) {
                return;
            }

            $tiposFatais = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
            if (($erro['type'] & $tiposFatais) === 0) {
                return;
            }

            error_log(sprintf(
                'Erro fatal: %s em %s:%d',
                $erro['message'],
                $erro['file'],
                $erro['line']
            ));
        });
    }

    public static function registrar(Throwable $erro, string $contexto = ''): void
    {
        $prefixo = $contexto !== '' ? $contexto . ': ' : '';
        error_log(sprintf(
            '%s%s em %s:%d',
            $prefixo,
            $erro->getMessage(),
            $erro->getFile(),
            $erro->getLine()
        ));
    }
}
