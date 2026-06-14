// ============================================================
// detalhes_pedido.js — Tela de ver os detalhes de um pedido já feito
// ============================================================

function imprimirComprovante() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {

    
    // Elementos do Modal de Cancelamento
    var modalCancelar = document.getElementById('modal-cancelar-cliente');
    var btnAbrirModal = document.querySelector('.btn-cancelar-pedido');
    var botoesFecharModal = document.querySelectorAll('.btn-close-modal');

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
});
