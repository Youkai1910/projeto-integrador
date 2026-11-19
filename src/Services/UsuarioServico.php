<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use DomainException;
use PDO;
use ProjetoIntegrador\Enum\TipoUsuario;
use ProjetoIntegrador\Models\Usuario;
use Throwable;

final class UsuarioServico
{
	private PDO $conexao;

	public function __construct(PDO $conexao)
	{
		$this->conexao = $conexao;
	}

	/** @return Usuario[] */
	public function listar(string $busca = ''): array
	{
		if ($busca === '') {
			$consulta = $this->conexao->query(
				'SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY criado_em DESC, id DESC'
			);
		} else {
			$consulta = $this->conexao->prepare(
				'SELECT id, nome, email, tipo, criado_em
				 FROM usuarios
				 WHERE nome LIKE :nome OR email LIKE :email
				 ORDER BY criado_em DESC, id DESC'
			);
			$termo = '%' . $busca . '%';
			$consulta->execute(['nome' => $termo, 'email' => $termo]);
		}

		$usuarios = [];
		foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $dados) {
			$usuarios[] = Usuario::deDados($dados);
		}

		return $usuarios;
	}

	public function ehAdministrador(int $idUsuario): bool
	{
		$consulta = $this->conexao->prepare('SELECT tipo FROM usuarios WHERE id = :id LIMIT 1');
		$consulta->execute(['id' => $idUsuario]);
		return $consulta->fetchColumn() === TipoUsuario::ADMIN;
	}

	public function autenticar(string $email, string $senha): ?Usuario
	{
		$email = trim($email);
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $senha === '') {
			return null;
		}

		$consulta = $this->conexao->prepare(
			'SELECT id, nome, email, senha, tipo, criado_em FROM usuarios WHERE email = :email LIMIT 1'
		);
		$consulta->execute(['email' => $email]);
		$dados = $consulta->fetch(PDO::FETCH_ASSOC);

		if ($dados === false || !password_verify($senha, (string) $dados['senha'])) {
			return null;
		}

		if (password_needs_rehash((string) $dados['senha'], PASSWORD_DEFAULT)) {
			$hashAtualizado = password_hash($senha, PASSWORD_DEFAULT);
			if ($hashAtualizado !== false) {
				$atualizacao = $this->conexao->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
				$atualizacao->execute(['senha' => $hashAtualizado, 'id' => (int) $dados['id']]);
			}
		}

		return Usuario::deDados($dados);
	}

	public function inserir(string $nome, string $email, string $senha, string $tipo = TipoUsuario::USUARIO): int
	{
		$nome = trim($nome);
		$email = trim($email);
		$tipo = TipoUsuario::validar($tipo);

		if ($nome === '' || !preg_match('/^.{1,150}$/us', $nome)) {
			throw new DomainException('Informe um nome com até 150 caracteres.');
		}
		if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new DomainException('Informe um e-mail válido com até 254 caracteres.');
		}
		if (strlen($senha) < 8 || strlen($senha) > 4096) {
			throw new DomainException('A senha deve ter pelo menos 8 caracteres.');
		}

		$hashSenha = password_hash($senha, PASSWORD_DEFAULT);
		if ($hashSenha === false) {
			throw new \RuntimeException('Não foi possível proteger a senha do usuário.');
		}

		try {
			$consulta = $this->conexao->prepare(
				'INSERT INTO usuarios (nome, senha, email, tipo) VALUES (:nome, :senha, :email, :tipo)'
			);
			$consulta->execute([
				'nome' => $nome,
				'senha' => $hashSenha,
				'email' => $email,
				'tipo' => $tipo,
			]);
		} catch (\PDOException $e) {
			if ($e->getCode() === '23000') {
				throw new DomainException('Já existe uma conta cadastrada com esse e-mail.');
			}
			throw $e;
		}

		return (int) $this->conexao->lastInsertId();
	}

	public function atualizarTipo(int $idUsuario, string $tipo, int $idAdministrador): void
	{
		$tipo = TipoUsuario::validar($tipo);
		if ($idUsuario === $idAdministrador) {
			throw new DomainException('Não é permitido alterar o próprio perfil.');
		}

		$this->conexao->beginTransaction();
		try {
			$administradores = $this->bloquearAdministradores();
			$this->confirmarAdministrador($administradores, $idAdministrador);
			$tipoAtual = $this->obterTipoBloqueado($idUsuario);

			if ($tipoAtual === null) {
				throw new DomainException('O usuário não foi encontrado.');
			}
			if ($tipoAtual === TipoUsuario::ADMIN && $tipo !== TipoUsuario::ADMIN && count($administradores) <= 1) {
				throw new DomainException('Não é possível remover o perfil do único administrador.');
			}

			$atualizacao = $this->conexao->prepare('UPDATE usuarios SET tipo = :tipo WHERE id = :id');
			$atualizacao->execute(['tipo' => $tipo, 'id' => $idUsuario]);
			$this->conexao->commit();
		} catch (Throwable $e) {
			$this->reverterTransacao();
			throw $e;
		}
	}

	public function excluir(int $idUsuario, int $idAdministrador): void
	{
		if ($idUsuario === $idAdministrador) {
			throw new DomainException('Não é permitido excluir a própria conta.');
		}

		$this->conexao->beginTransaction();
		try {
			$administradores = $this->bloquearAdministradores();
			$this->confirmarAdministrador($administradores, $idAdministrador);
			$tipoUsuario = $this->obterTipoBloqueado($idUsuario);

			if ($tipoUsuario === null) {
				throw new DomainException('O usuário não foi encontrado.');
			}
			if ($tipoUsuario === TipoUsuario::ADMIN && count($administradores) <= 1) {
				throw new DomainException('Não é possível excluir o único administrador.');
			}

			$exclusao = $this->conexao->prepare('DELETE FROM usuarios WHERE id = :id');
			$exclusao->execute(['id' => $idUsuario]);
			$this->conexao->commit();
		} catch (Throwable $e) {
			$this->reverterTransacao();
			throw $e;
		}
	}

	/** @return int[] */
	private function bloquearAdministradores(): array
	{
		$consulta = $this->conexao->query(
			"SELECT id FROM usuarios WHERE tipo = 'admin' ORDER BY id FOR UPDATE"
		);
		return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
	}

	/** @param int[] $administradores */
	private function confirmarAdministrador(array $administradores, int $idAdministrador): void
	{
		if (!in_array($idAdministrador, $administradores, true)) {
			throw new DomainException('Sua sessão administrativa não é mais válida.');
		}
	}

	private function obterTipoBloqueado(int $idUsuario): ?string
	{
		$consulta = $this->conexao->prepare('SELECT tipo FROM usuarios WHERE id = :id FOR UPDATE');
		$consulta->execute(['id' => $idUsuario]);
		$tipo = $consulta->fetchColumn();
		return $tipo === false ? null : (string) $tipo;
	}

	private function reverterTransacao(): void
	{
		if ($this->conexao->inTransaction()) {
			$this->conexao->rollBack();
		}
	}
}
