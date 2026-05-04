// ============================================================
// carrinho.js — Lógica da tela do carrinho
// ============================================================
// O QUE ESTE ARQUIVO FAZ (só visual):
//   1. Destaca a opção de entrega selecionada (delivery/retirada)
//   2. Atualiza o badge do rodapé lendo os itens do DOM (renderizado pelo PHP)
//
// O QUE O PHP FAZ (quando pronto):
//   - Renderizar os itens do carrinho via $_SESSION
//   - Botões + / - / remover → mini forms POST para carrinho.php
//   - Botão finalizar → POST para finalizarPedido.php
// ============================================================


// ── 1. Opções de entrega ──────────────────────────────────────
// Quando o usuário clica em Delivery ou Retirada, destaca a opção
var opcoesEntrega = document.querySelectorAll('.delivery-option');

if (opcoesEntrega.length > 0) {
    opcoesEntrega.forEach(function(opcao) {
        opcao.addEventListener('click', function() {

            // Remove o destaque de todas as opções
            opcoesEntrega.forEach(function(o) {
                o.classList.remove('active');
            });

            // Coloca o destaque na opção clicada
            opcao.classList.add('active');

            // Marca o radio button correspondente
            var radio = opcao.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;

                // Atualiza o campo oculto que o PHP vai ler
                var campoOculto = document.getElementById('hidden-entrega');
                if (campoOculto) {
                    campoOculto.value = radio.value;
                }
            }
        });
    });
}


// ── 2. Formulário de checkout ─────────────────────────────────
// JS não precisa mais interceptar. O form vai bater no finalizarPedido.php e o PHP cuida do resto!
var formCarrinho = document.getElementById('form-carrinho');
// Tudo feito nativamente via form submit!
// ── 3. Badge do rodapé ────────────────────────────────────────
// Conta os itens do carrinho lendo o HTML que o PHP gerou na tela
function atualizarBadgeCarrinho() {
    var badge = document.getElementById('cart-badge');
    if (!badge) {
        return;
    }

    // Pega todos os elementos de quantidade na tela
    var elementosQtd = document.querySelectorAll('.qty-value');
    var total = 0;

    elementosQtd.forEach(function(el) {
        var numero = parseInt(el.textContent);
        // Só soma se for um número válido
        if (!isNaN(numero)) {
            total = total + numero;
        }
    });

    if (total > 0) {
        badge.textContent = total;
        badge.classList.add('has-items');
    } else {
        badge.textContent = '';
        badge.classList.remove('has-items');
    }
}

// Roda a função de badge quando a página carrega
atualizarBadgeCarrinho();