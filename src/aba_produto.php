<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

$usuarioLogado = sessaoUsuarioLogado();
$primeiroNomeUsuario = sessaoPrimeiroNome();

function abaCalcularPromocaoVariacao(array $variacao): array
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

function abaSlug(string $texto): string
{
    $texto = trim($texto);
    if ($texto === '') {
        return '';
    }

    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($convertido !== false) {
        $texto = $convertido;
    }

    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?? '';
    return trim($texto, '-');
}

$categoriasProduto = [];
$categoriasProdutoMeta = [];

/*
 * Os filtros de categoria vêm diretamente do banco.
 * Assim, se o gerente criar, renomear ou excluir uma categoria,
 * a aba de produtos acompanha automaticamente.
 */
$resultadoCategorias = $conexao->query(
    "SELECT id_categoria_produto, nome, slug, tipo_pet
     FROM categorias_produtos
     WHERE ativo = 1
     ORDER BY ordem, nome, id_categoria_produto"
);

if ($resultadoCategorias) {
    while ($categoriaBanco = $resultadoCategorias->fetch_assoc()) {
        $slugCategoria = trim((string)($categoriaBanco['slug'] ?? ''));

        if ($slugCategoria === '') {
            continue;
        }

        $categoriasProduto[$slugCategoria] =
            (string)$categoriaBanco['nome'];

        $categoriasProdutoMeta[$slugCategoria] = [
            'tipo_pet' => (string)($categoriaBanco['tipo_pet'] ?? 'ambos'),
        ];
    }
}

$produtosBanco = [];
$marcasFiltro = [];

$resultadoProdutos = $conexao->query(
    "SELECT
        p.*,
        m.nome AS marca_nome,
        c.nome AS categoria_nome,
        c.slug AS categoria_slug,
        c.tipo_pet AS categoria_tipo_pet
     FROM produtos_destaque p
     LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
     LEFT JOIN categorias_produtos c
        ON c.id_categoria_produto = p.id_categoria_produto
     WHERE p.ativo = 1
     ORDER BY p.ordem, p.id_produto"
);

if ($resultadoProdutos) {
    while ($produto = $resultadoProdutos->fetch_assoc()) {
        $idProduto = (int)$produto['id_produto'];

        $precoOriginalProduto =
            (float)$produto['preco'];

        $emPromocao =
            (int)($produto['promocao'] ?? 0) === 1
            &&
            (float)($produto['desconto_percentual'] ?? 0) > 0;

        $desconto =
            $emPromocao
            ? min(
                99.99,
                max(
                    0,
                    (float)$produto['desconto_percentual']
                )
            )
            : 0.0;

        $precoProduto =
            $emPromocao
            ? $precoOriginalProduto
                * (1 - ($desconto / 100))
            : $precoOriginalProduto;

        $marcaNome =
            trim(
                (string)($produto['marca_nome'] ?? '')
            );

        $marcaFiltro =
            abaSlug($marcaNome);

        if (
            $marcaNome !== ''
            &&
            $marcaFiltro !== ''
        ) {
            $marcasFiltro[$marcaFiltro] =
                $marcaNome;
        }

        /*
         * Categoria oficial do produto:
         * primeiro usa o slug da FK categorias_produtos.
         * A coluna antiga `categoria` só fica como fallback para
         * produtos antigos que ainda não foram classificados.
         */
        $categoriaProduto =
            trim(
                (string)($produto['categoria_slug'] ?? '')
            );

        if ($categoriaProduto === '') {
            $categoriaProduto =
                trim(
                    (string)($produto['categoria'] ?? 'outros')
                );

            $categoriaProduto =
                abaSlug($categoriaProduto);

            /*
             * Compatibilidade com valores antigos que existiam
             * antes das categorias dinâmicas.
             */
            $aliasesCategoriaLegada = [
                'racao' => 'alimentacao',
                'racoes' => 'alimentacao',
                'brinquedo' => 'brinquedos',
                'brinquedos' => 'brinquedos',
            ];

            $categoriaProduto =
                $aliasesCategoriaLegada[$categoriaProduto]
                ??
                $categoriaProduto;
        }

        $tamanhos = [];

        $resultadoVariacoes =
            $conexao->query(
                'SELECT id_variacao, id_produto, nome, preco, imagem, promocao, desconto_percentual FROM variacoes_produto WHERE id_produto = '
                . $idProduto
                . ' ORDER BY id_variacao'
            );

        if ($resultadoVariacoes) {
            while (
                $variacao =
                    $resultadoVariacoes->fetch_assoc()
            ) {
                $promoVariacao = abaCalcularPromocaoVariacao($variacao);
                $precoOriginalVariacao = $promoVariacao['precoOriginal'];
                $precoVariacao = $promoVariacao['precoAtual'];

                $imagemVariacao =
                    trim(
                        (string)($variacao['imagem'] ?? '')
                    );

                $tamanhos[] = [
                    'id' =>
                        (int)$variacao['id_variacao'],

                    'nome' =>
                        (string)$variacao['nome'],

                    'preco' =>
                        $precoVariacao,

                    'precoAntigo' =>
                        $precoOriginalVariacao,

                    'imagem' =>
                        $imagemVariacao !== ''
                        ? $imagemVariacao
                        : (string)$produto['imagem'],
                ];
            }
        }

        $produtosBanco[] = [
            'id' =>
                $idProduto,

            'nome' =>
                (string)$produto['nome'],

            'marca' =>
                $marcaNome,

            'marcaFiltro' =>
                $marcaFiltro,

            'preco' =>
                $precoProduto,

            'precoAntigo' =>
                $precoOriginalProduto,

            'pet' =>
                (string)($produto['tipo_pet'] ?? 'ambos'),

            'categoria' =>
                $categoriaProduto,

            'categoriaNome' =>
                (string)($produto['categoria_nome'] ?? ''),

            'imagem' =>
                (string)$produto['imagem'],

            'avaliacao' =>
                null,

            'destaque' =>
                (int)($produto['destaque'] ?? 0) === 1,

            'tamanhos' =>
                $tamanhos,
        ];
    }
}

ksort($marcasFiltro, SORT_NATURAL | SORT_FLAG_CASE);

$produtosJson = json_encode(
    $produtosBanco,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="../css/style-aba_produto.css?v=11" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>
/* =========================================================
   RESPONSIVIDADE DA PÁGINA DE PRODUTOS
   Mantém a identidade visual e adapta somente proporções/fluxo.
========================================================= */
html, body { max-width: 100%; overflow-x: hidden; }

@media (max-width: 1200px) {
    .topo { padding-left: 18px !important; padding-right: 18px !important; }
    .logo img { margin-left: 12px !important; transform: scale(1.15) !important; }
    .search-box { max-width: min(56vw, 560px) !important; padding-left: 10px !important; }
    .icons { right: 0 !important; gap: 14px !important; }
}

@media (max-width: 900px) {
    .topo {
        flex-wrap: wrap !important;
        justify-content: space-between !important;
        gap: 9px;
        padding: 10px 16px !important;
    }
    .logo { flex: 0 0 auto; width: auto !important; }
    .logo img {
        width: 112px !important;
        max-width: 112px !important;
        max-height: 56px !important;
        margin-left: 0 !important;
        transform: none !important;
    }
    .busca {
        order: 3;
        flex: 0 0 100% !important;
        width: 100% !important;
        margin: 3px 0 0 !important;
    }
    .search-box { width: 100% !important; max-width: none !important; padding-left: 0 !important; }
    .search-box input { width: 100% !important; padding: 11px 44px 11px 16px !important; }
    .icons { margin-left: auto; right: 0 !important; gap: 10px !important; }
    .icons i { font-size: 25px !important; }
    .user span { font-size: 12px !important; white-space: nowrap; }
}

@media (max-width: 820px) {
    .produto-container {
        width: min(100% - 24px, 1120px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        grid-template-columns: 1fr !important;
        gap: 24px !important;
    }
    .produto-imagem {
        width: 100% !important;
        min-width: 0 !important;
        max-height: 430px !important;
    }
    .produto-imagem img {
        max-width: 100% !important;
        max-height: 410px !important;
        width: auto !important;
    }
    .produto-info { width: 100% !important; min-width: 0 !important; }
    .produto-titulo { font-size: clamp(25px, 5vw, 34px) !important; line-height: 1.15 !important; }
    .descricao { line-height: 1.55 !important; }
    .tamanhos { display: flex !important; flex-wrap: wrap !important; gap: 8px !important; }
    .btn-tamanho { min-height: 38px !important; }
    .botoes { display: flex !important; flex-wrap: wrap !important; gap: 10px !important; }
    .botoes .btn { min-height: 44px !important; }
}

@media (max-width: 560px) {
    .topo { padding-left: 12px !important; padding-right: 12px !important; }
    .logo img { width: 104px !important; max-width: 104px !important; }
    .produto-container { width: calc(100% - 20px) !important; gap: 18px !important; }
    .produto-imagem { max-height: 320px !important; }
    .produto-imagem img { max-height: 300px !important; }
    .produto-info { overflow-wrap: anywhere; }
    .produto-titulo { font-size: 27px !important; }
    .produto-marca, .mensagem-produto { font-size: 13px !important; }
    .preco { font-size: 22px !important; }
    .produto-desconto { margin-left: 5px !important; }
    .quantidade-area { display: flex !important; align-items: center !important; flex-wrap: wrap !important; gap: 8px !important; }
    .quantidade { width: 100% !important; max-width: 140px !important; }
    .botoes { flex-direction: column !important; align-items: stretch !important; }
    .botoes .btn { width: 100% !important; }
    .produto-similares-cabecalho { align-items: flex-start !important; flex-direction: column !important; gap: 5px !important; }
    .produto-similares-track { gap: 12px !important; }
    .produto-similar-card { flex-basis: min(78vw, 220px) !important; min-width: min(78vw, 220px) !important; }
}

@media (max-width: 390px) {
    .user span { display: none !important; }
    .icons { gap: 7px !important; }
    .produto-imagem { max-height: 270px !important; }
    .produto-imagem img { max-height: 250px !important; }
    .produto-titulo { font-size: 24px !important; }
    .produto-similar-card { flex-basis: 82vw !important; min-width: 82vw !important; }
}
</style>

    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
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
        <?php if ($usuarioLogado): ?>
            <a href="area-cliente.php">
                <i class="bi bi-person-check"></i>
                <span>Olá, <?= htmlspecialchars($primeiroNomeUsuario, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php else: ?>
            <a href="form.php">
                <i class="bi bi-person"></i>
                <span>Entre ou cadastre-se</span>
            </a>
        <?php endif; ?>
    </div>

    <i class="bi bi-heart" aria-hidden="true"></i>
    <a href="carrinho.php" class="icone-carrinho-produtos carrinho-cabecalho garras-cart-link cart-link" aria-label="Meu carrinho, 0 produtos">
        <i class="bi bi-cart" aria-hidden="true"></i>
        <span class="garras-cart-badge" data-garras-cart-badge aria-hidden="true">0</span>
    </a>
    </div>
</div>

<div class="menu-area">

    
<!-- =====================================================
     PÁGINA DE PRODUTOS
====================================================== -->

<main id="conteudoPrincipal" tabindex="-1" class="pagina-produtos">

    <!-- CAMINHO -->
    <div class="breadcrumb-produtos">
        <a href="menu.php">Início</a>
        <i class="bi bi-chevron-right"></i>
        <span>Produtos</span>
    </div>

    <!-- TÍTULO -->
    <section class="cabecalho-produtos">

        <div>
            <span class="subtitulo-produtos">
                Garra & Patas
            </span>

            <h1>Produtos para seu pet</h1>

            <p>
                Encontre rações, brinquedos, acessórios,
                higiene e muito mais.
            </p>
        </div>

        <div class="ordenacao-produtos">

            <label for="ordenar">
                Ordenar por:
            </label>

            <select id="ordenar">

                <option value="destaque">
                    Mais relevantes
                </option>

                <option value="menor">
                    Menor preço
                </option>

                <option value="maior">
                    Maior preço
                </option>

                <option value="nome">
                    Nome A-Z
                </option>

            </select>

        </div>

    </section>


    <!-- =====================================================
         ÁREA PRINCIPAL
    ====================================================== -->

    <div class="estrutura-produtos">


        <!-- =================================================
             FILTROS
        ================================================== -->

        <aside class="filtros-produtos">

            <div class="topo-filtro">

                <div>
                    <span>Refine sua busca</span>
                    <h2>Filtros</h2>
                </div>

                <i class="bi bi-sliders"></i>

            </div>


            <!-- PESQUISA -->

            <div class="grupo-filtro">

                <h3>Pesquisar</h3>

                <div class="pesquisa-filtro">

                    <label for="pesquisaProduto" class="visually-hidden">Pesquisar produtos</label>
                    <input
                        type="text"
                        id="pesquisaProduto"
                        placeholder="Buscar produto..."
                        autocomplete="off"
                        aria-label="Pesquisar produtos"
                    >

                    <i class="bi bi-search" aria-hidden="true"></i>

                </div>

            </div>


            <!-- PET -->

            <div class="grupo-filtro">

                <div class="titulo-filtro">
                    <h3>Pet</h3>
                    <i class="bi bi-chevron-up"></i>
                </div>

                <label class="opcao-filtro">

                    <input
                        type="checkbox"
                        class="filtro-pet"
                        value="cachorro"
                    >

                    <span class="check-personalizado"></span>

                    Cachorros

                </label>

                <label class="opcao-filtro">

                    <input
                        type="checkbox"
                        class="filtro-pet"
                        value="gato"
                    >

                    <span class="check-personalizado"></span>

                    Gatos

                </label>

            </div>


            <!-- CATEGORIA -->

            <div class="grupo-filtro">

                <div class="titulo-filtro">
                    <h3>Categoria</h3>
                    <i class="bi bi-chevron-up"></i>
                </div>

                <?php foreach ($categoriasProduto as $valorCategoria => $rotuloCategoria): ?>
                <label
                    class="opcao-filtro opcao-categoria"
                    data-pet-categoria="<?= htmlspecialchars(
                        $categoriasProdutoMeta[$valorCategoria]['tipo_pet'] ?? 'ambos',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <input
                        type="checkbox"
                        class="filtro-categoria"
                        value="<?= htmlspecialchars($valorCategoria, ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <span class="check-personalizado"></span>
                    <?= htmlspecialchars($rotuloCategoria, ENT_QUOTES, 'UTF-8') ?>
                </label>
                <?php endforeach; ?>

            </div>


            <!-- MARCAS -->

            <div class="grupo-filtro">

                <div class="titulo-filtro">
                    <h3>Marca</h3>
                    <i class="bi bi-chevron-up"></i>
                </div>

                <?php foreach ($marcasFiltro as $valorMarca => $rotuloMarca): ?>
                <label class="opcao-filtro">
                    <input
                        type="checkbox"
                        class="filtro-marca"
                        value="<?= htmlspecialchars($valorMarca, ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <span class="check-personalizado"></span>
                    <?= htmlspecialchars($rotuloMarca, ENT_QUOTES, 'UTF-8') ?>
                </label>
                <?php endforeach; ?>

            </div>


            <!-- PREÇO -->

            <div class="grupo-filtro">

                <div class="titulo-filtro">

                    <h3>Preço</h3>

                    <i class="bi bi-chevron-up"></i>

                </div>


                <label class="opcao-filtro">

                    <input
                        type="radio"
                        name="preco"
                        class="filtro-preco"
                        value="todos"
                        checked
                    >

                    <span class="radio-personalizado"></span>

                    Todos os preços

                </label>


                <label class="opcao-filtro">

                    <input
                        type="radio"
                        name="preco"
                        class="filtro-preco"
                        value="50"
                    >

                    <span class="radio-personalizado"></span>

                    Até R$ 50

                </label>


                <label class="opcao-filtro">

                    <input
                        type="radio"
                        name="preco"
                        class="filtro-preco"
                        value="100"
                    >

                    <span class="radio-personalizado"></span>

                    Até R$ 100

                </label>


                <label class="opcao-filtro">

                    <input
                        type="radio"
                        name="preco"
                        class="filtro-preco"
                        value="200"
                    >

                    <span class="radio-personalizado"></span>

                    Até R$ 200

                </label>


                <label class="opcao-filtro">

                    <input
                        type="radio"
                        name="preco"
                        class="filtro-preco"
                        value="mais200"
                    >

                    <span class="radio-personalizado"></span>

                    Acima de R$ 200

                </label>

            </div>


            <button
                type="button"
                id="limparFiltros"
                class="btn-limpar-filtros"
            >

                <i class="bi bi-arrow-counterclockwise"></i>

                Limpar filtros

            </button>

        </aside>


        <!-- =================================================
             CONTEÚDO DOS PRODUTOS
        ================================================== -->

        <section class="conteudo-produtos">

            <!-- RESULTADO DO FILTRO -->

            <div
                class="resultado-pesquisa"
                id="resultadoPesquisa"
            ></div>


            <!-- CARROSSEL 1 -->

            <section
                class="secao-carrossel"
                data-secao="destaques"
            >

                <div class="cabecalho-carrossel">

                    <div>

                        <span class="mini-titulo">
                            Escolhidos para você
                        </span>

                        <h2>Produtos em destaque</h2>

                    </div>

                    <div class="botoes-carrossel">

                        <button
                            class="seta-carrossel esquerda"
                            type="button"
                            aria-label="Anterior"
                        >

                            <i class="bi bi-chevron-left"></i>

                        </button>

                        <button
                            class="seta-carrossel direita"
                            type="button"
                            aria-label="Próximo"
                        >

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>

                </div>

                <div class="carrossel-viewport">

                    <div
                        class="carrossel-produtos"
                        id="carrosselDestaques"
                    ></div>

                </div>

            </section>


            <!-- CARROSSEL 2 -->

            <section
                class="secao-carrossel"
                data-secao="gatos"
            >

                <div class="cabecalho-carrossel">

                    <div>

                        <span class="mini-titulo">
                            Para os felinos
                        </span>

                        <h2>Favoritos dos gatos 🐱</h2>

                    </div>

                    <div class="botoes-carrossel">

                        <button
                            class="seta-carrossel esquerda"
                            type="button"
                        >

                            <i class="bi bi-chevron-left"></i>

                        </button>

                        <button
                            class="seta-carrossel direita"
                            type="button"
                        >

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>

                </div>

                <div class="carrossel-viewport">

                    <div
                        class="carrossel-produtos"
                        id="carrosselGatos"
                    ></div>

                </div>

            </section>


            <!-- BANNER -->

            

            <!-- CARROSSEL 3 -->

            <section
                class="secao-carrossel"
                data-secao="cachorros"
            >

                <div class="cabecalho-carrossel">

                    <div>

                        <span class="mini-titulo">
                            Para os cães
                        </span>

                        <h2>Produtos para cachorros 🐶</h2>

                    </div>

                    <div class="botoes-carrossel">

                        <button
                            class="seta-carrossel esquerda"
                            type="button"
                        >

                            <i class="bi bi-chevron-left"></i>

                        </button>

                        <button
                            class="seta-carrossel direita"
                            type="button"
                        >

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>

                </div>

                <div class="carrossel-viewport">

                    <div
                        class="carrossel-produtos"
                        id="carrosselCachorros"
                    ></div>

                </div>

            </section>


            <!-- CARROSSEL 4 -->

            <section
                class="secao-carrossel"
                data-secao="brinquedos"
            >

                <div class="cabecalho-carrossel">

                    <div>

                        <span class="mini-titulo">
                            Hora da diversão
                        </span>

                        <h2>Brinquedos e acessórios</h2>

                    </div>

                    <div class="botoes-carrossel">

                        <button
                            class="seta-carrossel esquerda"
                            type="button"
                        >

                            <i class="bi bi-chevron-left"></i>

                        </button>

                        <button
                            class="seta-carrossel direita"
                            type="button"
                        >

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>

                </div>

                <div class="carrossel-viewport">

                    <div
                        class="carrossel-produtos"
                        id="carrosselBrinquedos"
                    ></div>

                </div>

            </section>


            <!-- CARROSSEL 5 -->

            <section
                class="secao-carrossel"
                data-secao="cuidados"
            >

                <div class="cabecalho-carrossel">

                    <div>

                        <span class="mini-titulo">
                            Cuidados especiais
                        </span>

                        <h2>Higiene e bem-estar</h2>

                    </div>

                    <div class="botoes-carrossel">

                        <button
                            class="seta-carrossel esquerda"
                            type="button"
                        >

                            <i class="bi bi-chevron-left"></i>

                        </button>

                        <button
                            class="seta-carrossel direita"
                            type="button"
                        >

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>

                </div>

                <div class="carrossel-viewport">

                    <div
                        class="carrossel-produtos"
                        id="carrosselCuidados"
                    ></div>

                </div>

            </section>


            <!-- NENHUM PRODUTO -->

            <div
                class="nenhum-produto"
                id="nenhumProduto"
            >

                <div class="nenhum-produto-icone">

                    <i class="bi bi-search-heart"></i>

                </div>

                <h3>Nenhum produto encontrado</h3>

                <p>
                    Tente remover algum filtro ou pesquisar
                    por outro produto.
                </p>

                <button
                    type="button"
                    id="limparFiltrosResultado"
                >

                    Limpar filtros

                </button>

            </div>

        </section>

    </div>

</main>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->
<script src="js/carrinho.js?v=11"></script>
<script>

/* =====================================================
   BANCO DE PRODUTOS
===================================================== */

const produtos = <?= $produtosJson ?: '[]' ?>;


/* =====================================================
   ELEMENTOS PRINCIPAIS
===================================================== */

const pesquisa =
    document.getElementById("pesquisaProduto");

const ordenar =
    document.getElementById("ordenar");

const nenhumProduto =
    document.getElementById("nenhumProduto");

const resultadoPesquisa =
    document.getElementById("resultadoPesquisa");


/* =====================================================
   FORMATAR PREÇO
===================================================== */

function formatarPreco(valor) {

    if (
        valor === undefined ||
        valor === null ||
        isNaN(valor)
    ) {

        return "";

    }

    return Number(valor).toLocaleString(
        "pt-BR",
        {
            style: "currency",
            currency: "BRL"
        }
    );

}


/* =====================================================
   CALCULAR DESCONTO
===================================================== */

function calcularDesconto(
    precoAntigo,
    precoAtual
) {

    if (
        !precoAntigo ||
        precoAntigo <= precoAtual
    ) {

        return 0;

    }

    return Math.round(
        (
            (precoAntigo - precoAtual)
            /
            precoAntigo
        )
        *
        100
    );

}


/* =====================================================
   CRIAR TAMANHOS DO CARD
===================================================== */

function criarTamanhos(produto) {

    if (
        !produto.tamanhos ||
        produto.tamanhos.length === 0
    ) {

        return "";

    }


    return `

        <div class="area-variacoes">

            <span class="titulo-variacao">
                Tamanho:
            </span>


            <div class="variacoes-produto">

                ${produto.tamanhos.map(
                    (tamanho, index) => `

                        <button
                            type="button"

                            class="
                                btn-tamanho-card
                                ${index === 0 ? "ativo" : ""}
                            "

                            data-id-variacao="${tamanho.id || ''}"

                            data-preco="${tamanho.preco}"

                            data-preco-antigo="${tamanho.precoAntigo}"

                            data-imagem="${tamanho.imagem}"

                            data-tamanho="${tamanho.nome}"
                        >

                            ${tamanho.nome}

                        </button>

                    `
                ).join("")}

            </div>

        </div>

    `;

}


/* =====================================================
   CRIAR CARD
===================================================== */

function criarCard(produto) {

    const primeiroTamanho =
        produto.tamanhos &&
        produto.tamanhos.length > 0
        ?
        produto.tamanhos[0]
        :
        null;


    const precoInicial =
        primeiroTamanho
        ?
        primeiroTamanho.preco
        :
        produto.preco;


    const precoAntigoInicial =
        primeiroTamanho
        ?
        primeiroTamanho.precoAntigo
        :
        produto.precoAntigo;


    const imagemInicial =
        primeiroTamanho
        ?
        primeiroTamanho.imagem
        :
        produto.imagem;


    const desconto =
        calcularDesconto(
            precoAntigoInicial,
            precoInicial
        );


    return `

        <article
            class="card-produto"
            data-id="${produto.id}"
        >

            <!-- =====================================
                 IMAGEM
            ====================================== -->

            <div class="area-imagem-produto">


                <span
                    class="selo-desconto"
                    style="
                        display:
                        ${desconto > 0 ? "block" : "none"}
                    "
                >

                    -${desconto}%

                </span>


                <!-- FAVORITO -->

                <button
                    class="btn-favorito"
                    type="button"
                    title="Adicionar aos favoritos"
                >

                    <i class="bi bi-heart" aria-hidden="true"></i>

                </button>


                <!-- FOTO -->

                <a href="produto.php?id=${produto.id}">

                    <img
                        class="imagem-card-produto"

                        src="${imagemInicial}"

                        alt="${produto.nome}"
                    >

                </a>


                <!-- CARRINHO REDONDO -->

                <button
                    class="btn-carrinho-card"
                    type="button"
                    title="Adicionar ao carrinho"
                >

                    <i class="bi bi-cart-plus"></i>

                </button>

            </div>


            <!-- =====================================
                 INFORMAÇÕES
            ====================================== -->

            <div class="informacoes-card">


                <div class="linha-marca">

                    <span class="marca-produto">

                        ${produto.marca}

                    </span>


                    ${produto.avaliacao !== null && produto.avaliacao !== undefined
                        ? `<span class="nota-produto"><i class="bi bi-star-fill"></i> ${produto.avaliacao}</span>`
                        : ""}

                </div>


                <!-- NOME -->

                <a
                    href="produto.php?id=${produto.id}"
                    class="nome-produto"
                >

                    ${produto.nome}

                </a>


                <!-- TAMANHOS -->

                ${criarTamanhos(produto)}


                <!-- PREÇOS -->

                <div class="precos-produto">

                    <span
                        class="preco-antigo"
                        style="display:${precoAntigoInicial > precoInicial ? 'inline' : 'none'}"
                    >
                        ${formatarPreco(precoAntigoInicial)}
                    </span>


                    <strong class="preco-atual">

                        ${formatarPreco(
                            precoInicial
                        )}

                    </strong>


                    <span class="preco-pix">

                        à vista no Pix

                    </span>

                </div>


                <!-- TAMANHO ESCOLHIDO -->

                <input
                    type="hidden"
                    class="tamanho-escolhido"
                    value="${
                        primeiroTamanho
                        ?
                        primeiroTamanho.nome
                        :
                        ""
                    }"
                >


                <!-- PREÇO ESCOLHIDO -->

                <input
                    type="hidden"
                    class="preco-escolhido"
                    value="${precoInicial}"
                >


                <!-- BOTÃO ADICIONAR -->

                <button
                    class="comprar-produto"
                    type="button"
                >

                    <i class="bi bi-cart3"></i>

                    Adicionar

                </button>

            </div>

        </article>

    `;

}


/* =====================================================
   PEGAR FILTROS
===================================================== */

function normalizarTexto(texto) {

    return String(texto ?? "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();

}


function obterFiltros() {

    const pets = [

        ...document.querySelectorAll(
            ".filtro-pet:checked"
        )

    ].map(
        input => input.value
    );


    const categorias = [

        ...document.querySelectorAll(
            ".filtro-categoria:checked"
        )

    ].map(
        input => input.value
    );


    const marcas = [

        ...document.querySelectorAll(
            ".filtro-marca:checked"
        )

    ].map(
        input => input.value
    );


    const precoSelecionado =
        document.querySelector(
            ".filtro-preco:checked"
        );


    const preco =
        precoSelecionado
        ?
        precoSelecionado.value
        :
        "todos";


    return {

        pets,

        categorias,

        marcas,

        preco,

        pesquisa:
            pesquisa
            ?
            normalizarTexto(pesquisa.value)
            :
            ""

    };

}


/* =====================================================
   PEGAR MENOR PREÇO DO PRODUTO
===================================================== */

function obterPrecoParaFiltro(produto) {

    if (
        produto.tamanhos &&
        produto.tamanhos.length > 0
    ) {

        return Math.min(
            ...produto.tamanhos.map(
                tamanho =>
                    Number(tamanho.preco)
            )
        );

    }

    return Number(
        produto.preco
    );

}


/* =====================================================
   FILTRAR PRODUTOS
===================================================== */

function filtrarProdutos() {

    const filtros =
        obterFiltros();


    let lista =
        produtos.filter(
            produto => {


                /* ==========================
                   PET
                ========================== */

                const correspondePet =

                    filtros.pets.length === 0

                    ||

                    produto.pet === "ambos"

                    ||

                    filtros.pets.includes(
                        produto.pet
                    );


                /* ==========================
                   CATEGORIA
                ========================== */

                const correspondeCategoria =

                    filtros.categorias.length === 0

                    ||

                    filtros.categorias.includes(
                        produto.categoria
                    );


                /* ==========================
                   MARCA
                ========================== */

                const correspondeMarca =

                    filtros.marcas.length === 0

                    ||

                    filtros.marcas.includes(
                        produto.marcaFiltro
                    );


                /* ==========================
                   PESQUISA
                ========================== */

                const textoProduto =
                    normalizarTexto(
                        produto.nome
                        +
                        " "
                        +
                        produto.marca
                        +
                        " "
                        +
                        produto.categoria
                        +
                        " "
                        +
                        produto.categoriaNome
                        +
                        " "
                        +
                        produto.pet
                    );


                const correspondePesquisa =
                    textoProduto.includes(
                        filtros.pesquisa
                    );


                /* ==========================
                   PREÇO
                ========================== */

                const precoProduto =
                    obterPrecoParaFiltro(
                        produto
                    );


                let correspondePreco =
                    true;


                if (
                    filtros.preco === "50"
                ) {

                    correspondePreco =
                        precoProduto <= 50;

                }


                if (
                    filtros.preco === "100"
                ) {

                    correspondePreco =
                        precoProduto <= 100;

                }


                if (
                    filtros.preco === "200"
                ) {

                    correspondePreco =
                        precoProduto <= 200;

                }


                if (
                    filtros.preco === "mais200"
                ) {

                    correspondePreco =
                        precoProduto > 200;

                }


                return (

                    correspondePet

                    &&

                    correspondeCategoria

                    &&

                    correspondeMarca

                    &&

                    correspondePesquisa

                    &&

                    correspondePreco

                );

            }
        );


    /* =================================================
       ORDENAÇÃO
    ================================================= */


    if (
        ordenar &&
        ordenar.value === "menor"
    ) {

        lista.sort(
            (a, b) =>
                obterPrecoParaFiltro(a)
                -
                obterPrecoParaFiltro(b)
        );

    }


    if (
        ordenar &&
        ordenar.value === "maior"
    ) {

        lista.sort(
            (a, b) =>
                obterPrecoParaFiltro(b)
                -
                obterPrecoParaFiltro(a)
        );

    }


    if (
        ordenar &&
        ordenar.value === "nome"
    ) {

        lista.sort(
            (a, b) =>
                a.nome.localeCompare(
                    b.nome,
                    "pt-BR"
                )
        );

    }


    if (
        ordenar &&
        ordenar.value === "destaque"
    ) {

        lista.sort(
            (a, b) =>
                Number(b.destaque)
                -
                Number(a.destaque)
        );

    }


    return lista;

}


/* =====================================================
   PREENCHER CARROSSEL
===================================================== */

function preencherCarrossel(
    id,
    lista
) {

    const elemento =
        document.getElementById(id);


    if (!elemento) {

        return;

    }


    elemento.innerHTML =
        lista
        .map(criarCard)
        .join("");

}


/* =====================================================
   RENDERIZAR PRODUTOS
===================================================== */

function renderizarProdutos() {

    const filtrados =
        filtrarProdutos();


    const destaques =
        filtrados.filter(
            produto =>
                produto.destaque
        );


    const gatos =
        filtrados.filter(
            produto =>
                (produto.pet === "gato" || produto.pet === "ambos")
        );


    const cachorros =
        filtrados.filter(
            produto =>
                (produto.pet === "cachorro" || produto.pet === "ambos")
        );


    const brinquedos =
        filtrados.filter(
            produto =>
                produto.categoria ===
                "brinquedos"
        );


    const cuidados =
        filtrados.filter(
            produto =>

                produto.categoria ===
                "higiene"

                ||

                produto.categoria ===
                "transporte"

        );


    /* DESTAQUES */

    preencherCarrossel(
        "carrosselDestaques",

        destaques.length > 0
        ?
        destaques
        :
        filtrados
    );


    /* GATOS */

    preencherCarrossel(
        "carrosselGatos",
        gatos
    );


    /* CACHORROS */

    preencherCarrossel(
        "carrosselCachorros",
        cachorros
    );


    /* BRINQUEDOS */

    preencherCarrossel(
        "carrosselBrinquedos",
        brinquedos
    );


    /* CUIDADOS */

    preencherCarrossel(
        "carrosselCuidados",
        cuidados
    );


    /* =================================================
       ESCONDER CARROSSEL VAZIO
    ================================================= */

    document
    .querySelectorAll(
        ".secao-carrossel"
    )
    .forEach(
        secao => {

            const carrossel =
                secao.querySelector(
                    ".carrossel-produtos"
                );


            if (!carrossel) {

                return;

            }


            secao.style.display =
                carrossel.children.length > 0
                ?
                "block"
                :
                "none";

        }
    );


    /* =================================================
       RESULTADOS DO FILTRO
    ================================================= */

    if (
        resultadoPesquisa
    ) {

        const checkboxesMarcados =
            document.querySelectorAll(
                ".filtros-produtos input[type='checkbox']:checked"
            ).length;


        const precoMarcado =
            document.querySelector(
                ".filtro-preco:checked"
            );


        const temFiltroPreco =
            precoMarcado &&
            precoMarcado.value !== "todos";


        const temPesquisa =
            pesquisa &&
            pesquisa.value.trim() !== "";


        if (
            checkboxesMarcados > 0
            ||
            temFiltroPreco
            ||
            temPesquisa
        ) {

            resultadoPesquisa.style.display =
                "flex";


            resultadoPesquisa.innerHTML = `

                <i class="bi bi-funnel-fill"></i>

                <span>

                    ${filtrados.length}

                    produto(s) encontrado(s)

                </span>

            `;

        }

        else {

            resultadoPesquisa.style.display =
                "none";

        }

    }


    /* =================================================
       NENHUM PRODUTO
    ================================================= */

    if (
        nenhumProduto
    ) {

        nenhumProduto.style.display =

            filtrados.length === 0

            ?

            "flex"

            :

            "none";

    }


    /* REATIVAR EVENTOS DOS CARDS */

    ativarBotoesCards();

}


/* =====================================================
   TROCAR TAMANHO DO PRODUTO
===================================================== */

function ativarTrocaTamanho() {

    document
    .querySelectorAll(
        ".btn-tamanho-card"
    )
    .forEach(
        botao => {

            botao.onclick =
                function() {


                    /* CARD */

                    const card =
                        this.closest(
                            ".card-produto"
                        );


                    if (!card) {

                        return;

                    }


                    /* ==========================
                       DESMARCAR OUTROS TAMANHOS
                    ========================== */

                    card
                    .querySelectorAll(
                        ".btn-tamanho-card"
                    )
                    .forEach(
                        btn => {

                            btn.classList.remove(
                                "ativo"
                            );

                        }
                    );


                    /* MARCAR O CLICADO */

                    this.classList.add(
                        "ativo"
                    );


                    /* ==========================
                       PEGAR NOVOS DADOS
                    ========================== */

                    const novoPreco =
                        Number(
                            this.dataset.preco
                        );


                    const novoPrecoAntigo =
                        Number(
                            this.dataset.precoAntigo
                        );


                    const novaImagem =
                        this.dataset.imagem;


                    const novoTamanho =
                        this.dataset.tamanho;


                    /* ==========================
                       TROCAR IMAGEM
                    ========================== */

                    const imagem =
                        card.querySelector(
                            ".imagem-card-produto"
                        );


                    if (imagem) {

                        imagem.style.opacity =
                            "0";


                        setTimeout(
                            () => {

                                imagem.src =
                                    novaImagem;


                                imagem.style.opacity =
                                    "1";

                            },

                            150
                        );

                    }


                    /* ==========================
                       TROCAR PREÇO ATUAL
                    ========================== */

                    const precoAtual =
                        card.querySelector(
                            ".preco-atual"
                        );


                    if (precoAtual) {

                        precoAtual.textContent =
                            formatarPreco(
                                novoPreco
                            );

                    }


                    /* ==========================
                       TROCAR PREÇO ANTIGO
                    ========================== */

                    const precoAntigo =
                        card.querySelector(
                            ".preco-antigo"
                        );


                    if (precoAntigo) {

                        precoAntigo.textContent =
                            formatarPreco(
                                novoPrecoAntigo
                            );

                        precoAntigo.style.display =
                            novoPrecoAntigo > novoPreco
                            ? "inline"
                            : "none";

                    }


                    /* ==========================
                       ATUALIZAR DESCONTO
                    ========================== */

                    const desconto =
                        calcularDesconto(
                            novoPrecoAntigo,
                            novoPreco
                        );


                    const selo =
                        card.querySelector(
                            ".selo-desconto"
                        );


                    if (selo) {

                        if (
                            desconto > 0
                        ) {

                            selo.style.display =
                                "block";


                            selo.textContent =
                                `-${desconto}%`;

                        }

                        else {

                            selo.style.display =
                                "none";

                        }

                    }


                    /* ==========================
                       GUARDAR TAMANHO ESCOLHIDO
                    ========================== */

                    const tamanhoInput =
                        card.querySelector(
                            ".tamanho-escolhido"
                        );


                    if (tamanhoInput) {

                        tamanhoInput.value =
                            novoTamanho;

                    }


                    /* ==========================
                       GUARDAR PREÇO ESCOLHIDO
                    ========================== */

                    const precoInput =
                        card.querySelector(
                            ".preco-escolhido"
                        );


                    if (precoInput) {

                        precoInput.value =
                            novoPreco;

                    }

                };

        }
    );

}


/* =====================================================
   FAVORITOS
===================================================== */

function ativarFavoritos() {

    document
    .querySelectorAll(
        ".btn-favorito"
    )
    .forEach(
        botao => {

            botao.onclick =
                function(evento) {

                    evento.preventDefault();

                    evento.stopPropagation();


                    const icone =
                        this.querySelector(
                            "i"
                        );


                    this.classList.toggle(
                        "favoritado"
                    );


                    if (
                        this.classList.contains(
                            "favoritado"
                        )
                    ) {

                        icone.className =
                            "bi bi-heart-fill";

                    }

                    else {

                        icone.className =
                            "bi bi-heart";

                    }

                };

        }
    );

}


/* =====================================================
   BOTÕES DO CARRINHO
===================================================== */

function ativarCarrinho() {

    document
    .querySelectorAll(
        ".btn-carrinho-card, .comprar-produto"
    )
    .forEach(
        botao => {

            botao.onclick =
                function(evento) {

                    evento.preventDefault();
                    evento.stopPropagation();

                    const card =
                        this.closest(
                            ".card-produto"
                        );

                    if (!card) {
                        return;
                    }

                    const idProduto =
                        Number(
                            card.dataset.id
                        );

                    if (!idProduto) {
                        return;
                    }

                    const tamanhoAtivo =
                        card.querySelector(
                            ".btn-tamanho-card.ativo"
                        );

                    const idVariacao =
                        tamanhoAtivo
                        && tamanhoAtivo.dataset.idVariacao
                        ? Number(tamanhoAtivo.dataset.idVariacao)
                        : null;

                    const nomeVariacao =
                        tamanhoAtivo
                        ? tamanhoAtivo.dataset.tamanho || ""
                        : "";

                    const imagem =
                        card.querySelector(
                            ".imagem-card-produto"
                        );

                    const marca =
                        card.querySelector(
                            ".marca-produto"
                        );

                    const nome =
                        card.querySelector(
                            ".nome-produto"
                        );

                    const preco =
                        Number(
                            card.querySelector(
                                ".preco-escolhido"
                            )?.value || 0
                        );

                    GPAdicionarAoCarrinho({
                        idProduto: idProduto,
                        idVariacao: idVariacao,
                        nome: nome?.textContent.trim() || "Produto",
                        variacao: nomeVariacao,
                        marca: marca?.textContent.trim() || "",
                        imagem: imagem?.getAttribute("src") || "",
                        precoUnitario: preco,
                        quantidade: 1
                    });

                    if (typeof GPAtualizarContadoresCarrinho === "function") {
                        GPAtualizarContadoresCarrinho();
                    }

                    const textoOriginal = this.innerHTML;

                    this.classList.add("adicionado");

                    this.innerHTML =
                        this.classList.contains("btn-carrinho-card")
                        ? '<i class="bi bi-check-lg"></i>'
                        : '<i class="bi bi-check-lg"></i> Adicionado';

                    setTimeout(
                        () => {
                            this.classList.remove("adicionado");
                            this.innerHTML = textoOriginal;
                        },
                        this.classList.contains("btn-carrinho-card") ? 1200 : 1500
                    );
                };
        }
    );
}


/* =====================================================
   ATIVAR TODOS OS BOTÕES DOS CARDS
===================================================== */

function ativarBotoesCards() {

    ativarTrocaTamanho();

    ativarFavoritos();

    ativarCarrinho();

}


/* =====================================================
   CARROSSÉIS
===================================================== */

function ativarCarrosseis() {

    document
    .querySelectorAll(
        ".secao-carrossel"
    )
    .forEach(
        secao => {

            const carrossel =
                secao.querySelector(
                    ".carrossel-produtos"
                );


            const esquerda =
                secao.querySelector(
                    ".seta-carrossel.esquerda"
                );


            const direita =
                secao.querySelector(
                    ".seta-carrossel.direita"
                );


            if (
                !carrossel
                ||
                !esquerda
                ||
                !direita
            ) {

                return;

            }


            esquerda.onclick =
                function() {

                    carrossel.scrollBy({

                        left: -650,

                        behavior: "smooth"

                    });

                };


            direita.onclick =
                function() {

                    carrossel.scrollBy({

                        left: 650,

                        behavior: "smooth"

                    });

                };

        }
    );

}


/* =====================================================
   EVENTOS DOS FILTROS
===================================================== */

document
.querySelectorAll(
    ".filtros-produtos input"
)
.forEach(
    input => {

        input.addEventListener(
            "change",
            renderizarProdutos
        );

    }
);


/* PESQUISA */

if (pesquisa) {

    pesquisa.addEventListener(
        "input",
        renderizarProdutos
    );

}


/* ORDENAÇÃO */

if (ordenar) {

    ordenar.addEventListener(
        "change",
        renderizarProdutos
    );

}


/* =====================================================
   LIMPAR FILTROS
===================================================== */

function limparFiltros() {

    /* CHECKBOX */

    document
    .querySelectorAll(
        ".filtros-produtos input[type='checkbox']"
    )
    .forEach(
        input => {

            input.checked =
                false;

        }
    );


    /* PREÇO */

    const precoTodos =
        document.querySelector(
            ".filtro-preco[value='todos']"
        );


    if (precoTodos) {

        precoTodos.checked =
            true;

    }


    /* PESQUISA */

    if (pesquisa) {

        pesquisa.value =
            "";

    }


    /* ORDENAÇÃO */

    if (ordenar) {

        ordenar.value =
            "destaque";

    }


    renderizarProdutos();

}


/* BOTÃO LATERAL */

const botaoLimpar =
    document.getElementById(
        "limparFiltros"
    );


if (botaoLimpar) {

    botaoLimpar.addEventListener(
        "click",
        limparFiltros
    );

}


/* BOTÃO NENHUM RESULTADO */

const botaoLimparResultado =
    document.getElementById(
        "limparFiltrosResultado"
    );


if (botaoLimparResultado) {

    botaoLimparResultado.addEventListener(
        "click",
        limparFiltros
    );

}


/* =====================================================
   ABRIR / FECHAR GRUPOS DO FILTRO
===================================================== */

document
.querySelectorAll(
    ".titulo-filtro"
)
.forEach(
    titulo => {

        titulo.addEventListener(
            "click",
            function() {

                const grupo =
                    this.parentElement;


                grupo.classList.toggle(
                    "fechado"
                );


                const icone =
                    this.querySelector(
                        "i"
                    );


                if (!icone) {

                    return;

                }


                if (
                    grupo.classList.contains(
                        "fechado"
                    )
                ) {

                    icone.className =
                        "bi bi-chevron-down";

                }

                else {

                    icone.className =
                        "bi bi-chevron-up";

                }

            }
        );

    }
);


/* =====================================================
   FILTROS RECEBIDOS PELO MENU SUPERIOR
===================================================== */

const parametrosIniciais =
    new URLSearchParams(
        window.location.search
    );

const petInicial =
    parametrosIniciais.get("pet");

const buscaInicial =
    parametrosIniciais.get("busca");

let categoriaInicial =
    parametrosIniciais.get("categoria");


/*
 * Compatibilidade com links antigos.
 * Hoje o banco usa o slug `alimentacao`; versões anteriores
 * do menu enviavam `racao`.
 */
const aliasesCategoriaUrl = {
    racao: "alimentacao",
    racoes: "alimentacao",
    brinquedo: "brinquedos"
};


if (buscaInicial && pesquisa) {

    pesquisa.value =
        buscaInicial;

}


if (categoriaInicial) {

    categoriaInicial =
        aliasesCategoriaUrl[categoriaInicial]
        ||
        categoriaInicial;

}


if (petInicial) {

    document
        .querySelectorAll(".filtro-pet")
        .forEach(
            input => {

                if (
                    input.value === petInicial
                ) {
                    input.checked = true;
                }

            }
        );

}


if (categoriaInicial) {

    document
        .querySelectorAll(".filtro-categoria")
        .forEach(
            input => {

                if (
                    input.value === categoriaInicial
                ) {
                    input.checked = true;
                }

            }
        );

}


/*
 * Quando Cachorro ou Gato está marcado, categorias exclusivas
 * do outro pet ficam escondidas. Categorias "ambos" continuam.
 */
function atualizarCategoriasDisponiveis() {

    const petsMarcados = [
        ...document.querySelectorAll(
            ".filtro-pet:checked"
        )
    ].map(
        input => input.value
    );


    document
        .querySelectorAll(".opcao-categoria")
        .forEach(
            label => {

                const tipoCategoria =
                    label.dataset.petCategoria
                    ||
                    "ambos";


                const deveExibir =
                    petsMarcados.length === 0
                    ||
                    tipoCategoria === "ambos"
                    ||
                    petsMarcados.includes(
                        tipoCategoria
                    );


                label.style.display =
                    deveExibir
                    ? ""
                    : "none";


                if (!deveExibir) {

                    const checkbox =
                        label.querySelector(
                            ".filtro-categoria"
                        );


                    if (checkbox) {
                        checkbox.checked = false;
                    }

                }

            }
        );

}


document
    .querySelectorAll(".filtro-pet")
    .forEach(
        input => {

            input.addEventListener(
                "change",
                atualizarCategoriasDisponiveis
            );

        }
    );


atualizarCategoriasDisponiveis();


/* =====================================================
   INICIAR PÁGINA
===================================================== */

renderizarProdutos();

ativarCarrosseis();

</script>








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

                <a href="https://wa.me/5519994004101"><i class="bi bi-whatsapp"></i></a>

            </div>

        </div>

        <!-- Navegação -->
        <div class="footer-col">

            <h3>Institucional</h3>

            <ul>

                <li><a href="menu.html">Página Inicial</a></li>
                <li><a href="loja.html">Loja</a></li>
                <li><a href="#">Promoções</a></li>
                <li><a href="blog.html">Blog</a></li>

            </ul>

        </div>

        <!-- Categorias -->
        <div class="footer-col">

            <h3>Categorias</h3>

            <ul>

                <li><a href="#">Cachorros</a></li>
                <li><a href="#">Gatos</a></li>
                <li><a href="#">Rações</a></li>
                <li><a href="#">Brinquedos</a></li>
                <li><a href="#">Acessórios</a></li>

            </ul>

        </div>

        <!-- Contato -->
        <div class="footer-col">

            <h3>Atendimento</h3>

            <p>📞 (19) 99400-4101</p>

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


<script src="../js/acessibilidade.js" defer></script>
</body>
</html>