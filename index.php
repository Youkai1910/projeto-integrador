<?php
require_once __DIR__ . '/vendor/autoload.php';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-container">
    <header>
        <div class="logo-area">
            <img class="logo" src="images/logo_biblico.png" alt="Portal Diário">
        </div>
        <div class="menu-area">
            <button
                class="menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="menu-principal"
                aria-label="Abrir menu de navegação"
            >
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <nav class="banner" id="menu-principal" aria-label="Navegação principal">
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
                <div class="card-principal card-leitura">Clique para ver a leitura diária</div>
            </div>
            <div>
                <h3>NOVO TESTAMENTO</h3>
                <div class="card-principal card-novo-testamento">Clique para ver o novo testamento</div>
            </div>
            <div>
                <h3>ANTIGO TESTAMENTO</h3>
                <div class="card-principal card-antigo-testamento">Clique para ver o antigo testamento</div>
            </div>
        </section>

        <section class="inferior">
            <div>
                <h3>SALMOS</h3>
                <div class="card-inferior card-salmos">Ver Salmo</div>
            </div>
            <div>
                <h3>VERSÍCULOS</h3>
                <div class="card-inferior card-versiculos">Ver Versículo</div>
            </div>
            <div>
                <h3>EVANGELHO</h3>
                <div class="card-inferior card-evangelho">Ver Evangelho</div>
            </div>
            <div>
                <h3>AUTO AJUDA</h3>
                <div class="card-inferior card-auto-ajuda">Ver Conteúdo</div>
            </div>
        </section>

        <section class="livros-biblia">
            <h3>Livros da Bíblia</h3>
            <button
                class="btn btn-outline-light d-lg-none mb-3"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#lista-livros-collapse"
                aria-expanded="false"
                aria-controls="lista-livros-collapse"
                aria-label="Abrir ou fechar a lista de livros da Bíblia"
            >
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
                <span>Livros da Bíblia</span>
            </button>
            <div class="collapse d-lg-block" id="lista-livros-collapse">
                <div class="lista-livros">
                    <?php foreach ($livros as $livro): ?>
                        <div>
                            <?= htmlspecialchars($livro['label'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-col">
            <h4>Portal Bíblico</h4>
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
            <p><i class="fa-solid fa-envelope"></i> contato@portalbiblico.com</p>
            <p><i class="fa-solid fa-phone"></i> (11) 95648-2343</p>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Portal bíblico. Todos os direitos reservados.
        </div>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const menuPrincipal = document.querySelector('#menu-principal');

    if (menuToggle && menuPrincipal) {
        menuToggle.addEventListener('click', () => {
            const aberto = menuPrincipal.classList.toggle('menu-aberto');
            menuToggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
            menuToggle.setAttribute('aria-label', aberto ? 'Fechar menu de navegação' : 'Abrir menu de navegação');
            menuToggle.querySelector('i').className = aberto ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });
    }
</script>
</body>
</html>