<?php
declare(strict_types=1);

use ProjetoIntegrador\Helpers\Seguranca;

require_once dirname(__DIR__, 2) . '/src/Bootstrap/admin.php';

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
	if (!is_string($nome) || !is_string($email) || !is_string($senha)) {
		throw new DomainException('Os dados enviados são inválidos.');
	}

	if (!$ehAdministrador) {
		http_response_code(403);
		Seguranca::guardarMensagem('Somente a conta configurada pela equipe pode cadastrar usuários.', 'erro');
		Seguranca::redirecionarParaLista($busca);
	}

	$usuarioServico->inserir($nome, $email, $senha);
	Seguranca::guardarMensagem('A conta foi cadastrada com sucesso.', 'sucesso');
} catch (DomainException $e) {
	Seguranca::guardarMensagem($e->getMessage(), 'erro');
} catch (Throwable $e) {
	error_log($e->getMessage());
	Seguranca::guardarMensagem('Não foi possível cadastrar a conta. Tente novamente.', 'erro');
}

Seguranca::redirecionarParaLista($busca);
