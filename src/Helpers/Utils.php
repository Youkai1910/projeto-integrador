<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

final class Utils
{
	private function __construct()
	{
	}

	public static function escapar(string $valor): string
	{
		return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/** @param mixed $valor */
	public static function obterString($valor, string $padrao = '', int $limite = 0): string
	{
		if (!is_string($valor) && !is_numeric($valor)) {
			return $padrao;
		}

		$resultado = trim((string) $valor);
		if ($limite > 0) {
			$resultado = self::limitarTexto($resultado, $limite);
		}

		return $resultado;
	}

	public static function limitarTexto(string $texto, int $limite): string
	{
		if ($limite <= 0) {
			return '';
		}

		if (function_exists('mb_substr')) {
			return mb_substr($texto, 0, $limite, 'UTF-8');
		}

		if (preg_match_all('/./us', $texto, $caracteres) !== false) {
			return implode('', array_slice($caracteres[0], 0, $limite));
		}

		return substr($texto, 0, $limite);
	}

	public static function gerarToken(int $bytes = 32): string
	{
		if ($bytes < 16 || $bytes > 128) {
			throw new \InvalidArgumentException('O token deve usar entre 16 e 128 bytes aleatórios.');
		}

		return bin2hex(random_bytes($bytes));
	}

	/** @param array<string, mixed> $parametros */
	public static function urlComQuery(string $caminho, array $parametros = []): string
	{
		$url = parse_url($caminho);
		if ($url === false || isset($url['scheme']) || isset($url['host']) || isset($url['fragment'])) {
			throw new \InvalidArgumentException('Informe um caminho local válido para montar a URL.');
		}

		$queryExistente = [];
		if (isset($url['query'])) {
			parse_str($url['query'], $queryExistente);
		}

		$query = http_build_query(array_merge($queryExistente, $parametros));
		$resultado = $url['path'] ?? '';
		if ($query !== '') {
			$resultado .= '?' . $query;
		}

		return $resultado;
	}

	public static function formatarData(?string $data, string $formato = 'd/m/Y H:i'): string
	{
		if ($data === null || trim($data) === '') {
			return '';
		}

		try {
			return (new \DateTimeImmutable($data))->format($formato);
		} catch (\Exception $e) {
			return '';
		}
	}
}
