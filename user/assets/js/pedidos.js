// ============================================================
// pedidos.js — Lista de pedidos do cliente (Aba Pedidos)
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    var botoesRepetir = document.querySelectorAll('.btn-repetir-pedido');
    
    if (botoesRepetir.length > 0) {
        for (var i = 0; i < botoesRepetir.length; i++) {
            var btn = botoesRepetir[i];
            
            btn.addEventListener('click', function(evento) {
                // Descobre o ID do pedido clicado
                var pedidoId = evento.currentTarget.getAttribute('data-pedido-id');
                repetirPedido(pedidoId);
            });
        }
    }
});

function repetirPedido(pedidoId) {
    /* 
    NOTA BACKEND: quando o PHP estiver pronto, você deve fazer uma
    requisição para buscar os itens deste pedido antigo e jogar na
    sessão do carrinho.
    
    Exemplo:
    window.location.href = 'api/repetir_pedido.php?id=' + pedidoId;
    */

    alert('Repetir o pedido #' + pedidoId + ' estará disponível quando o backend estiver conectado.');
}
