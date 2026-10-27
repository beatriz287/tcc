<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login-admin.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function precoBR($valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}

function dataHoraBR($valor): string
{
    $valor = trim((string)($valor ?? ''));
    if ($valor === '') {
        return '—';
    }

    $timestamp = strtotime($valor);
    if ($timestamp === false) {
        return $valor;
    }

    return date('d/m/Y H:i', $timestamp);
}

function voltar(string $secao, array $extras = []): void
{
    $params = array_merge(['secao' => $secao], $extras);
    header('Location: admin.php?' . http_build_query($params));
    exit;
}

function definirFlash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function executarPreparado(mysqli $conexao, string $sql, string $tipos, array $valores): mysqli_stmt
{
    $stmt = $conexao->prepare($sql);

    if ($tipos !== '') {
        $params = [$tipos];
        foreach ($valores as $indice => $valor) {
            $params[] = &$valores[$indice];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);
    }

    $stmt->execute();
    return $stmt;
}

function buscarLinha(mysqli $conexao, string $tabela, string $pk, int $id): ?array
{
    // tabela e PK vêm exclusivamente do mapa interno de seções abaixo.
    $resultado = $conexao->query("SELECT * FROM {$tabela} WHERE {$pk} = {$id} LIMIT 1");
    $linha = $resultado->fetch_assoc();
    return $linha ?: null;
}

function buscarTodos(mysqli $conexao, string $sql): array
{
    $resultado = $conexao->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function normalizarDecimal(string $valor): float
{
    $valor = trim($valor);
    if ($valor === '') {
        return 0.0;
    }

    if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif (strpos($valor, ',') !== false) {
        $valor = str_replace(',', '.', $valor);
    }

    return (float)$valor;
}


function normalizarSlug(string $valor): string
{
    $valor = trim($valor);
    if ($valor === '') {
        return '';
    }

    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
    if ($convertido !== false) {
        $valor = $convertido;
    }

    $valor = strtolower($valor);
    $valor = preg_replace('/[^a-z0-9]+/', '-', $valor) ?? '';
    return trim($valor, '-');
}

function determinarModoDescontoProduto(array $produto, array $variacoes): string
{
    $modoSalvo = (int)($produto['promocao'] ?? 0);

    // 2 = desconto individual, salvo pelo painel.
    if ($modoSalvo === 2) {
        return '2';
    }

    if (!$variacoes) {
        return $modoSalvo === 1 ? '1' : '0';
    }

    $todosSem = true;
    $todosCom = true;
    $descontoComum = null;

    foreach ($variacoes as $variacao) {
        $promocao = (int)($variacao['promocao'] ?? 0) === 1;
        $desconto = min(99.99, max(0, (float)($variacao['desconto_percentual'] ?? 0)));

        if ($promocao && $desconto > 0) {
            $todosSem = false;
            if ($descontoComum === null) {
                $descontoComum = round($desconto, 2);
            } elseif (abs($descontoComum - $desconto) > 0.001) {
                $todosCom = false;
            }
        } else {
            $todosCom = false;
        }
    }

    if ($todosSem) return '0';
    if ($todosCom && $descontoComum !== null) return '1';
    return '2';
}

/**
 * Garante a primeira variação para produtos antigos que ainda não possuem nenhuma.
 *
 * Produtos que já possuem variações não são alterados. Para os demais, a primeira
 * variação copia o preço e a imagem atuais do produto. Promoções legadas do produto
 * são migradas para essa primeira variação para não perder o preço promocional.
 */
function garantirPrimeiraVariacaoProdutosExistentes(mysqli $conexao): void
{
    $sql = "
        INSERT INTO variacoes_produto
            (id_produto, nome, preco, imagem, promocao, desconto_percentual)
        SELECT
            p.id_produto,
            'Padrão',
            p.preco,
            p.imagem,
            CASE
                WHEN p.promocao = 1 AND p.desconto_percentual > 0 THEN 1
                ELSE 0
            END,
            CASE
                WHEN p.promocao = 1 AND p.desconto_percentual > 0
                    THEN LEAST(99.99, GREATEST(0, p.desconto_percentual))
                ELSE 0
            END
        FROM produtos_destaque p
        WHERE NOT EXISTS (
            SELECT 1
            FROM variacoes_produto v
            WHERE v.id_produto = p.id_produto
        )
    ";

    $conexao->query($sql);
}

function processarImagem(string $campo, string $imagemAtual = ''): string
{
    $caminhoInformado = trim((string)($_POST[$campo . '_caminho'] ?? ''));
    $arquivo = $_FILES[$campo] ?? null;

    if ($arquivo && ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        if (($arquivo['size'] ?? 0) > 8 * 1024 * 1024) {
            throw new RuntimeException('A imagem não pode ultrapassar 8 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo['tmp_name']);
        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($extensoes[$mime])) {
            throw new RuntimeException('Formato de imagem inválido. Use JPG, PNG, WEBP ou GIF.');
        }

        $diretorio = __DIR__ . '/uploads/admin';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível criar a pasta de uploads.');
        }

        $nome = date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $extensoes[$mime];
        $destino = $diretorio . '/' . $nome;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new RuntimeException('Não foi possível salvar a imagem enviada.');
        }

        // admin.php e menu.php ficam na mesma pasta, então este caminho funciona nos dois.
        return 'uploads/admin/' . $nome;
    }

    if ($arquivo && !in_array(($arquivo['error'] ?? UPLOAD_ERR_NO_FILE), [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK], true)) {
        throw new RuntimeException('Falha ao enviar a imagem. Tente novamente.');
    }

    if ($caminhoInformado !== '') {
        return $caminhoInformado;
    }

    return $imagemAtual;
}

$secoes = [
    'categorias' => [
        'titulo' => 'Menu principal',
        'icone' => 'bi-list-ul',
        'tabela' => 'categorias_menu',
        'pk' => 'id_categoria',
        'ordem_sql' => 'ordem, id_categoria',
        'campos' => [
            'nome' => ['rotulo' => 'Nome da categoria', 'tipo' => 'text', 'obrigatorio' => true],
            'link' => ['rotulo' => 'Link', 'tipo' => 'text', 'placeholder' => 'ex.: racoes.html'],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'categorias_produtos' => [
        'titulo' => 'Categorias de produtos',
        'icone' => 'bi-tags',
        'tabela' => 'categorias_produtos',
        'pk' => 'id_categoria_produto',
        'ordem_sql' => 'ordem, nome, id_categoria_produto',
        'campos' => [
            'nome' => ['rotulo' => 'Nome da categoria', 'tipo' => 'text', 'obrigatorio' => true],
            'slug' => ['rotulo' => 'Identificador / slug', 'tipo' => 'text', 'placeholder' => 'ex.: higiene-e-limpeza'],
            'tipo_pet' => [
                'rotulo' => 'Aparece para',
                'tipo' => 'select',
                'obrigatorio' => true,
                'padrao' => 'ambos',
                'opcoes' => [
                    'ambos' => 'Cachorro e gato',
                    'cachorro' => 'Cachorro',
                    'gato' => 'Gato',
                ],
            ],
            'ordem' => ['rotulo' => 'Ordem no menu', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no menu', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'carrossel' => [
        'titulo' => 'Imagens do carrossel',
        'icone' => 'bi-images',
        'tabela' => 'carrossel',
        'pk' => 'id_carrossel',
        'ordem_sql' => 'ordem, id_carrossel',
        'campos' => [
            'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'image', 'obrigatorio' => true],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'lojas' => [
        'titulo' => 'Lojas',
        'icone' => 'bi-shop',
        'tabela' => 'lojas',
        'pk' => 'id_loja',
        'ordem_sql' => 'ordem, nome, id_loja',
        'campos' => [
            'nome' => ['rotulo' => 'Nome da loja', 'tipo' => 'text', 'obrigatorio' => true],
            'endereco' => ['rotulo' => 'Endereço', 'tipo' => 'text', 'obrigatorio' => true, 'placeholder' => 'ex.: Rua das Flores'],
            'numero' => ['rotulo' => 'Número', 'tipo' => 'text', 'placeholder' => 'ex.: 123'],
            'complemento' => ['rotulo' => 'Complemento', 'tipo' => 'text', 'placeholder' => 'ex.: Sala 2'],
            'bairro' => ['rotulo' => 'Bairro', 'tipo' => 'text'],
            'cidade' => ['rotulo' => 'Cidade', 'tipo' => 'text', 'obrigatorio' => true],
            'estado' => ['rotulo' => 'Estado (UF)', 'tipo' => 'text', 'placeholder' => 'ex.: SP'],
            'cep' => ['rotulo' => 'CEP', 'tipo' => 'text', 'placeholder' => 'ex.: 13700-000'],
            'telefone' => ['rotulo' => 'Telefone', 'tipo' => 'text', 'placeholder' => 'ex.: (19) 0000-0000'],
            'whatsapp' => ['rotulo' => 'WhatsApp', 'tipo' => 'text', 'placeholder' => 'ex.: (19) 99999-9999'],
            'horario' => ['rotulo' => 'Horário de funcionamento', 'tipo' => 'textarea', 'obrigatorio' => true, 'placeholder' => 'ex.: Segunda a sexta, das 08:00 às 18:00'],
            'mapa_url' => ['rotulo' => 'Link do Google Maps', 'tipo' => 'text', 'placeholder' => 'Cole aqui o link do mapa'],
            'imagem' => ['rotulo' => 'Imagem da loja (opcional)', 'tipo' => 'image', 'obrigatorio' => false],
            'ordem' => ['rotulo' => 'Ordem de exibição', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'produtos' => [
        'titulo' => 'Produtos',
        'icone' => 'bi-box-seam',
        'tabela' => 'produtos_destaque',
        'pk' => 'id_produto',
        'ordem_sql' => 'ordem, id_produto',
        'campos' => [
            'nome' => ['rotulo' => 'Nome do produto', 'tipo' => 'text', 'obrigatorio' => true],
            'descricao' => [
                'rotulo' => 'Descrição do produto',
                'tipo' => 'textarea',
                'obrigatorio' => false,
            ],
            'imagem' => ['rotulo' => 'Imagem principal', 'tipo' => 'image', 'obrigatorio' => true],
            'preco' => ['rotulo' => 'Preço da primeira variação', 'tipo' => 'decimal', 'obrigatorio' => true],
            'id_marca' => ['rotulo' => 'Marca', 'tipo' => 'marca'],
            'tipo_pet' => [
                'rotulo' => 'Tipo de pet',
                'tipo' => 'select',
                'obrigatorio' => true,
                'padrao' => 'ambos',
                'opcoes' => [
                    'ambos' => 'Cachorro e gato',
                    'cachorro' => 'Cachorro',
                    'gato' => 'Gato',
                ],
            ],
            'id_categoria_produto' => [
                'rotulo' => 'Categoria',
                'tipo' => 'categoria_produto',
                'obrigatorio' => true,
            ],
            'destaque' => ['rotulo' => 'Exibir em Produtos em destaque', 'tipo' => 'checkbox', 'padrao' => 0],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'exclusivos' => [
        'titulo' => 'Você só encontra aqui',
        'icone' => 'bi-stars',
        'tabela' => 'exclusivos',
        'pk' => 'id_exclusivo',
        'ordem_sql' => 'ordem, id_exclusivo',
        'campos' => [
            'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'image', 'obrigatorio' => true],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'marcas' => [
        'titulo' => 'Marcas que trabalhamos',
        'icone' => 'bi-patch-check',
        'tabela' => 'marcas',
        'pk' => 'id_marca',
        'ordem_sql' => 'nome, id_marca',
        'campos' => [
            'nome' => ['rotulo' => 'Nome da marca', 'tipo' => 'text', 'obrigatorio' => true],
            'imagem' => ['rotulo' => 'Logo da marca (opcional)', 'tipo' => 'image', 'obrigatorio' => false],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'planos' => [
        'titulo' => 'Plano de saúde',
        'icone' => 'bi-heart-pulse',
        'tabela' => 'planos_saude',
        'pk' => 'id_plano',
        'ordem_sql' => 'ordem, id_plano',
        'campos' => [
            'nome' => ['rotulo' => 'Nome do plano', 'tipo' => 'text', 'obrigatorio' => true],
            'descricao' => ['rotulo' => 'Descrição', 'tipo' => 'textarea', 'obrigatorio' => true],
            'preco' => ['rotulo' => 'Preço mensal', 'tipo' => 'decimal', 'obrigatorio' => true],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'cachorro' => [
        'titulo' => 'Tudo para cachorro',
        'icone' => 'bi-circle-fill',
        'tabela' => 'tudo_cachorro',
        'pk' => 'id_item',
        'ordem_sql' => 'ordem, id_item',
        'campos' => [
            'nome' => ['rotulo' => 'Nome', 'tipo' => 'text', 'obrigatorio' => true],
            'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'image', 'obrigatorio' => true],
            'id_categoria_produto' => [
                'rotulo' => 'Categoria de produtos',
                'tipo' => 'categoria_tudo',
                'pet' => 'cachorro',
                'obrigatorio' => true,
            ],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'gato' => [
        'titulo' => 'Tudo para gato',
        'icone' => 'bi-circle-fill',
        'tabela' => 'tudo_gato',
        'pk' => 'id_item',
        'ordem_sql' => 'ordem, id_item',
        'campos' => [
            'nome' => ['rotulo' => 'Nome', 'tipo' => 'text', 'obrigatorio' => true],
            'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'image', 'obrigatorio' => true],
            'id_categoria_produto' => [
                'rotulo' => 'Categoria de produtos',
                'tipo' => 'categoria_tudo',
                'pet' => 'gato',
                'obrigatorio' => true,
            ],
            'ordem' => ['rotulo' => 'Ordem', 'tipo' => 'number', 'padrao' => 0],
            'ativo' => ['rotulo' => 'Exibir no site', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
    'usuarios' => [
        'titulo' => 'Usuários',
        'icone' => 'bi-people',
        'tabela' => 'usuarios',
        'pk' => 'id_usuario',
        'ordem_sql' => 'data_cadastro DESC, id_usuario DESC',
        'somente_edicao' => true,
        'campos' => [
            'nome' => ['rotulo' => 'Nome', 'tipo' => 'text', 'obrigatorio' => true],
            'email' => ['rotulo' => 'E-mail', 'tipo' => 'email', 'obrigatorio' => true],
            'ativo' => ['rotulo' => 'Conta ativa', 'tipo' => 'checkbox', 'padrao' => 1],
        ],
    ],
];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$secao = (string)($_GET['secao'] ?? 'categorias');
if ($secao !== 'variacoes' && $secao !== 'administradores' && !isset($secoes[$secao])) {
    $secao = 'categorias';
}

try {
    garantirPrimeiraVariacaoProdutosExistentes($conexao);
} catch (Throwable $erro) {
    // A migração é apenas de compatibilidade para dados antigos; o painel continua
    // funcionando normalmente caso o banco já esteja atualizado.
}

try {
    $marcasDisponiveis = buscarTodos($conexao, 'SELECT id_marca, nome FROM marcas ORDER BY nome, id_marca');
} catch (Throwable $erro) {
    $marcasDisponiveis = [];
}


try {
    $categoriasProdutosDisponiveis = buscarTodos(
        $conexao,
        "SELECT id_categoria_produto, nome, slug, tipo_pet, ativo
         FROM categorias_produtos
         ORDER BY ordem, nome, id_categoria_produto"
    );
} catch (Throwable $erro) {
    $categoriasProdutosDisponiveis = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('A sessão do formulário expirou. Atualize a página e tente novamente.');
        }

        $acao = (string)($_POST['acao'] ?? '');
        $secaoPost = (string)($_POST['secao'] ?? '');

        // CRUD e regras de desconto das variações dos produtos.
        if ($secaoPost === 'variacoes') {
            $idProduto = max(0, (int)($_POST['id_produto'] ?? 0));
            if ($idProduto <= 0) {
                throw new RuntimeException('Produto inválido para gerenciar variações.');
            }

            $produtoGerenciado = buscarLinha($conexao, 'produtos_destaque', 'id_produto', $idProduto);
            if (!$produtoGerenciado) {
                throw new RuntimeException('O produto selecionado não existe mais.');
            }

            if ($acao === 'configurar_desconto') {
                $modo = (string)($_POST['modo_desconto'] ?? '0');
                $descontoIgual = normalizarDecimal((string)($_POST['desconto_igual'] ?? '0'));

                if (!in_array($modo, ['0', '1', '2'], true)) {
                    throw new RuntimeException('Selecione uma forma de desconto válida.');
                }

                if ($modo === '0') {
                    executarPreparado(
                        $conexao,
                        'UPDATE variacoes_produto SET promocao = 0, desconto_percentual = 0 WHERE id_produto = ?',
                        'i',
                        [$idProduto]
                    );
                    // Estas colunas passam a guardar somente a preferência do painel.
                    // O site não utiliza mais promoção do produto.
                    executarPreparado(
                        $conexao,
                        'UPDATE produtos_destaque SET promocao = 0, desconto_percentual = 0 WHERE id_produto = ?',
                        'i',
                        [$idProduto]
                    );
                    definirFlash('success', 'Desconto removido de todas as variações.');
                } elseif ($modo === '1') {
                    if ($descontoIgual <= 0 || $descontoIgual >= 100) {
                        throw new RuntimeException('Informe um desconto maior que 0% e menor que 100%.');
                    }
                    $descontoIgual = min(99.99, $descontoIgual);

                    executarPreparado(
                        $conexao,
                        'UPDATE variacoes_produto SET promocao = 1, desconto_percentual = ? WHERE id_produto = ?',
                        'di',
                        [$descontoIgual, $idProduto]
                    );
                    executarPreparado(
                        $conexao,
                        'UPDATE produtos_destaque SET promocao = 1, desconto_percentual = ? WHERE id_produto = ?',
                        'di',
                        [$descontoIgual, $idProduto]
                    );
                    definirFlash('success', 'O mesmo desconto foi aplicado a todas as variações.');
                } else {
                    executarPreparado(
                        $conexao,
                        'UPDATE produtos_destaque SET promocao = 2, desconto_percentual = 0 WHERE id_produto = ?',
                        'i',
                        [$idProduto]
                    );
                    definirFlash('success', 'Desconto individual ativado.');
                }

                voltar('variacoes', ['produto' => $idProduto]);
            }

            if ($acao === 'excluir') {
                $idVariacao = max(0, (int)($_POST['id_variacao'] ?? 0));
                executarPreparado(
                    $conexao,
                    'DELETE FROM variacoes_produto WHERE id_variacao = ? AND id_produto = ?',
                    'ii',
                    [$idVariacao, $idProduto]
                );
                definirFlash('success', 'Variação excluída com sucesso.');
                voltar('variacoes', ['produto' => $idProduto]);
            }

            if ($acao !== 'salvar') {
                throw new RuntimeException('Ação inválida para variação.');
            }

            $idVariacao = max(0, (int)($_POST['id_variacao'] ?? 0));
            $nome = trim((string)($_POST['nome'] ?? ''));
            $preco = normalizarDecimal((string)($_POST['preco'] ?? '0'));
            $imagemAtual = trim((string)($_POST['imagem_atual'] ?? ''));
            $imagem = processarImagem('imagem', $imagemAtual);

            if ($nome === '') {
                throw new RuntimeException('Informe o nome/tamanho da variação.');
            }
            if ($preco < 0) {
                throw new RuntimeException('O preço da variação não pode ser negativo.');
            }

            $variacoesAtuais = buscarTodos(
                $conexao,
                'SELECT * FROM variacoes_produto WHERE id_produto = ' . $idProduto . ' ORDER BY id_variacao'
            );
            $modoAtual = determinarModoDescontoProduto($produtoGerenciado, $variacoesAtuais);
            $desconto = normalizarDecimal((string)($_POST['desconto_percentual'] ?? '0'));

            if ($modoAtual === '0') {
                $promocao = 0;
                $desconto = 0.0;
            } elseif ($modoAtual === '1') {
                $promocao = 1;
                $desconto = min(99.99, max(0, (float)($produtoGerenciado['desconto_percentual'] ?? 0)));
                if ($desconto <= 0 || $desconto >= 100) {
                    throw new RuntimeException('Configure primeiro o desconto igual para todas as variações.');
                }
            } else {
                $promocao = $desconto > 0 ? 1 : 0;
                if ($promocao === 1 && $desconto >= 100) {
                    throw new RuntimeException('O desconto deve ser menor que 100%.');
                }
                $desconto = $promocao === 1 ? min(99.99, max(0, $desconto)) : 0.0;
            }

            if ($idVariacao > 0) {
                $registroVariacao = buscarLinha($conexao, 'variacoes_produto', 'id_variacao', $idVariacao);
                if (!$registroVariacao || (int)$registroVariacao['id_produto'] !== $idProduto) {
                    throw new RuntimeException('A variação selecionada não pertence a este produto.');
                }

                executarPreparado(
                    $conexao,
                    'UPDATE variacoes_produto SET nome = ?, preco = ?, imagem = ?, promocao = ?, desconto_percentual = ? WHERE id_variacao = ? AND id_produto = ?',
                    'sdsidii',
                    [$nome, $preco, $imagem, $promocao, $desconto, $idVariacao, $idProduto]
                );
                definirFlash('success', 'Variação alterada com sucesso.');
            } else {
                executarPreparado(
                    $conexao,
                    'INSERT INTO variacoes_produto (id_produto, nome, preco, imagem, promocao, desconto_percentual) VALUES (?, ?, ?, ?, ?, ?)',
                    'isdsid',
                    [$idProduto, $nome, $preco, $imagem, $promocao, $desconto]
                );
                definirFlash('success', 'Variação adicionada com sucesso.');
            }

            voltar('variacoes', ['produto' => $idProduto]);
        }

        // Gerenciamento exclusivo das contas administrativas.
        if ($secaoPost === 'administradores') {
            $idAdmin = max(0, (int)($_POST['id_admin'] ?? 0));

            if ($acao === 'excluir') {
                if ($idAdmin <= 0) {
                    throw new RuntimeException('Administrador inválido.');
                }
                if ($idAdmin === (int)($_SESSION['admin_id'] ?? 0)) {
                    throw new RuntimeException('Você não pode excluir a conta administrativa que está usando.');
                }

                executarPreparado(
                    $conexao,
                    'DELETE FROM administradores WHERE id_admin = ?',
                    'i',
                    [$idAdmin]
                );
                definirFlash('success', 'Administrador excluído com sucesso.');
                voltar('administradores');
            }

            if ($acao !== 'salvar') {
                throw new RuntimeException('Ação inválida para administradores.');
            }

            $nomeAdmin = trim((string)($_POST['nome_admin'] ?? ''));
            $usuarioAdmin = trim((string)($_POST['usuario_admin'] ?? ''));
            $senhaAdmin = (string)($_POST['senha_admin'] ?? '');
            $confirmarSenha = (string)($_POST['confirmar_senha_admin'] ?? '');
            $ativoAdmin = isset($_POST['ativo_admin']) ? 1 : 0;

            if (mb_strlen($nomeAdmin, 'UTF-8') < 2) {
                throw new RuntimeException('Informe um nome válido para o administrador.');
            }

            if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $usuarioAdmin)) {
                throw new RuntimeException('O usuário deve ter entre 3 e 80 caracteres e usar apenas letras, números, ponto, hífen ou sublinhado.');
            }

            if ($idAdmin <= 0) {
                if ($senhaAdmin === '') {
                    throw new RuntimeException('Informe uma senha para o novo administrador.');
                }
                if (strlen($senhaAdmin) < 8) {
                    throw new RuntimeException('A senha deve ter pelo menos 8 caracteres.');
                }
                if (!hash_equals($senhaAdmin, $confirmarSenha)) {
                    throw new RuntimeException('A confirmação da senha não confere.');
                }

                $verificarUsuario = executarPreparado(
                    $conexao,
                    'SELECT id_admin FROM administradores WHERE usuario = ? LIMIT 1',
                    's',
                    [$usuarioAdmin]
                );
                if ($verificarUsuario->get_result()->fetch_assoc()) {
                    throw new RuntimeException('Já existe um administrador com esse usuário.');
                }

                $senhaHash = password_hash($senhaAdmin, PASSWORD_DEFAULT);
                if ($senhaHash === false) {
                    throw new RuntimeException('Não foi possível proteger a senha.');
                }

                executarPreparado(
                    $conexao,
                    'INSERT INTO administradores (usuario, senha_hash, nome, ativo) VALUES (?, ?, ?, ?)',
                    'sssi',
                    [$usuarioAdmin, $senhaHash, $nomeAdmin, $ativoAdmin]
                );

                definirFlash('success', 'Administrador criado com sucesso. A senha foi armazenada de forma segura.');
                voltar('administradores');
            }

            $adminAtual = buscarLinha($conexao, 'administradores', 'id_admin', $idAdmin);
            if (!$adminAtual) {
                throw new RuntimeException('O administrador que você tentou alterar não existe mais.');
            }

            if ($idAdmin === (int)($_SESSION['admin_id'] ?? 0) && $ativoAdmin !== 1) {
                throw new RuntimeException('Você não pode desativar a própria conta enquanto estiver conectado.');
            }

            $verificarUsuario = executarPreparado(
                $conexao,
                'SELECT id_admin FROM administradores WHERE usuario = ? AND id_admin <> ? LIMIT 1',
                'si',
                [$usuarioAdmin, $idAdmin]
            );
            if ($verificarUsuario->get_result()->fetch_assoc()) {
                throw new RuntimeException('Já existe outro administrador com esse usuário.');
            }

            if ($senhaAdmin !== '' || $confirmarSenha !== '') {
                if (strlen($senhaAdmin) < 8) {
                    throw new RuntimeException('A nova senha deve ter pelo menos 8 caracteres.');
                }
                if (!hash_equals($senhaAdmin, $confirmarSenha)) {
                    throw new RuntimeException('A confirmação da senha não confere.');
                }

                $senhaHash = password_hash($senhaAdmin, PASSWORD_DEFAULT);
                if ($senhaHash === false) {
                    throw new RuntimeException('Não foi possível proteger a nova senha.');
                }

                executarPreparado(
                    $conexao,
                    'UPDATE administradores SET usuario = ?, nome = ?, senha_hash = ?, ativo = ? WHERE id_admin = ?',
                    'sssii',
                    [$usuarioAdmin, $nomeAdmin, $senhaHash, $ativoAdmin, $idAdmin]
                );
            } else {
                executarPreparado(
                    $conexao,
                    'UPDATE administradores SET usuario = ?, nome = ?, ativo = ? WHERE id_admin = ?',
                    'ssii',
                    [$usuarioAdmin, $nomeAdmin, $ativoAdmin, $idAdmin]
                );
            }

            if ($idAdmin === (int)($_SESSION['admin_id'] ?? 0)) {
                $_SESSION['admin_usuario'] = $usuarioAdmin;
                $_SESSION['admin_nome'] = $nomeAdmin;
            }

            definirFlash('success', 'Dados do administrador atualizados com sucesso.');
            voltar('administradores');
        }

        if (!isset($secoes[$secaoPost])) {
            throw new RuntimeException('Seção inválida.');
        }

        $config = $secoes[$secaoPost];
        $tabela = $config['tabela'];
        $pk = $config['pk'];
        $id = max(0, (int)($_POST['id'] ?? 0));

        if ($secaoPost === 'usuarios' && $id <= 0) {
            throw new RuntimeException('Usuários só podem ser criados pela página Criar conta. O gerente pode apenas gerenciar contas existentes.');
        }

        if ($acao === 'excluir') {
            executarPreparado($conexao, "DELETE FROM {$tabela} WHERE {$pk} = ?", 'i', [$id]);
            if ($secaoPost === 'categorias_produtos') {
                definirFlash('success', 'Categoria excluída. Produtos que estavam nela ficaram sem categoria e podem ser reclassificados em Produtos.');
            } elseif ($secaoPost === 'usuarios') {
                definirFlash('success', 'Usuário excluído com sucesso.');
            } else {
                definirFlash('success', 'Item excluído. O menu.php já passará a usar os dados atualizados.');
            }
            voltar($secaoPost);
        }

        if ($acao !== 'salvar') {
            throw new RuntimeException('Ação inválida.');
        }

        $registroAtual = $id > 0 ? buscarLinha($conexao, $tabela, $pk, $id) : null;
        $dados = [];
        $tipos = '';

        foreach ($config['campos'] as $nomeCampo => $campo) {
            $tipo = $campo['tipo'];

            if ($tipo === 'image') {
                $atual = (string)($registroAtual[$nomeCampo] ?? '');
                $valor = processarImagem($nomeCampo, $atual);
                if (($campo['obrigatorio'] ?? false) && $valor === '') {
                    throw new RuntimeException('Selecione ou informe ' . strtolower($campo['rotulo']) . '.');
                }
                $dados[$nomeCampo] = $valor;
                $tipos .= 's';
                continue;
            }

            if ($tipo === 'checkbox') {
                $dados[$nomeCampo] = isset($_POST[$nomeCampo]) ? 1 : 0;
                $tipos .= 'i';
                continue;
            }

            if ($tipo === 'number' || $tipo === 'marca' || $tipo === 'categoria_produto' || $tipo === 'categoria_tudo') {
                $dados[$nomeCampo] = (int)($_POST[$nomeCampo] ?? 0);

                if ($tipo === 'marca' && $dados[$nomeCampo] <= 0) {
                    $dados[$nomeCampo] = null;
                }

                if ($tipo === 'categoria_produto' && $dados[$nomeCampo] <= 0) {
                    throw new RuntimeException('Selecione a categoria do produto.');
                }

                if ($tipo === 'categoria_tudo') {
                    if ($dados[$nomeCampo] <= 0) {
                        throw new RuntimeException('Selecione a categoria de produtos que será aberta por este item.');
                    }

                    $categoriaSelecionada = buscarLinha(
                        $conexao,
                        'categorias_produtos',
                        'id_categoria_produto',
                        $dados[$nomeCampo]
                    );

                    if (!$categoriaSelecionada) {
                        throw new RuntimeException('A categoria de produtos selecionada não existe mais.');
                    }

                    $petItem = (string)($campo['pet'] ?? '');
                    $petCategoria = (string)($categoriaSelecionada['tipo_pet'] ?? 'ambos');
                    $categoriaCompativel = in_array($petCategoria, [$petItem, 'ambos'], true);

                    if (!$categoriaCompativel) {
                        throw new RuntimeException('A categoria selecionada não está disponível para esta área.');
                    }
                }

                $tipos .= 'i';
                continue;
            }

            if ($tipo === 'select') {
                $valor = trim((string)($_POST[$nomeCampo] ?? ''));
                $opcoesPermitidas = array_keys($campo['opcoes'] ?? []);
                if (!in_array($valor, $opcoesPermitidas, true)) {
                    throw new RuntimeException('Valor inválido para ' . $campo['rotulo'] . '.');
                }
                $dados[$nomeCampo] = $valor;
                $tipos .= 's';
                continue;
            }

            if ($tipo === 'decimal' || $tipo === 'percentual') {
                $dados[$nomeCampo] = normalizarDecimal((string)($_POST[$nomeCampo] ?? '0'));
                if ($dados[$nomeCampo] < 0) {
                    throw new RuntimeException($campo['rotulo'] . ' não pode ser negativo.');
                }
                if ($tipo === 'percentual' && $dados[$nomeCampo] > 100) {
                    throw new RuntimeException($campo['rotulo'] . ' não pode ultrapassar 100%.');
                }
                $tipos .= 'd';
                continue;
            }

            $valor = trim((string)($_POST[$nomeCampo] ?? ''));
            if (($campo['obrigatorio'] ?? false) && $valor === '') {
                throw new RuntimeException('Preencha o campo ' . $campo['rotulo'] . '.');
            }
            $dados[$nomeCampo] = $valor;
            $tipos .= 's';
        }

        if ($secaoPost === 'usuarios') {
            $nomeUsuario = trim((string)($dados['nome'] ?? ''));
            $emailUsuario = trim((string)($dados['email'] ?? ''));

            if (mb_strlen($nomeUsuario, 'UTF-8') < 2) {
                throw new RuntimeException('Informe um nome válido para o usuário.');
            }

            if (!filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Informe um e-mail válido.');
            }

            /*
             * IMPORTANTE:
             * A senha não faz parte de $secoes['usuarios']['campos'].
             * Portanto senha_hash nunca é recebida pelo formulário
             * e nunca entra no UPDATE feito pelo painel do gerente.
             */
            $verificarEmail = executarPreparado(
                $conexao,
                'SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ? LIMIT 1',
                'si',
                [$emailUsuario, $id]
            );

            if ($verificarEmail->get_result()->fetch_assoc()) {
                throw new RuntimeException('Já existe outro usuário cadastrado com este e-mail.');
            }
        }

        if ($secaoPost === 'categorias_produtos') {
            $dados['slug'] = normalizarSlug((string)($dados['slug'] ?? ''));
            if ($dados['slug'] === '') {
                $dados['slug'] = normalizarSlug((string)($dados['nome'] ?? ''));
            }
            if ($dados['slug'] === '') {
                throw new RuntimeException('Não foi possível gerar o identificador da categoria.');
            }

            $slugEscapada = $conexao->real_escape_string($dados['slug']);
            $sqlDuplicada = "SELECT id_categoria_produto FROM categorias_produtos WHERE slug = '{$slugEscapada}'";
            if ($id > 0) {
                $sqlDuplicada .= ' AND id_categoria_produto <> ' . $id;
            }
            $sqlDuplicada .= ' LIMIT 1';
            $resultadoDuplicada = $conexao->query($sqlDuplicada);
            if ($resultadoDuplicada && $resultadoDuplicada->fetch_assoc()) {
                throw new RuntimeException('Já existe uma categoria usando esse identificador/slug.');
            }
        }

        if ($secaoPost === 'produtos') {
            $categoriaSelecionada = buscarLinha(
                $conexao,
                'categorias_produtos',
                'id_categoria_produto',
                (int)($dados['id_categoria_produto'] ?? 0)
            );
            if (!$categoriaSelecionada) {
                throw new RuntimeException('A categoria selecionada não existe mais.');
            }

            $petProduto = (string)($dados['tipo_pet'] ?? 'ambos');
            $petCategoria = (string)($categoriaSelecionada['tipo_pet'] ?? 'ambos');
            $categoriaCompativel = $petProduto === 'ambos'
                ? $petCategoria === 'ambos'
                : ($petCategoria === 'ambos' || $petCategoria === $petProduto);

            if (!$categoriaCompativel) {
                throw new RuntimeException('A categoria selecionada não é compatível com o tipo de pet do produto.');
            }
        }

        $colunas = array_keys($dados);
        $valores = array_values($dados);

        if ($id > 0) {
            if (!$registroAtual) {
                throw new RuntimeException('O item que você tentou alterar não existe mais.');
            }

            $sets = implode(', ', array_map(static function ($campo) { return $campo . ' = ?'; }, $colunas));
            $sql = "UPDATE {$tabela} SET {$sets} WHERE {$pk} = ?";
            $tiposUpdate = $tipos . 'i';
            $valores[] = $id;
            executarPreparado($conexao, $sql, $tiposUpdate, $valores);

            if ($secaoPost === 'usuarios') {
                definirFlash('success', 'Dados do usuário atualizados. A senha não foi alterada.');
            } else {
                definirFlash('success', 'Alterações salvas com sucesso. O menu.php já utiliza estes novos dados.');
            }
        } else {
            if ($secaoPost === 'usuarios') {
                throw new RuntimeException('O painel do gerente não cria usuários. Use a página Criar conta.');
            }

            $interrogacoes = implode(', ', array_fill(0, count($colunas), '?'));
            $sql = "INSERT INTO {$tabela} (" . implode(', ', $colunas) . ") VALUES ({$interrogacoes})";

            if ($secaoPost === 'produtos') {
                /*
                 * Todo produto novo já nasce com uma variação principal.
                 * O preço e a imagem informados no cadastro do produto são
                 * usados para criar automaticamente essa primeira variação.
                 */
                $conexao->begin_transaction();
                try {
                    $stmtProduto = executarPreparado($conexao, $sql, $tipos, $valores);
                    $idNovoProduto = (int)$conexao->insert_id;
                    if ($idNovoProduto <= 0) {
                        $idNovoProduto = (int)$stmtProduto->insert_id;
                    }

                    if ($idNovoProduto <= 0) {
                        throw new RuntimeException('Não foi possível identificar o produto recém-cadastrado.');
                    }

                    $precoPrimeiraVariacao = (float)($dados['preco'] ?? 0);
                    $imagemPrimeiraVariacao = (string)($dados['imagem'] ?? '');

                    executarPreparado(
                        $conexao,
                        'INSERT INTO variacoes_produto (id_produto, nome, preco, imagem, promocao, desconto_percentual) VALUES (?, ?, ?, ?, 0, 0)',
                        'isds',
                        [$idNovoProduto, 'Padrão', $precoPrimeiraVariacao, $imagemPrimeiraVariacao]
                    );

                    $conexao->commit();
                    definirFlash('success', 'Produto criado com a primeira variação cadastrada.');
                    voltar('variacoes', ['produto' => $idNovoProduto]);
                } catch (Throwable $erroProduto) {
                    $conexao->rollback();
                    throw $erroProduto;
                }
            } else {
                executarPreparado($conexao, $sql, $tipos, $valores);
                definirFlash('success', 'Novo item adicionado. Ele já poderá aparecer no menu.php.');
            }
        }

        voltar($secaoPost);
    } catch (Throwable $erro) {
        definirFlash('danger', $erro->getMessage());
        $destino = (string)($_POST['secao'] ?? 'categorias');
        $extras = [];
        if ($destino === 'variacoes' && !empty($_POST['id_produto'])) {
            $extras['produto'] = (int)$_POST['id_produto'];
        }
        voltar($destino, $extras);
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$registroEdicao = null;
$lista = [];
$configAtual = null;
$produtoVariacoes = null;
$variacoes = [];
$modoDescontoAtual = '0';
$descontoIgualAtual = 0.0;
$administradores = [];
$administradorEdicao = null;

try {
    if ($secao === 'variacoes') {
        $idProdutoVariacoes = max(0, (int)($_GET['produto'] ?? 0));
        if ($idProdutoVariacoes > 0) {
            $produtoVariacoes = buscarLinha($conexao, 'produtos_destaque', 'id_produto', $idProdutoVariacoes);
            if ($produtoVariacoes) {
                $variacoes = buscarTodos(
                    $conexao,
                    'SELECT * FROM variacoes_produto WHERE id_produto = ' . $idProdutoVariacoes . ' ORDER BY id_variacao'
                );
            }
            $modoDescontoAtual = determinarModoDescontoProduto($produtoVariacoes, $variacoes);
            $descontoIgualAtual = $modoDescontoAtual === '1'
                ? min(99.99, max(0, (float)($produtoVariacoes['desconto_percentual'] ?? 0)))
                : 0.0;
        }

        $idEditarVariacao = max(0, (int)($_GET['editar'] ?? 0));
        if ($idEditarVariacao > 0 && $idProdutoVariacoes > 0) {
            $resultado = $conexao->query(
                'SELECT * FROM variacoes_produto WHERE id_variacao = ' . $idEditarVariacao .
                ' AND id_produto = ' . $idProdutoVariacoes . ' LIMIT 1'
            );
            $registroEdicao = $resultado->fetch_assoc() ?: null;
        }
    } elseif ($secao === 'administradores') {
        $administradores = buscarTodos(
            $conexao,
            'SELECT id_admin, usuario, nome, ativo, data_cadastro, ultimo_login
             FROM administradores
             ORDER BY ativo DESC, nome ASC, id_admin ASC'
        );

        $idEditarAdmin = max(0, (int)($_GET['editar'] ?? 0));
        if ($idEditarAdmin > 0) {
            $stmtAdmin = executarPreparado(
                $conexao,
                'SELECT id_admin, usuario, nome, ativo, data_cadastro, ultimo_login
                 FROM administradores
                 WHERE id_admin = ?
                 LIMIT 1',
                'i',
                [$idEditarAdmin]
            );
            $administradorEdicao = $stmtAdmin->get_result()->fetch_assoc() ?: null;
        }
    } else {
        $configAtual = $secoes[$secao];

        if ($secao === 'usuarios') {
            $filtroCategoria = 0;
            $filtroMarca = 0;

            /*
             * senha_hash propositalmente NÃO é selecionada.
             */
            $lista = buscarTodos(
                $conexao,
                'SELECT id_usuario, nome, email, ativo, data_cadastro, ultimo_login
                 FROM usuarios
                 ORDER BY data_cadastro DESC, id_usuario DESC'
            );
        } elseif ($secao === 'produtos') {
            $filtroCategoria = max(0, (int)($_GET['filtro_categoria'] ?? 0));
            $filtroMarca = max(0, (int)($_GET['filtro_marca'] ?? 0));
            $buscaProduto = trim((string)($_GET['busca_produto'] ?? ''));

            $condicoes = [];
            $parametros = [];
            $tiposParametros = '';

            if ($buscaProduto !== '') {
                $condicoes[] = '(p.nome LIKE ? OR cp.nome LIKE ? OR m.nome LIKE ?)';
                $termoBusca = '%' . $buscaProduto . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tiposParametros .= 'sss';
            }

            if ($filtroCategoria > 0) {
                $condicoes[] = 'p.id_categoria_produto = ?';
                $parametros[] = $filtroCategoria;
                $tiposParametros .= 'i';
            }

            if ($filtroMarca > 0) {
                $condicoes[] = 'p.id_marca = ?';
                $parametros[] = $filtroMarca;
                $tiposParametros .= 'i';
            }

            $sqlLista = 'SELECT p.*
                         FROM ' . $configAtual['tabela'] . ' p
                         LEFT JOIN categorias_produtos cp ON cp.id_categoria_produto = p.id_categoria_produto
                         LEFT JOIN marcas m ON m.id_marca = p.id_marca';
            if ($condicoes) {
                $sqlLista .= ' WHERE ' . implode(' AND ', $condicoes);
            }
            $sqlLista .= ' ORDER BY p.ordem, p.id_produto';

            if ($parametros) {
                $stmtLista = executarPreparado($conexao, $sqlLista, $tiposParametros, $parametros);
                $lista = $stmtLista->get_result()->fetch_all(MYSQLI_ASSOC);
            } else {
                $lista = buscarTodos($conexao, $sqlLista);
            }
        } elseif ($secao !== 'usuarios') {
            $filtroCategoria = 0;
            $filtroMarca = 0;
            $lista = buscarTodos(
                $conexao,
                'SELECT * FROM ' . $configAtual['tabela'] . ' ORDER BY ' . $configAtual['ordem_sql']
            );
        }

        $idEditar = max(0, (int)($_GET['editar'] ?? 0));
        if ($idEditar > 0) {
            if ($secao === 'usuarios') {
                $stmtUsuario = executarPreparado(
                    $conexao,
                    'SELECT id_usuario, nome, email, ativo, data_cadastro, ultimo_login
                     FROM usuarios
                     WHERE id_usuario = ?
                     LIMIT 1',
                    'i',
                    [$idEditar]
                );
                $registroEdicao = $stmtUsuario->get_result()->fetch_assoc() ?: null;
            } else {
                $registroEdicao = buscarLinha(
                    $conexao,
                    $configAtual['tabela'],
                    $configAtual['pk'],
                    $idEditar
                );
            }
        }
    }
} catch (Throwable $erro) {
    $flash = ['tipo' => 'danger', 'mensagem' => 'Não foi possível carregar esta seção: ' . $erro->getMessage()];
}

function valorFormulario(?array $registro, string $campo, array $configCampo)
{
    if ($registro && array_key_exists($campo, $registro)) {
        return $registro[$campo];
    }
    return $configCampo['padrao'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Garras & Patas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style-menu.css" rel="stylesheet">
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
    <style>
        :root {
            --admin-bg: #f6f7f9;
            --admin-card: #ffffff;
            --admin-text: #30343b;
            --admin-muted: #72767e;
            --admin-border: #e5e7eb;
            --admin-accent: #f28b30;
            --admin-accent-dark: #d96f17;
            --admin-soft: #fff4e8;
            --admin-danger: #b42318;
        }

        body {
            background: var(--admin-bg);
            color: var(--admin-text);
        }

        .admin-topo {
            background: #fff;
            border-bottom: 1px solid var(--admin-border);
            padding: 14px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .admin-brand img {
            width: 118px;
            max-height: 64px;
            object-fit: contain;
        }

        .admin-brand-text h1 {
            font-size: 1.15rem;
            margin: 0;
            font-weight: 800;
        }

        .admin-brand-text p {
            margin: 2px 0 0;
            color: var(--admin-muted);
            font-size: .88rem;
        }

        .admin-site-link {
            text-decoration: none;
            border: 1px solid var(--admin-border);
            background: #fff;
            color: var(--admin-text);
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-site-link:hover {
            background: var(--admin-soft);
            color: var(--admin-accent-dark);
        }

        .admin-shell {
            width: min(1440px, 94%);
            margin: 28px auto 60px;
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 26px;
        }

        .admin-topo-acoes {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-logout-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: inherit;
            padding: 10px 14px;
            border-radius: 10px;
            transition: opacity .2s ease, background-color .2s ease;
        }

        .admin-logout-link:hover {
            opacity: .85;
            background: rgba(0,0,0,.05);
        }

        .admin-sidebar {
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            padding: 16px;
            height: fit-content;
            position: sticky;
            top: 108px;
        }

        .admin-sidebar-title {
            font-weight: 800;
            padding: 8px 10px 14px;
            font-size: .92rem;
            color: var(--admin-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .admin-nav {
            display: grid;
            gap: 6px;
        }

        .admin-nav a {
            text-decoration: none;
            color: var(--admin-text);
            border-radius: 14px;
            padding: 11px 12px;
            display: flex;
            gap: 10px;
            align-items: center;
            font-weight: 650;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--admin-soft);
            color: var(--admin-accent-dark);
        }

        .admin-main {
            min-width: 0;
        }

        .admin-heading {
            margin-bottom: 20px;
        }

        .admin-heading h2 {
            font-size: clamp(1.55rem, 2.4vw, 2.2rem);
            font-weight: 850;
            margin: 0 0 6px;
        }

        .admin-heading p {
            margin: 0;
            color: var(--admin-muted);
        }

        .admin-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 8px 30px rgba(30, 34, 40, .04);
        }

        .admin-card h3 {
            font-size: 1.08rem;
            font-weight: 800;
            margin: 0 0 18px;
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .admin-field.full {
            grid-column: 1 / -1;
        }

        .admin-field label {
            display: block;
            margin-bottom: 7px;
            font-weight: 750;
            font-size: .92rem;
        }

        .admin-field input[type="text"],
        .admin-field input[type="email"],
        .admin-field input[type="number"],
        .admin-field input[type="file"],
        .admin-field select,
        .admin-field textarea {
            width: 100%;
            border: 1px solid #d7dae0;
            border-radius: 12px;
            padding: 11px 12px;
            background: #fff;
            outline: none;
        }

        .admin-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .admin-field input:focus,
        .admin-field select:focus,
        .admin-field textarea:focus {
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 3px rgba(242, 139, 48, .13);
        }

        .admin-help {
            color: var(--admin-muted);
            font-size: .82rem;
            margin-top: 7px;
        }

        .admin-check {
            min-height: 46px;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .admin-check input {
            width: 20px;
            height: 20px;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .admin-btn {
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .admin-btn-primary {
            background: var(--admin-accent);
            color: #fff;
        }

        .admin-btn-primary:hover {
            background: var(--admin-accent-dark);
            color: #fff;
        }

        .admin-btn-secondary {
            background: #f1f2f4;
            color: var(--admin-text);
        }

        .admin-btn-danger {
            background: #fff0ef;
            color: var(--admin-danger);
        }

        .admin-btn-small {
            padding: 7px 12px;
            font-size: .84rem;
        }

        .admin-alert {
            border-radius: 15px;
            padding: 13px 16px;
            margin-bottom: 18px;
            font-weight: 650;
        }

        .admin-alert-success {
            background: #ecfdf3;
            color: #067647;
            border: 1px solid #abefc6;
        }

        .admin-alert-danger {
            background: #fef3f2;
            color: #b42318;
            border: 1px solid #fecdca;
        }

        .admin-table-wrap {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        .admin-table th,
        .admin-table td {
            padding: 13px 12px;
            border-bottom: 1px solid var(--admin-border);
            text-align: left;
            vertical-align: middle;
        }

        .admin-table th {
            color: var(--admin-muted);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .admin-thumb {
            width: 66px;
            height: 52px;
            object-fit: contain;
            border-radius: 10px;
            background: #f5f5f5;
            border: 1px solid var(--admin-border);
        }

        .admin-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fb;
            border-radius: 12px;
        }

        .admin-preview img {
            width: 96px;
            height: 72px;
            object-fit: contain;
            background: #fff;
            border-radius: 10px;
            border: 1px solid var(--admin-border);
        }

        .admin-badge {
            display: inline-flex;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: .78rem;
            font-weight: 800;
        }

        .admin-badge-on {
            background: #ecfdf3;
            color: #067647;
        }

        .admin-badge-off {
            background: #f2f4f7;
            color: #667085;
        }

        .admin-empty {
            text-align: center;
            padding: 32px 16px;
            color: var(--admin-muted);
        }

        .admin-table-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .admin-delete-form {
            display: inline;
            margin: 0;
        }

        .admin-note {
            border-left: 4px solid var(--admin-accent);
            background: var(--admin-soft);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            color: #6d4b2f;
        }

        @media (max-width: 980px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                position: static;
            }
            .admin-nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .admin-topo {
                align-items: flex-start;
                padding-inline: 4%;
            }
            .admin-brand-text p {
                display: none;
            }
            .admin-brand img {
                width: 86px;
            }
            .admin-site-link span {
                display: none;
            }
            .admin-shell {
                width: 94%;
                margin-top: 18px;
            }
            .admin-nav {
                grid-template-columns: 1fr;
            }
            .admin-form-grid {
                grid-template-columns: 1fr;
            }
            .admin-field.full {
                grid-column: auto;
            }
            .admin-card {
                padding: 17px;
                border-radius: 18px;
            }
        }

        .admin-filtros {
            display: grid;
            grid-template-columns: minmax(260px, 1.7fr) minmax(180px, 1fr) minmax(180px, 1fr) auto;
            gap: 12px;
            align-items: end;
            margin: 14px 0 22px;
            padding: 14px;
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            background: #fafafa;
        }

        .admin-filtros .admin-field {
            margin: 0;
        }

        .admin-filtros-acoes {
            display: flex;
            gap: 8px;
            align-items: center;
            padding-bottom: 1px;
        }

        @media (max-width: 900px) {
            .admin-filtros {
                grid-template-columns: 1fr;
            }
        }

    

/* =========================================================
   RESPONSIVIDADE EXTRA DO PAINEL
========================================================= */
@media (max-width: 760px) {
    .admin-topo {
        flex-wrap: wrap;
        gap: 12px;
        padding: 12px 4%;
    }
    .admin-brand { flex: 1 1 220px; }
    .admin-topo-acoes {
        flex: 1 1 100%;
        width: 100%;
        justify-content: flex-end;
    }
    .admin-shell {
        width: 94%;
        margin-bottom: 35px;
    }
    .admin-card { padding: 15px; }
    .admin-actions { align-items: stretch; }
    .admin-actions .admin-btn { min-height: 42px; }
    .admin-table-wrap {
        margin-inline: -4px;
        padding-inline: 4px;
    }
    .admin-table { min-width: 680px; }
}

@media (max-width: 520px) {
    .admin-topo-acoes {
        justify-content: stretch;
        gap: 7px;
    }
    .admin-topo-acoes > * {
        flex: 1 1 0;
        min-width: 0;
    }
    .admin-site-link,
    .admin-logout-link {
        justify-content: center;
        padding: 9px 10px;
        text-align: center;
    }
    .admin-card { border-radius: 16px; }
    .admin-filtros { padding: 11px; }
    .admin-actions .admin-btn { width: 100%; }
    .admin-table-actions { flex-direction: column; align-items: stretch; }
    .admin-table-actions .admin-btn { width: 100%; }
}

</style>
    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
</head>
<body>
<a class="skip-link" href="#conteudoPrincipal">Pular para o conteúdo principal</a>


<header class="admin-topo">
    <div class="admin-brand">
        <a href="menu.php">
            <img src="../imagens/logo.png" alt="Garras & Patas Pet Shop">
        </a>
        <div class="admin-brand-text">
            <h1>Painel Administrativo</h1>
            <p>Edite o conteúdo exibido na página principal.</p>
        </div>
    </div>

    <div class="admin-topo-acoes">
        <a class="admin-site-link" href="menu.php" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>Visualizar menu.php</span>
        </a>
        <a class="admin-logout-link" href="logout-admin.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sair</span>
        </a>
    </div>
</header>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-title">Conteúdo do site</div>
        <nav class="admin-nav" aria-label="Navegação administrativa">
            <?php foreach ($secoes as $chave => $itemSecao): ?>
                <a href="admin.php?secao=<?= e($chave) ?>" class="<?= $secao === $chave ? 'active' : '' ?>">
                    <i class="bi <?= e($itemSecao['icone']) ?>"></i>
                    <span><?= e($itemSecao['titulo']) ?></span>
                </a>
            <?php endforeach; ?>
            <a href="admin.php?secao=administradores" class="<?= $secao === 'administradores' ? 'active' : '' ?>">
                <i class="bi bi-shield-lock"></i>
                <span>Administradores</span>
            </a>
        </nav>
    </aside>

    <main id="conteudoPrincipal" tabindex="-1" class="admin-main">
        <?php if ($flash): ?>
            <div class="admin-alert admin-alert-<?= e($flash['tipo']) ?>">
                <?= e($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

        <?php if ($secao === 'variacoes'): ?>
            <div class="admin-heading">
                <h2>Variações do produto</h2>
                <p>
                    <?php if ($produtoVariacoes): ?>
                        <?= e($produtoVariacoes['nome']) ?> — altere tamanhos/versões, imagens e preços exibidos no card.
                    <?php else: ?>
                        Selecione um produto na seção “Produtos”.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($produtoVariacoes): ?>
                <div class="admin-card">
                    <h3>Descontos</h3>
                    <form method="post" class="admin-form-desconto-variacoes">
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                        <input type="hidden" name="acao" value="configurar_desconto">
                        <input type="hidden" name="secao" value="variacoes">
                        <input type="hidden" name="id_produto" value="<?= (int)$produtoVariacoes['id_produto'] ?>">

                        <div class="admin-field">
                            <label for="modo_desconto">Como aplicar o desconto?</label>
                            <select id="modo_desconto" name="modo_desconto">
                                <option value="0" <?= $modoDescontoAtual === '0' ? 'selected' : '' ?>>Sem desconto</option>
                                <option value="1" <?= $modoDescontoAtual === '1' ? 'selected' : '' ?>>Mesmo desconto em todas</option>
                                <option value="2" <?= $modoDescontoAtual === '2' ? 'selected' : '' ?>>Desconto individual</option>
                            </select>
                        </div>

                        <div class="admin-field" id="campoDescontoIgual" <?= $modoDescontoAtual === '1' ? '' : 'hidden' ?>>
                            <label for="desconto_igual">Desconto (%)</label>
                            <input id="desconto_igual" type="text" name="desconto_igual" inputmode="decimal" value="<?= e($descontoIgualAtual > 0 ? rtrim(rtrim(number_format($descontoIgualAtual, 2, ',', '.'), '0'), ',') : '') ?>" placeholder="ex.: 15">
                        </div>

                        <div class="admin-actions">
                            <button class="admin-btn admin-btn-primary admin-btn-small" type="submit">
                                <i class="bi bi-check-lg"></i> Aplicar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="admin-card">
                    <h3><?= $registroEdicao ? 'Alterar variação' : 'Adicionar variação' ?></h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                        <input type="hidden" name="acao" value="salvar">
                        <input type="hidden" name="secao" value="variacoes">
                        <input type="hidden" name="id_produto" value="<?= (int)$produtoVariacoes['id_produto'] ?>">
                        <input type="hidden" name="id_variacao" value="<?= (int)($registroEdicao['id_variacao'] ?? 0) ?>">
                        <input type="hidden" name="imagem_atual" value="<?= e($registroEdicao['imagem'] ?? '') ?>">

                        <div class="admin-form-grid">
                            <div class="admin-field">
                                <label for="nome">Nome / tamanho</label>
                                <input id="nome" type="text" name="nome" required value="<?= e($registroEdicao['nome'] ?? '') ?>" placeholder="ex.: 15Kg, 20Kg, Único">
                            </div>

                            <div class="admin-field">
                                <label for="preco">Preço</label>
                                <input id="preco" type="text" name="preco" inputmode="decimal" required value="<?= e(isset($registroEdicao['preco']) ? precoBR($registroEdicao['preco']) : '') ?>" placeholder="ex.: 32,99">
                            </div>

                            <?php if ($modoDescontoAtual === '2'): ?>
                                <div class="admin-field">
                                    <label for="desconto_percentual">Desconto (%)</label>
                                    <input id="desconto_percentual" type="text" name="desconto_percentual" inputmode="decimal" value="<?= e(isset($registroEdicao['desconto_percentual']) && (int)($registroEdicao['promocao'] ?? 0) === 1 ? rtrim(rtrim(number_format((float)$registroEdicao['desconto_percentual'], 2, ',', '.'), '0'), ',') : '') ?>" placeholder="ex.: 15">
                                </div>
                            <?php elseif ($modoDescontoAtual === '1'): ?>
                                <div class="admin-field">
                                    <label>Desconto</label>
                                    <input type="text" value="<?= e(rtrim(rtrim(number_format($descontoIgualAtual, 2, ',', '.'), '0'), ',')) ?>%" readonly>
                                </div>
                            <?php else: ?>
                                <div class="admin-field">
                                    <label>Desconto</label>
                                    <input type="text" value="Sem desconto" readonly>
                                </div>
                            <?php endif; ?>

                            <div class="admin-field full">
                                <label for="imagem">Imagem da variação</label>
                                <input id="imagem" type="file" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif">
                                <input type="text" name="imagem_caminho" class="mt-2" placeholder="ou informe um caminho existente, ex.: ../marcas/produto.png">
                                <?php if (!empty($registroEdicao['imagem'])): ?>
                                    <div class="admin-preview">
                                        <img src="<?= e($registroEdicao['imagem']) ?>" alt="Imagem atual">
                                        <span>Imagem atual</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="admin-actions">
                            <button class="admin-btn admin-btn-primary" type="submit">
                                <i class="bi bi-check-lg"></i> Salvar variação
                            </button>
                            <?php if ($registroEdicao): ?>
                                <a class="admin-btn admin-btn-secondary" href="admin.php?secao=variacoes&produto=<?= (int)$produtoVariacoes['id_produto'] ?>">Cancelar edição</a>
                            <?php endif; ?>
                            <a class="admin-btn admin-btn-secondary" href="admin.php?secao=produtos">Voltar aos produtos</a>
                        </div>
                    </form>
                </div>

                <div class="admin-card">
                    <h3>Variações cadastradas</h3>
                    <?php if (!$variacoes): ?>
                        <div class="admin-empty">Nenhuma variação cadastrada. O menu.php usará o preço e a imagem principal do produto.</div>
                    <?php else: ?>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Nome / tamanho</th>
                                        <th>Preço normal</th>
                                        <th>Promoção</th>
                                        <th>Preço atual</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($variacoes as $variacao): ?>
                                    <?php
                                        $vPromocao = (int)($variacao['promocao'] ?? 0) === 1;
                                        $vDesconto = $vPromocao
                                            ? min(99.99, max(0, (float)($variacao['desconto_percentual'] ?? 0)))
                                            : 0.0;
                                        $vPrecoNormal = (float)$variacao['preco'];
                                        $vPrecoAtual = ($vPromocao && $vDesconto > 0)
                                            ? round($vPrecoNormal * (1 - ($vDesconto / 100)), 2)
                                            : $vPrecoNormal;
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($variacao['imagem'])): ?>
                                                <img class="admin-thumb" src="<?= e($variacao['imagem']) ?>" alt="">
                                            <?php else: ?>
                                                <span class="text-muted">Imagem principal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($variacao['nome']) ?></td>
                                        <td>R$ <?= e(precoBR($vPrecoNormal)) ?></td>
                                        <td><?= $vPromocao ? 'Sim (-' . e(rtrim(rtrim(number_format($vDesconto, 2, ',', '.'), '0'), ',')) . '%)' : 'Não' ?></td>
                                        <td>R$ <?= e(precoBR($vPrecoAtual)) ?></td>
                                        <td>
                                            <div class="admin-table-actions">
                                                <a class="admin-btn admin-btn-secondary admin-btn-small" href="admin.php?secao=variacoes&produto=<?= (int)$produtoVariacoes['id_produto'] ?>&editar=<?= (int)$variacao['id_variacao'] ?>">
                                                    <i class="bi bi-pencil"></i> Alterar
                                                </a>
                                                <form class="admin-delete-form" method="post" onsubmit="return confirm('Excluir esta variação?');">
                                                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="secao" value="variacoes">
                                                    <input type="hidden" name="id_produto" value="<?= (int)$produtoVariacoes['id_produto'] ?>">
                                                    <input type="hidden" name="id_variacao" value="<?= (int)$variacao['id_variacao'] ?>">
                                                    <button class="admin-btn admin-btn-danger admin-btn-small" type="submit">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($secao === 'administradores'): ?>
            <div class="admin-heading">
                <h2>Administradores</h2>
                <p>Gerencie as contas que podem acessar o painel administrativo.</p>
            </div>

            <div class="admin-card">
                <h3><?= $administradorEdicao ? 'Alterar administrador' : 'Novo administrador' ?></h3>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="secao" value="administradores">
                    <input type="hidden" name="id_admin" value="<?= (int)($administradorEdicao['id_admin'] ?? 0) ?>">

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="nome_admin">Nome</label>
                            <input type="text" id="nome_admin" name="nome_admin" value="<?= e($administradorEdicao['nome'] ?? '') ?>" required>
                        </div>

                        <div class="admin-field">
                            <label for="usuario_admin">Usuário</label>
                            <input type="text" id="usuario_admin" name="usuario_admin" value="<?= e($administradorEdicao['usuario'] ?? '') ?>" autocomplete="username" required>
                        </div>

                        <div class="admin-field">
                            <label for="senha_admin"><?= $administradorEdicao ? 'Nova senha' : 'Senha' ?></label>
                            <input type="password" id="senha_admin" name="senha_admin" autocomplete="new-password" <?= $administradorEdicao ? '' : 'required' ?>>
                        </div>

                        <div class="admin-field">
                            <label for="confirmar_senha_admin">Confirmar senha</label>
                            <input type="password" id="confirmar_senha_admin" name="confirmar_senha_admin" autocomplete="new-password" <?= $administradorEdicao ? '' : 'required' ?>>
                        </div>

                        <div class="admin-field full">
                            <label class="admin-check">
                                <input type="checkbox" name="ativo_admin" value="1" <?= !isset($administradorEdicao['ativo']) || (int)$administradorEdicao['ativo'] === 1 ? 'checked' : '' ?>>
                                <span>Conta ativa</span>
                            </label>
                        </div>
                    </div>

                    <div class="admin-actions">
                        <button class="admin-btn admin-btn-primary" type="submit">
                            <i class="bi bi-check-lg"></i>
                            <?= $administradorEdicao ? 'Salvar alterações' : 'Criar administrador' ?>
                        </button>
                        <?php if ($administradorEdicao): ?>
                            <a class="admin-btn admin-btn-secondary" href="admin.php?secao=administradores">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="admin-card">
                <h3>Administradores cadastrados</h3>
                <?php if (!$administradores): ?>
                    <div class="admin-empty">Nenhum administrador cadastrado.</div>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Usuário</th>
                                    <th>Status</th>
                                    <th>Cadastro</th>
                                    <th>Último login</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($administradores as $adminItem): ?>
                                <?php $adminAtualLogado = (int)$adminItem['id_admin'] === (int)($_SESSION['admin_id'] ?? 0); ?>
                                <tr>
                                    <td><?= e($adminItem['nome']) ?><?= $adminAtualLogado ? ' <span class="admin-help">(você)</span>' : '' ?></td>
                                    <td><?= e($adminItem['usuario']) ?></td>
                                    <td>
                                        <span class="admin-badge <?= (int)$adminItem['ativo'] === 1 ? 'admin-badge-on' : 'admin-badge-off' ?>">
                                            <?= (int)$adminItem['ativo'] === 1 ? 'Ativo' : 'Desativado' ?>
                                        </span>
                                    </td>
                                    <td><?= e(dataHoraBR($adminItem['data_cadastro'] ?? '')) ?></td>
                                    <td><?= e(dataHoraBR($adminItem['ultimo_login'] ?? '')) ?></td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a class="admin-btn admin-btn-secondary admin-btn-small" href="admin.php?secao=administradores&editar=<?= (int)$adminItem['id_admin'] ?>">
                                                <i class="bi bi-pencil"></i> Alterar
                                            </a>
                                            <?php if (!$adminAtualLogado): ?>
                                                <form class="admin-delete-form" method="post" onsubmit="return confirm('Tem certeza que deseja excluir este administrador?');">
                                                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="secao" value="administradores">
                                                    <input type="hidden" name="id_admin" value="<?= (int)$adminItem['id_admin'] ?>">
                                                    <button class="admin-btn admin-btn-danger admin-btn-small" type="submit">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="admin-heading">
                <h2><?= e($configAtual['titulo']) ?></h2>
                <p>
                    <?= $secao === 'produtos'
                        ? 'Cadastre todos os produtos, classifique por pet/categoria/marca, escolha os destaques e aplique promoções.'
                        : ($secao === 'categorias_produtos'
                            ? 'Crie e altere as categorias exibidas nos menus de Cachorro e Gato. Os produtos ficam vinculados pelo ID, então renomear uma categoria não quebra os vínculos.'
                            : ($secao === 'usuarios'
                                ? 'Visualize e gerencie as contas cadastradas. O gerente pode alterar nome, e-mail e status, mas nunca a senha.'
                                : ($secao === 'lojas'
                                    ? 'Cadastre e mantenha atualizadas as lojas físicas do Garras & Patas. Você pode alterar endereço, contatos, horário, mapa, imagem e a ordem de exibição.'
                                    : 'Cadastre, altere, oculte ou exclua os elementos desta área do menu.php.'))) ?>
                </p>
            </div>

            <?php if ($secao === 'usuarios'): ?>
                <div class="admin-note">
                    <strong>Segurança:</strong> o gerente pode alterar somente nome, e-mail e o status da conta.
                    A senha e o hash da senha não são exibidos, não são carregados na listagem e não fazem parte do formulário de edição.
                    Novas contas continuam sendo criadas apenas em <code>criar-conta.php</code>.
                </div>
            <?php elseif ($secao === 'lojas'): ?>
                <div class="admin-note">
                    <strong>Importante:</strong> as lojas ficam salvas na tabela <code>lojas</code> do banco <code>petshop_admin</code>.
                    Marque “Exibir no site” para publicar a loja e deixe desmarcado para ocultá-la sem apagar o cadastro.
                </div>
            <?php else: ?>
                <div class="admin-note">
                    <strong>Como funciona:</strong> ao clicar em Salvar, a informação é gravada no banco <code>petshop_admin</code>. O <code>menu.php</code> consulta esse mesmo banco, portanto a alteração aparece quando a página é atualizada.
                </div>
            <?php endif; ?>

            <?php if ($secao !== 'usuarios' || $registroEdicao): ?>
            <div class="admin-card">
                <h3>
                    <?php if ($secao === 'usuarios'): ?>
                        Alterar usuário
                    <?php else: ?>
                        <?= $registroEdicao ? 'Alterar item' : 'Adicionar novo item' ?>
                    <?php endif; ?>
                </h3>

                <?php if ($secao === 'usuarios'): ?>
                    <div class="admin-note">
                        A senha deste usuário permanecerá exatamente como está.
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="secao" value="<?= e($secao) ?>">
                    <input type="hidden" name="id" value="<?= (int)($registroEdicao[$configAtual['pk']] ?? 0) ?>">

                    <div class="admin-form-grid">
                        <?php foreach ($configAtual['campos'] as $nomeCampo => $campo): ?>
                            <?php
                                $tipo = $campo['tipo'];
                                $valor = valorFormulario($registroEdicao, $nomeCampo, $campo);
                                $classeFull = in_array($tipo, ['image', 'textarea'], true) ? ' full' : '';
                            ?>
                            <div class="admin-field<?= $classeFull ?>">
                                <?php if ($tipo === 'checkbox'): ?>
                                    <label class="admin-check">
                                        <input type="checkbox" name="<?= e($nomeCampo) ?>" value="1" <?= (int)$valor === 1 ? 'checked' : '' ?>>
                                        <span><?= e($campo['rotulo']) ?></span>
                                    </label>

                                <?php elseif ($tipo === 'textarea'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <textarea id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>><?= e($valor) ?></textarea>

                                <?php elseif ($tipo === 'image'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <input type="file" id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" accept="image/jpeg,image/png,image/webp,image/gif">
                                    <input type="text" name="<?= e($nomeCampo) ?>_caminho" class="mt-2" placeholder="ou informe um caminho existente no projeto">
                                    <div class="admin-help">
                                        <?php if ($secao === 'marcas' && $nomeCampo === 'imagem'): ?>
                                            A logo é opcional. Você pode cadastrar a marca somente pelo nome e adicionar ou alterar a logo depois.
                                        <?php elseif ($secao === 'lojas' && $nomeCampo === 'imagem'): ?>
                                            A imagem da loja é opcional. Ao editar, deixe os campos vazios para manter a imagem atual.
                                        <?php else: ?>
                                            Você pode enviar uma nova imagem ou usar um caminho já existente. Ao editar, deixe ambos vazios para manter a imagem atual.
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($valor)): ?>
                                        <div class="admin-preview">
                                            <img src="<?= e($valor) ?>" alt="Imagem atual">
                                            <div>
                                                <strong>Imagem atual</strong><br>
                                                <small><?= e($valor) ?></small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif ($tipo === 'marca'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <select id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>">
                                        <option value="0">Sem marca</option>
                                        <?php foreach ($marcasDisponiveis as $marca): ?>
                                            <option value="<?= (int)$marca['id_marca'] ?>" <?= (int)$valor === (int)$marca['id_marca'] ? 'selected' : '' ?>>
                                                <?= e($marca['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($tipo === 'categoria_produto'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <select id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>>
                                        <option value="0">Selecione uma categoria</option>
                                        <?php foreach ($categoriasProdutosDisponiveis as $categoriaProduto): ?>
                                            <option
                                                value="<?= (int)$categoriaProduto['id_categoria_produto'] ?>"
                                                data-pet="<?= e($categoriaProduto['tipo_pet']) ?>"
                                                <?= (int)$valor === (int)$categoriaProduto['id_categoria_produto'] ? 'selected' : '' ?>
                                            >
                                                <?= e($categoriaProduto['nome']) ?> — <?= e($categoriaProduto['tipo_pet'] === 'ambos' ? 'Cachorro e gato' : ucfirst($categoriaProduto['tipo_pet'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="admin-help">As opções vêm da seção “Categorias de produtos”.</div>

                                <?php elseif ($tipo === 'categoria_tudo'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <select id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>>
                                        <option value="0">Selecione uma categoria</option>
                                        <?php foreach ($categoriasProdutosDisponiveis as $categoriaProduto): ?>
                                            <?php
                                                $petArea = (string)($campo['pet'] ?? '');
                                                $petCategoria = (string)($categoriaProduto['tipo_pet'] ?? 'ambos');
                                                $compativel = in_array($petCategoria, [$petArea, 'ambos'], true);
                                                $estaSelecionada = (int)$valor === (int)$categoriaProduto['id_categoria_produto'];
                                                $categoriaAtiva = (int)($categoriaProduto['ativo'] ?? 0) === 1;
                                            ?>
                                            <?php if (!$compativel || (!$categoriaAtiva && !$estaSelecionada)): ?>
                                                <?php continue; ?>
                                            <?php endif; ?>
                                            <option
                                                value="<?= (int)$categoriaProduto['id_categoria_produto'] ?>"
                                                <?= (int)$valor === (int)$categoriaProduto['id_categoria_produto'] ? 'selected' : '' ?>
                                            >
                                                <?= e($categoriaProduto['nome']) ?><?= (int)($categoriaProduto['ativo'] ?? 0) === 1 ? '' : ' (oculta)' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($tipo === 'select'): ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <select id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>>
                                        <?php foreach (($campo['opcoes'] ?? []) as $valorOpcao => $rotuloOpcao): ?>
                                            <option value="<?= e($valorOpcao) ?>" <?= (string)$valor === (string)$valorOpcao ? 'selected' : '' ?>>
                                                <?= e($rotuloOpcao) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php else: ?>
                                    <label for="<?= e($nomeCampo) ?>"><?= e($campo['rotulo']) ?></label>
                                    <?php if ($tipo === 'decimal'): ?>
                                        <input type="text" inputmode="decimal" id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" value="<?= e($valor !== '' ? precoBR($valor) : '') ?>" placeholder="ex.: 49,90" <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>>
                                    <?php elseif ($tipo === 'percentual'): ?>
                                        <input type="text" inputmode="decimal" id="<?= e($nomeCampo) ?>" name="<?= e($nomeCampo) ?>" value="<?= e($valor !== '' ? rtrim(rtrim(number_format((float)$valor, 2, ',', '.'), '0'), ',') : '') ?>" placeholder="<?= e($campo['placeholder'] ?? 'ex.: 15') ?>">
                                        
                                    <?php else: ?>
                                        <input
                                            type="<?= $tipo === 'number' ? 'number' : ($tipo === 'email' ? 'email' : 'text') ?>"
                                            id="<?= e($nomeCampo) ?>"
                                            name="<?= e($nomeCampo) ?>"
                                            value="<?= e($valor) ?>"
                                            placeholder="<?= e($campo['placeholder'] ?? '') ?>"
                                            <?= ($campo['obrigatorio'] ?? false) ? 'required' : '' ?>
                                            <?= $tipo === 'email' ? 'autocomplete="email"' : '' ?>
                                        >
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="admin-actions">
                        <button class="admin-btn admin-btn-primary" type="submit">
                            <i class="bi bi-check-lg"></i>
                            <?= $registroEdicao ? 'Salvar alterações' : 'Adicionar' ?>
                        </button>
                        <?php if ($registroEdicao): ?>
                            <a class="admin-btn admin-btn-secondary" href="admin.php?secao=<?= e($secao) ?>">
                                Cancelar edição
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="admin-card">
                <h3>
                    <?= $secao === 'produtos'
                        ? 'Produtos cadastrados'
                        : ($secao === 'usuarios'
                            ? 'Usuários cadastrados'
                            : ($secao === 'lojas' ? 'Lojas cadastradas' : 'Itens cadastrados')) ?>
                </h3>

                <?php if ($secao === 'produtos'): ?>
                    <form class="admin-filtros" method="get">
                        <input type="hidden" name="secao" value="produtos">

                        <div class="admin-field">
                            <label for="busca_produto">Buscar por nome, categoria ou marca</label>
                            <input
                                type="search"
                                id="busca_produto"
                                name="busca_produto"
                                value="<?= e($buscaProduto ?? '') ?>"
                                placeholder="Digite o nome, a categoria ou a marca..."
                            >
                        </div>

                        <div class="admin-field">
                            <label for="filtro_categoria">Filtrar por categoria</label>
                            <select id="filtro_categoria" name="filtro_categoria">
                                <option value="0">Todas as categorias</option>
                                <?php foreach ($categoriasProdutosDisponiveis as $categoriaFiltro): ?>
                                    <option value="<?= (int)$categoriaFiltro['id_categoria_produto'] ?>" <?= (int)($filtroCategoria ?? 0) === (int)$categoriaFiltro['id_categoria_produto'] ? 'selected' : '' ?>>
                                        <?= e($categoriaFiltro['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="filtro_marca">Filtrar por marca</label>
                            <select id="filtro_marca" name="filtro_marca">
                                <option value="0">Todas as marcas</option>
                                <?php foreach ($marcasDisponiveis as $marcaFiltro): ?>
                                    <option value="<?= (int)$marcaFiltro['id_marca'] ?>" <?= (int)($filtroMarca ?? 0) === (int)$marcaFiltro['id_marca'] ? 'selected' : '' ?>>
                                        <?= e($marcaFiltro['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-filtros-acoes">
                            <button class="admin-btn admin-btn-primary admin-btn-small" type="submit">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a class="admin-btn admin-btn-secondary admin-btn-small" href="admin.php?secao=produtos">
                                Limpar
                            </a>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (!$lista): ?>
                    <div class="admin-empty">Ainda não há itens cadastrados nesta seção.</div>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <?php foreach ($configAtual['campos'] as $nomeCampo => $campo): ?>
                                        <?php if ($nomeCampo === 'ordem' || $nomeCampo === 'ativo' || in_array($campo['tipo'], ['image', 'text', 'email', 'decimal', 'percentual', 'marca', 'categoria_produto', 'categoria_tudo', 'select', 'checkbox'], true)): ?>
                                            <th><?= e($campo['rotulo']) ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($secao === 'usuarios'): ?>
                                        <th>Cadastro</th>
                                        <th>Último login</th>
                                    <?php endif; ?>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lista as $item): ?>
                                <tr>
                                    <?php foreach ($configAtual['campos'] as $nomeCampo => $campo): ?>
                                        <?php if (!($nomeCampo === 'ordem' || $nomeCampo === 'ativo' || in_array($campo['tipo'], ['image', 'text', 'email', 'decimal', 'percentual', 'marca', 'categoria_produto', 'categoria_tudo', 'select', 'checkbox'], true))): ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <td>
                                            <?php if ($campo['tipo'] === 'image'): ?>
                                                <?php if (!empty($item[$nomeCampo])): ?>
                                                    <img class="admin-thumb" src="<?= e($item[$nomeCampo]) ?>" alt="">
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            <?php elseif ($campo['tipo'] === 'checkbox'): ?>
                                                <?php
                                                    $marcado = (int)($item[$nomeCampo] ?? 0) === 1;
                                                    if ($nomeCampo === 'ativo') {
                                                        if ($secao === 'usuarios') {
                                                            $textoBadge = $marcado ? 'Ativa' : 'Desativada';
                                                        } else {
                                                            $textoBadge = $marcado ? 'Visível' : 'Oculto';
                                                        }
                                                    } else {
                                                        $textoBadge = $marcado ? 'Sim' : 'Não';
                                                    }
                                                ?>
                                                <span class="admin-badge <?= $marcado ? 'admin-badge-on' : 'admin-badge-off' ?>">
                                                    <?= e($textoBadge) ?>
                                                </span>
                                            <?php elseif ($campo['tipo'] === 'decimal'): ?>
                                                R$ <?= e(precoBR($item[$nomeCampo])) ?>
                                            <?php elseif ($campo['tipo'] === 'percentual'): ?>
                                                <?= e(rtrim(rtrim(number_format((float)($item[$nomeCampo] ?? 0), 2, ',', '.'), '0'), ',')) ?>%
                                            <?php elseif ($campo['tipo'] === 'select'): ?>
                                                <?= e(($campo['opcoes'][$item[$nomeCampo] ?? ''] ?? ($item[$nomeCampo] ?? ''))) ?>
                                            <?php elseif ($campo['tipo'] === 'categoria_produto'): ?>
                                                <?php
                                                    $nomeCategoriaLista = 'Sem categoria';
                                                    foreach ($categoriasProdutosDisponiveis as $categoriaLista) {
                                                        if ((int)$categoriaLista['id_categoria_produto'] === (int)($item[$nomeCampo] ?? 0)) {
                                                            $nomeCategoriaLista = $categoriaLista['nome'];
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <?= e($nomeCategoriaLista) ?>
                                            <?php elseif ($campo['tipo'] === 'categoria_tudo'): ?>
                                                <?php
                                                    $nomeCategoriaTudoLista = 'Sem categoria';
                                                    foreach ($categoriasProdutosDisponiveis as $categoriaLista) {
                                                        if ((int)$categoriaLista['id_categoria_produto'] === (int)($item[$nomeCampo] ?? 0)) {
                                                            $nomeCategoriaTudoLista = $categoriaLista['nome'];
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <?= e($nomeCategoriaTudoLista) ?>
                                            <?php elseif ($campo['tipo'] === 'marca'): ?>
                                                <?php
                                                    $nomeMarcaLista = 'Sem marca';
                                                    foreach ($marcasDisponiveis as $marcaLista) {
                                                        if ((int)$marcaLista['id_marca'] === (int)($item[$nomeCampo] ?? 0)) {
                                                            $nomeMarcaLista = $marcaLista['nome'];
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <?= e($nomeMarcaLista) ?>
                                            <?php else: ?>
                                                <?= e($item[$nomeCampo] ?? '') ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <?php if ($secao === 'usuarios'): ?>
                                        <td><?= e(dataHoraBR($item['data_cadastro'] ?? '')) ?></td>
                                        <td><?= e(dataHoraBR($item['ultimo_login'] ?? '')) ?></td>
                                    <?php endif; ?>

                                    <td>
                                        <div class="admin-table-actions">
                                            <a class="admin-btn admin-btn-secondary admin-btn-small" href="admin.php?secao=<?= e($secao) ?>&editar=<?= (int)$item[$configAtual['pk']] ?>">
                                                <i class="bi bi-pencil"></i> Alterar
                                            </a>

                                            <?php if ($secao === 'produtos'): ?>
                                                <a class="admin-btn admin-btn-secondary admin-btn-small" href="admin.php?secao=variacoes&produto=<?= (int)$item[$configAtual['pk']] ?>">
                                                    <i class="bi bi-sliders"></i> Variações
                                                </a>
                                            <?php endif; ?>

                                            <form class="admin-delete-form" method="post" onsubmit="return confirm(<?= $secao === 'usuarios' ? e(json_encode('Tem certeza que deseja excluir esta conta de usuário? Esta ação não altera senhas; ela remove a conta.')) : e(json_encode('Tem certeza que deseja excluir este item?')) ?>);">
                                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                                <input type="hidden" name="acao" value="excluir">
                                                <input type="hidden" name="secao" value="<?= e($secao) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$item[$configAtual['pk']] ?>">
                                                <button class="admin-btn admin-btn-danger admin-btn-small" type="submit">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
(function () {
    const pet = document.getElementById('tipo_pet');
    const categoria = document.getElementById('id_categoria_produto');
    if (!pet || !categoria) return;

    function atualizarCategorias() {
        const petSelecionado = pet.value;
        Array.from(categoria.options).forEach(function (opcao) {
            if (!opcao.value || opcao.value === '0') return;
            const petCategoria = opcao.dataset.pet || 'ambos';
            const compativel = petSelecionado === 'ambos'
                ? petCategoria === 'ambos'
                : (petCategoria === 'ambos' || petCategoria === petSelecionado);
            opcao.hidden = !compativel;
            opcao.disabled = !compativel;
        });
        if (categoria.selectedOptions.length && categoria.selectedOptions[0].disabled) {
            categoria.value = '0';
        }
    }

    pet.addEventListener('change', atualizarCategorias);
    atualizarCategorias();
})();
</script>

<script>
(function () {
    const modo = document.getElementById('modo_desconto');
    const campo = document.getElementById('campoDescontoIgual');
    const input = document.getElementById('desconto_igual');

    function atualizar() {
        if (!modo || !campo || !input) return;
        const igual = modo.value === '1';
        campo.hidden = !igual;
        input.required = igual;
        input.disabled = !igual;
    }

    modo?.addEventListener('change', atualizar);
    atualizar();
})();
</script>

<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
