<?php
require_once __DIR__ . '/conexao.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

function hCarrinho($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

$usuarioLogado = !empty($_SESSION['usuario_id']);
$nomeUsuario = trim((string)($_SESSION['usuario_nome'] ?? ''));

// Sincroniza o carrinho do navegador com a sessão PHP e recalcula preços usando o banco.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'sincronizar') {
    header('Content-Type: application/json; charset=utf-8');
    $dados = json_decode((string)($_POST['carrinho'] ?? '[]'), true);
    if (!is_array($dados)) { $dados = []; }

    $sessao = [];
    try {
        $produtoStmt = $conexao->prepare(
            'SELECT p.id_produto, p.nome, p.imagem, p.preco,
                    p.promocao, p.desconto_percentual, p.ativo,
                    m.nome AS marca
             FROM produtos_destaque p
             LEFT JOIN marcas m ON m.id_marca = p.id_marca
             WHERE p.id_produto = ? AND p.ativo = 1 LIMIT 1'
        );
        $variacaoStmt = $conexao->prepare(
            'SELECT v.id_variacao, v.nome, v.preco, v.imagem,
                    v.promocao, v.desconto_percentual
             FROM variacoes_produto v
             WHERE v.id_variacao = ? AND v.id_produto = ? LIMIT 1'
        );
        $planoStmt = $conexao->prepare(
            'SELECT id_plano, nome, preco, descricao
             FROM planos_saude
             WHERE id_plano = ? AND ativo = 1 LIMIT 1'
        );

        foreach ($dados as $item) {
            $tipoItem = strtolower(trim((string)($item['tipoItem'] ?? 'produto')));
            $idPlano = (int)($item['idPlano'] ?? 0);
            $idProduto = (int)($item['idProduto'] ?? 0);
            $idVariacao = (int)($item['idVariacao'] ?? 0);
            $quantidade = max(1, min(99, (int)($item['quantidade'] ?? 1)));

            // Os planos usam id_plano, não id_produto. Valida o plano no banco.
            if ($tipoItem === 'plano' && $idPlano > 0) {
                $planoStmt->bind_param('i', $idPlano);
                $planoStmt->execute();
                $plano = $planoStmt->get_result()->fetch_assoc();
                if (!$plano) continue;

                $precoPlano = (float)$plano['preco'];
                $chavePlano = 'plano:' . $idPlano;
                $sessao[$chavePlano] = [
                    'chave' => $chavePlano,
                    'tipoItem' => 'plano',
                    'idPlano' => $idPlano,
                    'idProduto' => 0,
                    'idVariacao' => 0,
                    'nome' => (string)$plano['nome'],
                    'variacao' => 'Mensal',
                    'marca' => 'Garras & Patas',
                    'categoria' => 'plano de saúde',
                    'imagem' => (string)($item['imagem'] ?? '../imagens/logo.png'),
                    'precoOriginal' => $precoPlano,
                    'precoUnitario' => $precoPlano,
                    'descontoPercentual' => 0,
                    'quantidade' => 1
                ];
                continue;
            }

            if ($idProduto <= 0) continue;

            $produtoStmt->bind_param('i', $idProduto);
            $produtoStmt->execute();
            $produto = $produtoStmt->get_result()->fetch_assoc();
            if (!$produto) continue;

            $precoOriginal = (float)$produto['preco'];
            $preco = $precoOriginal;
            $imagem = (string)$produto['imagem'];
            $variacaoNome = '';

            if ($idVariacao > 0) {
                $variacaoStmt->bind_param('ii', $idVariacao, $idProduto);
                $variacaoStmt->execute();
                $variacao = $variacaoStmt->get_result()->fetch_assoc();

                // A variação precisa pertencer ao produto.
                // Quando existir, ela passa a ser a fonte oficial de preço/nome/imagem.
                if ($variacao) {
                    $precoOriginal = (float)$variacao['preco'];
                    $preco = $precoOriginal;
                    $variacaoNome = (string)$variacao['nome'];
                    if (!empty($variacao['imagem'])) {
                        $imagem = (string)$variacao['imagem'];
                    }
                } else {
                    // Não aceita uma variação inexistente/pertencente a outro produto.
                    $idVariacao = 0;
                }
            }

            // O preço promocional é calculado novamente no servidor.
            // Quando o item for uma variação, a promoção dela é independente.
            if ($idVariacao > 0 && !empty($variacao)) {
                $promocaoVariacao = (int)($variacao['promocao'] ?? 0) === 1;
                $desconto = $promocaoVariacao
                    ? min(99.99, max(0, (float)($variacao['desconto_percentual'] ?? 0)))
                    : 0.0;
            } else {
                $promocaoProduto = (int)($produto['promocao'] ?? 0) === 1;
                $desconto = $promocaoProduto
                    ? min(99.99, max(0, (float)($produto['desconto_percentual'] ?? 0)))
                    : 0.0;
            }

            $preco = $desconto > 0
                ? round($precoOriginal * (1 - ($desconto / 100)), 2)
                : $precoOriginal;

            $chave = $idProduto . ':' . $idVariacao;
            if (isset($sessao[$chave])) {
                $sessao[$chave]['quantidade'] = min(99, $sessao[$chave]['quantidade'] + $quantidade);
            } else {
                $sessao[$chave] = [
                    'chave' => $chave,
                    'tipoItem' => 'produto',
                    'idPlano' => 0,
                    'idProduto' => $idProduto,
                    'idVariacao' => $idVariacao,
                    'nome' => (string)$produto['nome'],
                    'variacao' => $variacaoNome,
                    'marca' => (string)($produto['marca'] ?? ''),
                    'imagem' => $imagem,
                    'precoOriginal' => $precoOriginal,
                    'precoUnitario' => $preco,
                    'descontoPercentual' => $desconto,
                    'quantidade' => $quantidade
                ];
            }
        }
        $_SESSION['carrinho'] = array_values($sessao);
        echo json_encode(['ok' => true, 'carrinho' => $_SESSION['carrinho'], 'logado' => $usuarioLogado]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensagem' => 'Não foi possível sincronizar o carrinho.']);
    }
    exit;
}


// Gera uma mensagem de pedido e o link para o WhatsApp da loja.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'finalizar_whatsapp') {
    header('Content-Type: application/json; charset=utf-8');

    if (!$usuarioLogado) {
        echo json_encode([
            'ok' => false,
            'codigo' => 'nao_logado',
            'mensagem' => 'Para finalizar o pedido, entre ou cadastre-se em sua conta.'
        ]);
        exit;
    }

    $dados = json_decode((string)($_POST['carrinho'] ?? '[]'), true);
    if (!is_array($dados) || !$dados) {
        echo json_encode([
            'ok' => false,
            'codigo' => 'carrinho_vazio',
            'mensagem' => 'Seu carrinho está vazio.'
        ]);
        exit;
    }

    try {
        $idUsuario = (int)$_SESSION['usuario_id'];

        // Endereço cadastrado pelo cliente.
        $enderecoStmt = $conexao->prepare(
            'SELECT cep, rua, numero, complemento, bairro, cidade, estado
             FROM enderecos_usuario
             WHERE id_usuario = ? LIMIT 1'
        );
        $enderecoStmt->bind_param('i', $idUsuario);
        $enderecoStmt->execute();
        $endereco = $enderecoStmt->get_result()->fetch_assoc();

        if (!$endereco || trim((string)($endereco['rua'] ?? '')) === '' ||
            trim((string)($endereco['numero'] ?? '')) === '' ||
            trim((string)($endereco['bairro'] ?? '')) === '' ||
            trim((string)($endereco['cidade'] ?? '')) === '' ||
            trim((string)($endereco['estado'] ?? '')) === '') {
            echo json_encode([
                'ok' => false,
                'codigo' => 'sem_endereco',
                'mensagem' => 'Cadastre seu endereço antes de finalizar o pedido.'
            ]);
            exit;
        }

        // Nome atualizado diretamente do cadastro do cliente.
        $usuarioStmt = $conexao->prepare('SELECT nome FROM usuarios WHERE id_usuario = ? LIMIT 1');
        $usuarioStmt->bind_param('i', $idUsuario);
        $usuarioStmt->execute();
        $usuario = $usuarioStmt->get_result()->fetch_assoc();
        $nomeCliente = trim((string)($usuario['nome'] ?? $nomeUsuario));

        $produtoStmt = $conexao->prepare(
            'SELECT p.id_produto, p.nome, p.imagem, p.preco, p.ativo,
                    m.nome AS marca
             FROM produtos_destaque p
             LEFT JOIN marcas m ON m.id_marca = p.id_marca
             WHERE p.id_produto = ? AND p.ativo = 1 LIMIT 1'
        );
        $variacaoStmt = $conexao->prepare(
            'SELECT v.id_variacao, v.nome, v.preco, v.promocao, v.desconto_percentual, v.imagem
             FROM variacoes_produto v
             WHERE v.id_variacao = ? AND v.id_produto = ? LIMIT 1'
        );
        $planoStmt = $conexao->prepare(
            'SELECT id_plano, nome, preco, descricao
             FROM planos_saude
             WHERE id_plano = ? AND ativo = 1 LIMIT 1'
        );

        $itens = [];
        $subtotalOriginal = 0.0;
        $subtotalFinal = 0.0;
        $descontoTotal = 0.0;
        $subtotalProdutosFrete = 0.0;

        foreach ($dados as $item) {
            $tipoItem = strtolower(trim((string)($item['tipoItem'] ?? 'produto')));
            $idPlano = (int)($item['idPlano'] ?? 0);
            $idProduto = (int)($item['idProduto'] ?? 0);
            $idVariacao = (int)($item['idVariacao'] ?? 0);
            $quantidade = max(1, min(99, (int)($item['quantidade'] ?? 1)));

            // Uma assinatura é sempre uma unidade mensal e não recebe desconto de produto.
            if ($tipoItem === 'plano' && $idPlano > 0) {
                $planoStmt->bind_param('i', $idPlano);
                $planoStmt->execute();
                $plano = $planoStmt->get_result()->fetch_assoc();
                if (!$plano) continue;

                $precoPlano = (float)$plano['preco'];
                $subtotalOriginal += $precoPlano;
                $subtotalFinal += $precoPlano;
                $itens[] = [
                    'tipoItem' => 'plano',
                    'nome' => (string)$plano['nome'],
                    'variacao' => 'Assinatura mensal',
                    'quantidade' => 1,
                    'precoOriginal' => $precoPlano,
                    'precoUnitario' => $precoPlano,
                    'descontoPercentual' => 0.0,
                    'descontoItem' => 0.0,
                    'totalItem' => $precoPlano
                ];
                continue;
            }

            if ($idProduto <= 0) {
                continue;
            }

            $produtoStmt->bind_param('i', $idProduto);
            $produtoStmt->execute();
            $produto = $produtoStmt->get_result()->fetch_assoc();
            if (!$produto) {
                continue;
            }

            $precoOriginal = (float)$produto['preco'];
            $precoUnitario = $precoOriginal;
            $variacaoNome = '';

            if ($idVariacao > 0) {
                $variacaoStmt->bind_param('ii', $idVariacao, $idProduto);
                $variacaoStmt->execute();
                $variacao = $variacaoStmt->get_result()->fetch_assoc();

                if (!$variacao) {
                    continue;
                }

                $precoOriginal = (float)$variacao['preco'];
                $variacaoNome = trim((string)$variacao['nome']);

                $temPromocao = (int)($variacao['promocao'] ?? 0) === 1;
                $descontoPercentual = $temPromocao
                    ? min(99.99, max(0.0, (float)($variacao['desconto_percentual'] ?? 0)))
                    : 0.0;

                $precoUnitario = $descontoPercentual > 0
                    ? round($precoOriginal * (1 - ($descontoPercentual / 100)), 2)
                    : $precoOriginal;
            } else {
                // As promoções do site pertencem às variações.
                $descontoPercentual = 0.0;
            }

            $subtotalItemOriginal = round($precoOriginal * $quantidade, 2);
            $subtotalItemFinal = round($precoUnitario * $quantidade, 2);
            $descontoItem = round(max(0, $subtotalItemOriginal - $subtotalItemFinal), 2);

            $subtotalOriginal += $subtotalItemOriginal;
            $subtotalFinal += $subtotalItemFinal;
            $descontoTotal += $descontoItem;
            $subtotalProdutosFrete += $subtotalItemFinal;

            $itens[] = [
                'tipoItem' => 'produto',
                'nome' => (string)$produto['nome'],
                'variacao' => $variacaoNome,
                'quantidade' => $quantidade,
                'precoOriginal' => $precoOriginal,
                'precoUnitario' => $precoUnitario,
                'descontoPercentual' => $descontoPercentual,
                'descontoItem' => $descontoItem,
                'totalItem' => $subtotalItemFinal
            ];
        }

        if (!$itens) {
            echo json_encode([
                'ok' => false,
                'codigo' => 'carrinho_vazio',
                'mensagem' => 'Não foi possível validar os produtos do carrinho.'
            ]);
            exit;
        }

        $subtotalOriginal = round($subtotalOriginal, 2);
        $subtotalFinal = round($subtotalFinal, 2);
        $descontoTotal = round($descontoTotal, 2);
        $subtotalProdutosFrete = round($subtotalProdutosFrete, 2);
        $freteGratis = $subtotalProdutosFrete >= 99.0;
        $freteNaoAplicavel = $subtotalProdutosFrete <= 0.0;

        // A mensalidade do plano não conta para a regra de frete dos produtos.
        $freteTexto = $freteNaoAplicavel ? 'Não se aplica ao plano' : ($freteGratis ? 'Grátis' : 'A calcular');

        // WhatsApp cadastrado na loja ativa.
        $lojaStmt = $conexao->prepare(
            "SELECT whatsapp
             FROM lojas
             WHERE ativo = 1 AND whatsapp IS NOT NULL AND TRIM(whatsapp) <> ''
             ORDER BY ordem ASC, id_loja ASC
             LIMIT 1"
        );
        $lojaStmt->execute();
        $loja = $lojaStmt->get_result()->fetch_assoc();
        $whatsapp = preg_replace('/\D+/', '', (string)($loja['whatsapp'] ?? ''));

        if ($whatsapp === '') {
            echo json_encode([
                'ok' => false,
                'codigo' => 'sem_whatsapp',
                'mensagem' => 'O WhatsApp da loja ainda não foi cadastrado.'
            ]);
            exit;
        }

        if (strlen($whatsapp) === 10 || strlen($whatsapp) === 11) {
            $whatsapp = '55' . $whatsapp;
        }

        $mensagem = "Olá, Garras & Patas! Gostaria de finalizar meu pedido.\n\n";
        $mensagem .= "Cliente: " . ($nomeCliente !== '' ? $nomeCliente : 'Não informado') . "\n\n";
        $mensagem .= "*Produtos:*\n";

        foreach ($itens as $indice => $item) {
            $mensagem .= ($indice + 1) . ". " . $item['nome'];
            if ($item['variacao'] !== '') {
                $mensagem .= " — " . $item['variacao'];
            }
            $mensagem .= "\n";
            $mensagem .= "Quantidade: " . $item['quantidade'] . "\n";
            $mensagem .= "Preço original: R$ " . number_format($item['precoOriginal'], 2, ',', '.') . "\n";

            if ($item['descontoPercentual'] > 0 && $item['descontoItem'] > 0) {
                $percentual = rtrim(rtrim(number_format($item['descontoPercentual'], 2, ',', '.'), '0'), ',');
                $mensagem .= "Desconto: " . $percentual . "% (-R$ " . number_format($item['descontoItem'], 2, ',', '.') . ")\n";
            } else {
                $mensagem .= "Desconto: sem desconto\n";
            }

            $mensagem .= "Preço final: R$ " . number_format($item['precoUnitario'], 2, ',', '.') . "\n";
            $mensagem .= "Total do item: R$ " . number_format($item['totalItem'], 2, ',', '.') . "\n\n";
        }

        $mensagem .= "*Resumo:*\n";
        $mensagem .= "Subtotal sem descontos: R$ " . number_format($subtotalOriginal, 2, ',', '.') . "\n";
        $mensagem .= "Descontos: -R$ " . number_format($descontoTotal, 2, ',', '.') . "\n";
        $mensagem .= "Subtotal com descontos: R$ " . number_format($subtotalFinal, 2, ',', '.') . "\n";
        $mensagem .= "Frete: " . $freteTexto . "\n";
        if ($freteNaoAplicavel) {
            $mensagem .= "Total do pedido: R$ " . number_format($subtotalFinal, 2, ',', '.') . "\n\n";
        } elseif ($freteGratis) {
            $mensagem .= "Total do pedido: R$ " . number_format($subtotalFinal, 2, ',', '.') . "\n\n";
        } else {
            $mensagem .= "Total dos itens: R$ " . number_format($subtotalFinal, 2, ',', '.') . "\nTotal final: a definir com o frete.\n\n";
        }

        $mensagem .= "*Endereço de entrega:*\n";
        $mensagem .= $endereco['rua'] . ", " . $endereco['numero'];
        if (trim((string)$endereco['complemento']) !== '') {
            $mensagem .= " — " . $endereco['complemento'];
        }
        $mensagem .= "\n" . $endereco['bairro'];
        $mensagem .= "\n" . ($endereco['cep'] !== '' ? 'CEP: ' . $endereco['cep'] . ' — ' : '');
        $mensagem .= $endereco['cidade'] . '/' . strtoupper($endereco['estado']) . "\n";

        $url = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($mensagem);

        echo json_encode([
            'ok' => true,
            'url' => $url,
            'mensagem' => 'Pedido preparado. Abrindo o WhatsApp...'
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'codigo' => 'erro_servidor',
            'mensagem' => 'Não foi possível preparar o pedido para o WhatsApp.'
        ]);
    }
    exit;
}

$carrinhoSessao = $_SESSION['carrinho'] ?? [];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Carrinho - Garras & Patas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #fff;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
        }

        .topo {
            width: 100%;
            min-height: 92px;
            display: flex;
            align-items: center;
            padding: 15px 6%;
            border-bottom: 1px solid #eee;
        }

        .logo img {
            width: 145px;
            max-height: 70px;
            object-fit: contain;
        }

        .espaco-topo {
            flex: 1;
        }

        .icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icons a {
            color: #333;
            text-decoration: none;
        }

        .user {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user i {
            font-size: 25px;
        }

        .user span {
            font-size: 13px;
        }

        .carrinho-pagina {
            width: min(1120px, calc(100% - 40px));
            margin: 38px auto 60px;
        }

        .carrinho-titulo {
            margin-bottom: 25px;
        }

        .carrinho-titulo h1 {
            margin: 0;
            font-size: 30px;
        }

        .carrinho-titulo p {
            margin: 7px 0 0;
            color: #777;
        }

        .carrinho-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 25px;
            align-items: start;
        }

        .carrinho-lista {
            border: 1px solid #eee;
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
        }

        .item-carrinho {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            padding: 18px;
            border-bottom: 1px solid #eee;
        }

        .item-carrinho:last-child {
            border-bottom: 0;
        }

        .item-imagem {
            width: 110px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-imagem img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .item-info h2 {
            margin: 0 0 5px;
            font-size: 17px;
        }

        .item-marca {
            color: #777;
            font-size: 12px;
        }

        .item-variacao {
            margin-top: 5px;
            color: #666;
            font-size: 13px;
        }

        .item-preco {
            margin-top: 9px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .item-preco-antigo {
            color: #888;
            font-size: 13px;
            font-weight: 500;
            text-decoration: line-through;
        }

        .item-preco-atual {
            color: #222;
            font-size: 17px;
            font-weight: 700;
        }

        .item-desconto {
            display: inline-block;
            margin-top: 3px;
            padding: 3px 7px;
            border-radius: 6px;
            background: #e74c3c;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
        }

        .item-acoes {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .quantidade-controle {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 9px;
            overflow: hidden;
        }

        .quantidade-controle button {
            width: 32px;
            height: 34px;
            border: 0;
            background: #f7f7f7;
            cursor: pointer;
            font-size: 17px;
        }

        .quantidade-controle span {
            min-width: 32px;
            text-align: center;
            font-weight: 700;
        }

        .btn-remover {
            border: 0;
            background: transparent;
            color: #777;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-remover:hover {
            color: #c33;
        }

        .resumo-carrinho {
            position: sticky;
            top: 20px;
            padding: 22px;
            border: 1px solid #eee;
            border-radius: 15px;
            background: #fff;
        }

        .resumo-carrinho h2 {
            margin: 0 0 20px;
            font-size: 20px;
        }

        .resumo-linha {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 12px;
            color: #666;
        }

        .resumo-total {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #eee;
            font-size: 20px;
            font-weight: 800;
        }

        .btn-finalizar {
            width: 100%;
            margin-top: 20px;
            padding: 14px 18px;
            border: 0;
            border-radius: 10px;
            background: #1AA5A5;
            color: #fff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
        }

        .btn-finalizar:hover {
            opacity: .92;
        }

        .btn-continuar {
            display: block;
            margin-top: 14px;
            color: #1AA5A5;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .btn-limpar {
            width: 100%;
            margin-top: 14px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 9px;
            background: #fff;
            color: #777;
            cursor: pointer;
        }

        .carrinho-vazio {
            padding: 55px 25px;
            text-align: center;
        }

        .carrinho-vazio i {
            display: block;
            margin-bottom: 12px;
            font-size: 48px;
        }

        .carrinho-vazio h2 {
            margin: 0 0 8px;
        }

        .carrinho-vazio p {
            margin: 0 0 20px;
            color: #777;
        }

        .carrinho-vazio a {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 9px;
            background: #1AA5A5;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }

        .mensagem-carrinho {
            margin-top: 15px;
            color: #1AA5A5;
            font-size: 13px;
            text-align: center;
        }

        .footer {
            border-top: 1px solid #eee;
            padding: 30px 6%;
            color: #666;
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 850px) {
            .carrinho-layout {
                grid-template-columns: 1fr;
            }

            .resumo-carrinho {
                position: static;
            }
        }

        @media (max-width: 620px) {
            .carrinho-pagina {
                width: min(100% - 24px, 1120px);
                margin-top: 25px;
            }

            .item-carrinho {
                grid-template-columns: 80px minmax(0, 1fr);
            }

            .item-imagem {
                width: 80px;
                height: 80px;
            }

            .item-acoes {
                grid-column: 2;
            }

            .topo {
                padding-left: 20px;
                padding-right: 20px;
            }

            .logo img {
                width: 120px;
            }

            .user span {
                display: none;
            }
        }
    

/* =========================================================
   RESPONSIVIDADE EXTRA DO CARRINHO
========================================================= */
html, body { max-width: 100%; overflow-x: hidden; }

@media (max-width: 760px) {
    .topo {
        min-height: 76px;
        padding: 12px 18px;
    }
    .logo img { width: 125px; max-width: 125px; }
    .carrinho-pagina { width: min(100% - 24px, 1120px); margin-top: 25px; }
    .carrinho-titulo h1 { font-size: 27px; }
    .carrinho-layout { gap: 16px; }
    .resumo-carrinho { padding: 18px; }
}

@media (max-width: 500px) {
    .topo { padding-left: 12px; padding-right: 12px; }
    .logo img { width: 108px; max-width: 108px; }
    .item-carrinho {
        grid-template-columns: 72px minmax(0, 1fr);
        gap: 12px;
        padding: 15px 12px;
    }
    .item-imagem { width: 72px; height: 72px; }
    .item-info h2 { font-size: 15px; overflow-wrap: anywhere; }
    .item-acoes {
        grid-column: 1 / -1;
        width: 100%;
        justify-content: space-between;
    }
    .item-preco-atual { font-size: 16px; }
    .resumo-carrinho h2 { font-size: 19px; }
    .resumo-linha, .resumo-total { font-size: 14px; }
    .resumo-total { font-size: 18px; }
    .btn-finalizar { min-height: 48px; }
}

@media (max-width: 360px) {
    .item-carrinho { grid-template-columns: 62px minmax(0, 1fr); }
    .item-imagem { width: 62px; height: 62px; }
    .quantidade-controle button { width: 38px; }
    .quantidade-controle span { min-width: 34px; }
}

</style>

    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>

<body>
<a class="skip-link" href="#conteudoPrincipal">Pular para o conteúdo principal</a>


    <header class="topo">

        <div class="logo">
            <a href="menu.php">
                <img src="../imagens/logo.png" alt="Garras & Patas Pet Shop">
            </a>
        </div>

        <div class="espaco-topo"></div>

        <div class="icons">

            <div class="user">

                <a href="carrinho.php" aria-label="Meu carrinho">

                    <i class="bi bi-cart"></i>

                    <span>Meu carrinho</span>

                </a>

            </div>

        </div>

    </header>


    <main id="conteudoPrincipal" tabindex="-1" class="carrinho-pagina">

        <div class="carrinho-titulo">

            <h1>Meu Carrinho</h1>

            <p>Confira seus produtos antes de finalizar a compra.</p>

        </div>


        <div id="carrinhoConteudo"></div>

    </main>


    <footer class="footer">

        © 2026 Garras & Patas - Todos os direitos reservados.

    </footer>


<script>

const CHAVE_CARRINHO =
    "garras_patas_carrinho_v2";


function lerCarrinho() {

    try {

        const salvo =
            JSON.parse(
                localStorage.getItem(
                    CHAVE_CARRINHO
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


function salvarCarrinho(carrinho) {

    localStorage.setItem(
        CHAVE_CARRINHO,
        JSON.stringify(carrinho)
    );

}


function moeda(valor) {

    return Number(valor || 0)
        .toLocaleString(
            "pt-BR",
            {
                style: "currency",
                currency: "BRL"
            }
        );

}


function escaparHtml(valor) {

    const div =
        document.createElement("div");

    div.textContent =
        String(valor ?? "");

    return div.innerHTML;

}


function quantidadeValida(valor) {

    const numero =
        parseInt(
            valor,
            10
        );

    if (
        !Number.isFinite(numero)
        ||
        numero < 1
    ) {
        return 1;
    }

    return Math.min(
        numero,
        99
    );

}


function renderizarCarrinho() {

    const carrinho =
        lerCarrinho();


    const conteudo =
        document.getElementById(
            "carrinhoConteudo"
        );


    if (!carrinho.length) {

        conteudo.innerHTML = `

            <div class="carrinho-lista">

                <div class="carrinho-vazio">

                    <i class="bi bi-cart-x"></i>

                    <h2>Seu carrinho está vazio</h2>

                    <p>
                        Adicione produtos para vê-los aqui.
                    </p>

                    <a href="aba_produto.php">
                        Ver produtos
                    </a>

                </div>

            </div>

        `;

        return;

    }


    let subtotal = 0;
    let quantidadeTotal = 0;
    let subtotalProdutosFrete = 0;
    let possuiPlano = false;


    const itensHtml =
        carrinho
        .map(
            (item, indice) => {

                const ehPlano =
                    String(item.tipoItem || "").toLowerCase() === "plano"
                    && Number(item.idPlano || 0) > 0;
                const quantidade = ehPlano ? 1 : quantidadeValida(item.quantidade);


                const preco =
                    Number(
                        item.precoUnitario
                    )
                    ||
                    0;

                const precoOriginal =
                    Number(item.precoOriginal) || preco;

                const descontoPercentual =
                    Number(item.descontoPercentual) || 0;

                const temDesconto =
                    precoOriginal > preco && descontoPercentual > 0;

                const blocoPreco = temDesconto
                    ? `
                        <span class="item-preco-antigo">${moeda(precoOriginal)}</span>
                        <strong class="item-preco-atual">${moeda(preco)}</strong>
                        <span class="item-desconto">-${String(descontoPercentual).replace('.', ',').replace(/,00$/, '')}%</span>
                    `
                    : `
                        <strong class="item-preco-atual">${moeda(preco)}</strong>
                    `;


                const totalItem =
                    preco
                    *
                    quantidade;


                subtotal += totalItem;
                if (ehPlano) {
                    possuiPlano = true;
                } else {
                    subtotalProdutosFrete += totalItem;
                }

                quantidadeTotal += quantidade;


                const variacao =
                    item.variacao
                    ? `
                        <div class="item-variacao">
                            Variação:
                            ${escaparHtml(item.variacao)}
                        </div>
                    `
                    : "";


                return `

                    <article class="item-carrinho">

                        <div class="item-imagem">

                            <img
                                src="${escaparHtml(item.imagem)}"
                                alt="${escaparHtml(item.nome)}"
                            >

                        </div>


                        <div class="item-info">

                            <h2>
                                ${escaparHtml(item.nome)}
                            </h2>

                            <div class="item-marca">
                                ${escaparHtml(item.marca || "")}
                            </div>

                            ${variacao}

                            <div class="item-preco">
                                ${blocoPreco}
                            </div>

                        </div>


                        <div class="item-acoes">

                            ${ehPlano ? '<span class="item-variacao">Assinatura mensal</span>' : `
                            <div class="quantidade-controle">
                                <button type="button" aria-label="Diminuir quantidade de ${escaparHtml(item.nome)}" onclick="alterarQuantidade(${indice}, -1)">−</button>
                                <span>${quantidade}</span>
                                <button type="button" aria-label="Aumentar quantidade de ${escaparHtml(item.nome)}" onclick="alterarQuantidade(${indice}, 1)">+</button>
                            </div>
                            `}

                            <button
                                class="btn-remover"
                                type="button"
                                aria-label="Remover produto"
                                onclick="removerItem(${indice})"
                            >
                                <i class="bi bi-trash3"></i>
                            </button>

                        </div>

                    </article>

                `;

            }
        )
        .join("");


    conteudo.innerHTML = `

        <div class="carrinho-layout">


            <section class="carrinho-lista">

                ${itensHtml}

            </section>


            <aside class="resumo-carrinho">

                <h2>Resumo da compra</h2>


                <div class="resumo-linha">

                    <span>
                        Produtos (${quantidadeTotal})
                    </span>

                    <strong>
                        ${moeda(subtotal)}
                    </strong>

                </div>


                <div class="resumo-linha">

                    <span>Frete</span>

                    <strong>
                        ${subtotalProdutosFrete <= 0 ? (possuiPlano ? "Não se aplica ao plano" : "A calcular") : (subtotalProdutosFrete >= 99 ? "Grátis" : "A calcular")}
                    </strong>

                </div>


                <div class="resumo-total">

                    <span>Total</span>

                    <span>
                        ${moeda(subtotal)}
                    </span>

                </div>


                <button
                    class="btn-finalizar"
                    type="button"
                    onclick="finalizarCompra()"
                >
                    Finalizar compra
                </button>


                <a
                    class="btn-continuar"
                    href="aba_produto.php"
                >
                    Continuar comprando
                </a>


                <button
                    class="btn-limpar"
                    type="button"
                    onclick="limparCarrinho()"
                >
                    Limpar carrinho
                </button>


                <div
                    class="mensagem-carrinho"
                    id="mensagemCarrinho" role="status" aria-live="polite" aria-atomic="true"
                ></div>

            </aside>

        </div>

    `;

}


function alterarQuantidade(indice, variacao) {

    const carrinho =
        lerCarrinho();


    if (!carrinho[indice]) {
        return;
    }
    if (String(carrinho[indice].tipoItem || "").toLowerCase() === "plano") {
        carrinho[indice].quantidade = 1;
        salvarCarrinho(carrinho);
        renderizarCarrinho();
        return;
    }


    const atual =
        quantidadeValida(
            carrinho[indice].quantidade
        );


    carrinho[indice].quantidade =
        quantidadeValida(
            atual + variacao
        );


    salvarCarrinho(
        carrinho
    );


    renderizarCarrinho();

}


function removerItem(indice) {

    const carrinho =
        lerCarrinho();


    if (!carrinho[indice]) {
        return;
    }


    carrinho.splice(
        indice,
        1
    );


    salvarCarrinho(
        carrinho
    );


    renderizarCarrinho();

}


function limparCarrinho() {

    const carrinho =
        lerCarrinho();


    if (!carrinho.length) {
        return;
    }


    if (
        !confirm(
            "Deseja realmente limpar o carrinho?"
        )
    ) {
        return;
    }


    localStorage.removeItem(
        CHAVE_CARRINHO
    );


    renderizarCarrinho();

}


async function sincronizarComPHP() {
    const carrinho = lerCarrinho();
    const dados = new URLSearchParams();
    dados.append("acao", "sincronizar");
    dados.append("carrinho", JSON.stringify(carrinho));

    try {
        const resposta = await fetch("carrinho.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: dados.toString()
        });
        const resultado = await resposta.json();
        if (resultado.ok && Array.isArray(resultado.carrinho)) {
            salvarCarrinho(resultado.carrinho);
            renderizarCarrinho();
        }
    } catch (erro) {
        console.warn("Carrinho PHP não pôde ser sincronizado.", erro);
    }
}

async function finalizarCompra() {
    let carrinho = lerCarrinho();
    const mensagem = document.getElementById("mensagemCarrinho");
    const estaLogado = <?php echo $usuarioLogado ? 'true' : 'false'; ?>;

    if (!carrinho.length) {
        mensagem.textContent = "Seu carrinho está vazio.";
        return;
    }

    if (!estaLogado) {
        mensagem.textContent = "Para finalizar o pedido, entre ou cadastre-se em sua conta.";
        setTimeout(function () { window.location.href = "form.php"; }, 900);
        return;
    }

    mensagem.textContent = "Preparando seu pedido...";

    // Sincroniza primeiro para atualizar o carrinho local com os dados validados pelo banco.
    await sincronizarComPHP();
    carrinho = lerCarrinho();

    if (!carrinho.length) {
        mensagem.textContent = "Não foi possível validar os itens do carrinho.";
        return;
    }

    try {
        const dados = new URLSearchParams();
        dados.append("acao", "finalizar_whatsapp");
        dados.append("carrinho", JSON.stringify(carrinho));

        const resposta = await fetch("carrinho.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: dados.toString()
        });

        const resultado = await resposta.json();

        if (resultado.codigo === "nao_logado") {
            mensagem.textContent = resultado.mensagem;
            setTimeout(function () { window.location.href = "form.php"; }, 900);
            return;
        }

        if (resultado.codigo === "sem_endereco") {
            mensagem.textContent = resultado.mensagem;
            setTimeout(function () { window.location.href = "area-cliente.php?aba=endereco"; }, 1200);
            return;
        }

        if (resultado.ok && resultado.url) {
            mensagem.textContent = resultado.mensagem || "Abrindo o WhatsApp...";
            window.location.href = resultado.url;
            return;
        }

        mensagem.textContent = resultado.mensagem || "Não foi possível finalizar o pedido.";
    } catch (erro) {
        console.error("Falha ao preparar o pedido para o WhatsApp.", erro);
        mensagem.textContent = "Não foi possível abrir o WhatsApp. Tente novamente.";
    }
}


window.addEventListener(
    "storage",
    renderizarCarrinho
);


window.addEventListener(
    "garras:carrinho-atualizado",
    renderizarCarrinho
);


document.addEventListener(
    "DOMContentLoaded",
    async function () {
        const carrinhoAntes = lerCarrinho();
        if (!carrinhoAntes.length) {
            renderizarCarrinho();
            return;
        }

        await sincronizarComPHP();
        renderizarCarrinho();
    }
);

</script>

<script src="../js/acessibilidade.js" defer></script>
</body>

</html>
