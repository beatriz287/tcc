<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

$usuarioLogadoId = (int)($_SESSION['usuario_id'] ?? 0);
$usuarioLogadoNome = trim((string)($_SESSION['usuario_nome'] ?? ''));

if ($usuarioLogadoNome !== '') {
    $partesNomeUsuario = preg_split('/\s+/u', $usuarioLogadoNome);
    $primeiroNomeUsuario = $partesNomeUsuario[0] ?? $usuarioLogadoNome;
} else {
    $primeiroNomeUsuario = '';
}

function h($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function menuCalcularPromocaoVariacao(array $variacao): array
{
    $precoOriginal = (float)($variacao['preco'] ?? 0);
    $promocao = (int)($variacao['promocao'] ?? 0) === 1;
    $desconto = $promocao
        ? min(99.99, max(0, (float)($variacao['desconto_percentual'] ?? 0)))
        : 0.0;

    return [
        'precoOriginal' => round($precoOriginal, 2),
        'precoAtual' => $promocao && $desconto > 0
            ? round($precoOriginal * (1 - ($desconto / 100)), 2)
            : round($precoOriginal, 2),
        'promocao' => $promocao && $desconto > 0,
        'desconto' => $desconto,
    ];
}

function menuBuscar(mysqli $conexao, string $sql): array {
    try {
        $resultado = $conexao->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

function precoMenu($valor): string {
    return number_format((float)$valor, 2, ',', '.');
}

function normalizarTextoMenu($valor): string {
    $texto = trim((string)$valor);

    if (function_exists('mb_strtolower')) {
        $texto = mb_strtolower($texto, 'UTF-8');
    } else {
        $texto = strtolower($texto);
    }

    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false) {
            $texto = $convertido;
        }
    }

    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim((string)$texto, '-');
}

function chavePlanoMenu($nome): string {
    return normalizarTextoMenu($nome);
}

$categoriasMenu = menuBuscar($conexao, "SELECT * FROM categorias_menu WHERE ativo = 1 ORDER BY ordem, id_categoria");
$slidesMenu = menuBuscar($conexao, "SELECT * FROM carrossel WHERE ativo = 1 ORDER BY ordem, id_carrossel");
$produtosMenu = menuBuscar($conexao, "SELECT p.*, m.nome AS marca_nome FROM produtos_destaque p LEFT JOIN marcas m ON m.id_marca = p.id_marca WHERE p.ativo = 1 AND p.destaque = 1 ORDER BY p.ordem, p.id_produto");
$exclusivosMenu = menuBuscar($conexao, "SELECT * FROM exclusivos WHERE ativo = 1 ORDER BY ordem, id_exclusivo");
$marcasMenu = menuBuscar($conexao, "SELECT * FROM marcas WHERE ativo = 1 ORDER BY nome, id_marca");
$planosMenu = menuBuscar($conexao, "SELECT * FROM planos_saude WHERE ativo = 1 ORDER BY ordem, id_plano");
$cachorroMenu = menuBuscar(
    $conexao,
    "SELECT tc.*, cp.slug AS categoria_slug, cp.nome AS categoria_nome
     FROM tudo_cachorro tc
     LEFT JOIN categorias_produtos cp
       ON cp.id_categoria_produto = tc.id_categoria_produto
     WHERE tc.ativo = 1
     ORDER BY tc.ordem, tc.id_item"
);
$gatoMenu = menuBuscar(
    $conexao,
    "SELECT tg.*, cp.slug AS categoria_slug, cp.nome AS categoria_nome
     FROM tudo_gato tg
     LEFT JOIN categorias_produtos cp
       ON cp.id_categoria_produto = tg.id_categoria_produto
     WHERE tg.ativo = 1
     ORDER BY tg.ordem, tg.id_item"
);
$categoriasProdutosCachorro = menuBuscar(
    $conexao,
    "SELECT * FROM categorias_produtos WHERE ativo = 1 AND tipo_pet IN ('cachorro', 'ambos') ORDER BY ordem, nome, id_categoria_produto"
);
$categoriasProdutosGato = menuBuscar(
    $conexao,
    "SELECT * FROM categorias_produtos WHERE ativo = 1 AND tipo_pet IN ('gato', 'ambos') ORDER BY ordem, nome, id_categoria_produto"
);


$favoritosUsuarioIds = [];
if ($usuarioLogadoId > 0) {
    try {
        $stmtFavoritosMenu = $conexao->prepare('SELECT id_produto FROM favoritos WHERE id_usuario = ?');
        $stmtFavoritosMenu->bind_param('i', $usuarioLogadoId);
        $stmtFavoritosMenu->execute();
        $rowsFavoritosMenu = $stmtFavoritosMenu->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($rowsFavoritosMenu as $rowFavorito) {
            $favoritosUsuarioIds[] = (int)$rowFavorito['id_produto'];
        }
    } catch (Throwable $e) {
        $favoritosUsuarioIds = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu </title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../css/style-menu.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<!--acessibilidade-->
<script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>

<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
<style>
.garras-cart-link{position:relative;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;}
.garras-cart-badge{position:absolute;top:-9px;right:-10px;min-width:18px;height:18px;padding:0 4px;border-radius:999px;background:#e74c3c;color:#fff;font:700 10px/18px Arial,sans-serif;text-align:center;box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;z-index:50;pointer-events:none;}
.menu-btn-favorito{position:absolute;top:10px;right:10px;z-index:4;width:36px;height:36px;border:0;border-radius:50%;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.12);cursor:pointer;display:flex;align-items:center;justify-content:center;}
.menu-btn-favorito i{font-size:18px;color:#555;}
.menu-btn-favorito.favoritado i{color:#e74c3c;}
.menu-btn-favorito:hover{transform:scale(1.05);}
/* =========================================================
   COBERTURA ABERTA DOS PLANOS
   Não altera o layout do card fechado.
   ========================================================= */

.plano-card {
    position: relative;
}

.cobertura-plano {
    position: absolute;
    inset: 0;
    z-index: 30;

    background: inherit;
    border-radius: inherit;

    padding: 32px 28px 26px;

    display: flex;
    flex-direction: column;

    text-align: left;

    overflow-y: auto;

    animation: coberturaPlanoEntrada .22s ease;
}

@keyframes coberturaPlanoEntrada {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fechar-cobertura {
    position: absolute;
    top: 16px;
    right: 16px;

    width: 36px;
    height: 36px;

    border: none;
    border-radius: 50%;

    background: rgba(255, 255, 255, .85);
    color: #1AA5A5;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 25px;
    line-height: 1;

    cursor: pointer;
}

.fechar-cobertura:hover {
    background: #ffffff;
}

.cobertura-mini {
    padding-right: 45px;

    color: #1AA5A5;

    font-size: 14px;
    font-weight: 700;
}

.cobertura-plano h3 {
    margin: 12px 0 8px;

    color: #1AA5A5;

    font-size: 38px;
    font-weight: 800;
}

.cobertura-preco {
    color: #1AA5A5;

    font-size: 23px;
    font-weight: 800;
}

.cobertura-linha {
    width: 100%;
    height: 1px;

    margin: 18px 0;

    background: rgba(26, 165, 165, .25);
}

.cobertura-plano h4 {
    margin: 0 0 14px;

    color: #444;

    font-size: 17px;
    font-weight: 800;
}

.lista-cobertura {
    margin: 0;
    padding: 0;

    list-style: none;

    display: flex;
    flex-direction: column;
    gap: 9px;
}

.lista-cobertura li {
    display: flex;
    align-items: flex-start;
    gap: 9px;

    color: #555;

    font-size: 14px;
    line-height: 1.35;
}

.lista-cobertura li i {
    flex-shrink: 0;

    margin-top: 2px;

    color: #1AA5A5;
}

.observacao-cobertura {
    margin-top: 20px;
    padding: 12px 14px;

    border-radius: 12px;

    background: rgba(255, 255, 255, .72);

    color: #666;

    font-size: 12px;
    line-height: 1.4;
}

.cobertura-plano::-webkit-scrollbar {
    width: 6px;
}

.cobertura-plano::-webkit-scrollbar-thumb {
    background: rgba(26, 165, 165, .35);
    border-radius: 20px;
}

.marca-card-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.marca-sem-logo {
    width: 100%;
    min-height: 90px;
    padding: 12px;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-weight: 700;
    line-height: 1.2;
    color: #555;
    word-break: break-word;
}


/* =========================================================
   RESPONSIVIDADE DO MENU
   Apenas adapta o layout para tablet e celular.
   O visual de desktop permanece inalterado.
========================================================= */

html,
body {
    max-width: 100%;
    overflow-x: hidden;
}

@media (max-width: 900px) {
    .topo {
        flex-wrap: wrap !important;
        justify-content: space-between !important;
        gap: 8px;
        padding: 10px 18px !important;
    }

    .logo {
        width: auto !important;
        flex: 0 0 auto;
    }

    .logo img {
        width: 120px !important;
        max-width: 120px !important;
        max-height: 58px !important;
        margin-left: 0 !important;
        transform: none !important;
    }

    .busca {
        order: 3;
        flex: 0 0 100% !important;
        width: 100% !important;
        margin: 4px 0 0 !important;
        padding: 0 !important;
    }

    .search-box {
        width: 100% !important;
        max-width: none !important;
        padding-left: 0 !important;
    }

    .search-box input {
        width: 100% !important;
        padding: 11px 44px 11px 16px !important;
    }

    .icons {
        right: auto !important;
        margin-left: auto;
        gap: 12px !important;
    }

    .icons i {
        font-size: 25px !important;
    }

    .user span {
        font-size: 12px !important;
        white-space: nowrap;
    }

    .menu {
        padding: 8px 10px !important;
    }

    .menu ul {
        width: max-content;
        max-width: none !important;
        justify-content: flex-start !important;
        gap: 8px !important;
        margin: 0 !important;
        overflow: visible;
    }

    .menu li {
        flex: 0 0 auto;
        font-size: 16px !important;
    }

    .menu .categoria {
        padding: 10px 12px !important;
        white-space: nowrap;
    }

    .menu-area > .menu {
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .menu-area > .menu::-webkit-scrollbar {
        display: none;
    }

    .mega-menu {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        width: 100% !important;
        max-height: calc(100vh - 120px);
        overflow-y: auto !important;
        border-radius: 0 0 14px 14px !important;
        z-index: 999 !important;
    }

    .mega-container {
        width: 100% !important;
        max-width: none !important;
        min-height: 0 !important;
        height: auto !important;
        display: block !important;
    }

    .mega-imagem,
    .mega-imagemGatos {
        display: none !important;
    }

    .mega-categorias {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        padding: 44px 10px 10px !important;
    }

    .mega-categorias a {
        padding: 11px 10px !important;
        font-size: 14px !important;
        min-width: 0;
    }

    .fechar-mega {
        top: 10px !important;
        right: 12px !important;
        left: auto !important;
        font-size: 13px !important;
    }

    .carousel {
        height: 260px !important;
        margin: 18px auto !important;
    }

    .carousel-track {
        gap: 30px !important;
    }

    .slide {
        min-width: 100% !important;
        max-width: 100% !important;
        height: 260px !important;
        margin: 0 !important;
        border-radius: 16px !important;
    }

    .carousel::before,
    .carousel::after {
        width: 45px !important;
    }

    .indicators {
        bottom: 8px !important;
    }

    .indicators span {
        width: 8px !important;
        height: 8px !important;
        margin: 4px !important;
    }

    .menu-destaques {
        width: calc(100% - 28px) !important;
        max-width: none !important;
        margin: 35px auto 25px !important;
    }

    .menu-destaques-cabecalho {
        margin-bottom: 14px !important;
    }

    .menu-destaques-cabecalho h2 {
        font-size: 21px !important;
    }

    .menu-carrossel-produtos {
        gap: 14px !important;
        padding: 2px 4px 8px !important;
        scroll-snap-type: x proximity;
    }

    .menu-card-produto {
        scroll-snap-align: start;
        min-width: 0 !important;
        flex: 0 0 46% !important;
        max-width: 46% !important;
        padding: 12px !important;
        border-radius: 14px !important;
    }

    .menu-imagem-card-produto {
        height: 150px !important;
    }

    .menu-informacoes-card {
        min-height: 170px !important;
    }

    .menu-nome-produto {
        font-size: 13px !important;
        line-height: 1.3 !important;
        min-height: 36px !important;
    }

    .menu-btn-tamanho {
        height: 32px !important;
        padding: 0 10px !important;
        font-size: 11px !important;
    }

    .menu-preco-atual {
        font-size: 17px !important;
    }

    .menu-preco-pix {
        font-size: 10px !important;
    }

    .menu-comprar-produto {
        min-height: 40px !important;
        font-size: 13px !important;
    }

    .menu-btn-carrinho-card {
        width: 40px !important;
        height: 40px !important;
        font-size: 16px !important;
    }

    .menu-btn-favorito {
        width: 32px !important;
        height: 32px !important;
        top: 8px !important;
        right: 8px !important;
    }

    .menu-btn-favorito i {
        font-size: 16px !important;
    }

    .menu-seta-carrossel.esquerda {
        left: -8px !important;
    }

    .menu-seta-carrossel.direita {
        right: -8px !important;
    }

    .container.mt-5,
    .container.mt-5.mb-5 {
        width: calc(100% - 28px) !important;
        max-width: none !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .titulo-destaque {
        font-size: 21px !important;
        margin-bottom: 12px !important;
    }

    .custom-card {
        flex: 0 0 82% !important;
        max-width: 82% !important;
    }

    .custom-card img {
        height: 205px !important;
    }

    .custom-track,
    .brands-track,
    .custom-track.cat-track {
        gap: 14px !important;
        overflow: visible;
    }

    .brand-card {
        flex: 0 0 100px !important;
        height: 78px !important;
        border-radius: 16px !important;
    }

    .brand-card img {
        width: 82px !important;
        height: 64px !important;
        object-fit: contain !important;
        transform: none !important;
    }

    .marca-sem-logo {
        min-height: 64px !important;
        font-size: 12px !important;
        padding: 8px !important;
    }

    .custom-arrow {
        width: 36px !important;
        height: 36px !important;
    }

    .custom-arrow.left {
        left: -8px !important;
    }

    .custom-arrow.right {
        right: -8px !important;
    }

    .cat-card {
        flex: 0 0 92px !important;
    }

    .cat-circle {
        width: 72px !important;
        height: 72px !important;
    }

    .cat-card p {
        font-size: 14px !important;
        line-height: 1.2 !important;
        margin-top: 6px !important;
    }

    .plano-section {
        padding: 65px 14px !important;
        margin-top: 55px !important;
    }

    .titulo-planos {
        font-size: 32px !important;
        line-height: 1.1 !important;
        margin-bottom: 28px !important;
    }

    .planos-carousel {
        gap: 8px !important;
    }

    .planos-wrapper {
        width: min(100%, 360px) !important;
        max-width: calc(100vw - 70px) !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .planos-track {
        gap: 14px !important;
    }

    .plano-card,
    .plano-card.destaque {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        min-height: 430px !important;
        border-radius: 22px !important;
        padding: 28px 20px !important;
        transform: none !important;
    }

    .plano-card h3 {
        font-size: 42px !important;
        margin: 12px 0 16px !important;
    }

    .valor {
        font-size: 22px !important;
    }

    .plano-arrow {
        width: 36px !important;
        height: 36px !important;
        flex: 0 0 36px;
    }

    .cobertura-plano {
        padding: 24px 18px 20px !important;
    }

    .cobertura-plano h3 {
        font-size: 30px !important;
    }

    .cobertura-preco {
        font-size: 19px !important;
    }

    .cobertura-plano h4 {
        font-size: 15px !important;
    }

    .lista-cobertura li {
        font-size: 13px !important;
    }

    .footer-container {
        grid-template-columns: 1fr !important;
        gap: 28px !important;
        padding: 38px 20px !important;
        text-align: center;
    }

    .footer-col h2 {
        font-size: 29px !important;
    }

    .footer-col h3 {
        font-size: 18px !important;
    }

    .footer-col p {
        font-size: 13px !important;
        line-height: 1.7 !important;
    }

    .social {
        display: flex !important;
        justify-content: center !important;
    }

    .footer-copy {
        padding: 16px 12px !important;
    }

    .footer-copy p {
        font-size: 12px !important;
        line-height: 1.5 !important;
    }
}

@media (max-width: 600px) {
    .user span {
        display: none !important;
    }

    .icons {
        gap: 9px !important;
    }

    .carousel {
        height: 220px !important;
    }

    .slide {
        height: 220px !important;
    }

    .menu-card-produto {
        flex-basis: 78% !important;
        max-width: 78% !important;
    }

    .menu-imagem-card-produto {
        height: 170px !important;
    }

    .custom-card {
        flex-basis: 88% !important;
        max-width: 88% !important;
    }

    .custom-card img {
        height: 190px !important;
    }

    .titulo-planos {
        font-size: 29px !important;
    }

    .planos-wrapper {
        max-width: calc(100vw - 72px) !important;
    }
}

@media (max-width: 430px) {
    .topo {
        padding-left: 13px !important;
        padding-right: 13px !important;
    }

    .logo img {
        width: 105px !important;
        max-width: 105px !important;
    }

    .carousel {
        height: 190px !important;
        margin-top: 14px !important;
    }

    .slide {
        height: 190px !important;
        border-radius: 12px !important;
    }

    .menu-destaques {
        width: calc(100% - 22px) !important;
    }

    .menu-destaques-cabecalho h2,
    .titulo-destaque {
        font-size: 19px !important;
    }

    .menu-card-produto {
        flex-basis: 86% !important;
        max-width: 86% !important;
    }

    .menu-imagem-card-produto {
        height: 155px !important;
    }

    .custom-card img {
        height: 170px !important;
    }

    .cat-card {
        flex-basis: 84px !important;
    }

    .cat-circle {
        width: 66px !important;
        height: 66px !important;
    }

    .cat-card p {
        font-size: 13px !important;
    }

    .plano-section {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .plano-card,
    .plano-card.destaque {
        min-height: 410px !important;
    }

    .footer-container {
        padding-left: 14px !important;
        padding-right: 14px !important;
    }
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
<div class="busca">
    <form class="search-box" action="aba_produto.php" method="get" role="search">
        <label for="buscaCabecalho" class="visually-hidden">Buscar produtos</label>
        <input type="text" id="buscaCabecalho" name="busca" autocomplete="off" placeholder="O melhor para seu pet você encontra aqui.">
        <button type="submit" class="busca-submit" aria-label="Buscar produtos" style="position:absolute;right:15px;top:50%;transform:translateY(-50%);border:0;background:transparent;padding:4px;color:#333;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="bi bi-search" aria-hidden="true"></i></button>
    </form>
</div>

    <div class="icons">
   <div class="user">
        <?php if ($usuarioLogadoId > 0): ?>
            <a href="area-cliente.php">
                <i class="bi bi-person-check"></i>
                <span>Olá, <?= h($primeiroNomeUsuario) ?></span>
            </a>
        <?php else: ?>
            <a href="form.php">
                <i class="bi bi-person"></i>
                <span>Entre ou cadastre-se</span>
            </a>
        <?php endif; ?>
    </div>

    
    <a href="carrinho.php" class="cart-link garras-cart-link" aria-label="Carrinho">
        <i class="bi bi-cart"></i>
        <span class="garras-cart-badge" data-garras-cart-badge aria-live="polite">0</span>
    </a>
    </div>
</div>

<div class="menu-area">

    <nav class="menu" aria-label="Navegação principal">
        <ul>
            <?php
            $nomesMenuRenderizados = [];

            foreach ($categoriasMenu as $categoria):
                $nomeCategoria = trim((string)$categoria['nome']);
                $nomeNormalizado = normalizarTextoMenu($nomeCategoria);
                $nomesMenuRenderizados[] = $nomeNormalizado;

                $idEspecial = '';
                if (strpos($nomeNormalizado, 'cachorr') !== false) $idEspecial = 'btnCachorros';
                if (strpos($nomeNormalizado, 'gato') !== false) $idEspecial = 'btnGatos';
            ?>
            <li class="menu-item">
                <a href="<?= h($categoria['link'] ?: '#') ?>" class="categoria"<?= $idEspecial ? ' id="' . h($idEspecial) . '" aria-controls="' . ($idEspecial === 'btnCachorros' ? 'megaMenu' : 'megaMenuGatos') . '" aria-haspopup="true" aria-expanded="false"' : '' ?>><?= h($nomeCategoria) ?></a>
            </li>
            <?php endforeach; ?>

            <?php
            $linksFixosMenu = [
                ['nome' => 'Produtos', 'link' => 'aba_produto.php'],
                ['nome' => 'Lojas', 'link' => 'loja.php'],
                ['nome' => 'Blog', 'link' => 'blog.html'],
                ['nome' => 'Atendimento', 'link' => 'atendimento.html'],
            ];

            foreach ($linksFixosMenu as $linkFixo):
                if (in_array(normalizarTextoMenu($linkFixo['nome']), $nomesMenuRenderizados, true)) {
                    continue;
                }
            ?>
            <li>
                <a href="<?= h($linkFixo['link']) ?>" class="categoria"><?= h($linkFixo['nome']) ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- ================= MEGA MENU ================= -->

    <div id="megaMenu" class="mega-menu" role="region" aria-label="Categorias para cachorro">

        <div class="mega-container">

           <button type="button" class="fechar-mega" id="fecharMega" aria-label="Fechar categorias de cachorro">
        ✕ Fechar
    </button>
            <div class="mega-imagem">
                <img src="../imagens/cachorro_menu.png" alt="Cachorro">
            </div>

          <div class="mega-categorias">
<?php foreach ($categoriasProdutosCachorro as $iCategoria => $categoriaProduto): ?>
    <a href="aba_produto.php?pet=cachorro&amp;categoria=<?= h($categoriaProduto['slug']) ?>" class="categoria<?= $iCategoria === 0 ? ' ativa' : '' ?>" data-categoria="<?= h($categoriaProduto['slug']) ?>"><?= h($categoriaProduto['nome']) ?></a>
<?php endforeach; ?>
</div>

            

        </div>

    </div>

<!-- ================= MEGA MENU GATOS ================= -->

<div id="megaMenuGatos" class="mega-menu" role="region" aria-label="Categorias para gato">

    <div class="mega-container">

      <button type="button" class="fechar-mega" id="fecharMegaGatos" aria-label="Fechar categorias de gato">
        ✕ Fechar
    </button>
        <div class="mega-imagemGatos">
            <img src="../imagens/gato_menu.png" alt="Gato">
        </div>

        <div class="mega-categorias mega-categorias-gatos">
<?php foreach ($categoriasProdutosGato as $iCategoria => $categoriaProduto): ?>
    <a href="aba_produto.php?pet=gato&amp;categoria=<?= h($categoriaProduto['slug']) ?>" class="categoria<?= $iCategoria === 0 ? ' ativa' : '' ?>" data-categoria="<?= h($categoriaProduto['slug']) ?>"><?= h($categoriaProduto['nome']) ?></a>
<?php endforeach; ?>
</div>

      

    </div>

</div>

</div>


<!-- MENU LATERAL -->


<!-- FUNDO ESCURO -->
<div id="overlay" class="overlay" onclick="fecharMenu()"></div> 

<main id="conteudoPrincipal" tabindex="-1" aria-label="Conteúdo principal da loja">
<div class="carousel">
  <div class="carousel-track">
    <?php foreach ($slidesMenu as $slide): ?>
    <div class="slide" style="--bg:url('<?= h($slide['imagem']) ?>')">
      <img src="<?= h($slide['imagem']) ?>" alt="Banner da Garras &amp; Patas">
    </div>
    <?php endforeach; ?>
  </div>
  <div class="indicators"></div>
</div>


  <!-- =====================================================
     PRODUTOS EM DESTAQUE
===================================================== -->

<section class="menu-destaques">

    <div class="menu-destaques-cabecalho">

        <div>
        

            <h2>Produtos em destaque</h2>
        </div>

    </div>


    <div class="menu-carrossel-area">

        <!-- SETA ESQUERDA -->
        <button
            class="menu-seta-carrossel esquerda"
            type="button"
            id="menuSetaEsquerda"
            aria-label="Anterior"
        >
            <i class="bi bi-chevron-left"></i>
        </button>


        <!-- ÁREA QUE ESCONDE OS PRODUTOS -->
        <div class="menu-carrossel-viewport">

            <div
                class="menu-carrossel-produtos"
                id="menuCarrosselDestaques"
            >
<?php foreach ($produtosMenu as $produto):
    $variacoes = menuBuscar($conexao, 'SELECT id_variacao, id_produto, nome, preco, imagem, promocao, desconto_percentual FROM variacoes_produto WHERE id_produto = ' . (int)$produto['id_produto'] . ' ORDER BY id_variacao');
    $precoBase = (float)$produto['preco'];
    $imagemBase = (string)$produto['imagem'];
    if ($variacoes) {
        $promoInicial = menuCalcularPromocaoVariacao($variacoes[0]);
        $emPromocao = $promoInicial['promocao'];
        $desconto = $promoInicial['desconto'];
        $precoOriginalInicial = $promoInicial['precoOriginal'];
        $precoAtualInicial = $promoInicial['precoAtual'];
    } else {
        // Promoções são controladas exclusivamente por variação.
        // Produtos sem variações não devem herdar descontos antigos do produto.
        $desconto = 0.0;
        $emPromocao = false;
        $precoOriginalInicial = $precoBase;
        $precoAtualInicial = $precoOriginalInicial;
    }
?>
                <article
                    class="menu-card-produto"
                    data-id-produto="<?= (int)$produto['id_produto'] ?>"
                    data-nome-produto="<?= h($produto['nome']) ?>"
                    data-marca-produto="<?= h($produto['marca_nome'] ?? '') ?>"
                    data-imagem-produto="<?= h($imagemBase) ?>"
                    data-preco-produto="<?= h(number_format($precoAtualInicial, 2, '.', '')) ?>"
                    data-preco-original="<?= h(number_format($precoOriginalInicial, 2, '.', '')) ?>"
                    data-desconto-percentual="<?= h(number_format($desconto, 2, '.', '')) ?>"
                    data-variacao-id="<?= $variacoes ? (int)$variacoes[0]['id_variacao'] : '' ?>"
                    data-variacao-nome="<?= $variacoes ? h($variacoes[0]['nome']) : '' ?>"
                >
                    <div class="menu-area-imagem-produto">
                        <?php if ($emPromocao): ?>
                            <span class="menu-selo-desconto">-<?= h(rtrim(rtrim(number_format($desconto, 2, ',', '.'), '0'), ',')) ?>%</span>
                        <?php endif; ?>
                        <button class="menu-btn-favorito<?= in_array((int)$produto['id_produto'], $favoritosUsuarioIds, true) ? ' favoritado' : '' ?>" type="button" data-id-produto-favorito="<?= (int)$produto['id_produto'] ?>" title="Favoritos: <?= h($produto['nome']) ?>" aria-label="Favoritos: <?= h($produto['nome']) ?>" aria-pressed="<?= in_array((int)$produto['id_produto'], $favoritosUsuarioIds, true) ? 'true' : 'false' ?>">
                            <i class="bi <?= in_array((int)$produto['id_produto'], $favoritosUsuarioIds, true) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                        <a href="produto.php?id=<?= (int)$produto['id_produto'] ?>">
                            <img class="menu-imagem-card-produto" src="<?= h($imagemBase) ?>" alt="<?= h($produto['nome']) ?>">
                        </a>
                        <button class="menu-btn-carrinho-card" type="button" aria-label="Adicionar ao carrinho: <?= h($produto['nome']) ?>"><i class="bi bi-cart-plus"></i></button>
                    </div>
                    <div class="menu-informacoes-card">
                        <div class="menu-linha-marca">
                            <span class="menu-marca-produto"><?= h($produto['marca_nome'] ?? '') ?></span>
                        </div>
                        <a href="produto.php?id=<?= (int)$produto['id_produto'] ?>" class="menu-nome-produto"><?= h($produto['nome']) ?></a>
                        <?php if ($variacoes): ?>
                        <div class="menu-area-variacoes">
                            <span class="menu-titulo-variacao">Tamanho:</span>
                            <div class="menu-variacoes-produto">
                                <?php foreach ($variacoes as $i => $variacao):
                                    $promoVariacao = menuCalcularPromocaoVariacao($variacao);
                                    $vPrecoOriginal = $promoVariacao['precoOriginal'];
                                    $vPrecoAtual = $promoVariacao['precoAtual'];
                                    $vImagem = trim((string)($variacao['imagem'] ?? '')) ?: $imagemBase;
                                ?>
                                <button
                                    class="menu-btn-tamanho<?= $i === 0 ? ' ativo' : '' ?>"
                                    type="button"
                                    aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                                    data-id-variacao="<?= (int)$variacao['id_variacao'] ?>"
                                    data-nome-variacao="<?= h($variacao['nome']) ?>"
                                    data-preco="<?= h(number_format($vPrecoAtual, 2, '.', '')) ?>"
                                    data-preco-antigo="<?= h(number_format($vPrecoOriginal, 2, '.', '')) ?>"
                                    data-promocao="<?= $promoVariacao['promocao'] ? '1' : '0' ?>"
                                    data-desconto-percentual="<?= h(number_format($promoVariacao['desconto'], 2, '.', '')) ?>"
                                    data-imagem="<?= h($vImagem) ?>"
                                ><?= h($variacao['nome']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="menu-precos-produto">
                            <?php if ($emPromocao): ?>
                                <span class="menu-preco-antigo">R$ <?= precoMenu($precoOriginalInicial) ?></span>
                            <?php endif; ?>
                            <strong class="menu-preco-atual">R$ <?= precoMenu($precoAtualInicial) ?></strong>
                            <span class="menu-preco-pix">à vista no Pix</span>
                        </div>
                        <button class="menu-comprar-produto" type="button"><i class="bi bi-cart3"></i> Adicionar</button>
                    </div>
                </article>
<?php endforeach; ?>


            </div>

        </div>


        <!-- SETA DIREITA -->
        <button
            class="menu-seta-carrossel direita"
            type="button"
            id="menuSetaDireita"
            aria-label="Próximo"
        >
            <i class="bi bi-chevron-right"></i>
        </button>

    </div>

</section>

<!-- ================================= -->
<!-- VOCÊ SÓ ENCONTRA AQUI -->
<!-- ================================= -->

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="titulo-destaque">Você só encontra aqui</h2>
        
    </div>

    <div class="custom-carousel">

        <button type="button" class="custom-arrow left" aria-label="Itens anteriores"
                onclick="moveCustomCarousel('carouselExclusivos', -1)">
            ‹
        </button>

        <div class="custom-wrapper">
            <div class="custom-track" id="carouselExclusivos">
<?php foreach ($exclusivosMenu as $item): ?>
                <div class="custom-card"><img src="<?= h($item['imagem']) ?>" alt=""></div>
<?php endforeach; ?>

            </div>
        </div>

        <button type="button" class="custom-arrow right" aria-label="Próximos itens"
                onclick="moveCustomCarousel('carouselExclusivos', 1)">
            ›
        </button>

    </div>

</div>



<!-- ================================= -->
<!-- MARCAS -->
<!-- ================================= -->

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="titulo-destaque">Marcas que trabalhamos</h2>
        
    </div>

    <div class="brands-carousel">

        <button type="button" class="custom-arrow left" aria-label="Itens anteriores"
                onclick="moveCustomCarousel('carouselMarcas', -1)">
            ‹
        </button>

        <div class="brands-wrapper">
            <div class="brands-track" id="carouselMarcas">
<?php foreach ($marcasMenu as $marca): ?>
                <div class="brand-card marca-card-logo">
                    <?php if (!empty(trim((string)($marca['imagem'] ?? '')))): ?>
                        <img
                            src="<?= h($marca['imagem']) ?>"
                            alt="<?= h($marca['nome']) ?>"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <span class="marca-sem-logo" style="display:none;"><?= h($marca['nome']) ?></span>
                    <?php else: ?>
                        <span class="marca-sem-logo"><?= h($marca['nome']) ?></span>
                    <?php endif; ?>
                </div>
<?php endforeach; ?>

            </div>
        </div>

        <button type="button" class="custom-arrow right" aria-label="Próximos itens"
                onclick="moveCustomCarousel('carouselMarcas', 1)">
            ›
        </button>

    </div>

</div>

<!-- ================================= -->
<!-- PLANOS DE SAÚDE -->
<!-- ================================= -->

<div class="plano-section">

    <h2 class="titulo-planos">
        Plano de saúde para<br>
        cuidar do seu pet
    </h2>

    <div class="planos-carousel">

        <!-- SETA ESQUERDA -->
        <button type="button" class="plano-arrow left" aria-label="Planos anteriores"
                onclick="movePlanos(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>

        <!-- WRAPPER -->
        <div class="planos-wrapper container">

            <!-- TRACK -->
            <div class="planos-track" id="carouselPlanos">
<?php foreach ($planosMenu as $i => $plano):
    $chavePlano = chavePlanoMenu($plano['nome']);
?>
                <div class="plano-card<?= $i % 3 === 1 ? ' destaque' : '' ?>">
                    <span class="mini">Garras &amp; patas</span>
                    <h3><?= h($plano['nome']) ?></h3>
                    <div class="valor">R$ <?= precoMenu($plano['preco']) ?>/mês</div>
                    <p>em São Paulo e região</p>

                    <button
                        class="btn-cobertura"
                        type="button"
                        data-plano-chave="<?= h($chavePlano) ?>"
                        data-plano-nome="<?= h($plano['nome']) ?>"
                        data-plano-preco="R$ <?= h(precoMenu($plano['preco'])) ?>/mês"
                        data-plano-descricao="<?= h($plano['descricao']) ?>"
                        data-plano-coberturas="<?= h($plano['coberturas'] ?? "") ?>"
                        onclick="abrirCobertura(this)"
                    >
                        Conferir cobertura
                    </button>

                    <a
                        href="carrinho.php"
                        class="btn-contratar-plano"
                        data-plano-id="<?= (int)$plano['id_plano'] ?>"
                        data-plano-nome="<?= h($plano['nome']) ?>"
                        data-plano-preco="<?= h(number_format((float)$plano['preco'], 2, '.', '')) ?>"
                    >Contratar</a>
                </div>
<?php endforeach; ?>

            </div>

        </div>

        

        <!-- SETA DIREITA -->
        <button type="button" class="plano-arrow right" aria-label="Próximos planos"
                onclick="movePlanos(1)">
            <i class="bi bi-chevron-right"></i>
        </button>

    </div>

    <!-- BOLINHAS -->
    

        <span class="active"></span>
        <span></span>
        <span></span>
        <span></span>

      </div>
      </div>

      <section class="custom-section">

  <div class="custom-header">
    <h2>Tudo para cachorro</h2>
  </div>

  <div class="custom-carousel">

    

    <div class="custom-wrapper">
      <div class="custom-track cat-track">
<?php foreach ($cachorroMenu as $item):
    $slugCategoriaTudo = trim((string)($item['categoria_slug'] ?? ''));
    $linkCategoriaTudo = $slugCategoriaTudo !== ''
        ? 'aba_produto.php?pet=cachorro&categoria=' . rawurlencode($slugCategoriaTudo)
        : '#';
?>
        <a href="<?= h($linkCategoriaTudo) ?>" class="cat-card">
          <div class="cat-circle"><img src="<?= h($item['imagem']) ?>" alt="<?= h($item['nome']) ?>"></div>
          <p><?= h($item['nome']) ?></p>
        </a>
<?php endforeach; ?>

      </div>
    </div>

   

  </div>
</section>


 <section class="custom-section">

  <div class="custom-header">
    <h2>Tudo para gato</h2>
  </div>

  <div class="custom-carousel">

   

    <div class="custom-wrapper">
      <div class="custom-track cat-track">
<?php foreach ($gatoMenu as $item):
    $slugCategoriaTudo = trim((string)($item['categoria_slug'] ?? ''));
    $linkCategoriaTudo = $slugCategoriaTudo !== ''
        ? 'aba_produto.php?pet=gato&categoria=' . rawurlencode($slugCategoriaTudo)
        : '#';
?>
        <a href="<?= h($linkCategoriaTudo) ?>" class="cat-card">
          <div class="cat-circle"><img src="<?= h($item['imagem']) ?>" alt="<?= h($item['nome']) ?>"></div>
          <p><?= h($item['nome']) ?></p>
        </a>
<?php endforeach; ?>

      </div>
    </div>

    

  </div>
</section>


    
</main>

<footer class="footer" aria-label="Rodapé do site">

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
                <li><a href="loja.php">Loja</a></li>
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

            <p>📧 garrasepatas00@gmail.com</p>

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
const track = document.querySelector('.carousel-track');
let slides = document.querySelectorAll('.slide');
const indicatorsContainer = document.querySelector('.indicators');

let index = 1;

/* 🔥 CLONAR PRIMEIRO E ÚLTIMO */
const firstClone = slides[0].cloneNode(true);
const lastClone = slides[slides.length - 1].cloneNode(true);

track.appendChild(firstClone);
track.insertBefore(lastClone, slides[0]);

slides = document.querySelectorAll('.slide');

/* 🔥 CRIAR BOLINHAS (sem clones) */
for (let i = 0; i < slides.length - 2; i++) {
    const dot = document.createElement('span');
    dot.addEventListener('click', () => goToSlide(i + 1));
    indicatorsContainer.appendChild(dot);
}

const dots = document.querySelectorAll('.indicators span');

function updateCarousel(animate = true) {
    const slideWidth = slides[0].offsetWidth + 30;
    const offset = (track.offsetWidth - slideWidth) / 2;

    track.style.transition = animate ? "0.7s ease-in-out" : "none";
    track.style.transform = `translateX(calc(-${index * slideWidth}px + ${offset}px))`;

    dots.forEach(dot => dot.classList.remove('active'));

    let realIndex = index - 1;
    if (realIndex < 0) realIndex = dots.length - 1;
    if (realIndex >= dots.length) realIndex = 0;

    dots[realIndex].classList.add('active');
}

function nextSlide() {
    index++;
    updateCarousel();

    if (index === slides.length - 1) {
        setTimeout(() => {
            index = 1;
            updateCarousel(false);
        }, 700);
    }
}

function prevSlide() {
    index--;
    updateCarousel();

    if (index === 0) {
        setTimeout(() => {
            index = slides.length - 2;
            updateCarousel(false);
        }, 700);
    }
}

function goToSlide(i) {
    index = i;
    updateCarousel();
}

/* autoplay */
setInterval(nextSlide, 4000);

window.addEventListener('resize', () => updateCarousel(false));

updateCarousel(false);
</script>

<script>
let position = 0;

function scrollCarousel(direction) {
  const container = document.getElementById('carousel');
  const cards = container.querySelectorAll('.card-prod');

  const cardWidth = cards[0].offsetWidth + 20;
  const visible = 5;

  position += direction * cardWidth * visible;

  const maxScroll = container.scrollWidth - container.parentElement.clientWidth;

  if (position < 0) position = 0;
  if (position > maxScroll) position = maxScroll;

  container.style.transform = `translateX(-${position}px)`;
}
</script>


  <script>
document.querySelectorAll('.card-prod').forEach(card => {

    const track = card.querySelector('.sizes-track');
    const buttons = track.querySelectorAll('button');

    const image = card.querySelector('.produto-img');
    const price = card.querySelector('.price');

    const leftArrow = card.querySelector('.variant-arrow.left');
    const rightArrow = card.querySelector('.variant-arrow.right');

    let current = 0;

    function updateVariant(index){

        const btn = buttons[index];

        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        image.src = btn.dataset.img;
        price.textContent = `R$ ${btn.dataset.price}`;

        const btnWidth = btn.offsetWidth + 8;

        track.style.transform =
            `translateX(-${Math.max(0, index - 1) * btnWidth}px)`;
    }

    updateVariant(0);

    rightArrow.addEventListener('click', () => {

        if(current < buttons.length - 1){
            current++;
        }

        updateVariant(current);
    });

    leftArrow.addEventListener('click', () => {

        if(current > 0){
            current--;
        }

        updateVariant(current);
    });

    buttons.forEach((btn,index)=>{

        btn.addEventListener('click',()=>{

            current = index;
            updateVariant(current);

        });

    });

});
</script>

<script>

const buttons = document.querySelectorAll('.sizes button:not(.variant-arrow)');

buttons.forEach(button => {

  button.addEventListener('click', () => {

    // pega o card correto
    const card = button.closest('.card-prod');

    // elementos do card clicado
    const price = card.querySelector('.price');
    const image = card.querySelector('.produto-img');

    // dados do botão
    const newPrice = button.dataset.price;
    const newImage = button.dataset.img;

    // altera preço e imagem
    price.textContent = `R$ ${newPrice}`;
    image.src = newImage;

  });

});

</script>
<script>

document.querySelectorAll('.card-prod').forEach(card => {

    const track = card.querySelector('.sizes-track');

    if(!track) return;

    const buttons = track.querySelectorAll('button');

    const image = card.querySelector('.produto-img');
    const price = card.querySelector('.price');

    const leftArrow = card.querySelector('.variant-arrow.left');
    const rightArrow = card.querySelector('.variant-arrow.right');

    let current = 0;

   function updateVariant(index){

    const btn = buttons[index];

    buttons.forEach(b => b.classList.remove('active'));

    btn.classList.add('active');

    image.src = btn.dataset.img;

    price.textContent = `R$ ${btn.dataset.price}`;
}

   

    updateVariant(current);

    rightArrow?.addEventListener('click', () => {

        current++;

        if(current >= buttons.length){
            current = 0;
        }

        updateVariant(current);
    });

    leftArrow?.addEventListener('click', () => {

        current--;

        if(current < 0){
            current = buttons.length - 1;
        }

        updateVariant(current);
    });

    buttons.forEach((button, index) => {

        button.addEventListener('click', () => {

            current = index;

            updateVariant(current);

        });

    });

});

</script>

<script>

const carouselPositions = {};

function moveCustomCarousel(id, direction){

    const track = document.getElementById(id);

    if (!track || !track.children.length) return;

    const card = track.children[0];

    const gap = 20;

    const move =
        card.offsetWidth + gap;

    if(!carouselPositions[id]){
        carouselPositions[id] = 0;
    }

    carouselPositions[id] += direction * move;

    const maxScroll =
        track.scrollWidth -
        track.parentElement.clientWidth;

    if(carouselPositions[id] < 0){
        carouselPositions[id] = 0;
    }

    if(carouselPositions[id] > maxScroll){
        carouselPositions[id] = maxScroll;
    }

    track.style.transform =
        `translateX(-${carouselPositions[id]}px)`;
}

</script>

<script>

let planosIndex = 0;

function movePlanos(direction){

    const track =
        document.getElementById('carouselPlanos');

    if (!track) return;

    const cards =
        track.querySelectorAll('.plano-card');

    if (!cards.length) return;

    const card =
        cards[0];

    const gap = 25;

    const cardWidth =
        card.offsetWidth + gap;

    let visibleCards = 3;

    if (window.innerWidth <= 900) {
        visibleCards = 1;
    }

    const totalPages =
        Math.ceil(cards.length / visibleCards);

    planosIndex += direction;

    if(planosIndex < 0){
        planosIndex = 0;
    }

    if(planosIndex >= totalPages){
        planosIndex = totalPages - 1;
    }

    const move =
        planosIndex *
        cardWidth *
        visibleCards;

    track.style.transform =
        `translateX(-${move}px)`;

}

</script>

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

<script>

const menu = document.getElementById("menuLateral");
const overlay = document.getElementById("overlay");

function abrirMenu(){

    menu.classList.add("ativo");
    overlay.classList.add("ativo");

}

function fecharMenu(){

    menu.classList.remove("ativo");
    overlay.classList.remove("ativo");

}

document.querySelector(".todos")?.addEventListener("click", abrirMenu);

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const btnCachorros = document.getElementById("btnCachorros");
    const btnGatos = document.getElementById("btnGatos");

    const megaMenuCachorros = document.getElementById("megaMenu");
    const megaMenuGatos = document.getElementById("megaMenuGatos");

    const fecharMega = document.getElementById("fecharMega");
    const fecharMegaGatos = document.getElementById("fecharMegaGatos");

    btnCachorros?.addEventListener("click", function (evento) {
        evento.preventDefault();
        evento.stopPropagation();

        megaMenuGatos?.classList.remove("ativo");
        btnGatos?.setAttribute("aria-expanded", "false");
        const abrir = !megaMenuCachorros?.classList.contains("ativo");
        megaMenuCachorros?.classList.toggle("ativo", abrir);
        btnCachorros?.setAttribute("aria-expanded", String(abrir));
        if (abrir) fecharMega?.focus();
    });

    btnGatos?.addEventListener("click", function (evento) {
        evento.preventDefault();
        evento.stopPropagation();

        megaMenuCachorros?.classList.remove("ativo");
        btnCachorros?.setAttribute("aria-expanded", "false");
        const abrir = !megaMenuGatos?.classList.contains("ativo");
        megaMenuGatos?.classList.toggle("ativo", abrir);
        btnGatos?.setAttribute("aria-expanded", String(abrir));
        if (abrir) fecharMegaGatos?.focus();
    });

    fecharMega?.addEventListener("click", function (evento) {
        evento.stopPropagation();
        megaMenuCachorros?.classList.remove("ativo");
        btnCachorros?.setAttribute("aria-expanded", "false");
        btnCachorros?.focus();
    });

    fecharMegaGatos?.addEventListener("click", function (evento) {
        evento.stopPropagation();
        megaMenuGatos?.classList.remove("ativo");
        btnGatos?.setAttribute("aria-expanded", "false");
        btnGatos?.focus();
    });

    megaMenuCachorros?.addEventListener("click", function (evento) {
        evento.stopPropagation();
    });

    megaMenuGatos?.addEventListener("click", function (evento) {
        evento.stopPropagation();
    });

    document.addEventListener("click", function () {
        megaMenuCachorros?.classList.remove("ativo");
        megaMenuGatos?.classList.remove("ativo");
        btnCachorros?.setAttribute("aria-expanded", "false");
        btnGatos?.setAttribute("aria-expanded", "false");
    });

    document.addEventListener("keydown", function (evento) {
        if (evento.key !== "Escape") return;
        const cachorroAberto = megaMenuCachorros?.classList.contains("ativo");
        const gatoAberto = megaMenuGatos?.classList.contains("ativo");
        if (!cachorroAberto && !gatoAberto) return;
        megaMenuCachorros?.classList.remove("ativo");
        megaMenuGatos?.classList.remove("ativo");
        btnCachorros?.setAttribute("aria-expanded", "false");
        btnGatos?.setAttribute("aria-expanded", "false");
        (gatoAberto ? btnGatos : btnCachorros)?.focus();
    });

});
</script>





<script>

/* =========================================================
   CARROSSEL PRODUTOS EM DESTAQUE DO MENU
========================================================= */

const menuCarrossel =
    document.getElementById("menuCarrosselDestaques");

const menuSetaEsquerda =
    document.getElementById("menuSetaEsquerda");

const menuSetaDireita =
    document.getElementById("menuSetaDireita");


function menuMoverCarrossel(direcao) {

    if (!menuCarrossel) {
        return;
    }

    const card =
        menuCarrossel.querySelector(".menu-card-produto");

    if (!card) {
        return;
    }


    /*
       Descobre automaticamente o tamanho
       exato de UM card + espaço entre eles.
    */

    const estilo =
        window.getComputedStyle(menuCarrossel);

    const gap =
        parseFloat(estilo.gap) || 18;

    const larguraCard =
        card.getBoundingClientRect().width;


    /*
       Move um produto por clique.
    */

    const movimento =
        larguraCard + gap;


    menuCarrossel.scrollBy({

        left:
            direcao * movimento,

        behavior:
            "smooth"

    });

}


if (menuSetaEsquerda) {

    menuSetaEsquerda.addEventListener(
        "click",
        function () {

            menuMoverCarrossel(-1);

        }
    );

}


if (menuSetaDireita) {

    menuSetaDireita.addEventListener(
        "click",
        function () {

            menuMoverCarrossel(1);

        }
    );

}


/* =========================================================
   TAMANHOS / VARIAÇÕES DOS PRODUTOS
========================================================= */

document
.querySelectorAll(".menu-card-produto")
.forEach(card => {

    const botoes = card.querySelectorAll(".menu-btn-tamanho");
    const imagem = card.querySelector(".menu-imagem-card-produto");
    const precoAtual = card.querySelector(".menu-preco-atual");
    const precoAntigo = card.querySelector(".menu-preco-antigo");
    const seloInicial = card.querySelector(".menu-selo-desconto");

    const formatarReal = valor => valor.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });

    const formatarDesconto = valor => {
        const numero = Number(valor || 0);
        if (!Number.isFinite(numero) || numero <= 0) return "";
        return Number.isInteger(numero)
            ? String(numero)
            : numero.toFixed(2).replace(/0+$/, "").replace(/\.$/, "").replace(".", ",");
    };

    function atualizarVisualPromocao(card, promoAtiva, descontoPercentual, precoOriginal) {
        const areaImagem = card.querySelector(".menu-area-imagem-produto");
        const areaPrecos = card.querySelector(".menu-precos-produto");
        if (!areaImagem || !areaPrecos) return;

        let selo = areaImagem.querySelector(".menu-selo-desconto");
        let precoAntigoAtual = areaPrecos.querySelector(".menu-preco-antigo");

        if (promoAtiva && descontoPercentual > 0 && precoOriginal > 0) {
            if (!selo) {
                selo = document.createElement("span");
                selo.className = "menu-selo-desconto";
                areaImagem.insertBefore(selo, areaImagem.firstChild);
            }
            selo.textContent = "-" + formatarDesconto(descontoPercentual) + "%";
            selo.style.display = "inline-flex";

            if (!precoAntigoAtual) {
                precoAntigoAtual = document.createElement("span");
                precoAntigoAtual.className = "menu-preco-antigo";
                const precoAtualEl = areaPrecos.querySelector(".menu-preco-atual");
                if (precoAtualEl) {
                    areaPrecos.insertBefore(precoAntigoAtual, precoAtualEl);
                } else {
                    areaPrecos.appendChild(precoAntigoAtual);
                }
            }
            precoAntigoAtual.textContent = formatarReal(precoOriginal);
            precoAntigoAtual.style.display = "inline";
        } else {
            if (selo) selo.style.display = "none";
            if (precoAntigoAtual) precoAntigoAtual.style.display = "none";
        }
    }

    function aplicarVariacao(botao) {
        const novoPreco = Number(botao.dataset.preco || 0);
        const novoPrecoAntigo = Number(botao.dataset.precoAntigo || novoPreco);
        const promocaoAtiva = botao.dataset.promocao === "1";
        const descontoPercentual = Number(botao.dataset.descontoPercentual || 0);

        if (imagem && botao.dataset.imagem) {
            imagem.src = botao.dataset.imagem;
        }

        card.dataset.variacaoId = botao.dataset.idVariacao || "";
        card.dataset.variacaoNome = botao.dataset.nomeVariacao || botao.textContent.trim();
        card.dataset.precoProduto = Number.isFinite(novoPreco) ? novoPreco.toFixed(2) : "0.00";
        card.dataset.precoOriginal = Number.isFinite(novoPrecoAntigo) ? novoPrecoAntigo.toFixed(2) : "0.00";
        card.dataset.descontoPercentual = promocaoAtiva ? String(descontoPercentual) : "0";
        card.dataset.promocao = promocaoAtiva ? "1" : "0";
        card.dataset.imagemProduto = botao.dataset.imagem || card.dataset.imagemProduto || "";

        if (precoAtual && Number.isFinite(novoPreco)) {
            precoAtual.textContent = formatarReal(novoPreco);
        }

        atualizarVisualPromocao(
            card,
            promocaoAtiva,
            descontoPercentual,
            novoPrecoAntigo
        );
    }

    // Garante que o primeiro tamanho já seja a variação real do card.
    if (botoes.length) {
        const primeira = botoes[0];
        card.dataset.variacaoId = primeira.dataset.idVariacao || "";
        card.dataset.variacaoNome = primeira.dataset.nomeVariacao || primeira.textContent.trim();
        card.dataset.precoProduto = primeira.dataset.preco || card.dataset.precoProduto || "0";
        card.dataset.precoOriginal = primeira.dataset.precoAntigo || card.dataset.precoOriginal || card.dataset.precoProduto || "0";
        card.dataset.descontoPercentual = primeira.dataset.descontoPercentual || "0";
        card.dataset.promocao = primeira.dataset.promocao || "0";
        card.dataset.imagemProduto = primeira.dataset.imagem || card.dataset.imagemProduto || "";
    }

    botoes.forEach(botao => {
        botao.addEventListener("click", function () {
            botoes.forEach(b => {
                b.classList.remove("ativo");
                b.setAttribute("aria-pressed", "false");
            });
            botao.classList.add("ativo");
            botao.setAttribute("aria-pressed", "true");
            aplicarVariacao(botao);
        });
    });

    // Normaliza o estado visual inicial usando os dados reais da primeira variação.
    if (botoes.length) {
        aplicarVariacao(botoes[0]);
    } else {
        atualizarVisualPromocao(card, false, 0, Number(card.dataset.precoOriginal || 0));
    }
});




/* =========================================================
   FAVORITOS
========================================================= */

async function menuAlternarFavorito(botao) {
    const idProduto = Number(botao.dataset.idProdutoFavorito || 0);
    if (!idProduto) return;

    try {
        const dados = new URLSearchParams();
        dados.append("id_produto", String(idProduto));

        const resposta = await fetch("favorito_toggle.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: dados.toString()
        });

        const resultado = await resposta.json();

        if (resposta.status === 401 && resultado.login) {
            const destino = window.location.pathname.split('/').pop() + window.location.search;
            window.location.href = "form.php?redirect=" + encodeURIComponent(destino);
            return;
        }

        if (!resultado.ok) {
            return;
        }

        botao.classList.toggle("favoritado", !!resultado.favoritado);
        botao.setAttribute("aria-pressed", String(!!resultado.favoritado));
        const icone = botao.querySelector("i");
        if (icone) {
            icone.className = resultado.favoritado
                ? "bi bi-heart-fill"
                : "bi bi-heart";
        }

    } catch (erro) {
        console.error("Erro ao atualizar favorito:", erro);
    }
}

document.querySelectorAll(".menu-btn-favorito").forEach(function (botao) {
    botao.addEventListener("click", function (evento) {
        evento.preventDefault();
        evento.stopPropagation();
        menuAlternarFavorito(botao);
    });
});


/* =========================================================
   CARRINHO - MENU
========================================================= */

const CHAVE_CARRINHO =
    "garras_patas_carrinho_v2";


function menuAdicionarCarrinho(botao) {

    const card = botao.closest(".menu-card-produto");

    if (!card) {
        return;
    }

    const idProduto = Number(card.dataset.idProduto || 0);
    if (!idProduto) {
        return;
    }

    const idVariacao = card.dataset.variacaoId
        ? Number(card.dataset.variacaoId)
        : null;

    const item = {
        idProduto: idProduto,
        idVariacao: idVariacao,
        nome: card.dataset.nomeProduto || "Produto",
        variacao: card.dataset.variacaoNome || "",
        marca: card.dataset.marcaProduto || "",
        imagem: card.dataset.imagemProduto || "",
        precoUnitario: Number(card.dataset.precoProduto || 0),
        precoOriginal: Number(card.dataset.precoOriginal || card.dataset.precoProduto || 0),
        descontoPercentual: Number(card.dataset.descontoPercentual || 0),
        quantidade: 1
    };

    /* Usa a rotina compartilhada quando disponível.
       Caso o arquivo externo ainda não tenha carregado, grava diretamente
       no mesmo localStorage para o botão nunca ficar sem ação. */
    if (typeof window.GPAdicionarAoCarrinho === "function") {
        window.GPAdicionarAoCarrinho(item);
    } else {
        const chave = String(idProduto) + ":" + (idVariacao === null ? "base" : String(idVariacao));
        let carrinho = [];

        try {
            carrinho = JSON.parse(localStorage.getItem(CHAVE_CARRINHO) || "[]");
        } catch (erro) {
            carrinho = [];
        }

        if (!Array.isArray(carrinho)) {
            carrinho = [];
        }

        const existente = carrinho.find(function (registro) {
            return registro && registro.chave === chave;
        });

        if (existente) {
            existente.quantidade = Math.min(99, Math.max(1, Number(existente.quantidade || 0)) + 1);
            existente.precoUnitario = Number(item.precoUnitario || 0);
            existente.precoOriginal = Number(item.precoOriginal || existente.precoOriginal || existente.precoUnitario || 0);
            existente.descontoPercentual = Number(item.descontoPercentual || existente.descontoPercentual || 0);
            existente.imagem = item.imagem || existente.imagem || "";
            existente.nome = item.nome || existente.nome || "Produto";
            existente.variacao = item.variacao || existente.variacao || "";
        } else {
            carrinho.push({
                ...item,
                chave: chave,
                quantidade: 1,
                precoOriginal: Number(item.precoOriginal ?? item.precoUnitario ?? 0)
            });
        }

        localStorage.setItem(CHAVE_CARRINHO, JSON.stringify(carrinho));
        window.dispatchEvent(new CustomEvent("garras:carrinho-atualizado", {
            detail: carrinho
        }));
    }

    const textoOriginal = botao.innerHTML;
    const ehBotaoRedondo = botao.classList.contains("menu-btn-carrinho-card");

    botao.classList.add("adicionado");
    botao.innerHTML = ehBotaoRedondo
        ? '<i class="bi bi-check-lg"></i>'
        : '<i class="bi bi-check-lg"></i> Adicionado';

    setTimeout(function () {
        botao.classList.remove("adicionado");
        botao.innerHTML = ehBotaoRedondo
            ? '<i class="bi bi-cart-plus"></i>'
            : '<i class="bi bi-cart3"></i> Adicionar';
    }, 1400);
}

/* BOTÕES DE ADICIONAR */
document.querySelectorAll(".menu-comprar-produto, .menu-btn-carrinho-card").forEach(function (botao) {
    botao.addEventListener("click", function () {
        menuAdicionarCarrinho(botao);
    });
});
</script>


<script>

/* =========================================================
   COBERTURA DOS PLANOS
   Os tópicos vêm do campo `coberturas` do banco de dados.
   Um tópico por linha. A estrutura visual da janela é mantida.
   ========================================================= */

function abrirCobertura(botao) {

    const card = botao.closest(".plano-card");

    if (!card) return;


    /* evita abrir duas vezes */
    if (card.querySelector(".cobertura-plano")) {
        return;
    }


    const nome =
        botao.dataset.planoNome || "Plano";


    const preco =
        botao.dataset.planoPreco || "";


    const coberturas =
        (botao.dataset.planoCoberturas || "").trim();


    let itens = coberturas
        .split(/\r?\n|;/)
        .map(item => item.trim())
        .filter(Boolean);


    if (!itens.length) {

        itens = [
            "Consulte a equipe para conhecer a cobertura deste plano."
        ];

    }


    const janela =
        document.createElement("div");


    janela.classList.add(
        "cobertura-plano"
    );


    const lista =
        itens
        .map(item => `
            <li>
                <i class="bi bi-check-circle-fill"></i>
                <span>${escaparHtmlPlano(item)}</span>
            </li>
        `)
        .join("");


    janela.innerHTML = `

        <button
            class="fechar-cobertura"
            type="button"
            onclick="fecharCobertura(this)"
            aria-label="Fechar"
        >
            ×
        </button>

        <span class="cobertura-mini">
            Garras & Patas
        </span>

        <h3>
            ${escaparHtmlPlano(nome)}
        </h3>

        <div class="cobertura-preco">
            ${escaparHtmlPlano(preco)}
        </div>

        <div class="cobertura-linha"></div>

        <h4>
            O que este plano cobre:
        </h4>

        <ul class="lista-cobertura">
            ${lista}
        </ul>

        <div class="observacao-cobertura">
            A cobertura pode variar conforme a região
            e a disponibilidade da rede veterinária.
        </div>

    `;


    card.appendChild(janela);

}


function fecharCobertura(botao) {

    const janela =
        botao.closest(".cobertura-plano");


    if (janela) {
        janela.remove();
    }

}


function escaparHtmlPlano(valor) {

    const div =
        document.createElement("div");


    div.textContent =
        String(valor ?? "");


    return div.innerHTML;

}
</script>


<script src="../js/carrinho.js?v=11"></script>
<script>
/* =========================================================
   CONTRATAR PLANO — adiciona ao carrinho e redireciona
========================================================= */
(function () {
    function configurarBotoesPlanos() {
        document.querySelectorAll(".btn-contratar-plano").forEach(function (botao) {
            if (botao.dataset.garrasPlanoConfigurado === "1") {
                return;
            }

            botao.dataset.garrasPlanoConfigurado = "1";

            botao.addEventListener("click", function (evento) {
                evento.preventDefault();
                evento.stopPropagation();

                const idPlano = Number(botao.dataset.planoId || 0);
                const nomePlano = String(botao.dataset.planoNome || "Plano de saúde");
                const precoPlano = Number(botao.dataset.planoPreco || 0);

                if (!idPlano || !precoPlano) {
                    console.error("Dados do plano inválidos.");
                    return;
                }

                const itemPlano = {
                    tipoItem: "plano",
                    idPlano: idPlano,
                    idProduto: 0,
                    idVariacao: null,
                    nome: nomePlano,
                    variacao: "Mensal",
                    marca: "Garras & Patas",
                    categoria: "plano de saúde",
                    imagem: "../imagens/logo.png",
                    precoOriginal: precoPlano,
                    precoUnitario: precoPlano,
                    quantidade: 1
                };

                // Usa o carrinho compartilhado quando a versão carregada já suporta planos.
                if (typeof window.GPAdicionarAoCarrinho === "function") {
                    window.GPAdicionarAoCarrinho(itemPlano);
                }

                // Verificação/fallback independente da versão antiga do carrinho.js.
                // Isso evita que um script antigo descarte o plano por idProduto = 0.
                const chave = "plano:" + String(idPlano);
                let carrinho = [];
                try {
                    const bruto = localStorage.getItem("garras_patas_carrinho_v2");
                    carrinho = bruto ? JSON.parse(bruto) : [];
                } catch (erro) {
                    carrinho = [];
                }
                if (!Array.isArray(carrinho)) carrinho = [];

                const existente = carrinho.find(function (item) {
                    return item && (
                        item.chave === chave ||
                        (String(item.tipoItem || "").toLowerCase() === "plano" && Number(item.idPlano || 0) === idPlano)
                    );
                });

                if (existente) {
                    existente.chave = chave;
                    existente.tipoItem = "plano";
                    existente.idPlano = idPlano;
                    existente.idProduto = 0;
                    existente.idVariacao = null;
                    existente.quantidade = 1;
                    existente.nome = nomePlano;
                    existente.variacao = "Mensal";
                    existente.precoOriginal = precoPlano;
                    existente.precoUnitario = precoPlano;
                } else {
                    carrinho.push({ ...itemPlano, chave: chave });
                }

                localStorage.setItem("garras_patas_carrinho_v2", JSON.stringify(carrinho));
                window.dispatchEvent(new CustomEvent("garras:carrinho-atualizado", { detail: carrinho }));

                // O localStorage é atualizado de forma síncrona antes desta navegação.
                window.location.href = "carrinho.php";
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", configurarBotoesPlanos);
    } else {
        configurarBotoesPlanos();
    }
})();
</script>

<script>
(function () {
    function lerQuantidadeCarrinho() {
        try {
            const chaves = [
                "garras_patas_carrinho_v2",
                "garras_patas_carrinho"
            ];

            for (const chave of chaves) {
                const bruto = localStorage.getItem(chave);
                if (!bruto) continue;

                const itens = JSON.parse(bruto);
                if (!Array.isArray(itens)) continue;

                return itens.reduce(function (total, item) {
                    const quantidade = Math.max(0, Number(item?.quantidade || 0));
                    return total + quantidade;
                }, 0);
            }
        } catch (erro) {
            console.warn("Não foi possível ler a quantidade do carrinho.", erro);
        }

        return 0;
    }

    function garantirBolinhaCarrinho() {
        const links = document.querySelectorAll(".garras-cart-link, .cart-link");

        links.forEach(function (link) {
            let badge = link.querySelector("[data-garras-cart-badge]");

            if (!badge) {
                badge = document.createElement("span");
                badge.className = "garras-cart-badge";
                badge.setAttribute("data-garras-cart-badge", "");
                badge.setAttribute("aria-label", "Quantidade de produtos no carrinho");
                link.appendChild(badge);
            }
        });
    }

    function atualizarBolinhaCarrinho() {
        garantirBolinhaCarrinho();
        const quantidade = lerQuantidadeCarrinho();

        document.querySelectorAll("[data-garras-cart-badge]").forEach(function (badge) {
            badge.textContent = quantidade > 99 ? "99+" : String(quantidade);
            badge.style.display = "inline-flex";
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", atualizarBolinhaCarrinho);
    } else {
        atualizarBolinhaCarrinho();
    }

    window.addEventListener("storage", atualizarBolinhaCarrinho);
    window.addEventListener("garras:carrinho-atualizado", atualizarBolinhaCarrinho);
})();
</script>
<style>
.search-box i.bi-search {
    pointer-events: auto !important;
    cursor: pointer !important;
    z-index: 5;
}
.search-box button {
    border: 0;
    background: transparent;
}


/* Ajustes adicionais para tablets e celulares estreitos. */
@media (max-width: 1200px) and (min-width: 901px) {
    .topo {
        padding-left: 18px !important;
        padding-right: 18px !important;
    }
    .logo img {
        margin-left: 12px !important;
        transform: scale(1.15) !important;
    }
    .search-box {
        max-width: min(56vw, 560px) !important;
        padding-left: 10px !important;
    }
    .icons {
        right: 0 !important;
        gap: 14px !important;
    }
}

@media (max-width: 760px) {
    html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
    .mega-menu { max-width: 100vw !important; }
    .cobertura-plano h3 { font-size: clamp(27px, 8vw, 34px) !important; }
    .cobertura-plano { padding: 22px 16px 18px !important; }
    .fechar-cobertura { top: 11px !important; right: 11px !important; }
    .lista-cobertura { gap: 8px !important; }
    .observacao-cobertura { margin-top: 14px !important; }
}

@media (max-width: 420px) {
    .menu ul { gap: 5px !important; }
    .menu .categoria { padding-left: 9px !important; padding-right: 9px !important; }
    .carousel::before, .carousel::after { width: 28px !important; }
    .menu-card-produto { flex-basis: 90% !important; max-width: 90% !important; }
    .custom-card { flex-basis: 92% !important; max-width: 92% !important; }
    .plano-card h3 { font-size: 37px !important; }
}

</style>
<script src="../js/busca-global.js?v=3"></script>

<script src="../js/acessibilidade.js" defer></script>
</body>

</html>