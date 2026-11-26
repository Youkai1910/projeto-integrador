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
