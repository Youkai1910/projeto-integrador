<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

final class Env
{
    private static bool $carregado = false;

    public static function carregar(): void
    {
        if (self::$carregado) {
            return;
        }

        $caminho = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (is_file($caminho)) {
            $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($linhas)) {
                foreach ($linhas as $linha) {
                    $linha = trim($linha);
                    if ($linha === '' || substr($linha, 0, 1) === '#' || strpos($linha, '=') === false) {
                        continue;
                    }

                    [$nome, $valor] = array_map('trim', explode('=', $linha, 2));
                    if ($nome === '' || array_key_exists($nome, $_ENV) || getenv($nome) !== false) {
                        continue;
                    }

                    $valor = trim($valor, " \t\n\r\0\x0B\"'");
                    putenv($nome . '=' . $valor);
                    $_ENV[$nome] = $valor;
                }
            }
        }

        self::$carregado = true;
    }

    public static function obter(string $nome, ?string $padrao = null): ?string
    {
        self::carregar();
        $valor = getenv($nome);
        if ($valor !== false && trim($valor) !== '') {
            return trim($valor);
        }
        if (isset($_ENV[$nome]) && is_string($_ENV[$nome]) && trim($_ENV[$nome]) !== '') {
            return trim($_ENV[$nome]);
        }
        return $padrao;
    }
}
