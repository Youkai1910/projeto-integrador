<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\UsuarioServico;

require_once __DIR__ . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        if (!is_string($email) || !is_string($senha)) {
            throw new DomainException('E-mail ou senha inválidos.');
        }

        $servico = new UsuarioServico(ConexaoBD::getConexao());
        $usuario = $servico->autenticar($email, $senha);
        if ($usuario === null || $usuario->obterId() === null) {
            throw new DomainException('E-mail ou senha inválidos.');
        }
        $ehAdministrador = $servico->ehAdministrador((int) $usuario->obterId());
        $ehEditor = $servico->ehEditor((int) $usuario->obterId());

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $usuario->obterId(),
            'nome' => $usuario->obterNome(),
        ];
        unset($_SESSION['usuarios_csrf']);
        Seguranca::obterTokenCsrf();

        if ($ehAdministrador || $ehEditor) {
            header('Location: admin/usuarios.php', true, 303);
        } else {
            header('Location: index.php', true, 303);
        }
        exit;
    } catch (DomainException $e) {
        $_SESSION['auth_flash'] = ['mensagem' => $e->getMessage(), 'tipo' => 'erro'];
    } catch (\Throwable $e) {
        error_log($e->getMessage());
        $_SESSION['auth_flash'] = ['mensagem' => 'Não foi possível entrar agora. Tente novamente.', 'tipo' => 'erro'];
    }

    header('Location: login.php', true, 303);
    exit;
}

$flash = $_SESSION['auth_flash'] ?? null;
unset($_SESSION['auth_flash']);
$mensagem = is_array($flash) ? (string) ($flash['mensagem'] ?? '') : '';
$tokenCsrf = Seguranca::obterTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Portal Bíblico</title>
    <link rel="stylesheet" href="css/publico.css">
</head>
<body>
    <main class="cartao-publico">
        <a class="voltar" href="index.php">← Voltar ao Portal Bíblico</a>
        <h1>Entrar</h1>
        <p>Acesse sua conta do Portal Bíblico.</p>
        <?php if ($mensagem !== ''): ?>
            <p class="aviso erro" role="alert"><?= Seguranca::escapar($mensagem) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" class="form-publico">
            <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
            <label>E-mail<input type="email" name="email" maxlength="254" autocomplete="username" required></label>
            <label>Senha<input type="password" name="senha" maxlength="4096" autocomplete="current-password" required></label>
            <button type="submit">Entrar</button>
        </form>
        <p class="nota">Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se</a>.</p>
    </main>
</body>
</html>
