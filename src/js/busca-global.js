/* =========================================================
   GARRAS & PATAS — BUSCA GLOBAL
   Pesquisa do cabeçalho -> página de produtos
========================================================= */
(function () {
    "use strict";

    function normalizarArquivo(nome) {
        return String(nome || "").toLowerCase();
    }

    function paginaProdutos() {
        const nomeAtual = normalizarArquivo(window.location.pathname.split("/").pop());

        /*
           O projeto possui duas versões da página de produtos.
           Mantemos a mesma versão quando o usuário já está nela.
        */
        if (nomeAtual === "aba_produto(3).php") {
            return "aba_produto(3).php";
        }

        return "aba_produto.php";
    }

    function executarBusca(campo) {
        if (!campo) return;

        const termo = String(campo.value || "").trim();
        const destino = new URL(paginaProdutos(), window.location.href);

        if (termo !== "") {
            destino.searchParams.set("busca", termo);
        }

        window.location.href = destino.toString();
    }

    function configurarCampo(campo) {
        if (!campo || campo.dataset.garrasBuscaConfigurada === "1") {
            return;
        }

        campo.dataset.garrasBuscaConfigurada = "1";

        campo.addEventListener("keydown", function (evento) {
            if (evento.key !== "Enter") return;

            evento.preventDefault();
            executarBusca(campo);
        });
    }

    function configurarLupa(icone) {
        if (!icone || icone.dataset.garrasBuscaConfigurada === "1") {
            return;
        }

        icone.dataset.garrasBuscaConfigurada = "1";

        /* O CSS antigo tinha pointer-events:none na lupa. */
        icone.style.pointerEvents = "auto";
        icone.style.cursor = "pointer";
        icone.setAttribute("role", "button");
        icone.setAttribute("tabindex", "0");
        icone.setAttribute("aria-label", "Pesquisar produtos");

        icone.addEventListener("click", function (evento) {
            evento.preventDefault();
            evento.stopPropagation();

            const caixa = icone.closest(".search-box");
            const campo = caixa ? caixa.querySelector("input") : null;

            executarBusca(campo);
        });

        icone.addEventListener("keydown", function (evento) {
            if (evento.key !== "Enter" && evento.key !== " ") return;

            evento.preventDefault();

            const caixa = icone.closest(".search-box");
            const campo = caixa ? caixa.querySelector("input") : null;

            executarBusca(campo);
        });
    }

    function iniciarBuscaGlobal() {
        document.querySelectorAll(".search-box input").forEach(configurarCampo);
        document.querySelectorAll(".search-box .bi-search").forEach(configurarLupa);
    }

    /*
       Delegação de eventos como proteção extra: funciona mesmo que
       algum cabeçalho seja inserido/recriado depois do carregamento.
    */
    document.addEventListener("keydown", function (evento) {
        if (evento.key !== "Enter") return;

        const campo = evento.target && evento.target.closest
            ? evento.target.closest(".search-box input")
            : null;

        if (!campo) return;

        evento.preventDefault();
        executarBusca(campo);
    }, true);

    document.addEventListener("click", function (evento) {
        const icone = evento.target && evento.target.closest
            ? evento.target.closest(".search-box .bi-search")
            : null;

        if (!icone) return;

        const caixa = icone.closest(".search-box");
        const campo = caixa ? caixa.querySelector("input") : null;

        if (!campo) return;

        evento.preventDefault();
        executarBusca(campo);
    }, true);

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciarBuscaGlobal);
    } else {
        iniciarBuscaGlobal();
    }
})();
