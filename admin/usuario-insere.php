<?php
declare(strict_types=1);

use ProjetoIntegrador\Enum\TipoUsuario;
use ProjetoIntegrador\Helpers\Seguranca;

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	header('Allow: POST');
	exit('Método não permitido.');
}

$busca = $_POST['q'] ?? '';
try {
	Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);

	$nome = $_POST['nome'] ?? '';
	$email = $_POST['email'] ?? '';
	$senha = $_POST['senha'] ?? '';
	$tipo = $_POST['tipo'] ?? TipoUsuario::USUARIO;
	if (!is_string($nome) || !is_string($email) || !is_string($senha) || !is_string($tipo)) {
		throw new DomainException('Os dados enviados são inválidos.');
	}

	$usuarioServico->inserir($nome, $email, $senha, $tipo);
	Seguranca::guardarMensagem('A conta foi cadastrada com sucesso.', 'sucesso');
} catch (DomainException $e) {
	Seguranca::guardarMensagem($e->getMessage(), 'erro');
} catch (Throwable $e) {
	error_log($e->getMessage());
	Seguranca::guardarMensagem('Não foi possível cadastrar a conta. Tente novamente.', 'erro');
}

Seguranca::redirecionarParaLista($busca);
