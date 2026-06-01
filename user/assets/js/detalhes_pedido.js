// ============================================================
// detalhes_pedido.js — Tela de ver os detalhes de um pedido já feito
// ============================================================

function imprimirComprovante() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {

    
    // Elementos do Modal de Cancelamento
    var modalCancelar = document.getElementById('modal-cancelar-cliente');
    var modalSucesso = document.getElementById('modal-sucesso-cliente');
    var btnAbrirModal = document.querySelector('.btn-cancelar-pedido');
    var botoesFecharModal = document.querySelectorAll('.btn-close-modal');
    var btnConfirmarCancelamento = document.getElementById('btn-confirmar-cancelamento');
    var motivoSelect = document.getElementById('motivo-cancelamento-cliente');
    var msgSucesso = document.getElementById('modal-sucesso-cliente-msg');

    // Abre modal de cancelar
    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', function() {
            if (modalCancelar) {
                modalCancelar.classList.add('show');
            }
        });
    }

    // Fecha os modais no "X" ou "Cancelar"
    if (botoesFecharModal.length > 0) {
        for (var i = 0; i < botoesFecharModal.length; i++) {
            botoesFecharModal[i].addEventListener('click', function() {
                if (modalCancelar) {
                    modalCancelar.classList.remove('show');
                }
            });
        }
    }

    // Botão vermelho de confirmar cancelamento
    if (btnConfirmarCancelamento) {
        btnConfirmarCancelamento.addEventListener('click', function() {
            var motivo = motivoSelect.value;
            var pedidoId = btnConfirmarCancelamento.getAttribute('data-pedido-id');

            if (!motivo) {
                alert('Por favor, selecione o motivo do cancelamento.');
                return;
            }
            
            // NOTA BACKEND: Aqui você vai enviar o ID e o motivo para cancelar no MySQL.
            // Exemplo:
            // window.location.href = 'api/cancelar_pedido.php?id=' + pedidoId + '&motivo=' + motivo;
            
            // Esconde modal de pergunta e mostra modal de sucesso
            if (modalCancelar) {
                modalCancelar.classList.remove('show');
            }
            
            if (msgSucesso) {
                msgSucesso.innerText = 'Seu pedido foi cancelado. O motivo informado foi registrado.';
            }
            if (modalSucesso) {
                modalSucesso.classList.add('show');
            }
        });
    }

    // Botão de Repetir Pedido
    var botoesRepetir = document.querySelectorAll('.btn-repetir-pedido');
    if (botoesRepetir.length > 0) {
        for (var j = 0; j < botoesRepetir.length; j++) {
            botoesRepetir[j].addEventListener('click', function(evento) {
                var pedidoId = evento.currentTarget.getAttribute('data-pedido-id');
                
                // NOTA BACKEND: redirecione o usuário para um PHP que pega os itens 
                // e joga na sessão do carrinho.
                alert('Repetir pedido estará disponível quando o backend estiver conectado. ID: ' + pedidoId);
            });
        }
    }

    // Botão Falar com Estabelecimento (WhatsApp)
    var btnContato = document.getElementById('btn-contato');
    if (btnContato) {
        btnContato.addEventListener('click', function() {
            var pedidoId = btnContato.getAttribute('data-pedido-id');
            
            // NOTA BACKEND: troque pelo número de WhatsApp real cadastrado na configuração
            // window.open('https://wa.me/5511999999999?text=Olá! Tenho duvida sobre o Pedido #' + pedidoId, '_blank');
            
            alert('WhatsApp: número a ser configurado pelo backend. Referência: Pedido #' + pedidoId);
        });
    }
});
