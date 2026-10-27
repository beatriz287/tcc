<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$erro = '';
$usuarioInformado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInformado = trim((string)($_POST['usuario'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');

    if ($usuarioInformado === '' || $senha === '') {
        $erro = 'Informe o usuário e a senha.';
    } else {
        $stmt = $conexao->prepare(
            'SELECT id_admin, usuario, senha_hash, nome
             FROM administradores
             WHERE usuario = ? AND ativo = 1
             LIMIT 1'
        );

        if (!$stmt) {
            $erro = 'A autenticação administrativa ainda não foi configurada no banco de dados.';
        } else {
            $stmt->bind_param('s', $usuarioInformado);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $admin = $resultado->fetch_assoc() ?: null;
            $stmt->close();

            if ($admin && password_verify($senha, (string)$admin['senha_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$admin['id_admin'];
                $_SESSION['admin_usuario'] = (string)$admin['usuario'];
                $_SESSION['admin_nome'] = (string)($admin['nome'] ?? 'Administrador');

                $stmtLogin = $conexao->prepare(
                    'UPDATE administradores SET ultimo_login = NOW() WHERE id_admin = ?'
                );
                if ($stmtLogin) {
                    $idAdmin = (int)$admin['id_admin'];
                    $stmtLogin->bind_param('i', $idAdmin);
                    $stmtLogin->execute();
                    $stmtLogin->close();
                }

                header('Location: admin.php');
                exit;
            }

            $erro = 'Usuário ou senha inválidos.';
        }
    }
}

function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso administrativo | Garras & Patas</title>
    <link rel="stylesheet" href="../css/style-menu.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .admin-login {
            width: min(440px, 100%);
            padding: 34px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 40px rgba(0,0,0,.12);
        }
        .admin-login-logo {
            display: block;
            width: 170px;
            max-width: 70%;
            height: auto;
            margin: 0 auto 22px;
        }
        .admin-login h1 {
            margin: 0 0 8px;
            text-align: center;
        }
        .admin-login-subtitulo {
            margin: 0 0 26px;
            text-align: center;
            opacity: .75;
        }
        .admin-login-campo {
            margin-bottom: 18px;
        }
        .admin-login-campo label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }
        .admin-login-campo input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d7d7d7;
            border-radius: 10px;
            font: inherit;
        }
        .admin-login-campo input:focus {
            outline: 2px solid rgba(0,0,0,.12);
            outline-offset: 1px;
        }
        .admin-login-botao {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 13px 16px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .admin-login-erro {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff0f0;
            color: #a11;
        }
        .admin-login-voltar {
            display: block;
            margin-top: 18px;
            text-align: center;
            text-decoration: none;
        }
        @media (max-width: 520px) {
            .admin-login { padding: 24px; }
        }
    

@media (max-width: 380px) {
    body { padding: 12px; }
    .admin-login { padding: 19px; border-radius: 15px; }
    .admin-login-logo { width: 145px; margin-bottom: 17px; }
    .admin-login h1 { font-size: 24px; }
    .admin-login-subtitulo { font-size: 13px; margin-bottom: 20px; }
    .admin-login-campo { margin-bottom: 14px; }
}

</style>
    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>
<body>
<a class="skip-link" href="#conteudoPrincipal">Pular para o conteúdo principal</a>

    <main id="conteudoPrincipal" tabindex="-1" class="admin-login">
        <img class="admin-login-logo" src="../imagens/logo.png" alt="Garras & Patas Pet Shop">
        <h1>Acesso administrativo</h1>
        <p class="admin-login-subtitulo">Entre com a conta do administrador</p>

        <?php if ($erro !== ''): ?>
            <div class="admin-login-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="login-admin.php" autocomplete="off">
            <div class="admin-login-campo">
                <label for="usuario">Usuário</label>
                <input id="usuario" type="text" name="usuario" value="<?= e($usuarioInformado) ?>" autocomplete="username" required autofocus>
            </div>

            <div class="admin-login-campo">
                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" autocomplete="current-password" required>
            </div>

            <button class="admin-login-botao" type="submit">Entrar</button>
        </form>

        <a class="admin-login-voltar" href="menu.php">Voltar para o site</a>
    </main>
<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
