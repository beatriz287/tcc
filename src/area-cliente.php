<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

function e($valor): string { return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8'); }
function hCliente($valor): string { return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8'); }
function precoCliente($valor): string { return number_format((float)$valor, 2, ',', '.'); }
function redirectCliente(string $aba = 'inicio'): void {
    header('Location: area-cliente.php?aba=' . urlencode($aba));
    exit;
}

$idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
if ($idUsuario <= 0) {
    header('Location: form.php');
    exit;
}

if (empty($_SESSION['csrf_cliente'])) {
    $_SESSION['csrf_cliente'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_cliente'];

$usuarioStmt = $conexao->prepare('SELECT id_usuario, nome, email, ativo FROM usuarios WHERE id_usuario = ? LIMIT 1');
$usuarioStmt->bind_param('i', $idUsuario);
$usuarioStmt->execute();
$usuario = $usuarioStmt->get_result()->fetch_assoc();
if (!$usuario || (int)$usuario['ativo'] !== 1) {
    session_unset();
    session_destroy();
    header('Location: form.php');
    exit;
}

$aba = (string)($_GET['aba'] ?? 'inicio');
$abasPermitidas = ['inicio', 'pedidos', 'carrinho', 'favoritos', 'endereco'];
if (!in_array($aba, $abasPermitidas, true)) { $aba = 'inicio'; }

$mensagem = '';
$tipoMensagem = '';

/* =========================
   ENDEREÇO
========================= */
$enderecoStmt = $conexao->prepare('SELECT * FROM enderecos_usuario WHERE id_usuario = ? LIMIT 1');
$enderecoStmt->bind_param('i', $idUsuario);
$enderecoStmt->execute();
$endereco = $enderecoStmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('A sessão expirou. Atualize a página e tente novamente.');
        }

        $acao = (string)($_POST['acao'] ?? '');

        if ($acao === 'salvar_endereco') {
            $cep = trim((string)($_POST['cep'] ?? ''));
            $rua = trim((string)($_POST['rua'] ?? ''));
            $numero = trim((string)($_POST['numero'] ?? ''));
            $complemento = trim((string)($_POST['complemento'] ?? ''));
            $bairro = trim((string)($_POST['bairro'] ?? ''));
            $cidade = trim((string)($_POST['cidade'] ?? ''));
            $estado = strtoupper(trim((string)($_POST['estado'] ?? '')));

            if ($rua === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '') {
                throw new RuntimeException('Preencha rua, número, bairro, cidade e estado.');
            }
            if ($estado !== '' && strlen($estado) !== 2) {
                throw new RuntimeException('Informe o estado usando a sigla de 2 letras.');
            }

            // Salva ou atualiza o endereço do próprio usuário sem depender
            // da existência de uma chave UNIQUE em id_usuario.
            $buscaEnderecoStmt = $conexao->prepare(
                'SELECT id_endereco FROM enderecos_usuario WHERE id_usuario = ? LIMIT 1'
            );
            if (!$buscaEnderecoStmt) {
                throw new RuntimeException('Não foi possível preparar o salvamento do endereço.');
            }
            $buscaEnderecoStmt->bind_param('i', $idUsuario);
            if (!$buscaEnderecoStmt->execute()) {
                throw new RuntimeException('Não foi possível verificar o endereço cadastrado.');
            }
            $enderecoExistente = $buscaEnderecoStmt->get_result()->fetch_assoc();
            $buscaEnderecoStmt->close();

            if ($enderecoExistente) {
                $stmt = $conexao->prepare(
                    'UPDATE enderecos_usuario
                     SET cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, atualizado_em = CURRENT_TIMESTAMP
                     WHERE id_endereco = ? AND id_usuario = ?'
                );
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível preparar a atualização do endereço.');
                }
                $idEndereco = (int)$enderecoExistente['id_endereco'];
                $stmt->bind_param(
                    'sssssssii',
                    $cep, $rua, $numero, $complemento, $bairro, $cidade, $estado,
                    $idEndereco, $idUsuario
                );
            } else {
                $stmt = $conexao->prepare(
                    'INSERT INTO enderecos_usuario
                     (id_usuario, cep, rua, numero, complemento, bairro, cidade, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível preparar o cadastro do endereço.');
                }
                $stmt->bind_param(
                    'isssssss',
                    $idUsuario, $cep, $rua, $numero, $complemento, $bairro, $cidade, $estado
                );
            }

            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('Não foi possível salvar o endereço.');
            }
            $stmt->close();

            $mensagem = 'Endereço salvo com sucesso.';
            $tipoMensagem = 'sucesso';
            $aba = 'endereco';

            $enderecoStmt = $conexao->prepare('SELECT * FROM enderecos_usuario WHERE id_usuario = ? LIMIT 1');
            $enderecoStmt->bind_param('i', $idUsuario);
            $enderecoStmt->execute();
            $endereco = $enderecoStmt->get_result()->fetch_assoc();
        }

        if ($acao === 'excluir_endereco') {
            $stmt = $conexao->prepare('DELETE FROM enderecos_usuario WHERE id_usuario = ?');
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $endereco = null;
            $mensagem = 'Endereço removido.';
            $tipoMensagem = 'sucesso';
            $aba = 'endereco';
        }
    } catch (Throwable $e) {
        $mensagem = $e->getMessage();
        $tipoMensagem = 'erro';
    }
}

/* =========================
   PEDIDOS
========================= */
$pedidos = [];
$pedidosErro = false;
try {
    $stmt = $conexao->prepare(
        'SELECT id_pedido, codigo_pedido, status, total, data_pedido
         FROM pedidos
         WHERE id_usuario = ?
         ORDER BY data_pedido DESC, id_pedido DESC'
    );
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $pedidosErro = true;
}

/* =========================
   FAVORITOS
========================= */
$favoritos = [];
$favoritosErro = false;
try {
    $stmt = $conexao->prepare(
        'SELECT f.id_produto, p.nome, p.imagem, p.preco, p.promocao, p.desconto_percentual,
                m.nome AS marca_nome
         FROM favoritos f
         INNER JOIN produtos_destaque p ON p.id_produto = f.id_produto
         LEFT JOIN marcas m ON m.id_marca = p.id_marca
         WHERE f.id_usuario = ? AND p.ativo = 1
         ORDER BY f.data_cadastro DESC'
    );
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $favoritos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $favoritosErro = true;
}

$carrinho = $_SESSION['carrinho'] ?? [];
$totalItensCarrinho = 0;
$totalCarrinho = 0.0;
foreach ($carrinho as $item) {
    $qtd = max(1, (int)($item['quantidade'] ?? 1));
    $totalItensCarrinho += $qtd;
    $totalCarrinho += ((float)($item['precoUnitario'] ?? 0)) * $qtd;
}

$primeiroNome = trim(explode(' ', trim((string)$usuario['nome']))[0] ?? $usuario['nome']);

function statusPedidoCliente(string $status): string {
    $map = [
        'pendente' => 'Pendente',
        'pago' => 'Pago',
        'processando' => 'Em preparação',
        'enviado' => 'Enviado',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado'
    ];
    return $map[$status] ?? ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minha conta | Garras & Patas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../css/style-menu.css" rel="stylesheet">
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
<style>
body{margin:0;background:#fff;color:#333;font-family:Arial,Helvetica,sans-serif}.topo{width:100%;min-height:92px;display:flex;align-items:center;padding:15px 6%;border-bottom:1px solid #eee;box-sizing:border-box}.logo img{width:145px;max-height:70px;object-fit:contain}.espaco-topo{flex:1}.icons{display:flex;align-items:center;gap:20px}.icons a{color:#333;text-decoration:none}.user{display:flex;align-items:center;gap:8px}.user i{font-size:25px}.user span{font-size:13px}.conta{width:min(1120px,calc(100% - 40px));margin:42px auto 70px}.conta-titulo h1{margin:0;font-size:32px}.conta-titulo p{margin:8px 0 0;color:#777}.conta-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:25px;margin-top:30px}.conta-menu,.conta-card{border:1px solid #eee;border-radius:16px;background:#fff}.conta-menu{padding:10px}.conta-menu a{display:flex;align-items:center;gap:11px;padding:13px 14px;border-radius:11px;text-decoration:none;color:#333;font-weight:600}.conta-menu a:hover,.conta-menu a.ativo{background:#fff3e7;color:#df761d}.conta-menu .sair{color:#9b2c2c}.conta-conteudo{min-width:0}.conta-card{padding:24px;margin-bottom:18px}.conta-card h2{margin:0 0 8px;font-size:22px}.conta-card p.descricao{margin:0 0 20px;color:#777}.resumo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.resumo{padding:18px;border:1px solid #eee;border-radius:14px}.resumo i{font-size:24px;color:#e47c21}.resumo strong{display:block;margin-top:8px;font-size:23px}.resumo span{color:#777;font-size:13px}.lista-item{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 0;border-bottom:1px solid #eee}.lista-item:last-child{border-bottom:0}.produto-mini{display:flex;align-items:center;gap:14px}.produto-mini img{width:70px;height:65px;object-fit:contain}.produto-mini h3{margin:0 0 5px;font-size:16px}.produto-mini p{margin:0;color:#777;font-size:13px}.btn-conta{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:999px;padding:10px 16px;background:#f28b30;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}.btn-sec{background:#f1f2f4;color:#333}.btn-danger{background:#fff0ef;color:#a33}.vazio{padding:30px;text-align:center;color:#777}.form-endereco{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.campo.full{grid-column:1/-1}.campo label{display:block;margin-bottom:7px;font-weight:700;font-size:14px}.campo input{width:100%;padding:11px 12px;border:1px solid #d8dbe0;border-radius:11px;box-sizing:border-box}.acoes{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.pedido-status{padding:6px 10px;border-radius:999px;background:#f3f4f6;font-size:12px;font-weight:700}.erro-box{background:#fdeaea;color:#9b2c2c;border:1px solid #f7caca;padding:12px;border-radius:12px;margin-bottom:16px}.sucesso-box{background:#e8f7ef;color:#246b45;border:1px solid #c3ecd4;padding:12px;border-radius:12px;margin-bottom:16px}.preco-antigo{text-decoration:line-through;color:#999;margin-right:6px}.preco-atual{font-weight:800}.selo{color:#fff;background:#e74c3c;padding:4px 7px;border-radius:999px;font-size:11px;font-weight:800;margin-left:6px}@media(max-width:850px){.conta-layout{grid-template-columns:1fr}.resumo-grid{grid-template-columns:1fr}.form-endereco{grid-template-columns:1fr}.campo.full{grid-column:auto}}

        /* Responsividade complementar — sem alterar o layout desktop */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 850px) {
            .conta {
                width: min(100% - 24px, 1120px);
                margin-top: 28px;
                margin-bottom: 45px;
            }

            .conta-layout {
                gap: 16px;
            }

            .conta-menu {
                padding: 8px;
            }

            .conta-menu a {
                padding: 11px 12px;
            }
        }

        @media (max-width: 620px) {
            .topo {
                min-height: 76px;
                padding: 10px 16px;
            }

            .logo img {
                width: 112px;
                max-height: 58px;
            }

            .icons {
                margin-left: auto;
                gap: 10px;
            }

            .user span {
                display: none;
            }

            .conta {
                width: calc(100% - 24px);
                margin-top: 22px;
            }

            .conta-titulo h1 {
                font-size: 26px;
                line-height: 1.15;
            }

            .conta-titulo p {
                font-size: 14px;
                line-height: 1.45;
            }

            .conta-menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 4px;
            }

            .conta-menu a {
                min-width: 0;
                justify-content: flex-start;
                gap: 8px;
                padding: 10px 9px;
                font-size: 13px;
            }

            .conta-menu a span {
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .conta-card {
                padding: 16px;
                border-radius: 13px;
            }

            .conta-card h2 {
                font-size: 19px;
            }

            .resumo-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .resumo {
                padding: 14px;
            }

            .resumo strong {
                font-size: 20px;
            }

            .lista-item {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .produto-mini {
                width: 100%;
            }

            .produto-mini img {
                width: 62px;
                height: 58px;
            }

            .lista-item .btn-conta {
                width: 100%;
                justify-content: center;
            }

            .form-endereco {
                gap: 12px;
            }

            .campo input {
                font-size: 16px;
            }

            .acoes {
                display: grid;
                grid-template-columns: 1fr;
            }

            .acoes .btn-conta {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 390px) {
            .resumo-grid {
                grid-template-columns: 1fr;
            }

            .conta-menu {
                grid-template-columns: 1fr;
            }
        }


/* =========================================================
   RESPONSIVIDADE EXTRA DA ÁREA DO CLIENTE
========================================================= */
html, body { max-width: 100%; overflow-x: hidden; }

@media (max-width: 850px) {
    .conta { width: min(100% - 24px, 1120px); margin-top: 28px; }
    .conta-layout { gap: 16px; }
    .conta-card { padding: 20px; }
    .lista-item { align-items: flex-start; }
}

@media (max-width: 620px) {
    .topo { min-height: 76px; padding: 12px 18px; }
    .logo img { width: 122px; max-width: 122px; }
    .conta { width: calc(100% - 20px); margin-top: 22px; }
    .conta-titulo h1 { font-size: 27px !important; line-height: 1.2; }
    .conta-titulo p { font-size: 14px; line-height: 1.45; }
    .conta-card { padding: 16px; border-radius: 14px; }
    .conta-card h2 { font-size: 20px; }
    .resumo { padding: 14px; }
    .lista-item { flex-wrap: wrap; gap: 12px; }
    .produto-mini { min-width: 0; flex: 1 1 220px; }
    .produto-mini > div { min-width: 0; }
    .produto-mini h3 { overflow-wrap: anywhere; }
    .lista-item > strong, .lista-item > div:last-child { margin-left: auto; }
    .form-endereco { grid-template-columns: 1fr !important; }
    .campo.full { grid-column: auto !important; }
    .acoes .btn-conta { width: 100%; justify-content: center; }
}

@media (max-width: 390px) {
    .topo { padding-left: 12px; padding-right: 12px; }
    .logo img { width: 106px; max-width: 106px; }
    .icons { gap: 8px; }
    .conta { width: calc(100% - 16px); }
    .conta-menu a { padding: 12px 10px; font-size: 14px; }
    .produto-mini img { width: 58px; height: 54px; }
    .btn-conta { width: 100%; justify-content: center; }
}
</style>
    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>
<body>
<a class="skip-link" href="#conteudoPrincipal">Pular para o conteúdo principal</a>

<header class="topo">
 <div class="logo"><a href="menu.php"><img src="../imagens/logo.png" alt="Garras & Patas Pet Shop"></a></div>
 <div class="espaco-topo"></div>
 <div class="icons"><div class="user"><a href="area-cliente.php"><i class="bi bi-person-check"></i><span>Olá, <?= hCliente($primeiroNome) ?></span></a></div><a href="carrinho.php" title="Carrinho"><i class="bi bi-cart" style="font-size:23px"></i></a></div>
</header>

<main id="conteudoPrincipal" tabindex="-1" class="conta">
 <div class="conta-titulo"><h1>Minha conta</h1><p>Olá, <?= hCliente($primeiroNome) ?>. Aqui você pode acompanhar seus pedidos e seus dados.</p></div>
 <?php if ($mensagem !== ''): ?><div class="<?= $tipoMensagem === 'erro' ? 'erro-box' : 'sucesso-box' ?>"><?= hCliente($mensagem) ?></div><?php endif; ?>
 <div class="conta-layout">
  <aside class="conta-menu">
   <?php foreach ([['inicio','bi-house','Início'],['pedidos','bi-bag-check','Meus pedidos'],['carrinho','bi-cart3','Meu carrinho'],['favoritos','bi-heart','Meus favoritos'],['endereco','bi-geo-alt','Meu endereço']] as $item): ?>
    <a class="<?= $aba === $item[0] ? 'ativo' : '' ?>" href="area-cliente.php?aba=<?= e($item[0]) ?>"><i class="bi <?= e($item[1]) ?>"></i><?= e($item[2]) ?></a>
   <?php endforeach; ?>
   <a class="sair" href="logout.php"><i class="bi bi-box-arrow-right"></i>Sair da conta</a>
  </aside>

  <section class="conta-conteudo">
   <?php if ($aba === 'inicio'): ?>
    <div class="conta-card"><h2>Resumo da conta</h2><p class="descricao">Acesse rapidamente as áreas da sua conta.</p>
     <div class="resumo-grid">
      <div class="resumo"><i class="bi bi-bag-check"></i><strong><?= count($pedidos) ?></strong><span>Pedidos realizados</span></div>
      <div class="resumo"><i class="bi bi-cart3"></i><strong id="totalItensCarrinho"><?= $totalItensCarrinho ?></strong><span>Itens no carrinho</span></div>
      <div class="resumo"><i class="bi bi-heart"></i><strong><?= count($favoritos) ?></strong><span>Favoritos</span></div>
     </div>
    </div>
    <div class="conta-card"><h2>Meu endereço</h2><p class="descricao"><?= $endereco ? hCliente($endereco['rua'].' '.$endereco['numero'].', '.$endereco['bairro'].' — '.$endereco['cidade'].'/'.$endereco['estado']) : 'Você ainda não cadastrou um endereço.' ?></p><a class="btn-conta" href="area-cliente.php?aba=endereco"><i class="bi bi-geo-alt"></i><?= $endereco ? 'Alterar endereço' : 'Cadastrar endereço' ?></a></div>
   <?php elseif ($aba === 'pedidos'): ?>
    <div class="conta-card"><h2>Meus pedidos</h2><p class="descricao">Aqui ficarão seus pedidos realizados e seus respectivos status.</p>
     <?php if ($pedidosErro): ?><div class="erro-box">A tabela de pedidos ainda não está disponível no banco. Execute o SQL fornecido junto desta página.</div><?php elseif (!$pedidos): ?><div class="vazio">Você ainda não possui pedidos registrados.</div><?php else: ?>
      <?php foreach ($pedidos as $pedido): ?><div class="lista-item"><div><strong><?= hCliente($pedido['codigo_pedido'] ?: ('Pedido #'.$pedido['id_pedido'])) ?></strong><div style="color:#777;font-size:13px;margin-top:5px"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></div></div><div style="text-align:right"><span class="pedido-status"><?= hCliente(statusPedidoCliente((string)$pedido['status'])) ?></span><div style="margin-top:8px;font-weight:800">R$ <?= precoCliente($pedido['total']) ?></div></div></div><?php endforeach; ?>
     <?php endif; ?>
    </div>
   <?php elseif ($aba === 'carrinho'): ?>
    <div class="conta-card" id="areaCarrinhoCard"><h2>Meu carrinho</h2><p class="descricao">Os itens adicionados nesta sessão aparecem no carrinho.</p>
     <div id="areaCarrinhoConteudo">
     <?php if (!$carrinho): ?><div class="vazio">Seu carrinho está vazio.</div><a class="btn-conta" href="aba_produto.php"><i class="bi bi-bag"></i> Continuar comprando</a><?php else: ?>
      <?php foreach ($carrinho as $item): ?><div class="lista-item"><div class="produto-mini"><img src="<?= hCliente((string)($item['imagem'] ?? '') ) ?>" alt=""><div><h3><?= hCliente($item['nome'] ?? '') ?></h3><p><?= hCliente($item['variacao'] ?? '') ?><?= !empty($item['quantidade']) ? ' · Quantidade: '.(int)$item['quantidade'] : '' ?></p></div></div><strong>R$ <?= precoCliente(((float)($item['precoUnitario'] ?? 0))*max(1,(int)($item['quantidade'] ?? 1))) ?></strong></div><?php endforeach; ?><div class="acoes"><a class="btn-conta" href="carrinho.php">Abrir carrinho completo</a></div>
     <?php endif; ?>
     </div>
    </div>
   <?php elseif ($aba === 'favoritos'): ?>
    <div class="conta-card"><h2>Meus favoritos</h2><p class="descricao">Produtos que você marcou para guardar.</p>
      <?php if ($favoritosErro): ?><div class="erro-box">A tabela de favoritos ainda não está disponível no banco. Execute o SQL fornecido junto desta página.</div><?php elseif (!$favoritos): ?><div class="vazio">Você ainda não possui favoritos.</div><?php else: ?>
       <?php foreach ($favoritos as $fav): $promocao = (int)($fav['promocao'] ?? 0) === 1; $desconto = (float)($fav['desconto_percentual'] ?? 0); $precoFinal = $promocao && $desconto > 0 ? (float)$fav['preco']*(1-$desconto/100) : (float)$fav['preco']; ?><div class="lista-item"><div class="produto-mini"><img src="<?= hCliente($fav['imagem']) ?>" alt=""><div><h3><?= hCliente($fav['nome']) ?></h3><p><?= hCliente($fav['marca_nome'] ?? '') ?></p></div></div><div style="text-align:right"><span class="preco-antigo" style="display:<?= $precoFinal < (float)$fav['preco'] ? 'inline' : 'none' ?>">R$ <?= precoCliente($fav['preco']) ?></span><span class="preco-atual">R$ <?= precoCliente($precoFinal) ?></span><?= $precoFinal < (float)$fav['preco'] ? '<span class="selo">-'.hCliente(rtrim(rtrim(number_format($desconto,2,',','.'),'0'),',' )).'%</span>' : '' ?><div style="margin-top:8px"><a class="btn-conta btn-sec" href="produto.php?id=<?= (int)$fav['id_produto'] ?>">Ver produto</a></div></div></div><?php endforeach; ?>
      <?php endif; ?>
    </div>
   <?php elseif ($aba === 'endereco'): ?>
    <div class="conta-card"><h2>Meu endereço</h2><p class="descricao">Cadastre o endereço que será utilizado no checkout e nos pedidos.</p>
     <form method="post"><input type="hidden" name="csrf" value="<?= hCliente($csrf) ?>"><input type="hidden" name="acao" value="salvar_endereco"><div class="form-endereco">
      <div class="campo"><label for="cep">CEP</label><input id="cep" name="cep" value="<?= hCliente($endereco['cep'] ?? '') ?>" maxlength="9" placeholder="00000-000"></div>
      <div class="campo"><label for="estado">Estado</label><input id="estado" name="estado" value="<?= hCliente($endereco['estado'] ?? '') ?>" maxlength="2" placeholder="SP" required></div>
      <div class="campo full"><label for="rua">Rua</label><input id="rua" name="rua" value="<?= hCliente($endereco['rua'] ?? '') ?>" required></div>
      <div class="campo"><label for="numero">Número</label><input id="numero" name="numero" value="<?= hCliente($endereco['numero'] ?? '') ?>" required></div>
      <div class="campo"><label for="complemento">Complemento</label><input id="complemento" name="complemento" value="<?= hCliente($endereco['complemento'] ?? '') ?>"></div>
      <div class="campo"><label for="bairro">Bairro</label><input id="bairro" name="bairro" value="<?= hCliente($endereco['bairro'] ?? '') ?>" required></div>
      <div class="campo"><label for="cidade">Cidade</label><input id="cidade" name="cidade" value="<?= hCliente($endereco['cidade'] ?? '') ?>" required></div>
     </div><div class="acoes"><button class="btn-conta" type="submit"><i class="bi bi-check-lg"></i> Salvar endereço</button><?php if($endereco): ?><button class="btn-conta btn-danger" type="submit" name="acao" value="excluir_endereco" onclick="return confirm('Remover seu endereço?');">Remover endereço</button><?php endif; ?></div></form>
    </div>
   <?php endif; ?>
  </section>
 </div>
</main>

<script>
(function () {
    const CHAVE_CARRINHO = "garras_patas_carrinho_v2";

    function lerCarrinhoLocal() {
        try {
            const salvo = localStorage.getItem(CHAVE_CARRINHO);
            if (salvo === null) return null;
            const dados = JSON.parse(salvo);
            return Array.isArray(dados) ? dados : [];
        } catch (e) {
            return [];
        }
    }

    function escaparHtml(valor) {
        const div = document.createElement("div");
        div.textContent = String(valor ?? "");
        return div.innerHTML;
    }

    function formatarMoeda(valor) {
        return Number(valor || 0).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
        });
    }

    function quantidadeValida(valor) {
        return Math.max(1, Math.min(99, Number(valor || 1)));
    }

    function renderizarCarrinhoArea(carrinho) {
        const conteudo = document.getElementById("areaCarrinhoConteudo");
        const contador = document.getElementById("totalItensCarrinho");
        if (contador) {
            const total = carrinho.reduce(function (soma, item) {
                return soma + quantidadeValida(item.quantidade);
            }, 0);
            contador.textContent = String(total);
        }
        if (!conteudo) return;

        if (!carrinho.length) {
            conteudo.innerHTML = '<div class="vazio">Seu carrinho está vazio.</div>' +
                '<a class="btn-conta" href="aba_produto.php"><i class="bi bi-bag"></i> Continuar comprando</a>';
            return;
        }

        const html = carrinho.map(function (item) {
            const quantidade = quantidadeValida(item.quantidade);
            const nome = escaparHtml(item.nome || (item.tipoItem === "plano" ? "Plano de saúde" : "Produto"));
            const variacao = escaparHtml(item.variacao || "");
            const imagem = escaparHtml(item.imagem || "");
            const totalItem = Number(item.precoUnitario || 0) * quantidade;
            const textoVariacao = variacao + (quantidade > 0 ? " · Quantidade: " + quantidade : "");

            return '<div class="lista-item">' +
                '<div class="produto-mini">' +
                    '<img src="' + imagem + '" alt="">' +
                    '<div><h3>' + nome + '</h3><p>' + textoVariacao + '</p></div>' +
                '</div>' +
                '<strong>' + formatarMoeda(totalItem) + '</strong>' +
            '</div>';
        }).join("");

        conteudo.innerHTML = html +
            '<div class="acoes"><a class="btn-conta" href="carrinho.php">Abrir carrinho completo</a></div>';
    }

    async function sincronizarCarrinhoArea() {
        const carrinhoLocal = lerCarrinhoLocal();
        if (carrinhoLocal === null) {
            return;
        }

        const dados = new URLSearchParams();
        dados.append("acao", "sincronizar");
        dados.append("carrinho", JSON.stringify(carrinhoLocal));

        try {
            const resposta = await fetch("carrinho.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: dados.toString(),
                credentials: "same-origin"
            });

            const resultado = await resposta.json();
            if (!resultado || !resultado.ok || !Array.isArray(resultado.carrinho)) {
                return;
            }

            localStorage.setItem(CHAVE_CARRINHO, JSON.stringify(resultado.carrinho));
            renderizarCarrinhoArea(resultado.carrinho);
        } catch (erro) {
            // Mantém a página funcionando mesmo sem sincronização com o servidor.
            if (carrinhoLocal !== null) {
                renderizarCarrinhoArea(carrinhoLocal);
            }
        }
    }

    window.addEventListener("storage", function (evento) {
        if (evento.key === CHAVE_CARRINHO) {
            renderizarCarrinhoArea(lerCarrinhoLocal());
            sincronizarCarrinhoArea();
        }
    });

    window.addEventListener("garras:carrinho-atualizado", function (evento) {
        const carrinho = evento.detail && Array.isArray(evento.detail)
            ? evento.detail
            : lerCarrinhoLocal();
        renderizarCarrinhoArea(carrinho);
        sincronizarCarrinhoArea();
    });

    document.addEventListener("DOMContentLoaded", function () {
        const carrinhoLocal = lerCarrinhoLocal();
        if (carrinhoLocal !== null) {
            renderizarCarrinhoArea(carrinhoLocal);
            sincronizarCarrinhoArea();
        }
    });
})();
</script>
<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
