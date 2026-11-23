<?php
declare(strict_types=1);

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
	$idUsuario = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
	$tipo = $_POST['tipo'] ?? '';
	if ($idUsuario === false || $idUsuario < 1 || !is_string($tipo)) {
		throw new DomainException('Os dados enviados são inválidos.');
	}

	if (!$ehAdministrador) {
		http_response_code(403);
		Seguranca::guardarMensagem('Somente administradores podem alterar perfis de acesso.', 'erro');
		Seguranca::redirecionarParaLista($busca);
	}

	$usuarioServico->atualizarTipo((int) $idUsuario, $tipo, $idAdministrador);
	Seguranca::guardarMensagem('O perfil do usuário foi atualizado.', 'sucesso');
} catch (DomainException $e) {
	Seguranca::guardarMensagem($e->getMessage(), 'erro');
} catch (Throwable $e) {
	error_log($e->getMessage());
	Seguranca::guardarMensagem('Não foi possível atualizar o perfil. Tente novamente.', 'erro');
}

Seguranca::redirecionarParaLista($busca);
