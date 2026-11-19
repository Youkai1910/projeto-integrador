<?php
namespace ProjetoIntegrador\Helpers;

final class Utils
{
    public static function escaparHtml($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}