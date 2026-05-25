// ============================================================
// carrinho.js — Lógica visual da página da Sacola
// ============================================================
// O que este arquivo faz:
//   Destaca a opção de entrega (Delivery / Retirada / Local)
//   quando o cliente clica nela, e atualiza o campo oculto
//   que o form vai mandar pro PHP.
//
// O resto (itens, valores, +/-) é 100% PHP via $_SESSION.
// ============================================================

var opcoesEntrega = document.querySelectorAll('.delivery-option');
var campoOcultoEntrega = document.getElementById('hidden-entrega');

if (opcoesEntrega.length > 0) {
    opcoesEntrega.forEach(function(opcao) {
        opcao.addEventListener('click', function() {

            // Remove o destaque de todas as opções
            opcoesEntrega.forEach(function(o) {
                o.classList.remove('active');
            });

            // Coloca o destaque na opção clicada
            opcao.classList.add('active');

            // Marca o radio button e atualiza o campo oculto do form
            var radio = opcao.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;

                if (campoOcultoEntrega) {
                    campoOcultoEntrega.value = radio.value;
                }
            }
        });
    });
}