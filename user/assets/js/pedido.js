// ============================================================
// pedido.js — Detalhe de UM único produto (Mockup)
// ============================================================
// NOTA BACKEND:
// Quando o PHP puxar as informações do banco de dados,
// tudo isso será gerado direto no HTML (preço, nome, etc).
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    var btnFinish = document.querySelector('.btn-finish');
    var overlay = document.getElementById('modal-overlay');
    var modalBody = document.getElementById('modal-body');

    if (btnFinish) {
        btnFinish.addEventListener('click', function() {
            // Pega as informações da tela
            var elementoTotal = document.querySelector('.summary div:nth-child(3) span:last-child');
            var elementoQtd = document.querySelector('.quantity-selector span');
            
            var total = elementoTotal ? elementoTotal.innerText : 'R$ 0,00';
            var quantidade = elementoQtd ? elementoQtd.innerText : '1';
            
            // Pega a data de hoje formatada
            var hoje = new Date();
            var dia = String(hoje.getDate()).padStart(2, '0');
            var mes = String(hoje.getMonth() + 1).padStart(2, '0');
            var ano = hoje.getFullYear();
            var dataAtual = dia + '/' + mes + '/' + ano;

            // Cria o modal de confirmação do pedido
            if (modalBody) {
                modalBody.innerHTML = 
                    '<div class="address-card" style="text-align: left; border: 2px solid #4a362d;">' +
                        '<strong>✅ Pedido Confirmado</strong>' +
                        '<div style="margin-top:10px; font-size:14px; color:#555;">' +
                            '<p><strong>Itens:</strong> ' + quantidade + 'x Chokko Melt Tradicional</p>' +
                            '<p><strong>Total Pago:</strong> ' + total + '</p>' +
                            '<p><strong>Data:</strong> ' + dataAtual + '</p>' +
                            '<p><strong>Status:</strong> Em preparação 🍫</p>' +
                        '</div>' +
                    '</div>' +
                    '<p style="margin-top: 15px; font-size: 13px; color: #777;">' +
                        'Acompanhe os detalhes na sua aba de pedidos.' +
                    '</p>';
            }

            // Mostra o Modal
            if (overlay) {
                overlay.style.display = 'flex';
            }
        });
    }

    // --- Lógica do Botão de Quantidade (+ e -) ---
    var botoesDiv = document.querySelector('.quantity-selector');
    if (botoesDiv) {
        var btnMinus = botoesDiv.querySelector('button:first-child');
        var btnPlus = botoesDiv.querySelector('button:last-child');
        var quantityDisplay = botoesDiv.querySelector('span');
        
        if (btnMinus && btnPlus && quantityDisplay) {
            var q = 1;
            
            btnPlus.onclick = function() {
                q = q + 1;
                quantityDisplay.innerText = q;
                atualizarTotal(q);
            };
            
            btnMinus.onclick = function() {
                if (q > 1) {
                    q = q - 1;
                    quantityDisplay.innerText = q;
                    atualizarTotal(q);
                }
            };
        }
    }
});

// Atualiza o subtotal e total com base na quantidade
function atualizarTotal(qtd) {
    var subtotal = qtd * 25.00;
    var total = subtotal + 5.00; // Taxa de entrega fixa para teste
    
    var spanSubtotal = document.querySelector('.summary div:nth-child(1) span:last-child');
    var spanTotal = document.querySelector('.summary div:nth-child(3) span:last-child');
    
    if (spanSubtotal) {
        spanSubtotal.innerText = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    }
    if (spanTotal) {
        spanTotal.innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
}
