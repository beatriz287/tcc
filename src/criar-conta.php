<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

function hCadastro($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function tamanhoTextoCadastro(string $texto): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($texto, 'UTF-8')
        : strlen($texto);
}

if (sessaoUsuarioLogado()) {
    header('Location: ' . sessaoRedirectSeguro('menu.php'));
    exit;
}

$erroCadastro = '';

$nomeCadastro = trim((string)($_POST['nome'] ?? ''));
$emailCadastro = trim((string)($_POST['email'] ?? ''));
$redirectCadastro = sessaoRedirectSeguro('menu.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaCadastro = (string)($_POST['senha'] ?? '');
    $confirmarSenha = (string)($_POST['confsenha'] ?? '');

    if ($nomeCadastro === '' || $emailCadastro === '' || $senhaCadastro === '' || $confirmarSenha === '') {
        $erroCadastro = 'Preencha todos os campos.';
    } elseif (tamanhoTextoCadastro($nomeCadastro) < 2) {
        $erroCadastro = 'Informe seu nome.';
    } elseif (tamanhoTextoCadastro($nomeCadastro) > 120) {
        $erroCadastro = 'O nome informado é muito grande.';
    } elseif (!filter_var($emailCadastro, FILTER_VALIDATE_EMAIL)) {
        $erroCadastro = 'Digite um e-mail válido.';
    } elseif (strlen($senhaCadastro) < 8) {
        $erroCadastro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senhaCadastro !== $confirmarSenha) {
        $erroCadastro = 'As senhas não coincidem.';
    } else {
        try {
            $verificar = $conexao->prepare(
                'SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1'
            );
            $verificar->bind_param('s', $emailCadastro);
            $verificar->execute();

            if ($verificar->get_result()->fetch_assoc()) {
                $erroCadastro = 'Este e-mail já está cadastrado. Faça login.';
            } else {
                $senhaHash = password_hash($senhaCadastro, PASSWORD_DEFAULT);

                $inserir = $conexao->prepare(
                    'INSERT INTO usuarios (nome, email, senha_hash, ativo)
                     VALUES (?, ?, ?, 1)'
                );
                $inserir->bind_param(
                    'sss',
                    $nomeCadastro,
                    $emailCadastro,
                    $senhaHash
                );
                $inserir->execute();

                $novoId = (int)$conexao->insert_id;

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $novoId;
                $_SESSION['usuario_nome'] = $nomeCadastro;
                $_SESSION['usuario_email'] = $emailCadastro;

                header('Location: ' . $redirectCadastro);
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                $erroCadastro = 'Este e-mail já está cadastrado. Faça login.';
            } else {
                $erroCadastro = 'Não foi possível criar a conta agora. Verifique se a tabela de usuários foi criada no banco.';
            }
        } catch (Throwable $e) {
            $erroCadastro = 'Não foi possível criar a conta agora. Verifique se a tabela de usuários foi criada no banco.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Criar conta</title>
    <link rel="stylesheet" href="../css/style-criar-conta.css">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>

    <style>
        .mensagem-conta {
            margin: 12px 0 16px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.35;
        }

        .mensagem-conta.erro {
            background: #fdeaea;
            color: #9b2c2c;
        }

        .mensagem-conta.sucesso {
            background: #e8f7ef;
            color: #246b45;
        }
    </style>


<style id="garras-responsivo-v2">
/* Responsividade adicional — preserva o layout original em desktop */
*, *::before, *::after { box-sizing: border-box; }
html, body { max-width: 100%; }
body { overflow-x: hidden; }

.topo {
  width: 100%;
  max-width: 100%;
  min-width: 0;
}

.logo, .icons, .user, .footer-col { min-width: 0; }
.logo img { max-width: 100%; height: auto; }
.user a { max-width: 100%; }
.user span { overflow-wrap: anywhere; }

.footer-container {
  width: min(1120px, calc(100% - 40px)) !important;
  max-width: none !important;
  left: auto !important;
  margin-left: auto !important;
  margin-right: auto !important;
}
.footer-col { overflow-wrap: anywhere; }
.footer-copy { overflow-wrap: anywhere; }

@media (max-width: 950px) {
  .topo {
    padding-left: 24px !important;
    padding-right: 24px !important;
    gap: 18px;
  }
  .espaco-topo { flex: 1 1 auto; min-width: 12px; }
  .icons {
    position: static !important;
    right: auto !important;
    gap: 14px !important;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
}

@media (max-width: 700px) {
  .topo {
    min-height: 70px !important;
    padding: 10px 16px !important;
    gap: 10px;
  }
  .logo { width: auto !important; height: auto !important; flex: 0 0 auto; }
  .logo img { max-width: 118px !important; max-height: 55px !important; margin: 0 !important; transform: none !important; }
  .icons { gap: 10px !important; }
  .user span { display: none !important; }
  .user i { font-size: 24px !important; }

  .footer-container {
    width: min(100% - 28px, 620px) !important;
    grid-template-columns: 1fr !important;
    gap: 26px !important;
  }
  .footer-col { width: 100%; }
  .footer-copy { padding-left: 14px !important; padding-right: 14px !important; }
}

@media (max-width: 430px) {
  .topo { padding-left: 12px !important; padding-right: 12px !important; }
  .logo img { max-width: 105px !important; }
  .icons { gap: 8px !important; }
  .user i { font-size: 22px !important; }
  .footer-container { width: min(100% - 22px, 620px) !important; }
}
</style>

<style id="garras-criar-conta-responsivo">
@media (max-width: 800px) {
  .box { width: min(100% - 30px, 680px) !important; min-height: auto !important; }
  .form-box { width: min(100%, 520px) !important; margin-left: auto !important; margin-right: auto !important; }
  .gato, .cachorro, .biscoito, .racao { max-width: 16vw !important; height: auto !important; }
}
@media (max-width: 600px) {
  .box { width: calc(100% - 20px) !important; padding: 18px !important; }
  .form-box { width: 100% !important; padding: 24px 18px !important; }
  .gato, .cachorro, .biscoito, .racao { opacity: .35; max-width: 23vw !important; }
  .input-group input, .input-group button { width: 100% !important; max-width: 100% !important; }
}
@media (max-width: 430px) {
  .box { width: calc(100% - 12px) !important; padding: 10px !important; }
  .form-box { padding: 20px 14px !important; }
  .gato, .cachorro, .biscoito, .racao { display: none !important; }
}
</style>

    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>
<body>
<a class="skip-link" href="#conteudoPrincipal">Pular para o conteúdo principal</a>


<div class="topo">

    <div class="logo">
    <a href="menu.php">
        <img src="../imagens/logo.png" alt="Garra & Patas Pet Shop">
    </a>
</div>

    <div class="espaco-topo"></div>

    <div class="icons">
        <div class="user">
            <a href="criar-conta.php">
                <i class="bi bi-lock"></i>
                <span>Ambiente seguro</span>
            </a>
        </div>
    </div>

</div>

<div class="box" id="conteudoPrincipal" role="main" tabindex="-1">

    <img src="../imagens/gato.png" class="gato">
    <img src="../imagens/cachorro.png" class="cachorro">
    <img src="../imagens/biscoito.png" class="biscoito">
    <img src="../imagens/racao.png" class="racao">

    <div class="form-box">
        <a href="menu.php" class="voltar">X</a>

        <h2>Criar conta</h2>
        <p>Já tem uma conta? <a href="form.php">Fazer login</a></p>

        <?php if ($erroCadastro !== ''): ?>
            <div class="mensagem-conta erro" role="alert" aria-live="assertive"><?= hCadastro($erroCadastro) ?></div>
        <?php endif; ?>

        <form action="criar-conta.php" method="POST">
            <input type="hidden" name="redirect" value="<?= hCadastro($redirectCadastro) ?>">

            <div class="input-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome"
                    value="<?= hCadastro($nomeCadastro) ?>"
                    placeholder="Digite seu nome" autocomplete="name" maxlength="120" required>
            </div>

            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                    value="<?= hCadastro($emailCadastro) ?>"
                    placeholder="Digite seu E-mail" autocomplete="email" required>
            </div>

            <div class="input-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha"
                    placeholder="Digite sua senha" autocomplete="new-password" minlength="8" required>
            </div>

            <div class="input-group">
                <label for="confsenha">Confirmar senha</label>
                <input type="password" id="confsenha" name="confsenha"
                    placeholder="Digite novamente sua senha" autocomplete="new-password" minlength="8" required>
            </div>

            <div class="input-group">
                <button type="submit">Criar conta</button>
            </div>

        </form>
    </div>
</div>

<footer class="footer">

    <div class="footer-top"></div>

    <div class="footer-container">

        <!-- Logo -->
        <div class="footer-col empresa">

            <h2>Garras <span>& Patas</span></h2>

            <p>
                Tudo para o seu pet em um só lugar. Encontre
                rações, brinquedos, acessórios e muito mais
                para cães e gatos.
            </p>

            <div class="social">

                <a href="#"><i class="bi bi-instagram"></i></a>

                <a href="#"><i class="bi bi-facebook"></i></a>

                <a href="https://wa.me/5519996418975"><i class="bi bi-whatsapp"></i></a>

            </div>

        </div>

        <!-- Navegação -->
        <div class="footer-col">

            <h3>Institucional</h3>

            <ul>

                <li><a href="menu.php">Página Inicial</a></li>
                <li><a href="lojas.php">Loja</a></li>
                <li><a href="aba_produto.php">Produtos</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="politica.html">Política e Privacidade</a></li>

            </ul>

        </div>

        <!-- Categorias -->
       

        <!-- Contato -->
        <div class="footer-col">

            <h3>Atendimento</h3>

            <p>📞 (19) 996418975</p>

            <p>📧 contato@carrarasepatas.com</p>

            <p>📍 Rua das Flores, 123</p>

            <p>Segunda a Sexta</p>

            <p>08:00 às 18:00</p>

        </div>

    </div>

    <div class="footer-copy">

        <p>
            © 2026 Garras & Patas - Todos os direitos reservados.
        </p>

        <p>
            Desenvolvido por <strong>Etec</strong> | TCC - Informatica para internet
        </p>

    </div>

</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {

  const track = document.querySelector(".cat-track");
  const leftBtn = document.querySelector(".custom-arrow.left");
  const rightBtn = document.querySelector(".custom-arrow.right");

  if (!track || !leftBtn || !rightBtn) return;

  let position = 0;
  const itemWidth = 130;

  rightBtn.addEventListener("click", () => {
    const maxScroll = track.scrollWidth - track.parentElement.offsetWidth;

    position -= itemWidth;

    if (Math.abs(position) > maxScroll) {
      position = 0;
    }

    track.style.transform = `translateX(${position}px)`;
  });

  leftBtn.addEventListener("click", () => {
    position += itemWidth;

    if (position > 0) {
      position = -(track.scrollWidth - track.parentElement.offsetWidth);
    }

    track.style.transform = `translateX(${position}px)`;
  });

});
</script>

<script src="../js/acessibilidade.js" defer></script>
</body>
</html>