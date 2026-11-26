<?php
declare(strict_types=1);

use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\UsuarioServico;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();

Seguranca::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cadastroRealizado = false;
    try {
        Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        if (!is_string($nome) || !is_string($email) || !is_string($senha)) {
            throw new \DomainException('Confira os dados informados e tente novamente.');
        }

        $servico = new UsuarioServico(ConexaoBD::getConexao());
        $servico->inserir($nome, $email, $senha);
        $cadastroRealizado = true;
    } catch (\DomainException $e) {
        $_SESSION['cadastro_flash'] = ['mensagem' => $e->getMessage(), 'tipo' => 'erro'];
    } catch (\Throwable $e) {
        error_log($e->getMessage());
        $_SESSION['cadastro_flash'] = ['mensagem' => 'Não foi possível concluir o cadastro. Confira a conexão e tente novamente.', 'tipo' => 'erro'];
    }

    if ($cadastroRealizado) {
        header('Location: login.php', true, 303);
    } else {
        header('Location: cadastro.php', true, 303);
    }
    exit;
}

$flash = $_SESSION['cadastro_flash'] ?? null;
unset($_SESSION['cadastro_flash']);
$mensagem = is_array($flash) ? (string) ($flash['mensagem'] ?? '') : '';
$tipoMensagem = is_array($flash) ? (string) ($flash['tipo'] ?? '') : '';
$tokenCsrf = Seguranca::obterTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Portal Bíblico</title>
    <link rel="stylesheet" href="css/publico.css">
</head>
<body>
    <main class="cartao-publico">
        <a class="voltar" href="index.php">← Voltar ao Portal Bíblico</a>
        <h1>Crie sua conta</h1>
        <p>Cadastre-se para fazer parte do Portal Bíblico.</p>
        <?php if ($mensagem !== ''): ?>
            <p class="aviso <?= $tipoMensagem === 'sucesso' ? 'sucesso' : 'erro' ?>" role="status"><?= Seguranca::escapar($mensagem) ?></p>
        <?php endif; ?>
        <form method="post" action="cadastro.php" class="form-publico">
            <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
            <label>Nome completo<input type="text" name="nome" maxlength="150" autocomplete="name" required></label>
            <label>E-mail<input type="email" name="email" maxlength="254" autocomplete="email" required></label>
            <label>Senha (mínimo 8 caracteres)<input type="password" name="senha" minlength="8" maxlength="4096" autocomplete="new-password" required></label>
            <button type="submit">Criar conta</button>
        </form>
        <p class="nota">Já possui uma conta? <a href="login.php">Entrar</a>.</p>
        <p class="nota">Novas contas são contas de usuário padrão.</p>
    </main>
</body>
</html>
