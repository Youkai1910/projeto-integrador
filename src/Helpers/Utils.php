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
        $resultado = '';
        $espacoAnterior = false;

        for ($indice = 0; $indice < strlen($valor); $indice++) {
            $caractere = $valor[$indice];
            $espaco = ctype_space($caractere);

            if ($espaco) {
                if (!$espacoAnterior && $resultado !== '') {
                    $resultado .= ' ';
                }
                $espacoAnterior = true;
            } else {
                $resultado .= $caractere;
                $espacoAnterior = false;
            }
        }

        return rtrim($resultado);
    }

    public static function textoLimite(string $valor, int $limite): string
    {
        $valor = trim($valor);
        if ($limite < 1) {
            return '';
        }

        $tamanho = strlen($valor);
        if ($tamanho <= $limite) {
            return $valor;
        }

        return substr($valor, 0, $limite);
    }
}