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
	if ($idUsuario === false || $idUsuario < 1) {
		throw new DomainException('O usuário informado é inválido.');
	}

	$usuarioServico->excluir((int) $idUsuario, $idAdministrador);
	Seguranca::guardarMensagem('A conta e suas anotações vinculadas foram removidas.', 'sucesso');
} catch (DomainException $e) {
	Seguranca::guardarMensagem($e->getMessage(), 'erro');
} catch (Throwable $e) {
	error_log($e->getMessage());
	Seguranca::guardarMensagem('Não foi possível excluir a conta. Tente novamente.', 'erro');
}

Seguranca::redirecionarParaLista($busca);
