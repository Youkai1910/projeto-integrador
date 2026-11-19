<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

final class Validacoes
{
	private function __construct()
	{
	}

	public static function nome(string $nome, int $limite = 150): bool
	{
		$nome = trim($nome);
		return $limite > 0 && $nome !== '' && self::comprimento($nome) <= $limite;
	}

	public static function email(string $email, int $limite = 254): bool
	{
		$email = trim($email);
		return $limite > 0
			&& self::comprimento($email) <= $limite
			&& filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	public static function senha(string $senha, int $minimo = 8, int $maximo = 4096): bool
	{
		$comprimento = strlen($senha);
		return $minimo >= 1 && $maximo >= $minimo
			&& $comprimento >= $minimo && $comprimento <= $maximo;
	}

	public static function url(string $url, bool $opcional = false): bool
	{
		$url = trim($url);
		if ($url === '') {
			return $opcional;
		}

		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			return false;
		}

		$esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));
		return in_array($esquema, ['http', 'https'], true);
	}

	public static function mensagem(string $mensagem, int $limite = 10000): bool
	{
		return $limite > 0 && trim($mensagem) !== '' && strlen($mensagem) <= $limite;
	}

	/** @param mixed $valor */
	public static function inteiroPositivo($valor): bool
	{
		$inteiro = filter_var($valor, FILTER_VALIDATE_INT);
		return $inteiro !== false && $inteiro > 0;
	}

	private static function comprimento(string $valor): int
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($valor, 'UTF-8');
		}

		$quantidade = preg_match_all('/./us', $valor, $correspondencias);
		return $quantidade === false ? PHP_INT_MAX : $quantidade;
	}
}
