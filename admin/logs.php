<?php
declare(strict_types=1);

use ProjetoIntegrador\Helpers\Seguranca;
use ProjetoIntegrador\Helpers\Utils;

require_once __DIR__ . '/_bootstrap.php';

if (!$ehAdministrador) {
    http_response_code(403);
    exit('Somente administradores podem consultar o catálogo de erros.');
}

$arquivoLog = dirname(__DIR__) . '/logs/erros.log';
$linhas = is_file($arquivoLog) ? file($arquivoLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$registros = [];

foreach (array_reverse($linhas) as $linha) {
    $registro = json_decode($linha, true);
    if (is_array($registro)) {
        $registros[] = $registro;
    }
}

$registros = array_slice($registros, 0, 200);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Catálogo de erros | Portal Bíblico</title>
    <link rel="stylesheet" href="../css/admin-usuarios.css">
</head>
<body>
<main>
    <header class="cabecalho-pagina">
        <div>
            <h1>Catálogo de erros e inconsistências</h1>
            <p class="subtitulo">Últimos 200 registros técnicos e soluções sugeridas.</p>
        </div>
        <a class="botao botao-secundario" href="usuarios.php">Voltar ao painel</a>
    </header>

    <section class="painel">
        <div class="barra-ferramentas">
            <h2>Registros</h2>
            <span class="proprio"><?= count($registros) ?> registros exibidos</span>
        </div>

        <?php if ($registros === []): ?>
            <p class="vazio">Nenhum erro foi registrado até o momento.</p>
        <?php else: ?>
            <div class="tabela-conteudo">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Severidade</th>
                            <th>Problema</th>
                            <th>Origem</th>
                            <th>Solução sugerida</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td data-label="Data"><?= Utils::escaparHtml((string) ($registro['timestamp'] ?? '')) ?></td>
                            <td data-label="Severidade"><span class="perfil <?= ($registro['severity'] ?? '') === 'critical' ? 'perfil-admin' : '' ?>"><?= Utils::escaparHtml(strtoupper((string) ($registro['severity'] ?? ''))) ?></span></td>
                            <td data-label="Problema"><?= Utils::escaparHtml((string) ($registro['message'] ?? '')) ?></td>
                            <td data-label="Origem">
                                <?= Utils::escaparHtml((string) ($registro['file'] ?? 'desconhecido')) ?>:<?= (int) ($registro['line'] ?? 0) ?><br>
                                <?= Utils::escaparHtml((string) ($registro['request_method'] ?? '')) ?> <?= Utils::escaparHtml((string) ($registro['request_uri'] ?? '')) ?>
                            </td>
                            <td data-label="Solução sugerida"><?= Utils::escaparHtml((string) ($registro['suggested_solution'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
