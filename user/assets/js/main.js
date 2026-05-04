/**
 * ============================================================
 * main.js — Roda em TODAS as páginas (carregado pelo user_footer.php)
 * ============================================================
 * O que este arquivo faz:
 *   1. Atualiza o badge (número) da sacola na navegação inferior
 *   2. Verifica se a loja está aberta ou fechada pelo horário
 *   3. Na tela de finalizar pedido: mostra campo de troco e valida CPF
 *
 * NOTA BACKEND:
 *   - O badge hoje lê o localStorage. Quando o PHP assumir o carrinho
 *     via sessão, o badge vai ser gerado direto pelo PHP no HTML.
 *   - O horário hoje lê o localStorage. Quando o PHP tiver uma tabela
 *     de configuração da loja, o main.js vai buscar via fetch().
 *   - O campo de troco (ensureTrocoUI) existe porque a tela de
 *     finalizar pedido ainda não tem esse HTML. Quando o PHP gerar
 *     o HTML dessa tela, coloque o campo de troco direto no PHP
 *     e remova a função ensureTrocoUI() daqui.
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // 1. Badge da sacola — mostra quantos itens estão no carrinho
    // ============================================================
    // NOTA BACKEND: remova este bloco quando o PHP gerar o número direto no HTML
    try {
        var dadosCarrinho = localStorage.getItem('chokko_cart');
        var carrinho = dadosCarrinho ? JSON.parse(dadosCarrinho) : [];
        var totalItens = 0;

        for (var i = 0; i < carrinho.length; i++) {
            var item = carrinho[i];
            var qtd = parseInt(item.qty);
            if (!isNaN(qtd)) {
                totalItens = totalItens + qtd;
            }
        }

        var badge = document.getElementById('cart-badge');
        if (badge) {
            if (totalItens > 0) {
                badge.textContent = totalItens;
                badge.classList.add('has-items');
            } else {
                badge.textContent = '';
                badge.classList.remove('has-items');
            }
        }
    } catch (e) {
        // Se der erro ao ler o carrinho, apenas ignora
    }


    // ============================================================
    // 2. Horário de funcionamento — loja aberta ou fechada?
    // ============================================================
    // Esta variável global é lida pelo cardapio.js ao abrir o modal
    window.isLojaAberta = true;

    function checarHorario() {
        // Lê o horário de abertura do localStorage (admin grava lá)
        var horaAbre  = localStorage.getItem('chokko_hora_abre')  || '15:00';
        var horaFecha = localStorage.getItem('chokko_hora_fecha') || '22:00';

        // Atualiza o texto de horário na tela se existir
        var textoHorario = document.getElementById('store-hours-text');
        if (textoHorario) {
            textoHorario.innerText = horaAbre + ' às ' + horaFecha;
        }

        // Pega a hora atual do computador do usuário
        var agora = new Date();
        var horaAtual = agora.getHours();
        var minAtual  = agora.getMinutes();

        // Converte tudo para "número decimal de horas" para facilitar a comparação
        // Exemplo: 15:30 vira 15.5 (quinze e meio)
        var horaAtualDecimal = horaAtual + (minAtual / 60);

        // Separa hora e minuto do horário de abertura
        var partesAbre  = horaAbre.split(':');
        var hAbre = parseInt(partesAbre[0]);
        var mAbre = parseInt(partesAbre[1]);
        var abreDecimal = hAbre + (mAbre / 60);

        // Separa hora e minuto do horário de fechamento
        var partesFecha = horaFecha.split(':');
        var hFecha = parseInt(partesFecha[0]);
        var mFecha = parseInt(partesFecha[1]);
        var fechaDecimal = hFecha + (mFecha / 60);

        // Caso especial: a loja fecha depois da meia-noite (ex: abre 20h fecha 2h)
        // Adicionamos 24 para poder comparar corretamente
        if (fechaDecimal < abreDecimal) {
            fechaDecimal = fechaDecimal + 24;
        }

        // Mesma correção para a hora atual quando passa da meia-noite
        var horaAtualParaCalculo = horaAtualDecimal;
        if (horaAtualParaCalculo < abreDecimal && fechaDecimal > 24) {
            horaAtualParaCalculo = horaAtualParaCalculo + 24;
        }

        // Define se está aberto ou fechado
        if (horaAtualParaCalculo >= abreDecimal && horaAtualParaCalculo <= fechaDecimal) {
            window.isLojaAberta = true;
        } else {
            window.isLojaAberta = false;
        }

        // Atualiza o badge de status (Loja Aberta / Fechado) na tela
        var badgeStatus = document.getElementById('store-status-badge');
        if (badgeStatus) {
            if (window.isLojaAberta) {
                badgeStatus.innerText = 'Loja Aberta';
                badgeStatus.style.background = '#43A047';
                badgeStatus.style.color = 'white';
            } else {
                badgeStatus.innerText = 'Fechado';
                badgeStatus.style.background = '#E53935';
                badgeStatus.style.color = 'white';
            }
        }
    }

    checarHorario();
    // Roda de novo a cada 1 minuto para atualizar o status automaticamente
    setInterval(checarHorario, 60000);


    // ============================================================
    // 3. Finalizar Pedido: Campo de Troco + Validação de CPF
    // ============================================================
    // Estas funções só fazem algo se existirem os elementos na tela.
    // Se não estiver na tela de finalizar pedido, não acontece nada.

    ensureTrocoUI();
    ensureCPFValidation();

    // Botão "Fazer Pedido" redireciona para a tela de detalhes
    var btnFazerPedido = document.getElementById('btn-fazer-pedido');
    if (btnFazerPedido) {
        btnFazerPedido.addEventListener('click', function() {
            window.location.href = 'detalhes_pedido.php';
        });
    }
});


// ============================================================
// FUNÇÃO: Campo de Troco (para pagamento em dinheiro)
// ============================================================
// NOTA BACKEND: quando o PHP gerar o HTML da tela de finalizar pedido,
// coloque o campo de troco direto no PHP e APAGUE esta função inteira.
// Ela existe só porque o HTML do troco ainda não está na tela.
function ensureTrocoUI() {
    var radiosDinheiro = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]');
    if (!radiosDinheiro) {
        return; // Não está na tela de pagamento, não faz nada
    }

    // Cria o campo de troco apenas se ele ainda não existir na tela
    var campoTroco = document.getElementById('dinheiro-troco-wrap');
    if (!campoTroco) {
        campoTroco = document.createElement('div');
        campoTroco.id = 'dinheiro-troco-wrap';
        campoTroco.style.marginTop = '12px';
        campoTroco.style.display = 'none';
        campoTroco.innerHTML =
            '<div style="padding:12px; border:1px solid var(--cinza-claro); border-radius:10px; background:#FAF7F5;">' +
                '<label style="display:block; font-size:.85rem; font-weight:600; color:var(--cinza-texto); margin-bottom:8px;">' +
                    'Pagará com quanto? (opcional)' +
                '</label>' +
                '<input id="valor-pago-dinheiro" inputmode="decimal" class="form-control" placeholder="Ex: 50,00" style="margin-bottom:10px;">' +
                '<div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">' +
                    '<span style="font-size:.8rem; color:var(--cinza-medio);">Troco estimado</span>' +
                    '<strong id="troco-valor" style="color:var(--marrom);">—</strong>' +
                '</div>' +
                '<p id="troco-ajuda" style="margin-top:8px; font-size:.75rem; color:var(--cinza-medio); line-height:1.3;">' +
                    'Se deixar em branco, assumimos que não precisa de troco.' +
                '</p>' +
            '</div>';

        // Coloca o campo no card de pagamento
        var cardPagamento = radiosDinheiro.closest('.card');
        if (cardPagamento) {
            cardPagamento.appendChild(campoTroco);
        }

        var inputValorPago = campoTroco.querySelector('#valor-pago-dinheiro');
        var textoTroco = campoTroco.querySelector('#troco-valor');
        var textoAjuda = campoTroco.querySelector('#troco-ajuda');

        // Calcula o troco quando o usuário digita o valor que vai pagar
        function recalcularTroco() {
            var elTotal = document.getElementById('fin-total');
            var totalPedido = 0;

            if (elTotal) {
                // Converte o texto "R$ 25,00" para o número 25.00
                var textoTotal = elTotal.textContent;
                textoTotal = textoTotal.replace('R$', '').trim().replace('.', '').replace(',', '.');
                totalPedido = parseFloat(textoTotal);
                if (isNaN(totalPedido)) {
                    totalPedido = 0;
                }
            }

            // Converte o valor digitado pelo usuário para número
            var textoPago = inputValorPago.value.replace(',', '.');
            var valorPago = parseFloat(textoPago);

            if (!inputValorPago.value.trim()) {
                textoTroco.textContent = '—';
                textoAjuda.textContent = 'Se deixar em branco, assumimos que não precisa de troco.';
                return;
            }

            if (isNaN(valorPago) || valorPago <= 0) {
                textoTroco.textContent = '—';
                textoAjuda.textContent = 'Digite um valor válido (ex: 50,00).';
                return;
            }

            var troco = valorPago - totalPedido;

            if (troco < 0) {
                textoTroco.textContent = '—';
                textoAjuda.textContent = 'O valor informado é menor que o total do pedido.';
                return;
            }

            textoTroco.textContent = 'R$ ' + troco.toFixed(2).replace('.', ',');
            textoAjuda.textContent = 'Troco calculado com base no total atual.';
        }

        inputValorPago.addEventListener('input', recalcularTroco);
        setTimeout(recalcularTroco, 0);
    }

    // Mostra ou esconde o campo de troco conforme o método de pagamento
    function mostrarOcultarTroco() {
        var dinheiroMarcado = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]');
        if (dinheiroMarcado && dinheiroMarcado.checked) {
            campoTroco.style.display = 'block';
        } else {
            campoTroco.style.display = 'none';
        }
    }

    var todosRadiosPagamento = document.querySelectorAll('input[type="radio"][name="pagamento"]');
    todosRadiosPagamento.forEach(function(radio) {
        radio.addEventListener('change', mostrarOcultarTroco);
    });
    mostrarOcultarTroco();

    // Bloqueia o clique no "Fazer Pedido" se o valor em dinheiro for insuficiente
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        if (!btn.classList.contains('btn-success')) return;
        if (!btn.textContent || btn.textContent.toLowerCase().indexOf('fazer pedido') === -1) return;

        var dinheiroMarcado = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]');
        var inputValorPago  = document.getElementById('valor-pago-dinheiro');

        if (dinheiroMarcado && dinheiroMarcado.checked && inputValorPago && inputValorPago.value.trim() !== '') {
            var elTotal = document.getElementById('fin-total');
            var totalPedido = 0;

            if (elTotal) {
                var textoTotal = elTotal.textContent.replace('R$', '').trim().replace('.', '').replace(',', '.');
                totalPedido = parseFloat(textoTotal) || 0;
            }

            var textoPago = inputValorPago.value.replace(',', '.');
            var valorPago = parseFloat(textoPago);

            if (isNaN(valorPago) || valorPago <= 0 || valorPago < totalPedido) {
                inputValorPago.style.borderColor = '#E53935';
                inputValorPago.style.boxShadow   = '0 0 0 3px rgba(229,57,53,0.12)';
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                alert('O valor em dinheiro informado é menor que o total do pedido.');
            } else {
                inputValorPago.style.borderColor = '';
                inputValorPago.style.boxShadow   = '';
            }
        }
    }, true);
}


// ============================================================
// FUNÇÃO: Validação e máscara de CPF
// ============================================================
// NOTA BACKEND: o PHP também deve validar o CPF no servidor.
// O JS valida só pra dar feedback rápido pro usuário.
function ensureCPFValidation() {
    var inputCPF = document.querySelector('input.form-control[placeholder="000.000.000-00"]');
    if (!inputCPF) {
        return; // Não tem campo de CPF na tela atual
    }

    // Aplica a máscara enquanto o usuário digita
    inputCPF.addEventListener('input', function() {
        var cursorAntes = inputCPF.selectionStart || 0;
        var valorAntes = inputCPF.value;
        inputCPF.value = formatarCPF(valorAntes);
        var diferenca = inputCPF.value.length - valorAntes.length;
        var novaPosicao = Math.max(cursorAntes + diferenca, 0);
        inputCPF.setSelectionRange(novaPosicao, novaPosicao);
    });

    // Valida quando o usuário sai do campo
    inputCPF.addEventListener('blur', function() {
        var cpfValido = validarCPF(inputCPF.value);
        if (!cpfValido) {
            inputCPF.style.borderColor = '#E53935';
            inputCPF.style.boxShadow   = '0 0 0 3px rgba(229,57,53,0.12)';
            alert('CPF inválido. Verifique e tente novamente.');
        } else {
            inputCPF.style.borderColor = '';
            inputCPF.style.boxShadow   = '';
        }
    });

    // Bloqueia o "Fazer Pedido" se o CPF for inválido
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        if (!btn.classList.contains('btn-success')) return;
        if (!btn.textContent || btn.textContent.toLowerCase().indexOf('fazer pedido') === -1) return;

        if (!validarCPF(inputCPF.value)) {
            inputCPF.style.borderColor = '#E53935';
            inputCPF.style.boxShadow   = '0 0 0 3px rgba(229,57,53,0.12)';
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            alert('CPF inválido. Corrija antes de finalizar o pedido.');
        }
    }, true);
}

// Formata o CPF enquanto o usuário digita: "12345678901" → "123.456.789-01"
function formatarCPF(valor) {
    // Remove tudo que não é número e limita a 11 dígitos
    var digitos = String(valor).replace(/\D/g, '').slice(0, 11);
    var tamanho = digitos.length;

    if (tamanho <= 3) {
        return digitos;
    } else if (tamanho <= 6) {
        return digitos.slice(0, 3) + '.' + digitos.slice(3);
    } else if (tamanho <= 9) {
        return digitos.slice(0, 3) + '.' + digitos.slice(3, 6) + '.' + digitos.slice(6);
    } else {
        return digitos.slice(0, 3) + '.' + digitos.slice(3, 6) + '.' + digitos.slice(6, 9) + '-' + digitos.slice(9);
    }
}

// Valida se o CPF digitado é matematicamente correto (os dois dígitos verificadores)
// O algoritmo da Receita Federal usa soma ponderada para calcular os dígitos finais.
function validarCPF(cpf) {
    // Remove pontos e traços
    var digitos = String(cpf).replace(/\D/g, '');

    // CPF vazio é considerado OK (campo opcional)
    if (digitos.length === 0) {
        return true;
    }

    // CPF precisa ter exatamente 11 dígitos
    if (digitos.length !== 11) {
        return false;
    }

    // CPFs com todos os dígitos iguais são inválidos (ex: 111.111.111-11)
    var todosIguais = true;
    for (var i = 1; i < 11; i++) {
        if (digitos[i] !== digitos[0]) {
            todosIguais = false;
            break;
        }
    }
    if (todosIguais) {
        return false;
    }

    // ── Calcula o 1º dígito verificador ──
    // Multiplica os 9 primeiros dígitos por 10, 9, 8... até 2
    var soma1 = 0;
    for (var j = 0; j < 9; j++) {
        soma1 = soma1 + (parseInt(digitos[j]) * (10 - j));
    }
    var resto1 = (soma1 * 10) % 11;
    var dv1 = (resto1 === 10) ? 0 : resto1;

    // ── Calcula o 2º dígito verificador ──
    // Multiplica os 10 primeiros dígitos por 11, 10, 9... até 2
    var soma2 = 0;
    for (var k = 0; k < 10; k++) {
        soma2 = soma2 + (parseInt(digitos[k]) * (11 - k));
    }
    var resto2 = (soma2 * 10) % 11;
    var dv2 = (resto2 === 10) ? 0 : resto2;

    // Verifica se os dígitos calculados batem com os do CPF digitado
    if (parseInt(digitos[9]) !== dv1 || parseInt(digitos[10]) !== dv2) {
        return false;
    }

    return true;
}
