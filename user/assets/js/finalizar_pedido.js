/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS — finalizar_pedido.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ── Bottom Sheet: Selecionar Endereço ────────────────────────

window.abrirSheetEnderecos = function() {
    const sheetOverlay = document.getElementById('sheetOverlay');
    const sheetEnderecos = document.getElementById('sheetEnderecos');
    if (sheetOverlay && sheetEnderecos) {
        sheetOverlay.classList.add('active');
        sheetEnderecos.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

window.fecharSheetEnderecos = function() {
    const sheetOverlay = document.getElementById('sheetOverlay');
    const sheetEnderecos = document.getElementById('sheetEnderecos');
    if (sheetOverlay && sheetEnderecos) {
        sheetOverlay.classList.remove('active');
        sheetEnderecos.classList.remove('active');
        document.body.style.overflow = '';
    }
}

window.selecionarEndereco = function(el, apelido, rua, bairro) {
    // Remove seleção anterior
    document.querySelectorAll('.addr-option').forEach(o => o.classList.remove('addr-option--selected'));
    // Seleciona o novo
    el.classList.add('addr-option--selected');

    // Atualiza o card de endereço na tela
    const endApelido = document.getElementById('end-apelido');
    const endLinha1 = document.getElementById('end-linha1');
    const endLinha2 = document.getElementById('end-linha2');
    
    if (endApelido) endApelido.textContent = apelido;
    if (endLinha1) endLinha1.textContent = rua;
    if (endLinha2) endLinha2.textContent = bairro;

    /*
     * NOTA BACKEND: guardar o ID do endereço selecionado para envio no POST.
     * Adicionar um <input type="hidden" id="endereco-id" name="endereco_id"> na page
     * e setar: document.getElementById('endereco-id').value = el.dataset.endId;
     * (Adicionar data-end-id=" $end['id'] ?>" no .addr-option no PHP)
     */

    // Fecha o sheet após um breve delay visual
    setTimeout(fecharSheetEnderecos, 250);
}

// ── Preenche totais do localStorage ──────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    function preencherResumo() {
        try {
            const cart    = JSON.parse(localStorage.getItem('chokko_cart') || '[]');
            const entrega = localStorage.getItem('chokko_entrega') || 'delivery';
            const TAXAS   = { delivery: 5.00, retirada: 0.00, local: 0.00 };
            const fmt     = v => 'R$ ' + parseFloat(v).toFixed(2).replace('.', ',');

            const subtotal = cart.reduce((s, i) => s + i.unitPrice * i.qty, 0);
            const taxa     = TAXAS[entrega] || 0;
            const total    = subtotal + taxa;

            const finSubtotal = document.getElementById('fin-subtotal');
            const finTaxa = document.getElementById('fin-taxa');
            const finTotal = document.getElementById('fin-total');

            if (finSubtotal) finSubtotal.textContent = fmt(subtotal);
            if (finTaxa) finTaxa.textContent = taxa > 0 ? fmt(taxa) : 'Grátis';
            if (finTotal) finTotal.textContent = fmt(total);
        } catch(e) {}
    }
    
    preencherResumo();
});
