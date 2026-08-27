/* =========================================================
   CARRINHO GLOBAL
   GARRA & PATAS
========================================================= */


/*
    Nome usado para guardar o carrinho
    no navegador.
*/

const CHAVE_CARRINHO =
    "garrasPatasCarrinho";




function obterCarrinho() {

    let carrinhoLocal = [];

    try {

        const salvoLocal =
            localStorage.getItem("garrasPatasCarrinho");

        if (salvoLocal) {

            carrinhoLocal =
                JSON.parse(salvoLocal);

        }

    } catch (erro) {

        console.log(
            "Não foi possível ler localStorage:",
            erro
        );

    }


    /*
        Se encontrou no localStorage,
        usa normalmente.
    */

    if (
        Array.isArray(carrinhoLocal)
        &&
        carrinhoLocal.length > 0
    ) {

        return carrinhoLocal;

    }


    /*
        SEGUNDA OPÇÃO:
        window.name funciona entre páginas
        abertas na mesma aba.
    */

    try {

        if (
            window.name
            &&
            window.name.startsWith(
                "GARRAS_PATAS_CARRINHO="
            )
        ) {

            const dados =
                window.name.replace(
                    "GARRAS_PATAS_CARRINHO=",
                    ""
                );

            const carrinhoJanela =
                JSON.parse(dados);

            if (
                Array.isArray(carrinhoJanela)
            ) {

                return carrinhoJanela;

            }

        }

    } catch (erro) {

        console.log(
            "Não foi possível ler window.name:",
            erro
        );

    }


    return [];

}



/* =========================================================
   SALVAR CARRINHO
========================================================= */

function salvarCarrinho(carrinho) {

    /*
        SALVA NORMALMENTE
    */

    try {

        localStorage.setItem(
            "garrasPatasCarrinho",
            JSON.stringify(carrinho)
        );

    } catch (erro) {

        console.log(
            "Erro ao salvar localStorage:",
            erro
        );

    }


    /*
        SALVA TAMBÉM NA ABA DO NAVEGADOR

        Isso ajuda quando o projeto está sendo
        aberto diretamente pelos arquivos HTML.
    */

    try {

        window.name =
            "GARRAS_PATAS_CARRINHO="
            +
            JSON.stringify(carrinho);

    } catch (erro) {

        console.log(
            "Erro ao salvar window.name:",
            erro
        );

    }


    atualizarContadoresCarrinho();

}



/* =========================================================
   FORMATAR REAL
========================================================= */

function formatarReal(valor) {

    return Number(
        valor || 0
    )
    .toLocaleString(

        "pt-BR",

        {

            style: "currency",

            currency: "BRL"

        }

    );

}



/* =========================================================
   TRANSFORMAR PREÇO EM NÚMERO
========================================================= */

function moedaParaNumero(texto) {

    if (!texto) {

        return 0;

    }


    let valor = texto

        .replace(
            /[^\d.,-]/g,
            ""
        )

        .trim();


    if (
        valor.includes(",")
    ) {

        valor = valor

            .replace(
                /\./g,
                ""
            )

            .replace(
                ",",
                "."
            );

    }


    return (
        Number(valor)
        ||
        0
    );

}



/* =========================================================
   CRIAR ID
========================================================= */

function criarId(texto) {

    return String(
        texto || "produto"
    )

    .normalize("NFD")

    .replace(
        /[\u0300-\u036f]/g,
        ""
    )

    .toLowerCase()

    .replace(
        /[^a-z0-9]+/g,
        "-"
    )

    .replace(
        /(^-|-$)/g,
        ""
    );

}



/* =========================================================
   ADICIONAR PRODUTO
========================================================= */

function adicionarProduto(produto) {


    const carrinho =
        obterCarrinho();


    /*
        Produto + tamanho formam
        um item único.
    */

    const chaveItem =

        produto.id
        +
        "__"
        +
        (
            produto.tamanho
            ||
            "padrao"
        );


    const existente =
        carrinho.find(

            item =>
                item.chave
                ===
                chaveItem

        );


    /*
        SE JÁ EXISTIR
    */

    if (existente) {

        existente.quantidade +=

            produto.quantidade
            ||
            1;

    }


    /*
        SE NÃO EXISTIR
    */

    else {

        carrinho.push({

            chave:
                chaveItem,


            id:
                produto.id,


            nome:
                produto.nome,


            marca:
                produto.marca
                ||
                "Garra & Patas",


            imagem:
                produto.imagem,


            tamanho:
                produto.tamanho
                ||
                "",


            preco:
                Number(
                    produto.preco
                ),


            quantidade:
                Number(
                    produto.quantidade
                )
                ||
                1

        });

    }


    salvarCarrinho(
        carrinho
    );

}



/* =========================================================
   PRODUTO DA ABA DE PRODUTOS
========================================================= */

function produtoDoCardDinamico(card) {


    const nome =

        card
        .querySelector(
            ".nome-produto"
        )
        ?.textContent
        .trim()

        ||

        "Produto";


    return {


        id:

            card.dataset.id
            ||
            criarId(nome),


        nome:
            nome,


        marca:

            card
            .querySelector(
                ".marca-produto"
            )
            ?.textContent
            .trim()

            ||
            "",


        imagem:

            card
            .querySelector(
                ".imagem-card-produto"
            )
            ?.getAttribute(
                "src"
            )

            ||
            "",


        tamanho:

            card
            .querySelector(
                ".tamanho-escolhido"
            )
            ?.value

            ||

            card
            .querySelector(
                ".btn-tamanho.ativo"
            )
            ?.textContent
            .trim()

            ||
            "",


        preco:

            Number(

                card
                .querySelector(
                    ".preco-escolhido"
                )
                ?.value

            )

            ||

            moedaParaNumero(

                card
                .querySelector(
                    ".preco-atual"
                )
                ?.textContent

            ),


        quantidade:
            1

    };

}



/* =========================================================
   PRODUTOS DO MENU / CARROSSEL
========================================================= */

function produtoDoMenuCard(card) {


    const nome =

        card
        .querySelector(
            ".menu-nome-produto"
        )
        ?.textContent
        .trim()

        ||

        "Produto";


    return {


        id:

            card.dataset.id
            ||
            criarId(nome),


        nome:
            nome,


        marca:

            card
            .querySelector(
                ".menu-marca-produto"
            )
            ?.textContent
            .trim()

            ||
            "",


        imagem:

            card
            .querySelector(
                ".menu-imagem-card-produto"
            )
            ?.getAttribute(
                "src"
            )

            ||
            "",


        tamanho:

            card
            .querySelector(
                ".menu-btn-tamanho.ativo"
            )
            ?.textContent
            .trim()

            ||
            "",


        preco:

            moedaParaNumero(

                card
                .querySelector(
                    ".menu-preco-atual"
                )
                ?.textContent

            ),


        quantidade:
            1

    };

}



/* =========================================================
   PRODUTO DA PÁGINA INDIVIDUAL
========================================================= */

function produtoDaPaginaProduto() {


    const nome =

        document
        .querySelector(
            ".produto-titulo"
        )
        ?.textContent
        .trim()

        ||

        "Produto";



    const quantidade =

        Math.max(

            1,

            Number(

                document
                .getElementById(
                    "quantidade"
                )
                ?.value

            )

            ||

            1

        );



    const totalExibido =

        moedaParaNumero(

            document
            .getElementById(
                "precoProduto"
            )
            ?.textContent

        );



    const precoUnitarioCalculado =

        totalExibido
        /
        quantidade;



    return {


        id:

            criarId(
                nome
            ),


        nome:
            nome,


        marca:

            "Garra & Patas",


        imagem:

            document
            .getElementById(
                "fotoProduto"
            )
            ?.getAttribute(
                "src"
            )

            ||
            "",


        tamanho:

            document
            .querySelector(
                ".btn-tamanho.ativo"
            )
            ?.textContent
            .trim()

            ||
            "",


        preco:

            precoUnitarioCalculado,


        quantidade:

            quantidade

    };

}



/* =========================================================
   ANIMAÇÃO DO BOTÃO
========================================================= */

function mostrarAdicionado(botao) {


    if (!botao) {

        return;

    }


    const htmlOriginal =
        botao.innerHTML;


    botao.classList.add(
        "adicionado"
    );



    /*
        BOTÃO REDONDO
    */

    if (

        botao.classList.contains(
            "btn-carrinho-card"
        )

        ||

        botao.classList.contains(
            "menu-btn-carrinho-card"
        )

    ) {


        botao.innerHTML =

            '<i class="bi bi-check-lg"></i>';

    }


    /*
        BOTÃO NORMAL
    */

    else {


        botao.innerHTML =

            '<i class="bi bi-check-lg"></i> Adicionado';

    }



    setTimeout(

        function () {


            botao.innerHTML =
                htmlOriginal;


            botao.classList.remove(
                "adicionado"
            );


        },

        1200

    );

}



/* =========================================================
   ATIVAR TODOS OS BOTÕES
========================================================= */

function ativarBotoesGlobaisCarrinho() {


    document.addEventListener(

        "click",

        function(evento) {


            const botao =

                evento.target.closest(

                    ".btn-carrinho-card, " +

                    ".comprar-produto, " +

                    ".menu-btn-carrinho-card, " +

                    ".menu-comprar-produto, " +

                    ".btn.carrinho"

                );


            if (!botao) {

                return;

            }



            /*
                CARD DA ABA PRODUTO
            */

            const cardDinamico =

                botao.closest(
                    ".card-produto"
                );



            /*
                CARD DO MENU
            */

            const menuCard =

                botao.closest(
                    ".menu-card-produto"
                );



            /* ==========================
               ABA PRODUTO
            ========================== */

            if (cardDinamico) {


                adicionarProduto(

                    produtoDoCardDinamico(
                        cardDinamico
                    )

                );


                mostrarAdicionado(
                    botao
                );


                return;

            }



            /* ==========================
               MENU / CARROSSEL
            ========================== */

            if (menuCard) {


                adicionarProduto(

                    produtoDoMenuCard(
                        menuCard
                    )

                );


                mostrarAdicionado(
                    botao
                );


                return;

            }



            /* ==========================
               PÁGINA PRODUTO
            ========================== */

            if (

                botao.classList.contains(
                    "carrinho"
                )

                &&

                document.querySelector(
                    ".produto-container"
                )

            ) {


                adicionarProduto(

                    produtoDaPaginaProduto()

                );


                mostrarAdicionado(
                    botao
                );

            }


        },

        true

    );

}



/* =========================================================
   TOTAL DE PRODUTOS
========================================================= */

function totalDeItens() {


    return obterCarrinho()

        .reduce(

            function(
                total,
                item
            ) {

                return (

                    total
                    +
                    Number(
                        item.quantidade
                        ||
                        0
                    )

                );

            },

            0

        );

}



/* =========================================================
   CONTADOR DO CARRINHO
========================================================= */

function atualizarContadoresCarrinho() {


    const total =
        totalDeItens();


    document

        .querySelectorAll(
            ".cart-count"
        )

        .forEach(

            function(contador) {


                contador.textContent =
                    total;


                contador.style.display =

                    total > 0

                    ?

                    "flex"

                    :

                    "none";

            }

        );

}



/* =========================================================
   MOSTRAR CARRINHO NA TELA
========================================================= */

function renderizarCarrinho() {


    const lista =

        document.getElementById(
            "listaCarrinho"
        );


    /*
        Se não estivermos na página
        carrinho.html, para aqui.
    */

    if (!lista) {

        return;

    }



    const vazio =

        document.getElementById(
            "carrinhoVazio"
        );


    const botaoLimpar =

        document.getElementById(
            "btnLimparCarrinho"
        );


    const botaoFinalizar =

        document.getElementById(
            "btnFinalizarCompra"
        );


    const textoQuantidade =

        document.getElementById(
            "textoQuantidadeCarrinho"
        );


    const subtotalElemento =

        document.getElementById(
            "subtotalCarrinho"
        );


    const freteElemento =

        document.getElementById(
            "freteCarrinho"
        );


    const totalElemento =

        document.getElementById(
            "totalCarrinho"
        );


    const freteAviso =

        document.getElementById(
            "freteAviso"
        );



    const carrinho =
        obterCarrinho();



    /* =====================================================
       CARRINHO VAZIO
    ===================================================== */

    if (
        carrinho.length
        ===
        0
    ) {


        lista.innerHTML =
            "";


        vazio
        ?.classList
        .remove(
            "oculto"
        );



        if (botaoLimpar) {

            botaoLimpar.style.display =
                "none";

        }



        if (botaoFinalizar) {

            botaoFinalizar.disabled =
                true;

        }



        if (textoQuantidade) {

            textoQuantidade.textContent =

                "Seu carrinho ainda não possui produtos.";

        }



        if (subtotalElemento) {

            subtotalElemento.textContent =

                formatarReal(0);

        }



        if (freteElemento) {

            freteElemento.textContent =

                formatarReal(0);

        }



        if (totalElemento) {

            totalElemento.textContent =

                formatarReal(0);

        }


        return;

    }



    /* =====================================================
       POSSUI PRODUTOS
    ===================================================== */

    vazio
    ?.classList
    .add(
        "oculto"
    );



    if (botaoLimpar) {

        botaoLimpar.style.display =
            "flex";

    }



    if (botaoFinalizar) {

        botaoFinalizar.disabled =
            false;

    }



    /* =====================================================
       QUANTIDADE TOTAL
    ===================================================== */

    const quantidadeTotal =

        carrinho.reduce(

            function(
                soma,
                item
            ) {

                return (

                    soma
                    +
                    Number(
                        item.quantidade
                    )

                );

            },

            0

        );



    if (textoQuantidade) {


        textoQuantidade.textContent =

            quantidadeTotal
            +
            " "
            +
            (
                quantidadeTotal === 1

                ?

                "item"

                :

                "itens"
            )

            +

            " no seu carrinho.";

    }



    /* =====================================================
       CRIAR PRODUTOS
    ===================================================== */

    lista.innerHTML =


        carrinho

        .map(

            function(item) {


                const subtotalItem =

                    Number(
                        item.preco
                    )

                    *

                    Number(
                        item.quantidade
                    );



                return `


                    <article class="item-carrinho">


                        <!-- IMAGEM -->

                        <div class="item-imagem">

                            <img

                                src="${item.imagem}"

                                alt="${item.nome}"

                            >

                        </div>



                        <!-- INFORMAÇÕES -->

                        <div class="item-info">


                            <span class="item-marca">

                                ${
                                    item.marca
                                    ||
                                    "Garra & Patas"
                                }

                            </span>



                            <h3 class="item-nome">

                                ${item.nome}

                            </h3>



                            ${
                                item.tamanho

                                ?

                                `

                                <p class="item-tamanho">

                                    Tamanho:

                                    <strong>

                                        ${item.tamanho}

                                    </strong>

                                </p>

                                `

                                :

                                ""
                            }



                            <!-- AÇÕES -->

                            <div class="item-acoes">


                                <!-- QUANTIDADE -->

                                <div class="quantidade-controle">


                                    <button

                                        type="button"

                                        data-acao="diminuir"

                                        data-chave="${item.chave}"

                                    >

                                        −

                                    </button>



                                    <span>

                                        ${item.quantidade}

                                    </span>



                                    <button

                                        type="button"

                                        data-acao="aumentar"

                                        data-chave="${item.chave}"

                                    >

                                        +

                                    </button>


                                </div>



                                <!-- REMOVER -->

                                <button

                                    type="button"

                                    class="btn-remover"

                                    data-acao="remover"

                                    data-chave="${item.chave}"

                                >

                                    <i class="bi bi-trash3"></i>

                                    Remover

                                </button>


                            </div>


                        </div>



                        <!-- PREÇO -->

                        <div class="item-preco">


                            <small>

                                ${formatarReal(item.preco)}

                                cada

                            </small>


                            <strong>

                                ${formatarReal(subtotalItem)}

                            </strong>


                            <span>

                                à vista no Pix

                            </span>


                        </div>


                    </article>


                `;

            }

        )

        .join("");



    /* =====================================================
       SUBTOTAL
    ===================================================== */

    const subtotal =

        carrinho.reduce(

            function(
                soma,
                item
            ) {

                return (

                    soma

                    +

                    (
                        Number(
                            item.preco
                        )

                        *

                        Number(
                            item.quantidade
                        )
                    )

                );

            },

            0

        );



    /* =====================================================
       FRETE
    ===================================================== */

    const frete =

        subtotal >= 99

        ?

        0

        :

        14.90;



    /* =====================================================
       TOTAL
    ===================================================== */

    const total =

        subtotal
        +
        frete;



    subtotalElemento.textContent =

        formatarReal(
            subtotal
        );



    freteElemento.textContent =

        frete === 0

        ?

        "Grátis"

        :

        formatarReal(
            frete
        );



    totalElemento.textContent =

        formatarReal(
            total
        );



    /* =====================================================
       AVISO DO FRETE
    ===================================================== */

    if (freteAviso) {


        /*
            FRETE GRÁTIS
        */

        if (
            frete === 0
        ) {


            freteAviso
            .classList
            .add(
                "frete-gratis"
            );


            freteAviso.innerHTML = `

                <i class="bi bi-check-circle-fill"></i>

                <span>

                    Você ganhou frete grátis!

                </span>

            `;

        }


        /*
            AINDA NÃO TEM FRETE GRÁTIS
        */

        else {


            const falta =

                Math.max(

                    0,

                    99
                    -
                    subtotal

                );


            freteAviso
            .classList
            .remove(
                "frete-gratis"
            );


            freteAviso.innerHTML = `

                <i class="bi bi-truck"></i>

                <span>

                    Faltam

                    ${formatarReal(falta)}

                    para o frete grátis.

                </span>

            `;

        }

    }

}



/* =========================================================
   AÇÕES DO CARRINHO
========================================================= */

function configurarPaginaCarrinho() {


    const lista =

        document.getElementById(
            "listaCarrinho"
        );


    if (!lista) {

        return;

    }



    /* =====================================================
       + / - / REMOVER
    ===================================================== */

    lista.addEventListener(

        "click",

        function(evento) {


            const botao =

                evento.target.closest(
                    "[data-acao]"
                );


            if (!botao) {

                return;

            }



            const acao =

                botao.dataset.acao;


            const chave =

                botao.dataset.chave;


            const carrinho =

                obterCarrinho();



            const indice =

                carrinho.findIndex(

                    function(item) {

                        return (

                            item.chave
                            ===
                            chave

                        );

                    }

                );



            if (
                indice === -1
            ) {

                return;

            }



            /* ==========================
               AUMENTAR
            ========================== */

            if (
                acao === "aumentar"
            ) {


                carrinho[indice]
                .quantidade++;

            }



            /* ==========================
               DIMINUIR
            ========================== */

            if (
                acao === "diminuir"
            ) {


                carrinho[indice]
                    .quantidade--;


                /*
                    Se chegar a zero
                    remove o produto.
                */

                if (

                    carrinho[indice]
                    .quantidade
                    <=
                    0

                ) {


                    carrinho.splice(
                        indice,
                        1
                    );

                }

            }



            /* ==========================
               REMOVER
            ========================== */

            if (
                acao === "remover"
            ) {


                carrinho.splice(
                    indice,
                    1
                );

            }



            salvarCarrinho(
                carrinho
            );


            renderizarCarrinho();

        }

    );



    /* =====================================================
       LIMPAR CARRINHO
    ===================================================== */

    document

        .getElementById(
            "btnLimparCarrinho"
        )

        ?.addEventListener(

            "click",

            function() {


                const carrinho =
                    obterCarrinho();


                if (
                    carrinho.length
                    ===
                    0
                ) {

                    return;

                }



                const confirmar =

                    confirm(

                        "Deseja remover todos os produtos do carrinho?"

                    );


                if (!confirmar) {

                    return;

                }



                salvarCarrinho(
                    []
                );


                renderizarCarrinho();

            }

        );



    /* =====================================================
       FINALIZAR COMPRA
    ===================================================== */

    document

        .getElementById(
            "btnFinalizarCompra"
        )

        ?.addEventListener(

            "click",

            function() {


                const carrinho =

                    obterCarrinho();


                if (
                    carrinho.length
                    ===
                    0
                ) {

                    return;

                }


                /*
                    Por enquanto envia
                    para login.
                */

                window.location.href =

                    "form.html";

            }

        );

}



/* =========================================================
   INICIAR
========================================================= */

function iniciarCarrinho() {

    ativarBotoesGlobaisCarrinho();

    atualizarContadoresCarrinho();

    configurarPaginaCarrinho();

    renderizarCarrinho();

}


/*
    Se a página ainda estiver carregando,
    espera terminar.
*/

if (
    document.readyState === "loading"
) {

    document.addEventListener(
        "DOMContentLoaded",
        iniciarCarrinho
    );

}


/*
    Se o HTML já terminou de carregar,
    executa imediatamente.
*/

else {

    iniciarCarrinho();

}