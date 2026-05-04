// ============================================================
// finalizar_pedido.js — Tela de Checkout do Cliente
// ============================================================

// Abre o painel (sheet) de baixo para escolher endereço
window.abrirSheetEnderecos = function() {
    var sheetOverlay = document.getElementById('sheetOverlay');
    var sheetEnderecos = document.getElementById('sheetEnderecos');
    
    if (sheetOverlay && sheetEnderecos) {
        sheetOverlay.classList.add('active');
        sheetEnderecos.classList.add('active');
        document.body.style.overflow = 'hidden'; // Trava o scroll
    }
}

// Fecha o painel de endereços
window.fecharSheetEnderecos = function() {
    var sheetOverlay = document.getElementById('sheetOverlay');
    var sheetEnderecos = document.getElementById('sheetEnderecos');
    
    if (sheetOverlay && sheetEnderecos) {
        sheetOverlay.classList.remove('active');
        sheetEnderecos.classList.remove('active');
        document.body.style.overflow = ''; // Libera o scroll
    }
}

// Quando o usuário clica em um endereço da lista
window.selecionarEndereco = function(elementoClicado, apelido, rua, bairro) {
    // Tira a borda verde de todos
    var todosEnderecos = document.querySelectorAll('.addr-option');
    for (var i = 0; i < todosEnderecos.length; i++) {
        todosEnderecos[i].classList.remove('addr-option--selected');
    }

    // Coloca borda verde no clicado
    elementoClicado.classList.add('addr-option--selected');

    // Atualiza o texto do endereço selecionado na tela principal
    var endApelido = document.getElementById('end-apelido');
    var endLinha1 = document.getElementById('end-linha1');
    var endLinha2 = document.getElementById('end-linha2');
    
    if (endApelido) { endApelido.textContent = apelido; }
    if (endLinha1) { endLinha1.textContent = rua; }
    if (endLinha2) { endLinha2.textContent = bairro; }

    // NOTA BACKEND: aqui você deve atualizar um input hidden com o ID do endereço
    // Exemplo: document.getElementById('endereco-id').value = elementoClicado.dataset.endId;

    // Fecha a janelinha depois de um tempinho
    setTimeout(fecharSheetEnderecos, 250);
}

// Preenche o resumo do pedido (Subtotal, Taxa e Total)
// NOTA BACKEND: Quando o PHP fizer isso, você pode apagar esta função.
document.addEventListener('DOMContentLoaded', function() {
    
    function preencherResumo() {
        try {
            var dadosCarrinho = localStorage.getItem('chokko_cart');
            var carrinho = dadosCarrinho ? JSON.parse(dadosCarrinho) : [];
            var entrega = localStorage.getItem('chokko_entrega') || 'delivery';
            
            var subtotal = 0;
            var taxa = 0;
            var total = 0;

            // Define a taxa de acordo com o tipo de entrega
            if (entrega === 'delivery') {
                taxa = 5.00;
            } else {
                taxa = 0.00;
            }

            // Calcula o subtotal somando os itens do localStorage
            for (var i = 0; i < carrinho.length; i++) {
                var item = carrinho[i];
                subtotal = subtotal + (item.unitPrice * item.qty);
            }

            total = subtotal + taxa;

            // Atualiza os valores na tela
            var finSubtotal = document.getElementById('fin-subtotal');
            var finTaxa = document.getElementById('fin-taxa');
            var finTotal = document.getElementById('fin-total');

            if (finSubtotal) {
                finSubtotal.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            }
            if (finTaxa) {
                if (taxa > 0) {
                    finTaxa.textContent = 'R$ ' + taxa.toFixed(2).replace('.', ',');
                } else {
                    finTaxa.textContent = 'Grátis';
                }
            }
            if (finTotal) {
                finTotal.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
            }
        } catch(erro) {
            // Se der erro ao ler localStorage, apenas ignora
        }
    }
    
    preencherResumo();
});
