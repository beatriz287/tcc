<?php
require_once __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $resultado = $conexao->query(
        "SELECT
            id_loja,
            nome,
            endereco,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            cep,
            telefone,
            whatsapp,
            horario,
            mapa_url,
            imagem,
            servicos,
            ordem
         FROM lojas
         WHERE ativo = 1
         ORDER BY ordem, nome, id_loja"
    );

    $lojas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

    echo json_encode([
        'ok' => true,
        'lojas' => $lojas,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $erro) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'lojas' => [],
        'mensagem' => 'Não foi possível carregar as lojas.'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
