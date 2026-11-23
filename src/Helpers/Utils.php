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
        return self::escaparHtml($valor);
    }

    public static function escaparHtml($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}