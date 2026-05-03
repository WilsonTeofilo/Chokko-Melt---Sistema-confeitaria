/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS — pedidos.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnsRepetir = document.querySelectorAll('.btn-repetir-pedido');
    if (btnsRepetir.length > 0) {
        btnsRepetir.forEach(btn => {
            btn.addEventListener('click', () => {
                const pedidoId = btn.dataset.pedidoId;
                repetirPedido(pedidoId);
            });
        });
    }
});

function repetirPedido(pedidoId) {
    /* NOTA BACKEND: quando o endpoint estiver pronto, descomente o fetch abaixo e remova o alert():

    fetch('api/pedido_itens.php?id=' + pedidoId)
        .then(function(r) { return r.json(); })
        .then(function(itens) {
            var cart = itens.map(function(i) {
                return {
                    key: i.id + '||',
                    id: i.id,
                    name: i.nome,
                    img: i.img,
                    basePrice: i.preco,
                    addons: [],
                    unitPrice: i.preco,
                    qty: i.qty,
                    obs: ''
                };
            });
            localStorage.setItem('chokko_cart', JSON.stringify(cart));
            window.location.href = 'carrinho.php';
        })
        .catch(function() {
            alert('Erro ao buscar itens do pedido. Tente novamente.');
        });
    */

    alert('Repetir pedido estará disponível quando o backend estiver conectado.');
}
