<?php
require_once __DIR__ . '/vendor/autoload.php';
\ProjetoIntegrador\Helpers\Seguranca::iniciarSessao();
$usuarioSessao = $_SESSION['usuario'] ?? null;
$idUsuarioSessao = is_array($usuarioSessao)
    ? filter_var($usuarioSessao['id'] ?? null, FILTER_VALIDATE_INT)
    : false;
$estaLogado = $idUsuarioSessao !== false && $idUsuarioSessao > 0;
$ehAdministrador = false;

if ($estaLogado) {
    try {
        $servicoUsuario = new \ProjetoIntegrador\Services\UsuarioServico(
            \ProjetoIntegrador\Database\ConexaoBD::getConexao()
        );
        $ehAdministrador = $servicoUsuario->ehAdministrador((int) $idUsuarioSessao);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
    }
}

$buscaRecebida = $_GET['q'] ?? '';
$busca = is_string($buscaRecebida) ? substr(trim($buscaRecebida), 0, 100) : '';
$livros = [];
$erroLivros = false;

try {
    $servicoLeitura = new \ProjetoIntegrador\Services\LeituraBiblicaServico(
        \ProjetoIntegrador\Database\ConexaoBD::getConexaoBiblia()
    );
    $livros = $servicoLeitura->listarLivros($busca);
} catch (\Throwable $e) {
    error_log($e->getMessage());
    $erroLivros = true;
}

$idSalmos = null;
foreach ($livros as $livroItem) {
    if ((string) $livroItem['label'] === 'Salmos') {
        $idSalmos = (int) $livroItem['id'];
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Diário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <header>
        <div class="logo-area">
            <img class="logo" src="images/logo.png" alt="Portal Diário">
        </div>
        <div class="menu-area">
            <nav class="banner" aria-label="Navegação principal">
                <form class="search-box" action="index.php" method="GET">
                    <input type="search" placeholder="Pesquisar livros..." name="q" value="<?= htmlspecialchars($busca, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" aria-label="Pesquisar livros">
                    <button type="submit" aria-label="Enviar pesquisa">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <a href="index.php"><i class="fa-solid fa-house"></i> HOME</a>
                <a href="#quem-somos"><i class="fa-solid fa-users"></i> QUEM SOMOS</a>
                <a href="contato.php"><i class="fa-solid fa-envelope"></i> CONTATO</a>
                <a href="cadastro.php"><i class="fa-solid fa-user-plus"></i> ÁREA DE CADASTRO</a>
                <?php if ($ehAdministrador): ?>
                    <a href="admin/usuarios.php"><i class="fa-solid fa-user-gear"></i> ADMINISTRAÇÃO</a>
                <?php endif; ?>
                <?php if ($estaLogado): ?>
                    <form class="logout-form" method="post" action="logout.php">
                        <input type="hidden" name="csrf_token" value="<?= \ProjetoIntegrador\Helpers\Seguranca::escapar(\ProjetoIntegrador\Helpers\Seguranca::obterTokenCsrf()) ?>">
                        <button type="submit">SAIR</button>
                    </form>
                <?php else: ?>
                    <a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> ENTRAR</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <section class="principal">
            <div>
                <h3>LEITURA DIÁRIA</h3>
                <a class="card-principal card-leitura" href="leitura.php?aleatoria=1">Clique para ver uma leitura diária</a>
            </div>
            <div>
                <h3>NOVO TESTAMENTO</h3>
                <a class="card-principal card-novo-testamento" href="leitura.php?testamento=2">Clique para abrir o Novo Testamento</a>
            </div>
            <div>
                <h3>ANTIGO TESTAMENTO</h3>
                <a class="card-principal card-antigo-testamento" href="leitura.php?testamento=1">Clique para abrir o Antigo Testamento</a>
            </div>
        </section>

        <section class="inferior">
            <div>
                <h3>SALMOS</h3>
                <a class="card-inferior card-salmos" href="<?= $idSalmos !== null ? 'leitura.php?livro=' . $idSalmos : 'leitura.php?q=Salmos' ?>">Abrir o livro de Salmos</a>
            </div>
            <div>
                <h3>VERSÍCULOS</h3>
                <a class="card-inferior card-versiculos" href="leitura.php?aleatoria=1">Ler um versículo</a>
            </div>
            <div>
                <h3>EVANGELHO</h3>
                <a class="card-inferior card-evangelho" href="leitura.php?testamento=2">Abrir os Evangelhos</a>
            </div>
            <div>
                <h3>AUTO AJUDA</h3>
                <a class="card-inferior card-auto-ajuda" href="contato.php">Fale conosco</a>
            </div>
        </section>

        <section class="livros-biblia">
            <h3>Livros da Bíblia</h3>
            <?php if ($erroLivros): ?>
                <p>Não foi possível carregar os livros. Verifique a conexão com o banco bíblico e a importação dos arquivos SQL.</p>
            <?php elseif ($livros === []): ?>
                <p><?= $busca !== '' ? 'Nenhum livro corresponde à pesquisa.' : 'A lista de livros ainda não está disponível.' ?></p>
            <?php endif; ?>
            <div class="lista-livros">
                <?php foreach ($livros as $livro): ?>
                    <a href="leitura.php?livro=<?= (int) $livro['id'] ?>">
                        <?= htmlspecialchars((string) $livro['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-col" id="quem-somos">
            <h4>Portal Diário</h4>
            <p>Um espaço dedicado a nutrir a sua espiritualidade todos os dias com conteúdos atualizados, leituras, salmos e reflexões.</p>
        </div>
        <div class="footer-col">
            <h4>Links Rápidos</h4>
            <a href="contato.php">Fale conosco</a>
            <a href="colaborador.php">Seja um Colaborador</a>
        </div>
        <div class="footer-col">
            <h4>Fale Conosco</h4>
            <p><i class="fa-solid fa-envelope"></i> <a href="mailto:contato@portaldiario.com">contato@portaldiario.com</a></p>
            <p><a href="contato.php">Enviar mensagem</a></p>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Portal Diário. Todos os direitos reservados.
        </div>
    </footer>
</div>
</body>
</html>