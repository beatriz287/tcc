/* Melhorias pequenas e seguras de teclado; a funcionalidade principal continua no PHP/páginas. */
(function () {
  'use strict';

  function fecharMenusExpandidos() {
    const cachorro = document.getElementById('megaMenu');
    const gato = document.getElementById('megaMenuGatos');
    const btnCachorro = document.getElementById('btnCachorros');
    const btnGato = document.getElementById('btnGatos');
    const abertoCachorro = !!(cachorro && cachorro.classList.contains('ativo'));
    const abertoGato = !!(gato && gato.classList.contains('ativo'));
    if (!abertoCachorro && !abertoGato) return false;
    cachorro && cachorro.classList.remove('ativo');
    gato && gato.classList.remove('ativo');
    btnCachorro && btnCachorro.setAttribute('aria-expanded', 'false');
    btnGato && btnGato.setAttribute('aria-expanded', 'false');
    (abertoGato ? btnGato : btnCachorro)?.focus();
    return true;
  }

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    if (fecharMenusExpandidos()) {
      event.preventDefault();
      return;
    }

    const lateral = document.getElementById('menuLateral');
    const overlay = document.getElementById('overlay');
    if (lateral && lateral.classList.contains('ativo')) {
      lateral.classList.remove('ativo');
      overlay && overlay.classList.remove('ativo');
      const abridor = document.querySelector('.todos');
      abridor?.focus();
      event.preventDefault();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    // A notícia é carregada dinamicamente. Anunciamos apenas o título final,
    // sem transformar o artigo inteiro em uma região live excessivamente falante.
    const paginaNoticia = document.getElementById('paginaNoticia');
    if (paginaNoticia) {
      const anuncioNoticia = document.createElement('div');
      anuncioNoticia.className = 'sr-only';
      anuncioNoticia.setAttribute('role', 'status');
      anuncioNoticia.setAttribute('aria-live', 'polite');
      anuncioNoticia.setAttribute('aria-atomic', 'true');
      paginaNoticia.insertAdjacentElement('afterend', anuncioNoticia);
      const anunciarTitulo = function () {
        if (paginaNoticia.querySelector('.noticia-carregando')) return;
        const titulo = paginaNoticia.querySelector('h1');
        if (titulo && titulo.textContent.trim()) {
          anuncioNoticia.textContent = 'Matéria carregada: ' + titulo.textContent.trim();
          observadorNoticia.disconnect();
        }
      };
      const observadorNoticia = new MutationObserver(anunciarTitulo);
      observadorNoticia.observe(paginaNoticia, { childList: true, subtree: true });
      anunciarTitulo();
    }

    document.querySelectorAll('[data-garras-cart-badge]').forEach(function (badge) {
      if (!badge.hasAttribute('aria-label')) {
        badge.setAttribute('aria-label', 'Quantidade de itens no carrinho');
      }
      // O valor do contador já é alterado pelo carrinho.js; a região anuncia o novo valor.
      badge.setAttribute('aria-live', 'polite');
      badge.setAttribute('aria-atomic', 'true');
    });
  });
})();
