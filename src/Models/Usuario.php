<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Models;

final class Usuario
{
	private ?int $id;
	private string $nome;
	private string $email;
	private ?string $criadoEm;

	public function __construct(?int $id, string $nome, string $email, ?string $criadoEm = null)
	{
		$this->id = $id;
		$this->nome = $nome;
		$this->email = $email;
		$this->criadoEm = $criadoEm;
	}

	/** @param array<string, mixed> $dados */
	public static function deDados(array $dados): self
	{
		return new self(
			isset($dados['id']) ? (int) $dados['id'] : null,
			(string) ($dados['nome'] ?? ''),
			(string) ($dados['email'] ?? ''),
			isset($dados['criado_em']) ? (string) $dados['criado_em'] : null
		);
	}

	public function obterId(): ?int
	{
		return $this->id;
	}

	public function obterNome(): string
	{
		return $this->nome;
	}

	public function obterEmail(): string
	{
		return $this->email;
	}

	public function obterDataCriacao(): ?string
	{
		return $this->criadoEm;
	}
}
