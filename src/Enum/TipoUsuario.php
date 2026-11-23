<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Enum;

/** Perfis válidos para contas da aplicação (compatível com PHP 7.4). */
final class TipoUsuario
{
	public const USUARIO = 'usuario';
	public const EDITOR = 'editor';
	public const ADMIN = 'admin';

	/** @var array<string, string> */
	private const ROTULOS = [
		self::USUARIO => 'Usuário',
		self::EDITOR => 'Editor',
		self::ADMIN => 'Administrador',
	];

	private function __construct()
	{
	}

	public static function ehValido(string $tipo): bool
	{
		return array_key_exists($tipo, self::ROTULOS);
	}

	public static function validar(string $tipo): string
	{
		if (!self::ehValido($tipo)) {
			throw new \DomainException('O perfil selecionado é inválido.');
		}

		return $tipo;
	}

	public static function obterRotulo(string $tipo): string
	{
		self::validar($tipo);
		return self::ROTULOS[$tipo];
	}

	/** @return array<string, string> */
	public static function obterOpcoes(): array
	{
		return self::ROTULOS;
	}
}
