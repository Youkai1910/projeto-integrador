<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

use Throwable;

final class RegistradorErros
{
    private const ARQUIVO_LOG = __DIR__ . '/../../logs/erros.log';

    private function __construct()
    {
    }

    public static function iniciar(): void
    {
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        error_reporting(E_ALL);

        set_error_handler([self::class, 'capturarErroPhp']);
        set_exception_handler([self::class, 'capturarExcecaoNaoTratada']);
        register_shutdown_function([self::class, 'capturarErroFatal']);

        if (!is_dir(dirname(self::ARQUIVO_LOG))) {
            @mkdir(dirname(self::ARQUIVO_LOG), 0775, true);
        }
    }

    public static function capturarErroPhp(int $nivel, string $mensagem, string $arquivo = '', int $linha = 0): bool
    {
        if (!(error_reporting() & $nivel)) {
            return false;
        }

        $severidade = self::severidadePhp($nivel);
        self::registrar($severidade, $mensagem, $arquivo, $linha, null);

        return $severidade !== 'critical' && $severidade !== 'error';
    }

    public static function capturarExcecaoNaoTratada(Throwable $excecao): void
    {
        self::registrar(
            'critical',
            $excecao->getMessage(),
            $excecao->getFile(),
            $excecao->getLine(),
            $excecao
        );

        if (!headers_sent()) {
            http_response_code(500);
        }

        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Erro no Portal</title></head><body><main><h1>Não foi possível concluir esta operação.</h1><p>Tente novamente. O problema foi registrado para análise.</p></main></body></html>';
    }

    public static function capturarErroFatal(): void
    {
        $erro = error_get_last();
        if ($erro === null || !in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            return;
        }

        self::registrar(
            'critical',
            (string) $erro['message'],
            (string) ($erro['file'] ?? 'desconhecido'),
            (int) ($erro['line'] ?? 0),
            null
        );
    }

    public static function registrar(
        string $severidade,
        string $mensagem,
        string $arquivo = 'desconhecido',
        int $linha = 0,
        ?Throwable $excecao = null
    ): void {
        $mensagemSegura = self::sanitizarMensagem($mensagem);
        $solucao = self::sugerirSolucao($mensagemSegura, $arquivo);

        $registro = [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'severity' => $severidade,
            'message' => $mensagemSegura,
            'file' => str_replace('\\', '/', $arquivo),
            'line' => $linha,
            'request_method' => isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'CLI',
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? self::sanitizarUri((string) $_SERVER['REQUEST_URI']) : 'CLI',
            'suggested_solution' => $solucao,
        ];

        if ($excecao !== null) {
            $registro['trace'] = substr(str_replace("\r", '', $excecao->getTraceAsString()), 0, 4000);
        }

        $linhaJson = json_encode($registro, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_string($linhaJson)) {
            @file_put_contents(self::ARQUIVO_LOG, $linhaJson . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        @error_log('[Portal Bíblico] ' . $mensagemSegura . ' | Solução sugerida: ' . $solucao);
    }

    private static function severidadePhp(int $nivel): string
    {
        if (($nivel & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) !== 0) {
            return 'critical';
        }

        if (($nivel & (E_WARNING | E_USER_WARNING)) !== 0) {
            return 'warning';
        }

        return 'notice';
    }

    private static function sanitizarMensagem(string $mensagem): string
    {
        $mensagem = preg_replace('/(?:password|senha)[=: ][^\s;]+/i', '$1=[oculto]', $mensagem) ?? $mensagem;
        $mensagem = preg_replace('/(?:mysql:)?host=[^;]+;db(name)?=[^;]+;?[^"]*/i', '[dados de conexão ocultos]', $mensagem) ?? $mensagem;
        return substr(trim($mensagem), 0, 2000);
    }

    private static function sanitizarUri(string $uri): string
    {
        $partes = parse_url($uri);
        $caminho = is_array($partes) && isset($partes['path']) ? (string) $partes['path'] : '/';
        return substr($caminho, 0, 300);
    }

    private static function sugerirSolucao(string $mensagem, string $arquivo): string
    {
        $texto = strtolower($mensagem);

        if (str_contains($texto, 'could not find driver') || str_contains($texto, 'pdo_mysql')) {
            return 'Habilite a extensão PDO MySQL no PHP e reinicie o servidor.';
        }
        if (str_contains($texto, 'access denied') || str_contains($texto, 'acesso negado')) {
            return 'Confira DB_USER e DB_PASSWORD e as permissões da conta MySQL.';
        }
        if (str_contains($texto, 'unknown database') || str_contains($texto, 'base não existe')) {
            return 'Confira DB_NAME/BIBLIA_DB_NAME e importe o banco selecionado.';
        }
        if (str_contains($texto, 'connection refused') || str_contains($texto, 'could not connect')) {
            return 'Inicie o servidor MySQL e confira DB_HOST e a porta.';
        }
        if (str_contains($texto, 'token') || str_contains($texto, 'csrf')) {
            return 'Atualize a página, renove a sessão e reenvie o formulário.';
        }
        if (str_contains($texto, 'permission denied') || str_contains($texto, 'permiss')) {
            return 'Confira permissões do perfil autenticado e a pasta logs.';
        }
        if (str_contains($texto, 'table') && str_contains($texto, 'exist')) {
            return 'Selecione o banco correto e execute os scripts SQL na ordem indicada.';
        }
        if (str_contains($texto, 'mail') || str_contains($texto, 'e-mail')) {
            return 'Configure MAIL_TO, MAIL_FROM e o transporte de e-mail do PHP.';
        }
        if (str_contains($texto, 'undefined') || str_contains($texto, 'call to')) {
            return 'Atualize o autoload Composer e confira a classe ou namespace chamado.';
        }

        return 'Revise o stack trace deste arquivo e teste a operação com dados válidos.';
    }
}
