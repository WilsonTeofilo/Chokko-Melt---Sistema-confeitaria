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
// NOTA BACKEND: quando o PHP de sessão estiver pronto,
// este bloco pode ser simplificado. O PHP vai checar se o usuário
// está logado antes de finalizar. O JS não precisa mais fazer isso.
var formCarrinho = document.getElementById('form-carrinho');

if (formCarrinho) {
    formCarrinho.addEventListener('submit', function(evento) {

        // Descobre qual botão foi clicado (finalizar ou continuar comprando)
        var acaoBotao = '';
        if (evento.submitter) {
            acaoBotao = evento.submitter.value;
        }

        if (acaoBotao === 'finalizar') {
            // TODO BACKEND: remova este bloco quando o PHP verificar a sessão.
            // Hoje, checar se está logado é simulado pelo localStorage.
            var usuarioLogado = localStorage.getItem('chokko_usuario_id');
            if (!usuarioLogado) {
                evento.preventDefault(); // Impede o envio
                localStorage.setItem('chokko_retorno', 'finalizar');
                window.location.href = 'login.php?retorno=finalizar';
            }
            // Se logado: o form envia normalmente e o PHP recebe
        }
        // Se o botão for "continuar": o form envia e o PHP redireciona
    });
}


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