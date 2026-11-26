<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Enum\TipoUsuario;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Services\UsuarioServico;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

$usuarioSessao = $_SESSION['usuario'] ?? null;
$usuarioId = is_array($usuarioSessao)
    ? Validacoes::idPositivo($usuarioSessao['id'] ?? null)
    : null;
if ($usuarioId === null) {
    $_SESSION['auth_flash'] = [
        'mensagem' => 'Entre na sua conta para acessar as configurações da conta.',
        'tipo' => 'erro',
    ];
    header('Location: login.php', true, 303);
    exit;
}
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Método não permitido.');
}

$erros = [];
$tipoUsuario = null;
$tokenCsrf = Seguranca::obterTokenCsrf();

try {
    $servico = new UsuarioServico(ConexaoBD::getConexao());
    $tipoUsuario = $servico->obterTipoUsuario($usuarioId);
    if ($tipoUsuario === null) {
        $_SESSION = [];
        header('Location: login.php', true, 303);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
        $senhaAtual = $_POST['senha_atual'] ?? null;
        if (($_POST['confirmar_exclusao'] ?? null) !== '1' || !is_string($senhaAtual)) {
            throw new DomainException('Confirme a exclusão e informe sua senha atual.');
        }

        $servico->excluirPropriaConta($usuarioId, $senhaAtual);
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['auth_flash'] = [
            'mensagem' => 'Sua conta e os dados vinculados foram excluídos.',
            'tipo' => 'sucesso',
        ];
        header('Location: login.php', true, 303);
        exit;
    }
} catch (DomainException $erro) {
    $erros[] = $erro->getMessage();
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    $erros[] = 'Não foi possível processar a solicitação. Tente novamente.';
}

$nomeUsuario = is_array($usuarioSessao) && is_string($usuarioSessao['nome'] ?? null)
    ? $usuarioSessao['nome']
    : 'usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta | Portal Bíblico</title>
    <link rel="stylesheet" href="css/publico.css">
</head>
<body>
    <main class="cartao-publico">
        <a class="voltar" href="index.php">← Voltar ao Portal Bíblico</a>
        <h1>Minha conta</h1>
        <p>Olá, <?= Seguranca::escapar($nomeUsuario) ?>.</p>

        <?php if ($erros): ?>
            <?php foreach ($erros as $erro): ?>
                <p class="aviso erro" role="alert"><?= Seguranca::escapar($erro) ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($tipoUsuario === TipoUsuario::USUARIO): ?>
            <section class="exclusao-conta">
                <h2>Excluir minha conta</h2>
                <p>
                    A exclusão é permanente. Suas anotações e favoritos bíblicos
                    também serão removidos, e não será possível recuperá-los.
                </p>
                <form method="post" action="conta.php" class="form-publico">
                    <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
                    <label>
                        Senha atual
                        <input type="password" name="senha_atual" maxlength="4096" autocomplete="current-password" required>
                    </label>
                    <label class="confirmacao-exclusao">
                        <input type="checkbox" name="confirmar_exclusao" value="1" required>
                        <span>Confirmo que quero excluir permanentemente minha conta.</span>
                    </label>
                    <button class="botao-perigo" type="submit">Excluir minha conta</button>
                </form>
            </section>
        <?php else: ?>
            <p class="aviso erro" role="status">
                A exclusão própria está disponível somente para contas de usuário.
                Para contas de edição ou administração, contate um administrador.
            </p>
        <?php endif; ?>

        <p class="nota"><a href="admin/anotacoes.php">Voltar às anotações</a></p>
    </main>
</body>
</html>
