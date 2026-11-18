<?php
require_once __DIR__ . '/database/conexao.php';
$pdo = \ProjetoIntegrador\Database\ConexaoBD::getConexao();
$livros = $pdo->query('SELECT label FROM books ORDER BY id')->fetchAll();
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
            PORTAL
            <div class="logo"></div>
        </div>
        <div class="menu-area">
            <nav class="banner" aria-label="Navegação principal">
                <form class="search-box" action="#" method="GET">
                    <input type="text" placeholder="Pesquisar..." name="q" aria-label="Pesquisar">
                    <button type="submit" aria-label="Enviar pesquisa">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <a href="#"><i class="fa-solid fa-house"></i> HOME</a>
                <a href="#"><i class="fa-solid fa-users"></i> QUEM SOMOS</a>
                <a href="#"><i class="fa-solid fa-envelope"></i> CONTATO</a>
                <a href="#"><i class="fa-solid fa-user-plus"></i> ÁREA DE CADASTRO</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="principal">
            <div>
                <h3>LEITURA DIÁRIA</h3>
                <div class="card-principal">Clique para ver a leitura diária</div>
            </div>
            <div>
                <h3>NOVO TESTAMENTO</h3>
                <div class="card-principal">Clique para ver o novo testamento</div>
            </div>
            <div>
                <h3>ANTIGO TESTAMENTO</h3>
                <div class="card-principal">Clique para ver o antigo testamento</div>
            </div>
        </section>

        <section class="inferior">
            <div>
                <h3>SALMOS</h3>
                <div class="card-inferior">Ver Salmo</div>
            </div>
            <div>
                <h3>VERSÍCULOS</h3>
                <div class="card-inferior">Ver Versículo</div>
            </div>
            <div>
                <h3>EVANGELHO</h3>
                <div class="card-inferior">Ver Evangelho</div>
            </div>
            <div>
                <h3>AUTO AJUDA</h3>
                <div class="card-inferior">Ver Conteúdo</div>
            </div>
        </section>

        <section class="livros-biblia">
            <h3>Livros da Bíblia</h3>
            <div class="lista-livros">
                <?php foreach ($livros as $livro): ?>
                    <div>
                        <?= htmlspecialchars($livro['label'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-col">
            <h4>Portal Diário</h4>
            <p>Um espaço dedicado a nutrir a sua espiritualidade todos os dias com conteúdos atualizados, leituras, salmos e reflexões.</p>
        </div>
        <div class="footer-col">
            <h4>Links Rápidos</h4>
            <a href="#">Política de Privacidade</a>
            <a href="#">Termos de Uso</a>
            <a href="#">Seja um Colaborador</a>
        </div>
        <div class="footer-col">
            <h4>Fale Conosco</h4>
            <p><i class="fa-solid fa-envelope"></i> contato@portaldiario.com</p>
            <p><i class="fa-solid fa-phone"></i> (00) 0000-0000</p>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Portal Diário. Todos os direitos reservados.
        </div>
    </footer>
</div>
</body>
</html>