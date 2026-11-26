<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use PHPMailer\PHPMailer\OAuthTokenProvider;
use RuntimeException;

final class OutlookOAuthTokenProvider implements OAuthTokenProvider
{
    private const TOKEN_ENDPOINT = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';
    private const SMTP_SCOPE = 'https://outlook.office.com/SMTP.Send offline_access';

    private string $clientId;
    private string $email;

    public function __construct(string $clientId, string $email)
    {
        $this->clientId = trim($clientId);
        $this->email = trim($email);

        if ($this->clientId === '' || filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Configure um ID de aplicativo Microsoft e um e-mail Outlook válidos.');
        }
    }

    public function getOauth64()
    {
        $accessToken = $this->obterTokenDeAcesso();

        return base64_encode('user=' . $this->email . "\001auth=Bearer " . $accessToken . "\001\001");
    }

    public static function caminhoArquivoToken(): string
    {
        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . '.secrets'
            . DIRECTORY_SEPARATOR . 'outlook-refresh-token.txt';
    }

    public static function salvarTokenRefresh(string $token): void
    {
        $token = trim($token);
        if ($token === '') {
            throw new RuntimeException('O token de atualização do Outlook está vazio.');
        }

        $caminho = self::caminhoArquivoToken();
        $diretorio = dirname($caminho);
        if (!is_dir($diretorio) && !mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível criar o diretório local protegido para o token Outlook.');
        }

        if (file_put_contents($caminho, $token, LOCK_EX) === false) {
            throw new RuntimeException('Não foi possível salvar o token Outlook fora da raiz pública.');
        }

        if (DIRECTORY_SEPARATOR !== '\\' && !chmod($caminho, 0600)) {
            throw new RuntimeException('Não foi possível restringir as permissões do arquivo de token Outlook.');
        }
    }

    private function obterTokenDeAcesso(): string
    {
        $caminho = self::caminhoArquivoToken();
        if (!is_file($caminho)) {
            throw new RuntimeException('Autorize o Outlook executando php scripts/autorizar-outlook.php.');
        }

        $tokenRefresh = file_get_contents($caminho);
        if ($tokenRefresh === false || trim($tokenRefresh) === '') {
            throw new RuntimeException('O arquivo de token Outlook está ausente ou vazio.');
        }

        $dados = $this->postarFormulario([
            'client_id' => $this->clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => trim($tokenRefresh),
            'scope' => self::SMTP_SCOPE,
        ]);

        if (!isset($dados['access_token']) || !is_string($dados['access_token'])) {
            $codigoErro = is_string($dados['error'] ?? null) ? $dados['error'] : 'resposta_inválida';
            throw new RuntimeException('Não foi possível renovar o token OAuth2 do Outlook: ' . $codigoErro);
        }

        if (isset($dados['refresh_token']) && is_string($dados['refresh_token'])) {
            self::salvarTokenRefresh($dados['refresh_token']);
        }

        return $dados['access_token'];
    }

    /** @param array<string, string> $campos
     *  @return array<string, mixed>
     */
    private function postarFormulario(array $campos): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('A extensão PHP cURL precisa estar ativada para usar o OAuth2 do Outlook.');
        }

        $curl = curl_init(self::TOKEN_ENDPOINT);
        if ($curl === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão OAuth2 com a Microsoft.');
        }

        if (!curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($campos, '', '&', PHP_QUERY_RFC3986),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ])) {
            $erroCurl = curl_error($curl);
            throw new RuntimeException('Não foi possível configurar a conexão OAuth2: ' . $erroCurl);
        }

        $resposta = curl_exec($curl);
        $codigoHttp = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $erroCurl = curl_error($curl);

        if ($resposta === false) {
            throw new RuntimeException('Falha ao conectar ao serviço OAuth2 da Microsoft: ' . $erroCurl);
        }

        $dados = json_decode($resposta, true);
        if (!is_array($dados) || $codigoHttp < 200 || $codigoHttp >= 300) {
            $codigoErro = is_array($dados) && is_string($dados['error'] ?? null)
                ? $dados['error']
                : 'resposta_inválida';
            throw new RuntimeException('Falha ao renovar o token OAuth2 do Outlook: ' . $codigoErro);
        }

        return $dados;
    }
}
