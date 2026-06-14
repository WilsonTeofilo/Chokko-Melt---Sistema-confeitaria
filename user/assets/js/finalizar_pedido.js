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
window.selecionarEndereco = function(elementoClicado, apelido, rua, bairro, idEndereco) {
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

    // Atualiza o input hidden com o ID do endereço
    var hiddenInput = document.getElementById('id_endereco_input');
    if (hiddenInput) {
        hiddenInput.value = idEndereco;
    }

    // Fecha a janelinha depois de um tempinho
    setTimeout(fecharSheetEnderecos, 250);
}

// Controla a exibição do campo de troco ao selecionar a opção Dinheiro
document.addEventListener('DOMContentLoaded', function() {
    var radiosPagamento = document.querySelectorAll('input[name="pagamento"]');
    var trocoWrapper = document.getElementById('troco-wrapper');
    var campoTroco = document.getElementById('valor_pago_dinheiro');

    function gerenciarTroco() {
        if (!trocoWrapper) return;
        
        var selecionado = document.querySelector('input[name="pagamento"]:checked');
        if (selecionado && selecionado.value === 'dinheiro') {
            trocoWrapper.style.display = 'block';
            if (campoTroco) campoTroco.setAttribute('required', 'required');
        } else {
            trocoWrapper.style.display = 'none';
            if (campoTroco) {
                campoTroco.removeAttribute('required');
                campoTroco.value = ''; // Limpa valor se mudar de ideia
            }
        }
    }

    // Registra o evento de escuta em todos os radios
    if (radiosPagamento.length > 0) {
        radiosPagamento.forEach(function(radio) {
            radio.addEventListener('change', gerenciarTroco);
        });
        // Roda uma vez no início para garantir o estado inicial
        gerenciarTroco();
    }

    // Validação no envio do formulário (Bloqueia troco menor que o total)
    var form = document.getElementById('checkoutForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var selecionado = document.querySelector('input[name="pagamento"]:checked');
            if (selecionado && selecionado.value === 'dinheiro') {
                var campoTroco = document.getElementById('valor_pago_dinheiro');
                if (campoTroco) {
                    var totalTexto = document.getElementById('fin-total').innerText;
                    var pagoTexto = campoTroco.value.trim();
                    
                    if (pagoTexto === '') {
                        alert('Por favor, insira o valor em dinheiro que será entregue.');
                        e.preventDefault();
                        return;
                    }

                    // Função auxiliar para parsing robusto de moeda
                    function parseCurrencyJS(valueStr) {
                        valueStr = valueStr.trim().replace(/[^\d.,]/g, '');
                        var temPonto = valueStr.indexOf('.') !== -1;
                        var temVirgula = valueStr.indexOf(',') !== -1;
                        
                        if (temPonto && temVirgula) {
                            if (valueStr.lastIndexOf('.') > valueStr.lastIndexOf(',')) {
                                // US style: 1,000.00
                                valueStr = valueStr.replace(/,/g, '');
                            } else {
                                // BR style: 1.000,00
                                valueStr = valueStr.replace(/\./g, '').replace(',', '.');
                            }
                        } else if (temVirgula) {
                            // Apenas vírgula: 100,00 -> 100.00
                            valueStr = valueStr.replace(',', '.');
                        }
                        return parseFloat(valueStr);
                    }

                    var totalVal = parseCurrencyJS(totalTexto);
                    var pagoVal = parseCurrencyJS(pagoTexto);
                    
                    if (isNaN(pagoVal) || pagoVal <= 0) {
                        alert('Por favor, insira um valor válido para o pagamento em dinheiro.');
                        e.preventDefault();
                        return;
                    }
                    
                    if ((pagoVal - totalVal) < -0.01) {
                        alert('O valor em dinheiro (R$ ' + pagoVal.toFixed(2).replace('.', ',') + ') não pode ser menor que o total do pedido (R$ ' + totalVal.toFixed(2).replace('.', ',') + ').');
                        e.preventDefault();
                        return;
                    }
                }
            }
        });
    }
});
