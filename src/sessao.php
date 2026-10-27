<?php
/* =========================================================
   GARRAS & PATAS — SESSÃO PADRONIZADA
   Mantém a mesma configuração de sessão em todas as páginas.
========================================================= */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function sessaoUsuarioId(): int
{
    return (int)($_SESSION['usuario_id'] ?? 0);
}

function sessaoUsuarioLogado(): bool
{
    return sessaoUsuarioId() > 0;
}

function sessaoPrimeiroNome(): string
{
    $nome = trim((string)($_SESSION['usuario_nome'] ?? ''));

    if ($nome === '') {
        return '';
    }

    $partes = preg_split('/\s+/u', $nome);
    return (string)($partes[0] ?? $nome);
}

function sessaoRedirectSeguro(string $padrao = 'menu.php'): string
{
    $destino = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? ''));

    if ($destino === '') {
        return $padrao;
    }

    /* Aceita apenas caminhos locais do próprio site. */
    if (
        preg_match('#^https?://#i', $destino) ||
        str_starts_with($destino, '//') ||
        str_starts_with($destino, '\\') ||
        preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $destino)
    ) {
        return $padrao;
    }

    return ltrim($destino, '/');
}
