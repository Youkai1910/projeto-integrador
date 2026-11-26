<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use DomainException;
use PDO;
use ProjetoIntegrador\Helpers\Env;
use ProjetoIntegrador\Helpers\Validacoes;
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
				'SELECT id, nome, email, criado_em FROM usuarios ORDER BY criado_em DESC, id DESC'
			);
		} else {
			$consulta = $this->conexao->prepare(
				'SELECT id, nome, email, criado_em
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
		$consultaTipo = $this->conexao->prepare(
			"SELECT tipo FROM usuarios WHERE id = :id LIMIT 1"
		);
		$consultaTipo->execute(['id' => $idUsuario]);
		$tipo = $consultaTipo->fetchColumn();
		if ($tipo === 'admin') {
			return true;
		}

		$idAdministradorConfigurado = Env::obter('ADMIN_USER_ID');
		if ($idAdministradorConfigurado === null) {
			return false;
		}
		$idAdministrador = filter_var(
			$idAdministradorConfigurado,
			FILTER_VALIDATE_INT,
			['options' => ['min_range' => 1]]
		);
		if ($idAdministrador === false) {
			throw new \RuntimeException('A configuração ADMIN_USER_ID deve ser um identificador positivo.');
		}

		return $idUsuario === (int) $idAdministrador && $this->usuarioExiste($idUsuario);
	}

	public function autenticar(string $email, string $senha): ?Usuario
	{
		$email = trim($email);
		if (!Validacoes::email($email) || $senha === '') {
			return null;
		}

		$consulta = $this->conexao->prepare(
			'SELECT id, nome, email, senha, criado_em FROM usuarios WHERE email = :email LIMIT 1'
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

	public function inserir(string $nome, string $email, string $senha): int
	{
		$nome = trim($nome);
		$email = trim($email);

		if (!Validacoes::nome($nome)) {
			throw new DomainException('Informe um nome com até 150 caracteres.');
		}
		if (!Validacoes::email($email)) {
			throw new DomainException('Informe um e-mail válido com até 254 caracteres.');
		}
		if (!Validacoes::senha($senha)) {
			throw new DomainException('A senha deve ter pelo menos 8 caracteres.');
		}

		$hashSenha = password_hash($senha, PASSWORD_DEFAULT);
		if ($hashSenha === false) {
			throw new \RuntimeException('Não foi possível proteger a senha do usuário.');
		}

		try {
			$consulta = $this->conexao->prepare(
				"INSERT INTO usuarios (nome, senha, email, tipo) VALUES (:nome, :senha, :email, 'usuario')"
			);
			$consulta->execute([
				'nome' => $nome,
				'senha' => $hashSenha,
				'email' => $email,
			]);
		} catch (\PDOException $e) {
			if ($e->getCode() === '23000') {
				throw new DomainException('Já existe uma conta cadastrada com esse e-mail.');
			}
			throw $e;
		}

		return (int) $this->conexao->lastInsertId();
	}

	public function excluir(int $idUsuario, int $idAdministrador): void
	{
		if (!$this->ehAdministrador($idAdministrador)) {
			throw new DomainException('A exclusão permanente exige autorização de um administrador.');
		}

		if ($idUsuario === $idAdministrador) {
			throw new DomainException('Não é permitido excluir a própria conta.');
		}

		$this->conexao->beginTransaction();
		try {
			$consulta = $this->conexao->prepare('SELECT id FROM usuarios WHERE id = :id FOR UPDATE');
			$consulta->execute(['id' => $idUsuario]);

			if ($consulta->fetchColumn() === false) {
				throw new DomainException('O usuário não foi encontrado.');
			}

			$exclusao = $this->conexao->prepare('DELETE FROM usuarios WHERE id = :id');
			$exclusao->execute(['id' => $idUsuario]);
			$this->conexao->commit();
		} catch (Throwable $e) {
			$this->reverterTransacao();
			throw $e;
		}
	}

	public function excluirPropriaConta(int $idUsuario, string $senhaAtual): void
	{
		if ($idUsuario < 1) {
			throw new DomainException('A conta informada é inválida.');
		}
		if ($senhaAtual === '' || strlen($senhaAtual) > 4096) {
			throw new DomainException('Informe sua senha atual para confirmar a exclusão.');
		}
		if ($this->ehAdministrador($idUsuario)) {
			throw new DomainException('A conta configurada para administração não pode ser excluída por esta página.');
		}

		$this->conexao->beginTransaction();
		try {
			$consulta = $this->conexao->prepare(
				'SELECT senha FROM usuarios WHERE id = :id FOR UPDATE'
			);
			$consulta->execute(['id' => $idUsuario]);
			$usuario = $consulta->fetch(PDO::FETCH_ASSOC);

			if ($usuario === false) {
				throw new DomainException('A conta não foi encontrada.');
			}
			if (!password_verify($senhaAtual, (string) $usuario['senha'])) {
				throw new DomainException('A senha atual está incorreta.');
			}

			$exclusao = $this->conexao->prepare('DELETE FROM usuarios WHERE id = :id');
			$exclusao->execute(['id' => $idUsuario]);
			if ($exclusao->rowCount() !== 1) {
				throw new DomainException('Não foi possível confirmar a exclusão da conta.');
			}

			$this->conexao->commit();
		} catch (Throwable $e) {
			$this->reverterTransacao();
			throw $e;
		}
	}

	private function reverterTransacao(): void
	{
		if ($this->conexao->inTransaction()) {
			$this->conexao->rollBack();
		}
	}

	public function usuarioExiste(int $idUsuario): bool
	{
		$consulta = $this->conexao->prepare('SELECT 1 FROM usuarios WHERE id = :id LIMIT 1');
		$consulta->execute(['id' => $idUsuario]);
		return $consulta->fetchColumn() !== false;
	}
}
