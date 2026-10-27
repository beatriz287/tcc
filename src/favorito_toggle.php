<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

header('Content-Type: application/json; charset=utf-8');

if (!sessaoUsuarioLogado()) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'login' => true,
        'mensagem' => 'Faça login para salvar seus favoritos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idUsuario = sessaoUsuarioId();
$idProduto = max(0, (int)($_POST['id_produto'] ?? 0));

if ($idProduto <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensagem' => 'Produto inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $produtoStmt = $conexao->prepare(
        'SELECT id_produto FROM produtos_destaque WHERE id_produto = ? AND ativo = 1 LIMIT 1'
    );
    $produtoStmt->bind_param('i', $idProduto);
    $produtoStmt->execute();

    if (!$produtoStmt->get_result()->fetch_assoc()) {
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'mensagem' => 'Produto não encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $buscar = $conexao->prepare(
        'SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_produto = ? LIMIT 1'
    );
    $buscar->bind_param('ii', $idUsuario, $idProduto);
    $buscar->execute();
    $existente = $buscar->get_result()->fetch_assoc();

    if ($existente) {
        $excluir = $conexao->prepare(
            'DELETE FROM favoritos WHERE id_usuario = ? AND id_produto = ?'
        );
        $excluir->bind_param('ii', $idUsuario, $idProduto);
        $excluir->execute();

        echo json_encode([
            'ok' => true,
            'favoritado' => false,
            'mensagem' => 'Produto removido dos favoritos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $inserir = $conexao->prepare(
        'INSERT INTO favoritos (id_usuario, id_produto) VALUES (?, ?)'
    );
    $inserir->bind_param('ii', $idUsuario, $idProduto);
    $inserir->execute();

    echo json_encode([
        'ok' => true,
        'favoritado' => true,
        'mensagem' => 'Produto adicionado aos favoritos.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensagem' => 'Não foi possível atualizar os favoritos. Execute o SQL da área do cliente se necessário.'
    ], JSON_UNESCAPED_UNICODE);
}
