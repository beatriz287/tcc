<?php
require_once __DIR__ . '/conexao.php';

function h($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function normalizarTextoLoja(string $texto): string {
    $texto = trim($texto);
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
    return $texto;
}

function montarEnderecoLoja(array $loja): string {
    $linha1 = trim((string)$loja['endereco']);
    if (trim((string)$loja['numero']) !== '') {
        $linha1 .= ', ' . trim((string)$loja['numero']);
    }
    if (trim((string)$loja['complemento']) !== '') {
        $linha1 .= ' — ' . trim((string)$loja['complemento']);
    }

    $linha2 = [];
    if (trim((string)$loja['bairro']) !== '') {
        $linha2[] = trim((string)$loja['bairro']);
    }

    $cidadeEstado = trim((string)$loja['cidade']);
    if (trim((string)$loja['estado']) !== '') {
        $cidadeEstado .= ' — ' . strtoupper(trim((string)$loja['estado']));
    }
    if ($cidadeEstado !== '') {
        $linha2[] = $cidadeEstado;
    }

    return h($linha1) . ($linha2 ? '<br>' . h(implode(' — ', $linha2)) : '');
}

function imagemLojaSrc(?string $imagem): string {
    $imagem = trim((string)$imagem);
    if ($imagem === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $imagem) || str_starts_with($imagem, 'data:image/')) {
        return $imagem;
    }
    return $imagem;
}

function estadoNome(string $uf): string {
    static $estados = [
        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
    ];
    $uf = strtoupper(trim($uf));
    return $estados[$uf] ?? $uf;
}

function normalizarDiaSemanaLoja(string $dia): ?int {
    $dia = normalizarTextoLoja($dia);
    $mapa = [
        'domingo' => 0,
        'segunda' => 1, 'segunda-feira' => 1,
        'terca' => 2, 'terca-feira' => 2,
        'quarta' => 3, 'quarta-feira' => 3,
        'quinta' => 4, 'quinta-feira' => 4,
        'sexta' => 5, 'sexta-feira' => 5,
        'sabado' => 6, 'sábado' => 6,
    ];
    return $mapa[$dia] ?? null;
}

function lojaEstaAbertaAgora(string $horario): ?bool {
    $horario = trim($horario);
    if ($horario === '') return null;

    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $diaAtual = (int)$agora->format('w');
    $minutoAtual = ((int)$agora->format('H') * 60) + (int)$agora->format('i');

    $linhas = preg_split('/\R+/u', $horario) ?: [];
    $encontrouDia = false;
    $encontrouHorario = false;

    foreach ($linhas as $linha) {
        $linha = trim((string)$linha);
        if ($linha === '') continue;

        $texto = normalizarTextoLoja($linha);
        $diasPermitidos = [];

        if (preg_match('/segunda\s+a\s+sexta/', $texto)) {
            $diasPermitidos = [1,2,3,4,5];
        } elseif (preg_match('/segunda\s+a\s+sabado/', $texto)) {
            $diasPermitidos = [1,2,3,4,5,6];
        } elseif (preg_match('/segunda\s*,?\s*quarta\s*,?\s*sexta/', $texto)) {
            $diasPermitidos = [1,3,5];
        } else {
            foreach ([
                'domingo' => 0, 'segunda' => 1, 'terca' => 2, 'quarta' => 3,
                'quinta' => 4, 'sexta' => 5, 'sabado' => 6
            ] as $nomeDia => $numeroDia) {
                if (str_contains($texto, $nomeDia)) {
                    $diasPermitidos[] = $numeroDia;
                }
            }
        }

        if (!$diasPermitidos) continue;
        $encontrouDia = true;

        if (!in_array($diaAtual, $diasPermitidos, true)) continue;

        preg_match_all('/(\d{1,2})(?:[:h](\d{2}))?\s*(?:as|a|-|–|—)\s*(\d{1,2})(?:[:h](\d{2}))?/u', $texto, $matches, PREG_SET_ORDER);
        if (!$matches) continue;
        $encontrouHorario = true;

        foreach ($matches as $match) {
            $inicioH = (int)$match[1];
            $inicioM = isset($match[2]) && $match[2] !== '' ? (int)$match[2] : 0;
            $fimH = (int)$match[3];
            $fimM = isset($match[4]) && $match[4] !== '' ? (int)$match[4] : 0;
            $inicio = $inicioH * 60 + $inicioM;
            $fim = $fimH * 60 + $fimM;

            if ($minutoAtual >= $inicio && $minutoAtual < $fim) {
                return true;
            }
        }
    }

    if ($encontrouDia && $encontrouHorario) return false;
    return null;
}

$lojas = [];
$estadosDisponiveis = [];

try {
    $resultado = $conexao->query(
        "SELECT id_loja, nome, endereco, numero, complemento, bairro, cidade, estado, cep,
                telefone, whatsapp, horario, mapa_url, imagem, servicos, ordem
         FROM lojas
         WHERE ativo = 1
         ORDER BY ordem, nome, id_loja"
    );
    $lojas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $erro) {
    $lojas = [];
}

foreach ($lojas as $loja) {
    $uf = strtoupper(trim((string)$loja['estado']));
    if ($uf !== '') {
        $estadosDisponiveis[$uf] = estadoNome($uf);
    }
}
asort($estadosDisponiveis, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja</title>
        <link
        rel="stylesheet"
        href="../css/style-lojas.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <link rel="stylesheet" href="../css/acessibilidade-responsivo.css?v=1">
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@latest/dist/accessible-web-widget.min.js"></script>
</head>
<body>
    <a class="skip-link" href="#conteudo-principal">Pular para o conteúdo principal</a>
     <header class="topo">
    
    <div class="logo">
    <a href="menu.php">
        <img src="../imagens/logo.png" alt="Garras & Patas Pet Shop">
    </a>
</div>

  <div class="espaco-topo"></div>

    <div class="icons">

        <div class="user">
            <a href="form.html">
                <i class="bi bi-lock"></i>
                <span>Ambiente seguro</span>
            </a>
        </div>

        

    </div>

</header>



   <section class="lojas-hero">
    <a href="menu.php" class="btn-fechar">
    <i class="bi bi-x-lg"></i>
    Fechar
</a>

    

    <div class="lojas-hero-conteudo">

        <span class="lojas-selo">
            <i class="bi bi-geo-alt-fill"></i>
            Nossas unidades
        </span>

        <h1>
            Encontre uma Garra & Patas perto de você
        </h1>

        <p>
            Visite nossas lojas e encontre rações, brinquedos,
            acessórios e tudo o que seu pet precisa.
        </p>

        <div class="lojas-hero-botoes">

            <a href="#lista-lojas" class="btn-loja-principal">
                Ver nossas lojas
                <i class="bi bi-arrow-down"></i>
            </a>

            

        </div>

    </div>

    <div class="lojas-hero-decoracao">

        <div class="circulo-loja circulo-mapa">
            <i class="bi bi-geo-alt-fill"></i>
        </div>

        <div class="circulo-loja circulo-petshop">
            <i class="bi bi-shop"></i>
        </div>

        <i class="bi bi-paw-fill pata-loja pata-loja-1"></i>
        <i class="bi bi-paw-fill pata-loja pata-loja-2"></i>
        <i class="bi bi-paw-fill pata-loja pata-loja-3"></i>

    </div>

</section>


    <!-- ================= CONTEÚDO ================= -->

    <main class="lojas-container" id="conteudo-principal" tabindex="-1">

        <!-- INTRODUÇÃO -->

        <section class="inicio-lojas" aria-labelledby="titulo-inicio-lojas">

            <span class="subtitulo">
                Estamos perto de você
            </span>

            <h2 id="titulo-inicio-lojas">
                Escolha a unidade mais próxima
            </h2>

            <p>
                Consulte endereço, horário de funcionamento e
                os canais de atendimento de cada loja.
            </p>

        </section>


        <!-- PESQUISA E FILTROS -->

        <section class="filtros-lojas" aria-labelledby="titulo-filtros-lojas">
            <h2 class="sr-only" id="titulo-filtros-lojas">Pesquisar e filtrar lojas</h2>

            <div class="campo-pesquisa">

                <i class="bi bi-search"></i>

                <label class="sr-only" for="pesquisaLoja">Pesquisar por cidade, bairro ou endereço</label>
                <input
                    type="search"
                    id="pesquisaLoja"
                    placeholder="Digite uma cidade, bairro ou endereço"
                    autocomplete="off"
                    aria-controls="lista-lojas"
                >

            </div>

            <div class="filtro-estado">

                <i class="bi bi-map"></i>

                <label class="sr-only" for="filtroEstado">Filtrar por estado</label>
                <select id="filtroEstado" aria-controls="lista-lojas">

                    <option value="todos">
                        Todos os estados
                    </option>

                    <?php foreach ($estadosDisponiveis as $uf => $nomeEstado): ?>
                        <option value="<?= h(strtolower($uf)) ?>"><?= h($nomeEstado) ?></option>
                    <?php endforeach; ?>

                </select>

            </div>

        </section>


        <!-- ================= CARTÕES DAS LOJAS ================= -->

        <h2 class="sr-only" id="titulo-lista-lojas">Unidades disponíveis</h2>

        <section
            class="lista-lojas"
            id="lista-lojas"
            aria-labelledby="titulo-lista-lojas"
        >

            <?php foreach ($lojas as $loja): ?>
                <?php
                    $idLoja = (int)$loja['id_loja'];
                    $uf = strtoupper(trim((string)$loja['estado']));
                    $pesquisa = implode(' ', [
                        $loja['nome'] ?? '', $loja['endereco'] ?? '', $loja['numero'] ?? '',
                        $loja['complemento'] ?? '', $loja['bairro'] ?? '', $loja['cidade'] ?? '',
                        $loja['estado'] ?? '', $loja['cep'] ?? ''
                    ]);
                    $statusAberta = lojaEstaAbertaAgora((string)$loja['horario']);
                    $imagem = imagemLojaSrc($loja['imagem'] ?? '');
                    $servicos = preg_split('/\R+|[,;]+/u', (string)($loja['servicos'] ?? '')) ?: [];
                    $servicos = array_values(array_filter(array_map('trim', $servicos)));
                ?>

                <article
                    class="loja-card"
                    data-estado="<?= h(strtolower($uf)) ?>"
                    data-pesquisa="<?= h(normalizarTextoLoja($pesquisa)) ?>"
                >

                    <div class="loja-imagem">

                        <?php if ($imagem !== ''): ?>
                            <img src="<?= h($imagem) ?>" alt="<?= h($loja['nome']) ?>" class="loja-imagem-real">
                        <?php else: ?>
                            <div class="loja-imagem-placeholder">
                                <i class="bi bi-shop-window"></i>
                            </div>
                        <?php endif; ?>

                        <span class="loja-status <?= $statusAberta === true ? 'aberta' : 'fechada' ?>">
                            <?php if ($statusAberta === true): ?>
                                Aberta agora
                            <?php elseif ($statusAberta === false): ?>
                                Fechada
                            <?php else: ?>
                                Confira o horário
                            <?php endif; ?>
                        </span>

                    </div>

                    <div class="loja-conteudo">

                        <div class="loja-cabecalho">

                            <div>

                                <span class="loja-tipo">
                                    Loja física
                                </span>

                                <h3>
                                    <?= h($loja['nome']) ?>
                                </h3>

                            </div>

                            <button
                                class="btn-favoritar"
                                aria-label="Favoritar loja"
                                aria-pressed="false"
                                type="button"
                                data-id-loja="<?= $idLoja ?>"
                            >

                                <i class="bi bi-heart"></i>

                            </button>

                        </div>

                        <div class="loja-informacoes">

                            <div class="informacao-item">

                                <i class="bi bi-geo-alt-fill"></i>

                                <div>

                                    <strong>Endereço</strong>

                                    <p>
                                        <?= montarEnderecoLoja($loja) ?>
                                    </p>

                                </div>

                            </div>

                            <div class="informacao-item">

                                <i class="bi bi-clock-fill"></i>

                                <div>

                                    <strong>Horário de funcionamento</strong>

                                    <p>
                                        <?= nl2br(h($loja['horario'])) ?>
                                    </p>

                                </div>

                            </div>

                            <div class="informacao-item">

                                <i class="bi bi-telephone-fill"></i>

                                <div>

                                    <strong>Telefone</strong>

                                    <p>
                                        <?= trim((string)$loja['telefone']) !== '' ? h($loja['telefone']) : 'Não informado' ?>
                                    </p>

                                </div>

                            </div>

                        </div>

                        <div class="servicos-loja">

                            <?php if ($servicos): ?>
                                <?php foreach ($servicos as $servico): ?>
                                    <span>
                                        <i class="bi bi-check2-circle"></i>
                                        <?= h($servico) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span>
                                    <i class="bi bi-shop"></i>
                                    Atendimento na loja
                                </span>
                            <?php endif; ?>

                            <?php if (trim((string)$loja['whatsapp']) !== ''): ?>
                                <span>
                                    <i class="bi bi-whatsapp"></i>
                                    WhatsApp
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="loja-botoes">

                            <?php if (trim((string)$loja['mapa_url']) !== ''): ?>
                                <a
                                    href="<?= h($loja['mapa_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-loja btn-mapa"
                                    aria-label="Ver a localização da loja no mapa (abre em nova aba)"
                                >

                                    <i class="bi bi-map"></i>

                                    Ver no mapa

                                </a>
                            <?php else: ?>
                                <span
                                    class="btn-loja btn-mapa btn-mapa-indisponivel"
                                    aria-disabled="true"
                                    role="status"
                                >

                                    <i class="bi bi-map"></i>

                                    Mapa não informado

                                </span>
                            <?php endif; ?>

                            <?php if (trim((string)$loja['whatsapp']) !== ''): ?>
                                <?php
                                    $whatsapp = preg_replace('/\D+/', '', (string)$loja['whatsapp']);
                                    if ($whatsapp !== '' && !str_starts_with($whatsapp, '55')) {
                                        $whatsapp = '55' . $whatsapp;
                                    }
                                ?>
                                <a
                                    href="https://wa.me/<?= h($whatsapp) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-loja btn-whatsapp"
                                    aria-label="Falar com a loja pelo WhatsApp (abre em nova aba)"
                                >

                                    <i class="bi bi-whatsapp"></i>

                                    Falar com a loja

                                </a>
                            <?php elseif (trim((string)$loja['telefone']) !== ''): ?>
                                <?php $telefoneLink = preg_replace('/\D+/', '', (string)$loja['telefone']); ?>
                                <a
                                    href="tel:<?= h($telefoneLink) ?>"
                                    class="btn-loja btn-whatsapp"
                                >

                                    <i class="bi bi-telephone"></i>

                                    Ligar para a loja

                                </a>
                            <?php endif; ?>

                        </div>

                    </div>

                </article>
            <?php endforeach; ?>

        </section>


        <!-- MENSAGEM QUANDO NÃO ENCONTRAR -->

        <div class="sr-only" id="lojaResultados" aria-live="polite" aria-atomic="true"></div>

        <div class="nenhuma-loja" id="nenhumaLoja" role="status" aria-live="polite" aria-atomic="true">

            <i class="bi bi-geo-alt"></i>

            <h3>Nenhuma loja encontrada</h3>

            <p>
                Tente pesquisar outra cidade, bairro ou estado.
            </p>

        </div>


        <!-- ================= BENEFÍCIOS ================= -->

        <section class="beneficios-lojas">

            <div class="beneficios-titulo">

                <span>
                    Experiência Garra & Patas
                </span>

                <h2>
                    Por que visitar nossas lojas?
                </h2>

            </div>

            <div class="beneficios-grid">

                <article class="beneficio-card">

                    <div class="beneficio-icone">

                        <i class="bi bi-person-heart"></i>

                    </div>

                    <h3>
                        Atendimento especializado
                    </h3>

                    <p>
                        Nossa equipe ajuda você a escolher os melhores
                        produtos de acordo com as necessidades do seu pet.
                    </p>

                </article>

                <article class="beneficio-card">

                    <div class="beneficio-icone">

                        <i class="bi bi-box-seam"></i>

                    </div>

                    <h3>
                        Retirada de pedidos
                    </h3>

                    <p>
                        Compre pelo site e retire seus produtos em uma
                        de nossas unidades disponíveis.
                    </p>

                </article>

                <article class="beneficio-card">

                    <div class="beneficio-icone">

                        <i class="bi bi-stars"></i>

                    </div>

                    <h3>
                        Produtos selecionados
                    </h3>

                    <p>
                        Trabalhamos com produtos de qualidade para
                        proporcionar segurança e bem-estar ao seu pet.
                    </p>

                </article>

            </div>

        </section>

    </main>

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

                <a href="#" tabindex="-1" aria-hidden="true"><i class="bi bi-instagram" aria-hidden="true"></i></a>

                <a href="#" tabindex="-1" aria-hidden="true"><i class="bi bi-facebook" aria-hidden="true"></i></a>

                <a href="https://wa.me/5519996418975"><i class="bi bi-whatsapp"></i></a>

            </div>

        </div>

        <!-- Navegação -->
        <div class="footer-col">

            <h3>Institucional</h3>

            <ul>

                <li><a href="menu.php">Página Inicial</a></li>
                <li><a href="loja.php">Loja</a></li>
                <li><a href="produto.html">Produtos</a></li>
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

        const campoPesquisa =
            document.getElementById("pesquisaLoja");

        const filtroEstado =
            document.getElementById("filtroEstado");

        const lojas =
            document.querySelectorAll(".loja-card");

        const nenhumaLoja =
            document.getElementById("nenhumaLoja");


        function removerAcentos(texto) {

            return texto
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

        }


        function filtrarLojas() {

            const pesquisa = removerAcentos(
                campoPesquisa.value.toLowerCase().trim()
            );

            const estadoSelecionado =
                filtroEstado.value;

            let quantidadeVisivel = 0;


            lojas.forEach(function (loja) {

                const dadosPesquisa = removerAcentos(
                    loja.dataset.pesquisa.toLowerCase()
                );

                const estadoLoja =
                    loja.dataset.estado;

                const correspondePesquisa =
                    dadosPesquisa.includes(pesquisa);

                const correspondeEstado =
                    estadoSelecionado === "todos" ||
                    estadoLoja === estadoSelecionado;


                if (
                    correspondePesquisa &&
                    correspondeEstado
                ) {

                    loja.style.display = "grid";

                    quantidadeVisivel++;

                } else {

                    loja.style.display = "none";

                }

            });


            if (quantidadeVisivel === 0) {

                nenhumaLoja.classList.add("ativo");

            } else {

                nenhumaLoja.classList.remove("ativo");

            }

            const resultado = document.getElementById("lojaResultados");
            if (resultado) {
                resultado.textContent = quantidadeVisivel === 1
                    ? "1 loja encontrada."
                    : `${quantidadeVisivel} lojas encontradas.`;
            }

        }


        campoPesquisa.addEventListener(
            "input",
            filtrarLojas
        );


        filtroEstado.addEventListener(
            "change",
            filtrarLojas
        );


        const botoesFavoritar =
            document.querySelectorAll(".btn-favoritar");

        const favoritosLojasStorageKey = "garras_patas_lojas_favoritas_v1";

        function lerFavoritosLojas() {
            try {
                const dados = JSON.parse(localStorage.getItem(favoritosLojasStorageKey) || "[]");
                return Array.isArray(dados) ? dados.map(Number) : [];
            } catch (erro) {
                return [];
            }
        }

        function salvarFavoritosLojas(ids) {
            try {
                localStorage.setItem(favoritosLojasStorageKey, JSON.stringify(ids));
            } catch (erro) {
                // Mantém o funcionamento visual mesmo se o navegador bloquear o armazenamento.
            }
        }

        const favoritosLojas = lerFavoritosLojas();

        botoesFavoritar.forEach(function (botao) {

            const idLoja = Number(botao.dataset.idLoja || 0);
            const icone = botao.querySelector("i");
            if (icone) icone.setAttribute("aria-hidden", "true");
            botao.setAttribute("aria-pressed", "false");

            if (idLoja && favoritosLojas.includes(idLoja)) {
                botao.classList.add("favoritado");
                icone.className = "bi bi-heart-fill";
                botao.setAttribute("aria-pressed", "true");
                botao.setAttribute("aria-label", "Remover loja dos favoritos");
            }

            botao.addEventListener("click", function () {

                if (!idLoja) return;

                const atual = lerFavoritosLojas();
                const indice = atual.indexOf(idLoja);

                if (indice >= 0) {
                    atual.splice(indice, 1);
                    botao.classList.remove("favoritado");
                    icone.className = "bi bi-heart";
                    botao.setAttribute("aria-pressed", "false");
                    botao.setAttribute("aria-label", "Favoritar loja");
                } else {
                    atual.push(idLoja);
                    botao.classList.add("favoritado");
                    icone.className = "bi bi-heart-fill";
                    botao.setAttribute("aria-pressed", "true");
                    botao.setAttribute("aria-label", "Remover loja dos favoritos");
                }

                salvarFavoritosLojas(atual);

            });

        });

    </script>
<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
