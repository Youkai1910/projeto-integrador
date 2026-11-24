<?php
declare(strict_types=1);

use ProjetoIntegrador\Enum\TipoUsuario;
use ProjetoIntegrador\Helpers\Seguranca;

require_once dirname(__DIR__, 2) . '/src/Bootstrap/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Método não permitido.');
}

$buscaRecebida = $_GET['q'] ?? '';
$busca = is_string($buscaRecebida) ? substr(trim($buscaRecebida), 0, 100) : '';
$mensagemFlash = $_SESSION['usuarios_flash'] ?? null;
unset($_SESSION['usuarios_flash']);
$mensagem = is_array($mensagemFlash) ? (string) ($mensagemFlash['mensagem'] ?? '') : '';
$tipoMensagem = is_array($mensagemFlash) ? (string) ($mensagemFlash['tipo'] ?? '') : '';
$tokenCsrf = Seguranca::obterTokenCsrf();

try {
    $usuarios = $usuarioServico->listar($busca);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $usuarios = [];
    $mensagem = 'Não foi possível carregar a lista de usuários.';
    $tipoMensagem = 'erro';
}

$quantidadeAdministradores = count(array_filter($usuarios, static function ($usuario): bool {
    return $usuario->obterTipo() === TipoUsuario::ADMIN;
}));
$urlLista = 'usuarios.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gerenciar usuários | Portal Bíblico</title>
    <link rel="stylesheet" href="../css/admin-usuarios.css">
</head>
<body>
    <header class="topo">
        <div class="topo-conteudo">
            <a class="marca" href="../index.php">
                <img src="../images/logo.png" alt="">
                <span>Portal Bíblico · Administração</span>
            </a>
            <a class="link-inicio" href="../index.php">Voltar ao início</a>
        </div>
    </header>

    <main>
        <section class="cabecalho-pagina">
            <div>
                <h1>Gerenciar usuários</h1>
                <p class="subtitulo"><?= $ehAdministrador ? 'Acesso administrativo integral.' : 'Acesso administrativo parcial como editor.' ?></p>
            </div>
            <div class="resumo" aria-label="Resumo dos resultados">
                <div class="indicador"><strong><?= count($usuarios) ?></strong><span><?= $busca !== '' ? 'resultados' : 'usuários' ?></span></div>
                <div class="indicador"><strong><?= $quantidadeAdministradores ?></strong><span>administradores listados</span></div>
            </div>
        </section>

        <section class="painel" aria-labelledby="titulo-lista">
            <div class="barra-ferramentas">
                <h2 id="titulo-lista">Contas cadastradas</h2>
                <form class="busca" method="get" action="<?= Seguranca::escapar($urlLista) ?>">
                    <label class="visualmente-oculto" for="busca-usuarios">Pesquisar por nome ou e-mail</label>
                    <input id="busca-usuarios" type="search" name="q" value="<?= Seguranca::escapar($busca) ?>" maxlength="100" placeholder="Nome ou e-mail">
                    <button class="botao" type="submit">Pesquisar</button>
                    <?php if ($busca !== ''): ?><a class="botao botao-secundario" href="<?= Seguranca::escapar($urlLista) ?>">Limpar</a><?php endif; ?>
                </form>
            </div>

            <?php if ($mensagem !== ''): ?>
                <p class="alerta <?= $tipoMensagem === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>" role="status"><?= Seguranca::escapar($mensagem) ?></p>
            <?php endif; ?>

            <?php if ($ehAdministrador): ?>
                <details class="cadastro">
                    <summary>Cadastrar usuário</summary>
                    <form class="form-cadastro" method="post" action="usuario-insere.php">
                        <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
                        <input type="hidden" name="q" value="<?= Seguranca::escapar($busca) ?>">
                        <label>Nome<input type="text" name="nome" maxlength="150" autocomplete="name" required></label>
                        <label>E-mail<input type="email" name="email" maxlength="254" autocomplete="email" required></label>
                        <label>Senha (mínimo 8 caracteres)<input type="password" name="senha" minlength="8" maxlength="4096" autocomplete="new-password" required></label>
                        <label>Perfil
                            <select name="tipo" required>
                                <?php foreach (TipoUsuario::obterOpcoes() as $tipo => $rotulo): ?>
                                    <option value="<?= Seguranca::escapar($tipo) ?>" <?= $tipo === TipoUsuario::USUARIO ? 'selected' : '' ?>><?= Seguranca::escapar($rotulo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="botao" type="submit">Criar conta</button>
                    </form>
                </details>
            <?php endif; ?>

            <?php if ($usuarios === []): ?>
                <p class="vazio"><?= $busca !== '' ? 'Nenhum usuário corresponde à pesquisa.' : 'Ainda não há usuários cadastrados.' ?></p>
            <?php else: ?>
                <div class="tabela-conteudo">
                    <table>
                        <thead>
                            <tr><th>Usuário</th><th>Perfil</th><th>Cadastro</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <?php
                            $idUsuario = $usuario->obterId();
                            if ($idUsuario === null) {
                                continue;
                            }
                            $tipoUsuario = $usuario->obterTipo();
                            $nomeUsuario = $usuario->obterNome();
                            ?>
                            <tr>
                                <td data-label="Usuário">
                                    <div class="nome-usuario"><?= Seguranca::escapar($nomeUsuario) ?><?= $idUsuario === $idAdministrador ? ' <span class="proprio">(você)</span>' : '' ?></div>
                                    <div class="email-usuario"><?= Seguranca::escapar($usuario->obterEmail()) ?></div>
                                </td>
                                <td data-label="Perfil"><span class="perfil <?= $tipoUsuario === TipoUsuario::ADMIN ? 'perfil-admin' : '' ?>"><?= Seguranca::escapar(TipoUsuario::obterRotulo($tipoUsuario)) ?></span></td>
                                <td data-label="Cadastro"><?= Seguranca::escapar($usuario->obterDataCriacao() ?? '') ?></td>
                                <td class="acoes-celula" data-label="Ações">
                                    <?php if ($idUsuario === $idAdministrador): ?>
                                        <span class="proprio">Sua própria conta não pode ser alterada aqui.</span>
                                    <?php else: ?>
                                        <?php if ($ehAdministrador): ?>
                                        <div class="acoes">
                                            <form class="form-perfil" method="post" action="usuario-atualiza.php">
                                                <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
                                                <input type="hidden" name="id" value="<?= $idUsuario ?>">
                                                <input type="hidden" name="q" value="<?= Seguranca::escapar($busca) ?>">
                                                <label class="visualmente-oculto" for="tipo-<?= $idUsuario ?>">Perfil de <?= Seguranca::escapar($nomeUsuario) ?></label>
                                                <select id="tipo-<?= $idUsuario ?>" name="tipo">
                                                    <?php foreach (TipoUsuario::obterOpcoes() as $tipo => $rotulo): ?>
                                                        <option value="<?= Seguranca::escapar($tipo) ?>" <?= $tipoUsuario === $tipo ? 'selected' : '' ?>><?= Seguranca::escapar($rotulo) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="botao botao-pequeno" type="submit">Salvar</button>
                                            </form>
                                            <form method="post" action="usuario-exclui.php" onsubmit="return confirm('Excluir esta conta? As anotações vinculadas também serão removidas.');">
                                                <input type="hidden" name="csrf_token" value="<?= Seguranca::escapar($tokenCsrf) ?>">
                                                <input type="hidden" name="id" value="<?= $idUsuario ?>">
                                                <input type="hidden" name="q" value="<?= Seguranca::escapar($busca) ?>">
                                                <button class="botao botao-pequeno botao-excluir" type="submit">Excluir</button>
                                            </form>
                                        </div>
                                        <?php else: ?>
                                            <span class="proprio">Ações permanentes disponíveis somente ao administrador.</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <div class="rodape">A exclusão de uma conta também remove as anotações vinculadas a ela.</div>
        </section>
    </main>
</body>
</html>