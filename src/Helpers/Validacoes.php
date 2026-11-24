<?php
namespace ProjetoIntegrador\Helpers;

use InvalidArgumentException;

final class Validacoes
{
    public static function idPositivo($valor): ?int
    {
        if (is_int($valor)) {
            return $valor > 0 ? $valor : null;
        }

        if (!is_string($valor) || $valor === '' || strspn($valor, '0123456789') !== strlen($valor)) {
            return null;
        }

        if ($valor[0] === '0') {
            return null;
        }

        $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : $id;
    }

    public static function termoBusca($valor): string
    {
        if (!is_string($valor)) {
            throw new InvalidArgumentException('O termo de busca precisa ser um texto.');
        }

        $valor = trim($valor);
        if (strlen($valor) > 200 || !self::textoUtf8Valido($valor)) {
            throw new InvalidArgumentException('O termo de busca é inválido ou muito longo.');
        }

        return $valor;
    }

    public static function textoAnotacao($valor): string
    {
        if (!is_string($valor)) {
            throw new InvalidArgumentException('A anotação precisa ser um texto.');
        }

        $valor = trim($valor);
        if ($valor === '' || strlen($valor) > 65535 || !self::textoUtf8Valido($valor)) {
            throw new InvalidArgumentException('A anotação é obrigatória e deve ter até 65.535 bytes.');
        }

        return $valor;
    }

    public static function nome(string $valor): bool
    {
        $valor = trim($valor);
        return $valor !== '' && self::textoUtf8Valido($valor) && strlen($valor) <= 150;
    }

    public static function email(string $valor): bool
    {
        $valor = trim($valor);
        return strlen($valor) <= 254
            && self::textoUtf8Valido($valor)
            && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function senha(string $valor): bool
    {
        $comprimento = strlen($valor);
        return $comprimento >= 8 && $comprimento <= 4096;
    }

    private static function textoUtf8Valido(string $valor): bool
    {
        if (!function_exists('iconv')) {
            return true;
        }

        $convertido = iconv('UTF-8', 'UTF-8//IGNORE', $valor);
        return $convertido !== false && $convertido === $valor;
    }
}