<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Helpers;

use DomainException;

final class Seguranca
{
    private function __construct()
    {
    }

    public static function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function obterTokenCsrf(): string
    {
        if (!isset($_SESSION['usuarios_csrf']) || !is_string($_SESSION['usuarios_csrf'])) {
            $_SESSION['usuarios_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['usuarios_csrf'];
    }

    /** @param mixed $tokenEnviado O valor recebido pode não ser uma string. */
    public static function validarTokenCsrf($tokenEnviado): void
    {
        $tokenSessao = $_SESSION['usuarios_csrf'] ?? '';
        if (!is_string($tokenEnviado) || !is_string($tokenSessao) || $tokenSessao === '' || !hash_equals($tokenSessao, $tokenEnviado)) {
            throw new DomainException('A solicitação expirou. Atualize a página e tente novamente.');
        }
    }

    public static function escapar(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function guardarMensagem(string $mensagem, string $tipo): void
    {
        $_SESSION['usuarios_flash'] = ['mensagem' => $mensagem, 'tipo' => $tipo];
    }

    public static function redirecionarParaLista($busca = ''): void
    {
        $url = 'usuarios.php';
        if (is_string($busca) && trim($busca) !== '') {
            $url .= '?' . http_build_query(['q' => substr(trim($busca), 0, 100)]);
        }

        header('Location: ' . $url, true, 303);
        exit;
    }
}