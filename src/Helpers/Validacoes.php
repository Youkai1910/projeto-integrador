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

        if (!is_string($valor) || !preg_match('/^[1-9][0-9]*$/D', $valor)) {
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
        if (strlen($valor) > 200 || preg_match('//u', $valor) !== 1) {
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
        if ($valor === '' || strlen($valor) > 65535 || preg_match('//u', $valor) !== 1) {
            throw new InvalidArgumentException('A anotação é obrigatória e deve ter até 65.535 bytes.');
        }

        return $valor;
    }

    public static function nome(string $valor): bool
    {
        $valor = trim($valor);
        return $valor !== '' && strlen($valor) <= 150 && preg_match('//u', $valor) === 1;
    }

    public static function email(string $valor): bool
    {
        $valor = trim($valor);
        return strlen($valor) <= 254
            && preg_match('//u', $valor) === 1
            && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function senha(string $valor): bool
    {
        $comprimento = strlen($valor);
        return $comprimento >= 8 && $comprimento <= 4096;
    }
}