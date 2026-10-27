<?php
require_once __DIR__ . '/sessao.php';

// Esvazia também a cópia do carrinho armazenada na sessão PHP.
$_SESSION['carrinho'] = [];

// Encerra a sessão do usuário cliente.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();

// O servidor não consegue apagar localStorage diretamente; a limpeza é feita
// no navegador antes do redirecionamento para a tela de login.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2;url=form.php?logout=1">
    <title>Saindo da conta - Garras &amp; Patas</title>
</head>
<body>
    <p>Saindo da sua conta...</p>
    <script>
        (function () {
            try {
                // A chave principal do carrinho e a chave antiga usada na migração.
                localStorage.setItem('garras_patas_carrinho_v2', '[]');
                localStorage.removeItem('garras_patas_carrinho');
            } catch (erro) {
                // O redirecionamento ainda acontece se o navegador bloquear storage.
            }
            window.location.replace('form.php?logout=1');
        })();
    </script>
</body>
</html>
