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
}