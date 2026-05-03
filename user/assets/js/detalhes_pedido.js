/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS — detalhes_pedido.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Modal Cancelar Pedido
    const modalCancelar = document.getElementById('modal-cancelar-cliente');
    const modalSucesso = document.getElementById('modal-sucesso-cliente');
    const btnAbrirModal = document.querySelector('.btn-cancelar-pedido');
    const btnFecharModal = document.querySelectorAll('.btn-close-modal');
    const btnConfirmarCancelamento = document.getElementById('btn-confirmar-cancelamento');
    const motivoSelect = document.getElementById('motivo-cancelamento-cliente');
    const msgSucesso = document.getElementById('modal-sucesso-cliente-msg');

    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', () => {
            if (modalCancelar) modalCancelar.classList.add('show');
        });
    }

    if (btnFecharModal.length > 0) {
        btnFecharModal.forEach(btn => {
            btn.addEventListener('click', () => {
                if (modalCancelar) modalCancelar.classList.remove('show');
            });
        });
    }

    if (btnConfirmarCancelamento) {
        btnConfirmarCancelamento.addEventListener('click', () => {
            const motivo = motivoSelect.value;
            const pedidoId = btnConfirmarCancelamento.dataset.pedidoId;

            if (!motivo) {
                alert('Por favor, selecione o motivo do cancelamento.');
                return;
            }
            
            // NOTA BACKEND: POST para api/cancelar_pedido.php
            // Body: { pedido_id: pedidoId, motivo: motivo }
            
            if (modalCancelar) modalCancelar.classList.remove('show');
            
            if (msgSucesso) msgSucesso.innerText = 'Seu pedido foi cancelado. O motivo informado foi registrado.';
            if (modalSucesso) modalSucesso.classList.add('show');
        });
    }

    const btnReload = document.getElementById('btn-reload-page');
    if (btnReload) {
        btnReload.addEventListener('click', () => {
            window.location.reload();
        });
    }

    // Repetir Pedido
    const btnsRepetir = document.querySelectorAll('.btn-repetir-pedido');
    if (btnsRepetir.length > 0) {
        btnsRepetir.forEach(btn => {
            btn.addEventListener('click', () => {
                const pedidoId = btn.dataset.pedidoId;
                
                /* NOTA BACKEND: quando o endpoint estiver pronto, descomente abaixo e remova o alert():
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
                        alert('Erro ao buscar itens. Tente novamente.');
                    });
                */

                alert('Repetir pedido estara disponivel quando o backend estiver conectado. ID: ' + pedidoId);
            });
        });
    }

    // Botão Falar com Estabelecimento
    const btnContato = document.getElementById('btn-contato');
    if (btnContato) {
        btnContato.addEventListener('click', () => {
            const pedidoId = btnContato.dataset.pedidoId;
            /* NOTA BACKEND: descomente a linha abaixo e insira o numero real:
            window.open(`https://wa.me/5511999999999?text=Ola! Tenho duvida sobre o Pedido #${pedidoId}`, '_blank');
            */
            alert('WhatsApp: numero a ser configurado pelo backend. Referência do Pedido: ' + pedidoId);
        });
    }
});
