<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use ProjetoIntegrador\Helpers\Env;

final class ContatoServico
{
    public function enviar(string $nome, string $email, string $assunto, string $mensagem): bool
    {
        Env::carregar();
        $formId = Env::obter('FORMSPREE_FORM_ID', '');
        if ($formId === '' || !preg_match('/^[A-Za-z0-9]+$/D', $formId)) {
            error_log('Contato: configure um FORMSPREE_FORM_ID válido.');
            return false;
        }

        $nome = trim(str_replace(["\r", "\n"], ' ', $nome));
        $email = trim($email);
        $assunto = trim(str_replace(["\r", "\n"], '', $assunto));
        $mensagem = trim($mensagem);
        if ($nome === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || $assunto === '' || $mensagem === '') {
            error_log('Contato: os dados do formulário são inválidos.');
            return false;
        }

        if (!function_exists('curl_init')) {
            error_log('Contato: a extensão PHP cURL precisa estar ativada para enviar pelo Formspree.');
            return false;
        }

        $corpo = json_encode([
            'name' => $nome,
            'email' => $email,
            '_subject' => $assunto,
            'message' => $mensagem,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $curl = curl_init('https://formspree.io/f/' . $formId);
        if ($curl === false) {
            error_log('Contato: não foi possível iniciar a conexão com o Formspree.');
            return false;
        }

        $opcoes = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $corpo,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ];

        $arquivoCa = Env::obter('CURL_CA_BUNDLE');
        if ($arquivoCa !== null) {
            if (!is_file($arquivoCa) || !is_readable($arquivoCa)) {
                error_log('Contato: o arquivo configurado em CURL_CA_BUNDLE não existe ou não pode ser lido.');
                return false;
            }
            $opcoes[CURLOPT_CAINFO] = $arquivoCa;
        }

        if (!curl_setopt_array($curl, $opcoes)) {
            error_log('Contato: não foi possível configurar a requisição ao Formspree: ' . curl_error($curl));
            return false;
        }

        $resposta = curl_exec($curl);
        $erroCurl = curl_error($curl);
        $codigoHttp = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if ($resposta === false) {
            error_log('Contato: falha ao conectar ao Formspree: ' . $erroCurl);
            return false;
        }

        if ($codigoHttp < 200 || $codigoHttp >= 300) {
            error_log('Contato: Formspree respondeu com HTTP ' . $codigoHttp . '.');
            return false;
        }

        return true;
    }
}
