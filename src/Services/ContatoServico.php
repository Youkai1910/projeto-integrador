<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use ProjetoIntegrador\Helpers\LogServico;

final class ContatoServico
{
    public function enviar(string $nome, string $email, string $assunto, string $mensagem): bool
    {
        $destinatario = getenv('MAIL_TO') ?: '';
        $remetente = getenv('MAIL_FROM') ?: '';

        if (filter_var($destinatario, FILTER_VALIDATE_EMAIL) === false
            || filter_var($remetente, FILTER_VALIDATE_EMAIL) === false) {
            error_log('Contato: MAIL_TO ou MAIL_FROM não está configurado corretamente.');
            return false;
        }

        $nome = trim($nome);
        $email = trim($email);
        $assunto = trim(str_replace(["\r", "\n"], '', $assunto));
        $corpo = "Nome: {$nome}\nE-mail: {$email}\n\n{$mensagem}";
        $cabecalhos = [
            'From' => $remetente,
            'Reply-To' => $email,
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Mailer' => 'PHP/' . PHP_VERSION,
        ];

        $enviado = mail($destinatario, $assunto, $corpo, $cabecalhos);
        if (!$enviado) {
            error_log('Contato: a função mail() não conseguiu enviar a mensagem.');
        }

        return $enviado;
    }
}
