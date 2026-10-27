/* =========================================================
   GARRAS & PATAS — CARRINHO COMPARTILHADO
   Usado pelas páginas de menu, categorias e produto.
========================================================= */

const GP_CHAVE_CARRINHO = "garras_patas_carrinho_v2";
const GP_CHAVE_CARRINHO_ANTIGO = "garras_patas_carrinho";

function GPLerCarrinho() {
    try {
        let dados = JSON.parse(
            localStorage.getItem(GP_CHAVE_CARRINHO) || "null"
        );

        // Migra automaticamente o carrinho da versão anterior.
        if (!Array.isArray(dados)) {
            const antigo = JSON.parse(
                localStorage.getItem(GP_CHAVE_CARRINHO_ANTIGO) || "[]"
            );
            dados = Array.isArray(antigo) ? antigo : [];
            localStorage.setItem(
                GP_CHAVE_CARRINHO,
                JSON.stringify(dados)
            );
        }

        return GPNormalizarCarrinho(dados);
    } catch (erro) {
        return [];
    }
}

function GPSalvarCarrinho(carrinho) {
    carrinho = GPNormalizarCarrinho(carrinho);

    localStorage.setItem(
        GP_CHAVE_CARRINHO,
        JSON.stringify(carrinho)
    );

    window.dispatchEvent(
        new CustomEvent("garras:carrinho-atualizado", {
            detail: carrinho
        })
    );
}

function GPChaveItem(idProduto, idVariacao) {
    return String(idProduto) + ":" + (
        idVariacao === null || idVariacao === undefined || idVariacao === ""
            ? "base"
            : String(idVariacao)
    );
}

function GPChavePlano(idPlano) {
    return "plano:" + String(idPlano);
}

function GPItemEhPlano(item) {
    return String(item?.tipoItem || "").toLowerCase() === "plano"
        && Number(item?.idPlano || 0) > 0;
}

function GPAdicionarAoCarrinho(item) {
    const carrinho = GPLerCarrinho();
    const ehPlano = GPItemEhPlano(item);
    const idProduto = Number(item.idProduto || 0);
    const idVariacao = (
        item.idVariacao === null ||
        item.idVariacao === undefined ||
        item.idVariacao === ""
    ) ? null : Number(item.idVariacao);

    if (!ehPlano && !idProduto) {
        return carrinho;
    }

    if (ehPlano) {
        const idPlano = Number(item.idPlano || 0);
        const chavePlano = GPChavePlano(idPlano);
        const existentePlano = carrinho.find(
            produto => produto.chave === chavePlano
        );

        if (existentePlano) {
            existentePlano.quantidade = 1;
            if (item.precoUnitario !== undefined) {
                existentePlano.precoUnitario = Number(item.precoUnitario) || 0;
            }
            if (item.precoOriginal !== undefined) {
                existentePlano.precoOriginal = Number(item.precoOriginal) || existentePlano.precoUnitario;
            }
        } else {
            carrinho.push({
                ...item,
                chave: chavePlano,
                tipoItem: "plano",
                idPlano: idPlano,
                idProduto: 0,
                idVariacao: null,
                nome: String(item.nome || "Plano de saúde"),
                variacao: String(item.variacao || "Mensal"),
                marca: String(item.marca || "Garras & Patas"),
                categoria: String(item.categoria || "plano de saúde"),
                imagem: String(item.imagem || ""),
                precoOriginal: Number(item.precoOriginal ?? item.precoUnitario ?? 0),
                precoUnitario: Number(item.precoUnitario || 0),
                quantidade: 1
            });
        }

        GPSalvarCarrinho(carrinho);
        return carrinho;
    }

    const chave = GPChaveItem(idProduto, idVariacao);
    const quantidade = Math.max(
        1,
        Math.min(99, Number(item.quantidade || 1))
    );

    const existente = carrinho.find(
        produto => produto.chave === chave
    );

    if (existente) {
        existente.quantidade = Math.min(
            99,
            Number(existente.quantidade || 0) + quantidade
        );
        if (item.precoUnitario !== undefined) {
            existente.precoUnitario = Number(item.precoUnitario) || 0;
        }
        if (item.precoOriginal !== undefined) {
            existente.precoOriginal = Number(item.precoOriginal) || existente.precoUnitario;
        }
        if (item.imagem !== undefined && item.imagem !== "") {
            existente.imagem = item.imagem;
        }
        if (item.variacao !== undefined) {
            existente.variacao = String(item.variacao || "");
        }
        if (item.nome !== undefined) {
            existente.nome = String(item.nome || "Produto");
        }
    } else {
        carrinho.push({
            chave: chave,
            idProduto: idProduto,
            idVariacao: idVariacao,
            nome: String(item.nome || "Produto"),
            variacao: String(item.variacao || ""),
            marca: String(item.marca || ""),
            categoria: String(item.categoria || ""),
            imagem: String(item.imagem || ""),
            precoOriginal: Number(item.precoOriginal ?? item.precoUnitario ?? 0),
            precoUnitario: Number(item.precoUnitario || 0),
            quantidade: quantidade
        });
    }

    GPSalvarCarrinho(carrinho);
    return carrinho;
}


function GPNormalizarCarrinho(carrinho) {
    if (!Array.isArray(carrinho)) return [];

    const normalizado = [];

    carrinho.forEach((item) => {
        if (!item) return;

        const ehPlano = GPItemEhPlano(item);
        const idPlano = ehPlano ? Number(item.idPlano || 0) : 0;
        const idProduto = Number(item.idProduto || 0);

        if (ehPlano && !idPlano) return;
        if (!ehPlano && !idProduto) return;

        const idVariacao = (
            item.idVariacao === null ||
            item.idVariacao === undefined ||
            item.idVariacao === ""
        ) ? null : Number(item.idVariacao);

        const chave = ehPlano
            ? GPChavePlano(idPlano)
            : GPChaveItem(idProduto, idVariacao);

        const quantidade = ehPlano
            ? 1
            : Math.max(1, Math.min(99, Number(item.quantidade || 1)));
        const existente = normalizado.find((registro) => registro.chave === chave);

        if (existente) {
            existente.quantidade = ehPlano
                ? 1
                : Math.min(99, existente.quantidade + quantidade);
            return;
        }

        normalizado.push({
            ...item,
            chave,
            tipoItem: ehPlano ? "plano" : String(item.tipoItem || "produto"),
            idPlano: ehPlano ? idPlano : (Number(item.idPlano || 0) || null),
            idProduto: ehPlano ? 0 : idProduto,
            idVariacao: ehPlano ? null : idVariacao,
            quantidade,
            precoOriginal: Number(item.precoOriginal ?? item.precoUnitario ?? 0),
            precoUnitario: Number(item.precoUnitario || 0)
        });
    });

    return normalizado;
}

function GPQuantidadeCarrinho() {
    return GPLerCarrinho().reduce(
        (total, item) => total + Math.max(0, Number(item.quantidade || 0)),
        0
    );
}

/* =========================================================
   CONTADOR DO CARRINHO
========================================================= */

function GPGarantirContadoresCarrinho() {
    document.querySelectorAll(".garras-cart-link, .cart-link").forEach(function (link) {
        let badge = link.querySelector("[data-garras-cart-badge]");

        if (!badge) {
            badge = document.createElement("span");
            badge.className = "garras-cart-badge";
            badge.setAttribute("data-garras-cart-badge", "");
            badge.setAttribute("aria-label", "Quantidade de produtos no carrinho");
            badge.style.cssText = [
                "position:absolute",
                "top:-9px",
                "right:-10px",
                "min-width:18px",
                "height:18px",
                "padding:0 4px",
                "border-radius:999px",
                "background:#e74c3c",
                "color:#fff",
                "font:700 10px/18px Arial,sans-serif",
                "text-align:center",
                "box-sizing:border-box",
                "display:inline-flex",
                "align-items:center",
                "justify-content:center",
                "z-index:50",
                "pointer-events:none"
            ].join(";");
            link.style.position = link.style.position || "relative";
            link.appendChild(badge);
        }
    });
}

function GPAtualizarContadoresCarrinho() {
    GPGarantirContadoresCarrinho();
    const quantidade = GPQuantidadeCarrinho();

    document.querySelectorAll("[data-garras-cart-badge]").forEach(function (badge) {
        badge.textContent = quantidade > 99 ? "99+" : String(quantidade);
        badge.style.display = "inline-flex";
    });
}

window.addEventListener("garras:carrinho-atualizado", GPAtualizarContadoresCarrinho);
window.addEventListener("storage", GPAtualizarContadoresCarrinho);
document.addEventListener("DOMContentLoaded", GPAtualizarContadoresCarrinho);



/* API pública para as páginas do site. */
window.GPLerCarrinho = GPLerCarrinho;
window.GPSalvarCarrinho = GPSalvarCarrinho;
window.GPAdicionarAoCarrinho = GPAdicionarAoCarrinho;
window.GPQuantidadeCarrinho = GPQuantidadeCarrinho;
window.GPChaveItem = GPChaveItem;
window.GPChavePlano = GPChavePlano;
