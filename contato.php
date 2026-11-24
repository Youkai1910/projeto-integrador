<?php
declare(strict_types=1);

use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\ContatoServico;

require_once __DIR__ . '/vendor/autoload.php';
Seguranca::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        if (!is_string($nome) || !is_string($email) || !is_string($mensagem)
            || trim($nome) === '' || strlen(trim($nome)) > 150
            || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false
            || strlen(trim($email)) > 254 || trim($mensagem) === '' || strlen($mensagem) > 10000) {
            throw new DomainException('Confira seu nome, e-mail e mensagem antes de enviar.');
        }

        $enviado = (new ContatoServico())->enviar(trim($nome), trim($email), 'Contato pelo Portal Diário', trim($mensagem));
        if (!$enviado) {
            throw new DomainException('O envio pelo site ainda não está configurado. Você pode escrever diretamente para contato@portalbiblico.com.');
        }
        $_SESSION['contato_flash'] = ['mensagem' => 'Mensagem enviada. Obrigado por entrar em contato.', 'tipo' => 'sucesso'];
    } catch (DomainException $e) {
        $_SESSION['contato_flash'] = ['mensagem' => $e->getMessage(), 'tipo' => 'erro'];
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $_SESSION['contato_flash'] = ['mensagem' => 'Não foi possível enviar a mensagem agora. Tente novamente ou use o e-mail informado.', 'tipo' => 'erro'];
    }

    header('Location: contato.php', true, 303);
    exit;
}

$flash = $_SESSION['contato_flash'] ?? null;
unset($_SESSION['contato_flash']);
$mensagemFlash = is_array($flash) ? (string) ($flash['mensagem'] ?? '') : '';
$tipoFlash = is_array($flash) ? (string) ($flash['tipo'] ?? '') : '';
$tokenCsrf = Seguranca::obterTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato | Portal Bíblico</title>
    <link rel="stylesheet" href="css/publico.css">
</head>
<body>
    <main class="cartao-publico">
        <a class="voltar" href="index.php">← Voltar ao Portal Bíblico</a>
        <h1>Fale conosco</h1>
        <p>Envie uma mensagem para a equipe do Portal Bíblico.</p>
        <?php if ($mensagemFlash !== ''): ?>
            <p class="aviso <?= $tipoFlash === 'sucesso' ? 'sucesso' : 'erro' ?>" role="status"><?= Seguranca::escapar($mensagemFlash) ?></p>
        <?php endif; ?>
        <p class="contato-info">E-mail: <a href="mailto:contato@portalbiblico.com">contato@portalbiblico.com</a></p>
        <form method="post" action="contato.php" class="form-publico">
            <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
            <label>Nome<input type="text" name="nome" maxlength="150" autocomplete="name" required></label>
            <label>E-mail<input type="email" name="email" maxlength="254" autocomplete="email" required></label>
            <label>Mensagem<textarea name="mensagem" maxlength="10000" required></textarea></label>
            <button type="submit">Enviar mensagem</button>
        </form>
        <p class="nota">O envio pelo site depende da configuração de e-mail do servidor. Se ela não estiver disponível, use o endereço acima.</p>
    </main>
</body>
</html>
