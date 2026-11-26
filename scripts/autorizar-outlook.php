<?php
declare(strict_types=1);

use ProjetoIntegrador\Services\OutlookOAuthTokenProvider;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$clientId = getenv('OUTLOOK_CLIENT_ID');
if ($clientId === false || trim($clientId) === '') {
    fwrite(STDERR, "Configure OUTLOOK_CLIENT_ID antes de autorizar o Outlook.\n");
    exit(1);
}

$escopos = 'https://outlook.office.com/SMTP.Send offline_access';
$urlCodigoDispositivo = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/devicecode';
$urlToken = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';

try {
    $dispositivo = postarFormularioOAuth($urlCodigoDispositivo, [
        'client_id' => trim($clientId),
        'scope' => $escopos,
    ]);

    if (!isset($dispositivo['device_code'], $dispositivo['message'], $dispositivo['expires_in'])) {
        throw new RuntimeException('A Microsoft não retornou os dados necessários para iniciar a autorização.');
    }

    fwrite(STDOUT, $dispositivo['message'] . PHP_EOL);

    $intervalo = max(1, (int) ($dispositivo['interval'] ?? 5));
    $limite = time() + (int) $dispositivo['expires_in'];
    while (time() < $limite) {
        sleep($intervalo);
        $token = postarFormularioOAuth($urlToken, [
            'client_id' => trim($clientId),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            'device_code' => (string) $dispositivo['device_code'],
        ]);

        if (isset($token['refresh_token']) && is_string($token['refresh_token'])) {
            OutlookOAuthTokenProvider::salvarTokenRefresh($token['refresh_token']);
            fwrite(STDOUT, 'Autorização concluída; token salvo fora da raiz pública e excluído do Git.' . PHP_EOL);
            exit(0);
        }

        $erro = is_string($token['error'] ?? null) ? $token['error'] : '';
        if ($erro === 'authorization_pending') {
            continue;
        }
        if ($erro === 'slow_down') {
            $intervalo += 5;
            continue;
        }

        throw new RuntimeException('A autorização do Outlook não foi concluída: ' . ($erro !== '' ? $erro : 'erro desconhecido'));
    }

    throw new RuntimeException('O código de autorização expirou. Execute o script novamente.');
} catch (RuntimeException $erro) {
    fwrite(STDERR, $erro->getMessage() . PHP_EOL);
    exit(1);
}

/**
 * @param array<string, string> $campos
 * @return array<string, mixed>
 */
function postarFormularioOAuth(string $url, array $campos): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão PHP cURL precisa estar ativada para autorizar o Outlook.');
    }

    $curl = curl_init($url);
    if ($curl === false) {
        throw new RuntimeException('Não foi possível iniciar a conexão com a Microsoft.');
    }

    if (!curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($campos, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ])) {
        $erroCurl = curl_error($curl);
        throw new RuntimeException('Não foi possível configurar a conexão com a Microsoft: ' . $erroCurl);
    }

    $resposta = curl_exec($curl);
    $erroCurl = curl_error($curl);

    if ($resposta === false) {
        throw new RuntimeException('Falha ao conectar ao serviço OAuth2 da Microsoft: ' . $erroCurl);
    }

    $dados = json_decode($resposta, true);
    if (!is_array($dados)) {
        throw new RuntimeException('A Microsoft retornou uma resposta OAuth2 inválida.');
    }

    return $dados;
}
