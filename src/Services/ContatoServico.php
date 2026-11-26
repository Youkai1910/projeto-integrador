<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class ContatoServico
{
    private const EMAIL_CONTATO = 'contato.portalbiblico@outlook.com';

    public function enviar(string $nome, string $email, string $assunto, string $mensagem): bool
    {
        $destinatario = getenv('MAIL_TO') ?: self::EMAIL_CONTATO;
        $remetente = getenv('OUTLOOK_SMTP_USERNAME') ?: self::EMAIL_CONTATO;
        $clientId = getenv('OUTLOOK_CLIENT_ID') ?: '';

        if (filter_var($destinatario, FILTER_VALIDATE_EMAIL) === false
            || filter_var($remetente, FILTER_VALIDATE_EMAIL) === false
            || $clientId === '') {
            error_log('Contato: configure OUTLOOK_CLIENT_ID e verifique OUTLOOK_SMTP_USERNAME e MAIL_TO.');
            return false;
        }

        $nome = trim(str_replace(["\r", "\n"], ' ', $nome));
        $email = trim($email);
        $assunto = trim(str_replace(["\r", "\n"], '', $assunto));
        if ($nome === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || $assunto === '') {
            error_log('Contato: os dados do remetente ou do assunto são inválidos.');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp-mail.outlook.com';
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->Username = $remetente;
            $mail->setOAuth(new OutlookOAuthTokenProvider($clientId, $remetente));
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Timeout = 20;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->setFrom($remetente, 'Portal Bíblico');
            $mail->addAddress($destinatario);
            $mail->addReplyTo($email, $nome);
            $mail->Subject = $assunto;
            $mail->Body = "Nome: {$nome}\r\nE-mail: {$email}\r\n\r\n{$mensagem}";

            $enviado = $mail->send();
            if (!$enviado) {
                error_log('Contato: o servidor SMTP do Outlook não aceitou a mensagem.');
            }

            return $enviado;
        } catch (MailerException $erro) {
            error_log('Contato: falha no envio via Outlook SMTP OAuth2. ' . $mail->ErrorInfo);
            return false;
        } catch (RuntimeException $erro) {
            error_log('Contato: falha na autorização OAuth2 do Outlook. ' . $erro->getMessage());
            return false;
        }
    }
}
