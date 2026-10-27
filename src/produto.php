<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

$usuarioLogadoId = sessaoUsuarioId();

function produtoH($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function produtoPreco($valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}

function produtoCalcularPromocaoVariacao(array $variacao): array
{
    $precoOriginal = (float)($variacao['preco'] ?? 0);
    $promocao = (int)($variacao['promocao'] ?? 0) === 1;
    $desconto = $promocao
        ? min(99.99, max(0, (float)($variacao['desconto_percentual'] ?? 0)))
        : 0.0;

    $precoAtual = ($promocao && $desconto > 0)
        ? round($precoOriginal * (1 - ($desconto / 100)), 2)
        : $precoOriginal;

    return [
        'precoOriginal' => round($precoOriginal, 2),
        'precoAtual' => $precoAtual,
        'promocao' => $promocao && $desconto > 0,
        'desconto' => $desconto,
    ];
}

function produtoPetLabel(string $tipoPet): string
{
    return match ($tipoPet) {
        'cachorro' => '🐶',
        'gato' => '🐱',
        default => '🐶 🐱',
    };
}

function produtoBuscarTodos(mysqli $conexao, string $sql, string $tipos = '', array $valores = []): array
{
    $stmt = $conexao->prepare($sql);

    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$valores);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$idProduto = max(0, (int)($_GET['id'] ?? 0));

if ($idProduto <= 0) {
    header('Location: aba_produto.php');
    exit;
}

$stmtProduto = $conexao->prepare(
    "SELECT
        p.*,
        m.nome AS marca_nome,
        c.nome AS categoria_nome,
        c.slug AS categoria_slug
     FROM produtos_destaque p
     LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
     LEFT JOIN categorias_produtos c
        ON c.id_categoria_produto = p.id_categoria_produto
     WHERE p.id_produto = ?
       AND p.ativo = 1
     LIMIT 1"
);

$stmtProduto->bind_param('i', $idProduto);
$stmtProduto->execute();

$produto = $stmtProduto->get_result()->fetch_assoc();

if (!$produto) {
    header('Location: aba_produto.php');
    exit;
}

$produtoFavoritado = false;
if ($usuarioLogadoId > 0) {
    try {
        $favProdutoStmt = $conexao->prepare('SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_produto = ? LIMIT 1');
        $favProdutoStmt->bind_param('ii', $usuarioLogadoId, $idProduto);
        $favProdutoStmt->execute();
        $produtoFavoritado = (bool)$favProdutoStmt->get_result()->fetch_assoc();
    } catch (Throwable $e) {
        $produtoFavoritado = false;
    }
}

$variacoes = produtoBuscarTodos(
    $conexao,
    "SELECT id_variacao, nome, preco, imagem, promocao, desconto_percentual
     FROM variacoes_produto
     WHERE id_produto = ?
     ORDER BY id_variacao",
    'i',
    [$idProduto]
);

if ($variacoes) {
    $promocaoInicial = produtoCalcularPromocaoVariacao($variacoes[0]);
    $emPromocao = $promocaoInicial['promocao'];
    $desconto = $promocaoInicial['desconto'];
    $precoOriginalInicial = $promocaoInicial['precoOriginal'];
    $precoAtualInicial = $promocaoInicial['precoAtual'];
} else {
    $desconto = (int)($produto['promocao'] ?? 0) === 1
        ? min(99.99, max(0, (float)($produto['desconto_percentual'] ?? 0)))
        : 0.0;
    $emPromocao = $desconto > 0;
    $precoOriginalInicial = round((float)$produto['preco'], 2);
    $precoAtualInicial = $emPromocao
        ? round($precoOriginalInicial * (1 - ($desconto / 100)), 2)
        : $precoOriginalInicial;
}

$imagemPrincipal = trim((string)($produto['imagem'] ?? ''));

$imagemInicial = $variacoes
    ? (trim((string)($variacoes[0]['imagem'] ?? '')) ?: $imagemPrincipal)
    : $imagemPrincipal;

$descricaoProduto = trim((string)($produto['descricao'] ?? ''));

if ($descricaoProduto === '') {
    $descricaoProduto = 'Descrição ainda não cadastrada.';
}

$categoriaNome = trim((string)($produto['categoria_nome'] ?? ''));

if ($categoriaNome === '') {
    $categoriaNome = trim((string)($produto['categoria'] ?? 'Produto'));
}

$marcaNome = trim((string)($produto['marca_nome'] ?? ''));
$tipoPet = (string)($produto['tipo_pet'] ?? 'ambos');
$iconePet = produtoPetLabel($tipoPet);

$idCategoria = (int)($produto['id_categoria_produto'] ?? 0);

$similares = [];

if ($idCategoria > 0) {
    $similares = produtoBuscarTodos(
        $conexao,
        "SELECT
            p.id_produto,
            p.nome,
            p.imagem,
            p.preco,
            p.promocao,
            p.desconto_percentual,
            p.tipo_pet,
            m.nome AS marca_nome,
            c.nome AS categoria_nome
         FROM produtos_destaque p
         LEFT JOIN marcas m
            ON m.id_marca = p.id_marca
         LEFT JOIN categorias_produtos c
            ON c.id_categoria_produto = p.id_categoria_produto
         WHERE p.ativo = 1
           AND p.id_produto <> ?
           AND p.id_categoria_produto = ?
           AND (
                p.tipo_pet = ?
                OR p.tipo_pet = 'ambos'
                OR ? = 'ambos'
           )
         ORDER BY p.destaque DESC, p.promocao DESC, p.ordem, p.id_produto
         LIMIT 10",
        'iiss',
        [$idProduto, $idCategoria, $tipoPet, $tipoPet]
    );
}

if (count($similares) < 6) {
    $fallback = produtoBuscarTodos(
        $conexao,
        "SELECT
            p.id_produto,
            p.nome,
            p.imagem,
            p.preco,
            p.promocao,
            p.desconto_percentual,
            p.tipo_pet,
            m.nome AS marca_nome,
            c.nome AS categoria_nome
         FROM produtos_destaque p
         LEFT JOIN marcas m
            ON m.id_marca = p.id_marca
         LEFT JOIN categorias_produtos c
            ON c.id_categoria_produto = p.id_categoria_produto
         WHERE p.ativo = 1
           AND p.id_produto <> ?
           AND (
                p.tipo_pet = ?
                OR p.tipo_pet = 'ambos'
                OR ? = 'ambos'
           )
         ORDER BY
            (p.id_categoria_produto = ?) DESC,
            p.destaque DESC,
            p.promocao DESC,
            p.ordem,
            p.id_produto
         LIMIT 14",
        'issi',
        [$idProduto, $tipoPet, $tipoPet, $idCategoria]
    );

    $idsJaUsados = array_map(
        static fn(array $item): int => (int)$item['id_produto'],
        $similares
    );

    foreach ($fallback as $item) {
        $idSimilar = (int)$item['id_produto'];

        if (in_array($idSimilar, $idsJaUsados, true)) {
            continue;
        }

        $similares[] = $item;
        $idsJaUsados[] = $idSimilar;

        if (count($similares) >= 10) {
            break;
        }
    }
}

$produtoJs = [
    'id' => (int)$produto['id_produto'],
    'nome' => (string)$produto['nome'],
    'imagem' => $imagemInicial,
    'marca' => $marcaNome,
    'categoria' => $categoriaNome,
    'preco' => round($precoAtualInicial, 2),
];

$produtoJsJson = json_encode(
    $produtoJs,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= produtoH($produto['nome']) ?> - Garras &amp; Patas</title>
  <link href="../css/style-produto.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
  <style>
.carrinho-cabecalho{position:relative;display:inline-flex;align-items:center;}
.garras-cart-badge{position:absolute;top:-7px;right:-9px;min-width:17px;height:17px;padding:0 4px;border-radius:999px;background:#e74c3c;color:#fff;font:700 10px/17px Arial,sans-serif;text-align:center;box-sizing:border-box;display:none;}
.btn.favorito.favoritado{color:#e74c3c;}

    .produto-marca {
        margin-top: 6px;
        color: #666;
        font-size: 14px;
    }

    .preco-antigo-produto {
        display: block;
        margin-bottom: 2px;
        color: #888;
        font-size: 15px;
        text-decoration: line-through;
    }

    .produto-desconto {
        display: inline-block;
        margin-left: 8px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #e7f7f7;
        color: #168b8b;
        font-size: 12px;
        font-weight: 700;
        vertical-align: middle;
    }

    .mensagem-produto {
        margin-top: 12px;
        min-height: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .mensagem-produto.sucesso {
        color: #168b55;
    }

    .btn.favorito.favoritado {
        font-weight: 700;
    }

    .produto-similares {
        width: min(1120px, calc(100% - 40px));
        margin: 26px auto 50px;
    }

    .produto-similares-cabecalho {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 15px;
    }

    .produto-similares-cabecalho h2 {
        margin: 0;
        font-size: 22px;
    }

    .produto-similares-cabecalho p {
        margin: 4px 0 0;
        color: #777;
        font-size: 14px;
    }

    .produto-similares-area {
        position: relative;
    }

    .produto-similares-viewport {
        overflow: hidden;
    }

    .produto-similares-track {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none;
        padding: 4px 2px 10px;
    }

    .produto-similares-track::-webkit-scrollbar {
        display: none;
    }

    .produto-similar-card {
        flex: 0 0 190px;
        min-width: 190px;
        border: 1px solid #ececec;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .produto-similar-card a {
        color: inherit;
        text-decoration: none;
    }

    .produto-similar-imagem {
        position: relative;
        width: 100%;
        height: 145px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .produto-similar-imagem img {
        width: 100%;
        height: 100%;
        padding: 12px;
        object-fit: contain;
    }

    .produto-similar-selo {
        position: absolute;
        top: 9px;
        left: 9px;
        z-index: 2;
        padding: 4px 7px;
        border-radius: 999px;
        background: #168f8f;
        color: white;
        font-size: 11px;
        font-weight: 700;
    }

    .produto-similar-info {
        padding: 12px;
    }

    .produto-similar-marca {
        display: block;
        min-height: 17px;
        color: #777;
        font-size: 12px;
    }

    .produto-similar-nome {
        display: -webkit-box;
        min-height: 39px;
        margin: 5px 0 9px;
        overflow: hidden;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
    }

    .produto-similar-preco-antigo {
        display: block;
        min-height: 17px;
        color: #999;
        font-size: 11px;
        text-decoration: line-through;
    }

    .produto-similar-preco {
        display: block;
        color: #168f8f;
        font-size: 17px;
        font-weight: 800;
    }

    .produto-similar-seta {
        position: absolute;
        top: 50%;
        z-index: 5;
        width: 34px;
        height: 34px;
        border: 1px solid #ddd;
        border-radius: 50%;
        background: #fff;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .produto-similar-seta.esquerda {
        left: -17px;
    }

    .produto-similar-seta.direita {
        right: -17px;
    }

    .produto-similares-vazio {
        padding: 20px;
        border: 1px solid #eee;
        border-radius: 12px;
        color: #777;
        text-align: center;
    }

    @media (max-width: 700px) {
        .produto-similares {
            width: min(100% - 24px, 1120px);
        }

        .produto-similar-card {
            flex-basis: 160px;
            min-width: 160px;
        }

        .produto-similar-seta {
            display: none;
        }
    }
  </style>
    <link rel="stylesheet" href="../css/footer.css?v=2">

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

<style id="garras-produto-header-responsivo">
/* Correção do cabeçalho da página de produto */
body.pagina-produto .topo {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 24px;
  min-height: 78px;
  padding-left: clamp(18px, 4vw, 46px);
  padding-right: clamp(18px, 4vw, 46px);
  overflow: visible;
}
body.pagina-produto .topo .logo {
  flex: 0 0 auto;
  width: auto;
  height: auto;
  margin: 0;
}
body.pagina-produto .topo .logo img {
  width: auto;
  max-width: 145px;
  max-height: 62px;
  height: auto;
  margin: 0 !important;
  transform: none !important;
  object-fit: contain;
}
body.pagina-produto .topo .espaco-topo {
  flex: 1 1 auto;
  min-width: 12px;
}
body.pagina-produto .topo .icons {
  position: static !important;
  right: auto !important;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 18px;
  flex: 0 1 auto;
  min-width: 0;
}
body.pagina-produto .topo .user { min-width: 0; }
body.pagina-produto .topo .user a {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  white-space: nowrap;
  text-decoration: none;
}
body.pagina-produto .topo .user span {
  max-width: min(32vw, 240px);
  overflow: hidden;
  text-overflow: ellipsis;
}
body.pagina-produto .produto-container {
  width: min(1120px, calc(100% - 40px));
  max-width: 100%;
  margin-left: auto;
  margin-right: auto;
}
@media (max-width: 900px) {
  body.pagina-produto .topo { gap: 14px; }
  body.pagina-produto .topo .icons { gap: 12px; }
  body.pagina-produto .topo .user span { max-width: 180px; }
}
@media (max-width: 700px) {
  body.pagina-produto .topo {
    min-height: 68px;
    padding: 9px 14px;
    gap: 8px;
  }
  body.pagina-produto .topo .logo img { max-width: 112px; max-height: 52px; }
  body.pagina-produto .topo .icons { gap: 8px; }
  body.pagina-produto .topo .user span { display: none; }
  body.pagina-produto .topo .user i { font-size: 24px; }
  body.pagina-produto .produto-container { width: min(100% - 24px, 1120px); }
}
@media (max-width: 520px) {
  body.pagina-produto .topo { padding-left: 10px; padding-right: 10px; }
  body.pagina-produto .topo .logo img { max-width: 100px; }
  body.pagina-produto .topo .user i { font-size: 22px; }
}
</style>

    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>

<body class="pagina-produto">
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
                <?php if ($usuarioLogadoId > 0): ?>
                    <a href="area-cliente.php">
                        <i class="bi bi-person-check"></i>
                        <span>Olá, <?= produtoH(sessaoPrimeiroNome()) ?></span>
                    </a>
                <?php else: ?>
                    <a href="form.php">
                        <i class="bi bi-person"></i>
                        <span>Entre ou cadastre-se</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="user">
                <a href="carrinho.php" class="carrinho-cabecalho">
                    <i class="bi bi-cart"></i>
                    <span>Acessar meu carrinho</span>
                    <span class="garras-cart-badge" data-garras-cart-badge aria-live="polite">0</span>
                </a>
            </div>
        </div>

    </div>

    <main id="conteudoPrincipal" class="produto-main" tabindex="-1" aria-label="Detalhes do produto">
    <div class="produto-container">

        <a href="javascript:history.back()" class="voltar" aria-label="Voltar">
            <i class="bi bi-x-lg"></i>
        </a>

        <div class="produto-imagem">
            <img
                id="fotoProduto"
                src="<?= produtoH($imagemInicial) ?>"
                alt="<?= produtoH($produto['nome']) ?>"
            >
        </div>

        <div class="produto-info">

            <div class="categoria">
                <?= produtoH($iconePet) ?>
                <?= produtoH($categoriaNome) ?>
            </div>

            <h1 class="produto-titulo">
                <?= produtoH($produto['nome']) ?>
            </h1>

            <?php if ($marcaNome !== ''): ?>
                <div class="produto-marca">
                    Marca: <?= produtoH($marcaNome) ?>
                </div>
            <?php endif; ?>

            <div class="avaliacao">
                Sem avaliações ainda
            </div>

            <p class="descricao">
                <?= nl2br(produtoH($descricaoProduto)) ?>
            </p>

            <?php if ($emPromocao): ?>
                <span
                    class="preco-antigo-produto"
                    id="precoAntigoProduto"
                >
                    R$ <?= produtoPreco($precoOriginalInicial) ?>
                </span>
            <?php else: ?>
                <span
                    class="preco-antigo-produto"
                    id="precoAntigoProduto"
                    style="display:none;"
                ></span>
            <?php endif; ?>

            <div class="preco" id="precoProduto">
                <span id="precoProdutoValor">R$ <?= produtoPreco($precoAtualInicial) ?></span>

                <span
                    class="produto-desconto"
                    id="produtoDesconto"
                    style="<?= $emPromocao ? '' : 'display:none;' ?>"
                >
                    -<?= produtoH(rtrim(rtrim(number_format($desconto, 2, ',', '.'), '0'), ',')) ?>%
                </span>
            </div>

            <?php if ($variacoes): ?>
                <div class="tamanhos">
                    <label><strong>Tamanho:</strong></label>

                    <?php foreach ($variacoes as $i => $variacao):
                        $promoVariacao = produtoCalcularPromocaoVariacao($variacao);
                        $precoOriginalVariacao = $promoVariacao['precoOriginal'];
                        $precoAtualVariacao = $promoVariacao['precoAtual'];

                        $imagemVariacao =
                            trim((string)($variacao['imagem'] ?? ''))
                            ?: $imagemPrincipal;
                    ?>
                        <button
                            class="btn-tamanho<?= $i === 0 ? ' ativo' : '' ?>"
                            type="button"
                            data-id-variacao="<?= (int)$variacao['id_variacao'] ?>"
                            data-nome-variacao="<?= produtoH($variacao['nome']) ?>"
                            data-preco="<?= produtoH(number_format($precoAtualVariacao, 2, '.', '')) ?>"
                            data-preco-original="<?= produtoH(number_format($precoOriginalVariacao, 2, '.', '')) ?>"
                            data-promocao="<?= $promoVariacao['promocao'] ? '1' : '0' ?>"
                            data-desconto="<?= produtoH(number_format($promoVariacao['desconto'], 2, '.', '')) ?>"
                            data-imagem="<?= produtoH($imagemVariacao) ?>"
                            onclick="selecionarVariacao(this)"
                        >
                            <?= produtoH($variacao['nome']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="quantidade-area">
                <label for="quantidade">
                    Quantidade:
                </label>

                <input
                    type="number"
                    id="quantidade"
                    class="quantidade"
                    value="1"
                    min="1"
                    max="99"
                    onchange="atualizarPreco()"
                    oninput="atualizarPreco()"
                >
            </div>

            <div class="botoes">

                <button
                    class="btn carrinho"
                    type="button"
                    id="adicionarCarrinhoProduto"
                    onclick="adicionarProdutoAoCarrinho()"
                >
                    🛒 Adicionar ao Carrinho
                </button>

                <button
                    class="btn favorito<?= $produtoFavoritado ? ' favoritado' : '' ?>"
                    type="button"
                    id="favoritarProduto"
                    aria-pressed="<?= $produtoFavoritado ? 'true' : 'false' ?>"
                    onclick="alternarFavorito()"
                >
                    <?= $produtoFavoritado ? '♥ Favoritado' : '❤ Favoritar' ?>
                </button>

            </div>

            <div
                class="mensagem-produto"
                id="mensagemProduto"
                role="status" aria-live="polite" aria-atomic="true"
            ></div>

            <div class="beneficios">

                <div class="beneficio">
                    <strong>🚚 Frete Grátis</strong>
                    <p>Compras acima de R$ 99</p>
                </div>

                <div class="beneficio">
                    <strong>🐾 Produto Premium</strong>
                    <p>Alta qualidade</p>
                </div>

                <div class="beneficio">
                    <strong>📦 Entrega Rápida</strong>
                    <p>Receba em até 3 dias úteis</p>
                </div>

                <div class="beneficio">
                    <strong>💳 Parcelamento</strong>
                    <p>Até 10x sem juros</p>
                </div>

            </div>

        </div>

    </div>

    <section class="produto-similares">

        <div class="produto-similares-cabecalho">
            <div>
                <h2>Você também pode gostar</h2>
                <p>Produtos da mesma categoria ou para o mesmo tipo de pet.</p>
            </div>
        </div>

        <?php if ($similares): ?>

            <div class="produto-similares-area">

                <button
                    class="produto-similar-seta esquerda"
                    type="button"
                    aria-label="Produtos anteriores"
                    onclick="moverSimilares(-1)"
                >
                    <i class="bi bi-chevron-left"></i>
                </button>

                <div class="produto-similares-viewport">

                    <div
                        class="produto-similares-track"
                        id="produtoSimilaresTrack"
                    >

                        <?php foreach ($similares as $similar):
                            $similarPromocao =
                                (int)($similar['promocao'] ?? 0) === 1
                                && (float)($similar['desconto_percentual'] ?? 0) > 0;

                            $similarDesconto = $similarPromocao
                                ? min(99.99, max(0, (float)$similar['desconto_percentual']))
                                : 0;

                            $similarPrecoOriginal = (float)$similar['preco'];

                            $similarPrecoAtual = $similarPromocao
                                ? $similarPrecoOriginal * (1 - ($similarDesconto / 100))
                                : $similarPrecoOriginal;
                        ?>

                            <article class="produto-similar-card">

                                <a href="produto.php?id=<?= (int)$similar['id_produto'] ?>">

                                    <div class="produto-similar-imagem">

                                        <?php if ($similarPromocao): ?>
                                            <span class="produto-similar-selo">
                                                -<?= produtoH(rtrim(rtrim(number_format($similarDesconto, 2, ',', '.'), '0'), ',')) ?>%
                                            </span>
                                        <?php endif; ?>

                                        <img
                                            src="<?= produtoH($similar['imagem']) ?>"
                                            alt="<?= produtoH($similar['nome']) ?>"
                                        >

                                    </div>

                                    <div class="produto-similar-info">

                                        <span class="produto-similar-marca">
                                            <?= produtoH($similar['marca_nome'] ?? '') ?>
                                        </span>

                                        <span class="produto-similar-nome">
                                            <?= produtoH($similar['nome']) ?>
                                        </span>

                                        <?php if ($similarPromocao): ?>
                                            <span class="produto-similar-preco-antigo">
                                                R$ <?= produtoPreco($similarPrecoOriginal) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="produto-similar-preco-antigo"></span>
                                        <?php endif; ?>

                                        <strong class="produto-similar-preco">
                                            R$ <?= produtoPreco($similarPrecoAtual) ?>
                                        </strong>

                                    </div>

                                </a>

                            </article>

                        <?php endforeach; ?>

                    </div>

                </div>

                <button
                    class="produto-similar-seta direita"
                    type="button"
                    aria-label="Próximos produtos"
                    onclick="moverSimilares(1)"
                >
                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>

        <?php else: ?>

            <div class="produto-similares-vazio">
                Ainda não há produtos similares cadastrados.
            </div>

        <?php endif; ?>

    </section>

    </main>

<footer class="footer" aria-label="Rodapé do site">

    <div class="footer-top" aria-hidden="true"></div>

    <div class="footer-container">

        <section class="footer-col empresa" aria-labelledby="footer-empresa-titulo">
            <h2 id="footer-empresa-titulo">Garras <span>&amp; Patas</span></h2>

            <p>
                Tudo para o seu pet em um só lugar. Encontre
                rações, brinquedos, acessórios e muito mais
                para cães e gatos.
            </p>

            <div class="social" aria-label="Redes sociais">
                <a href="#" aria-label="Instagram" title="Instagram">
                    <i class="bi bi-instagram" aria-hidden="true"></i>
                </a>
                <a href="#" aria-label="Facebook" title="Facebook">
                    <i class="bi bi-facebook" aria-hidden="true"></i>
                </a>
                <a
                    href="https://wa.me/5519996418975"
                    aria-label="WhatsApp"
                    title="WhatsApp"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="bi bi-whatsapp" aria-hidden="true"></i>
                </a>
            </div>
        </section>

        <nav class="footer-col" aria-labelledby="footer-institucional-titulo">
            <h3 id="footer-institucional-titulo">Institucional</h3>

            <ul>
                <li><a href="menu.php">Página Inicial</a></li>
                <li><a href="loja.php">Loja</a></li>
                <li><a href="aba_produto.php">Produtos</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="politica.html">Política e Privacidade</a></li>
            </ul>
        </nav>

        <section class="footer-col" aria-labelledby="footer-atendimento-titulo">
            <h3 id="footer-atendimento-titulo">Atendimento</h3>

            <p>📞 (19) 996418975</p>
            <p>📧 garrasepatas00@gmail.com</p>
            <p>📍 Rua das Flores, 123</p>
            <p>Segunda a Sexta</p>
            <p>08:00 às 18:00</p>
        </section>

    </div>

    <div class="footer-copy">
        <p>© 2026 Garras &amp; Patas - Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Etec</strong> | TCC - Informatica para internet</p>
    </div>

</footer>

<script>
const produtoAtual = <?= $produtoJsJson ?>;
let promocaoAtiva = <?= $emPromocao ? 'true' : 'false' ?>;
let descontoPromocaoAtual = <?= json_encode(round($desconto, 2), JSON_PRESERVE_ZERO_FRACTION) ?>;

let precoUnitarioAtual =
    <?= json_encode(round($precoAtualInicial, 2), JSON_PRESERVE_ZERO_FRACTION) ?>;

let precoUnitarioOriginal =
    <?= json_encode(round($precoOriginalInicial, 2), JSON_PRESERVE_ZERO_FRACTION) ?>;

let variacaoAtual = {
    id: <?= $variacoes ? (int)$variacoes[0]['id_variacao'] : 'null' ?>,
    nome: <?= json_encode($variacoes ? (string)$variacoes[0]['nome'] : '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};


function formatarReal(valor) {

    return Number(valor || 0)
        .toLocaleString(
            "pt-BR",
            {
                style: "currency",
                currency: "BRL"
            }
        );

}


function quantidadeProduto() {

    const input =
        document.getElementById("quantidade");


    let quantidade =
        parseInt(input.value, 10);


    if (
        !Number.isFinite(quantidade)
        ||
        quantidade < 1
    ) {

        quantidade = 1;

    }


    if (quantidade > 99) {
        quantidade = 99;
    }


    input.value =
        quantidade;


    return quantidade;

}


function selecionarVariacao(botao) {

    document
        .querySelectorAll(".btn-tamanho")
        .forEach(
            item => item.classList.remove("ativo")
        );


    botao.classList.add("ativo");


    precoUnitarioAtual =
        Number(botao.dataset.preco);


    precoUnitarioOriginal =
        Number(botao.dataset.precoOriginal);

    promocaoAtiva = botao.dataset.promocao === "1" && Number(botao.dataset.desconto || 0) > 0;
    descontoPromocaoAtual = Number(botao.dataset.desconto || 0);


    variacaoAtual = {

        id:
            botao.dataset.idVariacao
            ? Number(botao.dataset.idVariacao)
            : null,

        nome:
            botao.dataset.nomeVariacao || ""

    };


    const foto =
        document.getElementById("fotoProduto");


    if (
        foto
        &&
        botao.dataset.imagem
    ) {

        foto.src =
            botao.dataset.imagem;

    }


    atualizarPreco();

}


function atualizarPreco() {

    const quantidade =
        quantidadeProduto();


    const total =
        precoUnitarioAtual
        *
        quantidade;


    const totalOriginal =
        precoUnitarioOriginal
        *
        quantidade;


    document.getElementById(
        "precoProdutoValor"
    ).textContent =
        formatarReal(total);


    const precoAntigo =
        document.getElementById(
            "precoAntigoProduto"
        );

    const seloDesconto =
        document.getElementById("produtoDesconto");


    if (
        promocaoAtiva
        &&
        totalOriginal > total
    ) {

        precoAntigo.style.display =
            "block";


        precoAntigo.textContent =
            formatarReal(totalOriginal);

    }

    else {

        precoAntigo.style.display =
            "none";

    }

    if (seloDesconto) {
        if (promocaoAtiva && descontoPromocaoAtual > 0) {
            seloDesconto.style.display = "inline-block";
            seloDesconto.textContent =
                "-" + String(descontoPromocaoAtual).replace(".", ",") + "%";
        } else {
            seloDesconto.style.display = "none";
        }
    }

}


function lerCarrinho() {

    try {

        const salvo =
            JSON.parse(
                localStorage.getItem(
                    "garras_patas_carrinho"
                )
                ||
                "[]"
            );


        return Array.isArray(salvo)
            ? salvo
            : [];

    }

    catch (erro) {

        return [];

    }

}


function adicionarProdutoAoCarrinho() {

    const quantidade = quantidadeProduto();
    const foto = document.getElementById("fotoProduto");

    const item = {
        idProduto: Number(produtoAtual.id),
        idVariacao: variacaoAtual.id,
        nome: produtoAtual.nome,
        variacao: variacaoAtual.nome,
        marca: produtoAtual.marca || "",
        categoria: produtoAtual.categoria || "",
        imagem: foto?.getAttribute("src") || produtoAtual.imagem,
        precoUnitario: precoUnitarioAtual,
        quantidade: quantidade
    };

    if (typeof window.GPAdicionarAoCarrinho === "function") {
        window.GPAdicionarAoCarrinho(item);
    } else {
        const chave = String(item.idProduto) + ":" + (item.idVariacao === null ? "base" : String(item.idVariacao));
        let carrinho = [];
        try { carrinho = JSON.parse(localStorage.getItem("garras_patas_carrinho_v2") || "[]"); } catch (erro) {}
        if (!Array.isArray(carrinho)) carrinho = [];
        const existente = carrinho.find(registro => registro && registro.chave === chave);
        if (existente) {
            existente.quantidade = Math.min(99, Number(existente.quantidade || 0) + quantidade);
        } else {
            carrinho.push({ ...item, chave });
        }
        localStorage.setItem("garras_patas_carrinho_v2", JSON.stringify(carrinho));
        window.dispatchEvent(new CustomEvent("garras:carrinho-atualizado", { detail: carrinho }));
    }

    const botao = document.getElementById("adicionarCarrinhoProduto");
    const mensagem = document.getElementById("mensagemProduto");

    const textoOriginal = botao.innerHTML;

    botao.innerHTML = "✓ Adicionado";
    mensagem.className = "mensagem-produto sucesso";
    mensagem.textContent = "Produto adicionado ao carrinho.";

    setTimeout(
        () => {
            botao.innerHTML = textoOriginal;
            mensagem.textContent = "";
        },
        1600
    );
}


function lerFavoritos() {
    return [];
}

function atualizarBotaoFavorito() {
    const botao = document.getElementById("favoritarProduto");
    if (!botao) return;

    const inicial = <?= $produtoFavoritado ? 'true' : 'false' ?>;
    botao.classList.toggle("favoritado", inicial);
    botao.setAttribute("aria-pressed", String(inicial));
    botao.innerHTML = inicial ? "♥ Favoritado" : "❤ Favoritar";
}

async function alternarFavorito() {
    const botao = document.getElementById("favoritarProduto");
    if (!botao) return;

    try {
        const dados = new URLSearchParams();
        dados.append("id_produto", String(produtoAtual.id));

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

        if (!resultado.ok) return;

        botao.classList.toggle("favoritado", !!resultado.favoritado);
        botao.setAttribute("aria-pressed", String(!!resultado.favoritado));
        botao.innerHTML = resultado.favoritado ? "♥ Favoritado" : "❤ Favoritar";
    } catch (erro) {
        console.error("Erro ao atualizar favorito:", erro);
    }
}

function moverSimilares(direcao) {

    const track =
        document.getElementById(
            "produtoSimilaresTrack"
        );


    if (!track) {
        return;
    }


    const primeiroCard =
        track.querySelector(
            ".produto-similar-card"
        );


    const movimento =
        primeiroCard
        ? primeiroCard.offsetWidth * 3 + 32
        : 600;


    track.scrollBy({

        left:
            direcao
            *
            movimento,

        behavior:
            "smooth"

    });

}


document.addEventListener(
    "DOMContentLoaded",
    function () {

        atualizarBotaoFavorito();
        atualizarPreco();

    }
);
</script>
<script src="../js/carrinho.js"></script>

<script>
(function(){
    function atualizar(){
        const qtd = typeof GPQuantidadeCarrinho === "function" ? GPQuantidadeCarrinho() : 0;
        document.querySelectorAll("[data-garras-cart-badge]").forEach(function(badge){
            badge.textContent = qtd > 99 ? "99+" : String(qtd);
            badge.style.display = qtd > 0 ? "inline-flex" : "none";
        });
    }
    document.addEventListener("DOMContentLoaded", atualizar);
    window.addEventListener("storage", atualizar);
    window.addEventListener("garras:carrinho-atualizado", atualizar);
})();
</script>

<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
