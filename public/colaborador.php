<?php
declare(strict_types=1);

use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Services\ContatoServico;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ProjetoIntegrador\Helpers\LogServico;

LogServico::configurar();
Seguranca::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Seguranca::validarTokenCsrf($_POST['csrf_token'] ?? null);
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $portfolio = $_POST['portfolio'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        if (!is_string($nome) || !is_string($email) || !is_string($portfolio) || !is_string($mensagem)
            || trim($nome) === '' || strlen(trim($nome)) > 150
            || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false
            || strlen(trim($email)) > 254 || strlen($portfolio) > 2048
            || ($portfolio !== '' && filter_var(trim($portfolio), FILTER_VALIDATE_URL) === false)
            || trim($mensagem) === '' || strlen($mensagem) > 10000) {
            throw new DomainException('Confira seus dados, o link de portfólio e a mensagem.');
        }

        $detalhes = trim($mensagem);
        if (trim($portfolio) !== '') {
            $detalhes .= "\n\nPortfólio ou perfil: " . trim($portfolio);
        }
        $enviado = (new ContatoServico())->enviar(trim($nome), trim($email), 'Proposta de colaboração - Portal Bíblico', $detalhes);
        if (!$enviado) {
            throw new DomainException('O envio pelo site não está disponível agora. Você pode escrever diretamente para contato.portalbiblico@outlook.com.');
        }
        $_SESSION['colaborador_flash'] = ['mensagem' => 'Sua proposta foi enviada. Obrigado pelo interesse em colaborar.', 'tipo' => 'sucesso'];
    } catch (DomainException $e) {
        $_SESSION['colaborador_flash'] = ['mensagem' => $e->getMessage(), 'tipo' => 'erro'];
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $_SESSION['colaborador_flash'] = ['mensagem' => 'Não foi possível enviar sua proposta agora. Tente novamente ou use o e-mail informado.', 'tipo' => 'erro'];
    }

    header('Location: colaborador.php', true, 303);
    exit;
}

$flash = $_SESSION['colaborador_flash'] ?? null;
unset($_SESSION['colaborador_flash']);
$mensagemFlash = is_array($flash) ? (string) ($flash['mensagem'] ?? '') : '';
$tipoFlash = is_array($flash) ? (string) ($flash['tipo'] ?? '') : '';
$tokenCsrf = Seguranca::obterTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seja um colaborador | Portal Bíblico</title>
    <link rel="stylesheet" href="css/publico.css">
</head>
<body>
    <main class="cartao-publico">
        <a class="voltar" href="index.php">← Voltar ao Portal Bíblico</a>
        <h1>Seja um colaborador</h1>
        <p>Conte um pouco sobre você e como gostaria de contribuir com o Portal Bíblico.</p>
        <?php if ($mensagemFlash !== ''): ?>
            <p class="aviso <?= $tipoFlash === 'sucesso' ? 'sucesso' : 'erro' ?>" role="status"><?= Seguranca::escapar($mensagemFlash) ?></p>
        <?php endif; ?>
        <p class="contato-info">Prefere e-mail? <a href="mailto:contato.portalbiblico@outlook.com?subject=Proposta%20de%20colabora%C3%A7%C3%A3o">contato.portalbiblico@outlook.com</a></p>
        <form method="post" action="colaborador.php" class="form-publico">
            <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
            <label>Nome<input type="text" name="nome" maxlength="150" autocomplete="name" required></label>
            <label>E-mail<input type="email" name="email" maxlength="254" autocomplete="email" required></label>
            <label>Portfólio ou perfil (opcional)<input type="url" name="portfolio" maxlength="2048" placeholder="https://"></label>
            <label>Como gostaria de colaborar?<textarea name="mensagem" maxlength="10000" required></textarea></label>
            <button type="submit">Enviar proposta</button>
        </form>
        <p class="nota">Se o formulário não enviar sua proposta, escreva diretamente para o endereço acima.</p>
    </main>
</body>
</html>
