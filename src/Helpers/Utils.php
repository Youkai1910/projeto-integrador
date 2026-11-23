<?php
namespace ProjetoIntegrador\Helpers;

final class Utils
{
    public static function gerarToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function escapar($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function escaparHtml($valor): string
    {
        return self::escapar($valor);
    }

    public static function normalizarEspacos(string $valor): string
    {
        return trim(preg_replace('/\s+/u', ' ', $valor) ?? '');
    }

    public static function textoLimite(string $valor, int $limite): string
    {
        return mb_substr(trim($valor), 0, $limite, 'UTF-8');
    }
}