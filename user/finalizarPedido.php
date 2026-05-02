<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/carrinho.css">
<link rel="stylesheet" href="assets/css/finalizar.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="carrinho.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <p class="logo-mini">Chokko<span> Melt</span></p>
        <p class="page-subtitle">Finalizar Pedido</p>
    </div>
</header>
</div>

<div class="page-pad">

    <!-- AVISO DE CONTA -->
    <div style="background: #FFF3E0; border: 1px solid #FFE0B2; padding: 12px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-start;">
        <i class="fa-solid fa-circle-user" style="color: #F57C00; font-size: 1.2rem; margin-top: 2px;"></i>
        <div>
            <p style="font-size: .85rem; color: #E65100; font-weight: 600;">Fazendo pedido como Maria Silva</p>
            <!-- NOTA BACKEND: exibir o nome do $_SESSION['usuario_nome'] -->
            <p style="font-size: .75rem; color: #F57C00; margin-top: 2px;">Não é você? <a href="login.php" style="font-weight: bold; text-decoration: underline;">Trocar de conta</a></p>
        </div>
    </div>

    <!-- ENDEREÇO DE ENTREGA -->
    <h3 style="font-size: .9rem; color: var(--marrom); margin-bottom: 10px;">Endereço de Entrega</h3>
    <div class="card mb-20">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="display: flex; gap: 12px;">
                <i class="fa-solid fa-location-dot" style="color: var(--marrom); font-size: 1.2rem; margin-top: 2px;"></i>
                <div>
                    <!-- NOTA BACKEND: exibir o endereço padrão do usuário logado -->
                    <p id="end-apelido" style="font-size: .85rem; font-weight: 600; color: var(--cinza-texto);">Casa</p>
                    <p id="end-linha1" style="font-size: .75rem; color: var(--cinza-medio); line-height: 1.4; margin-top: 2px;">Rua das Flores, 123</p>
                    <p id="end-linha2" style="font-size: .75rem; color: var(--cinza-medio);">Jardim Primavera · Grajaú</p>
                </div>
            </div>
            <!-- Botão Trocar abre o bottom sheet de endereços -->
            <button class="btn-trocar-end" onclick="abrirSheetEnderecos()">Trocar</button>
        </div>
    </div>

    <!-- FORMA DE PAGAMENTO -->
    <h3 style="font-size: .9rem; color: var(--marrom); margin-bottom: 10px;">Pagamento na Entrega</h3>
    <div class="card mb-20">
        <!-- NOTA BACKEND: Os values dos radios devem ser capturados no POST -->
        <label class="pay-option">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-brands fa-pix" style="color: #32BCAD; font-size: 1.2rem; width: 20px; text-align: center;"></i>
                <span style="font-size: .85rem; font-weight: 600; color: var(--cinza-texto);">Pix</span>
            </div>
            <input type="radio" name="pagamento" value="pix" style="accent-color: var(--marrom);">
        </label>

        <label class="pay-option">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-regular fa-credit-card" style="color: #1976D2; font-size: 1.2rem; width: 20px; text-align: center;"></i>
                <span style="font-size: .85rem; font-weight: 600; color: var(--cinza-texto);">Cartão de Crédito</span>
            </div>
            <input type="radio" name="pagamento" value="credito" style="accent-color: var(--marrom);" checked>
        </label>

        <label class="pay-option" style="border-bottom: none; padding-bottom: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-money-bill-wave" style="color: var(--verde); font-size: 1.2rem; width: 20px; text-align: center;"></i>
                <span style="font-size: .85rem; font-weight: 600; color: var(--cinza-texto);">Dinheiro</span>
            </div>
            <input type="radio" name="pagamento" value="dinheiro" style="accent-color: var(--marrom);">
        </label>
    </div>

    <!-- CPF NA NOTA E OBSERVAÇÃO -->
    <div class="card mb-20">
        <label style="display: block; font-size: .85rem; font-weight: 600; color: var(--cinza-texto); margin-bottom: 8px;">CPF na Nota (Opcional)</label>
        <input type="text" class="form-control" placeholder="000.000.000-00" style="margin-bottom: 15px;">

        <label style="display: block; font-size: .85rem; font-weight: 600; color: var(--cinza-texto); margin-bottom: 8px;">Observação para o Restaurante</label>
        <textarea class="form-control" rows="3" placeholder="Ex: Tocar o interfone, sem cebola, etc..."></textarea>
    </div>

    <!-- RESUMO -->
    <div class="card mb-20">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: .85rem; color: var(--cinza-texto);">
            <span>Subtotal</span>
            <!-- NOTA BACKEND: calcular do carrinho real -->
            <span id="fin-subtotal">R$ 26,00</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: .85rem; color: var(--cinza-texto);">
            <span>Taxa de Entrega</span>
            <span id="fin-taxa">R$ 5,00</span>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--cinza-claro); padding-top: 12px; font-size: 1rem; font-weight: 700; color: var(--marrom);">
            <span>Total a pagar</span>
            <span id="fin-total">R$ 31,00</span>
        </div>
    </div>

    <!-- NOTA BACKEND: O botão deve enviar via fetch ou form POST para api/gravar_pedido.php -->
    <button id="btn-fazer-pedido" class="btn btn-success" style="width: 100%; font-size: 1rem; padding: 14px;">
        Fazer Pedido
    </button>
    <p style="text-align: center; font-size: .75rem; color: var(--cinza-medio); margin-top: 12px;">
        Ao fazer o pedido você concorda com nossos termos.
    </p>

</div>

<!-- ══════════════════════════════════════
     BOTTOM SHEET — SELECIONAR ENDEREÇO
     ══════════════════════════════════════ -->
<div class="sheet-overlay" id="sheetOverlay" onclick="fecharSheetEnderecos()"></div>
<div class="sheet" id="sheetEnderecos">
    <div class="sheet-handle"></div>
    <div class="sheet-header">
        <h3 class="sheet-title">Selecionar Endereço</h3>
        <button class="sheet-close" onclick="fecharSheetEnderecos()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sheet-body" id="listaEnderecos">
        <!--
            NOTA BACKEND: foreach ($enderecos as $end) — gerar um .addr-option para cada endereço do usuário.
            Query: SELECT id, apelido, logradouro, numero, bairro FROM enderecos
                   WHERE usuario_id = $_SESSION['usuario_id'];
            O onclick passa os dados do endereço para selecionarEndereco().
        -->

        <!-- Endereço 1 (mockup) -->
        <div class="addr-option addr-option--selected" id="addr-1"
             onclick="selecionarEndereco(this, '🏠 Casa', 'Rua das Flores, 123', 'Jardim Primavera · Grajaú')">
            <div class="addr-icon">🏠</div>
            <div class="addr-info">
                <p class="addr-apelido">Casa</p>
                <p class="addr-rua">Rua das Flores, 123</p>
                <p class="addr-bairro">Jardim Primavera · Grajaú</p>
            </div>
            <i class="fa-solid fa-circle-check addr-check"></i>
        </div>

        <!-- Endereço 2 (mockup) -->
        <div class="addr-option" id="addr-2"
             onclick="selecionarEndereco(this, '💼 Trabalho', 'Av. Paulista, 1000', 'Bela Vista · São Paulo')">
            <div class="addr-icon">💼</div>
            <div class="addr-info">
                <p class="addr-apelido">Trabalho</p>
                <p class="addr-rua">Av. Paulista, 1000</p>
                <p class="addr-bairro">Bela Vista · São Paulo</p>
            </div>
            <i class="fa-solid fa-circle-check addr-check"></i>
        </div>

    </div>

    <div class="sheet-footer">
        <a href="perfil.php" class="btn btn-outline" style="width:100%; text-align:center;">
            <i class="fa-solid fa-plus"></i> Adicionar novo endereço
        </a>
    </div>
</div>

<?php include '../includes/user_footer.php'; ?>

<script>
/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS desta página + PHP (iniciante) — INTEGRACAO_JS_PHP.txt na raiz
 * ═══════════════════════════════════════════════════════════════════════════
 * Ordem de carregamento: user_header → conteúdo → user_footer (inclui main.js)
 * → ESTE <script> (sheet de endereço + totais do localStorage).
 *
 * O que o PHP deve fazer aqui:
 *   • Mostrar nome real: <?= htmlspecialchars($_SESSION['nome'] ?? '') ?>
 *   • Listar endereços com foreach e data-end-id="<?= $e['id'] ?>"; o JS já
 *     comenta onde colocar <input type="hidden" name="endereco_id"> no form.
 *   • Botão "Fazer Pedido": trocar onclick por <form method="post"
 *     action="api/gravar_pedido.php"> ou fetch POST com JSON do carrinho
 *     (lido do localStorage via JS e enviado no body).
 *   • Taxa de entrega: ideal vir do PHP (config_loja) em vez de constante JS.
 * main.js (global) já trata CPF na nota e troco em dinheiro — repita regra no PHP.
 * ═══════════════════════════════════════════════════════════════════════════
 */
// ── Bottom Sheet: Selecionar Endereço ────────────────────────

function abrirSheetEnderecos() {
    document.getElementById('sheetOverlay').classList.add('active');
    document.getElementById('sheetEnderecos').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharSheetEnderecos() {
    document.getElementById('sheetOverlay').classList.remove('active');
    document.getElementById('sheetEnderecos').classList.remove('active');
    document.body.style.overflow = '';
}

function selecionarEndereco(el, apelido, rua, bairro) {
    // Remove seleção anterior
    document.querySelectorAll('.addr-option').forEach(o => o.classList.remove('addr-option--selected'));
    // Seleciona o novo
    el.classList.add('addr-option--selected');

    // Atualiza o card de endereço na tela
    document.getElementById('end-apelido').textContent = apelido;
    document.getElementById('end-linha1').textContent  = rua;
    document.getElementById('end-linha2').textContent  = bairro;

    /*
     * NOTA BACKEND: guardar o ID do endereço selecionado para envio no POST.
     * Adicionar um <input type="hidden" id="endereco-id" name="endereco_id"> na page
     * e setar: document.getElementById('endereco-id').value = el.dataset.endId;
     * (Adicionar data-end-id="<?= $end['id'] ?>" no .addr-option no PHP)
     */

    // Fecha o sheet após um breve delay visual
    setTimeout(fecharSheetEnderecos, 250);
}

// ── Preenche totais do localStorage ──────────────────────────
(function preencherResumo() {
    try {
        const cart    = JSON.parse(localStorage.getItem('chokko_cart') || '[]');
        const entrega = localStorage.getItem('chokko_entrega') || 'delivery';
        const TAXAS   = { delivery: 5.00, retirada: 0.00, local: 0.00 };
        const fmt     = v => 'R$ ' + parseFloat(v).toFixed(2).replace('.', ',');

        const subtotal = cart.reduce((s, i) => s + i.unitPrice * i.qty, 0);
        const taxa     = TAXAS[entrega] || 0;
        const total    = subtotal + taxa;

        document.getElementById('fin-subtotal').textContent = fmt(subtotal);
        document.getElementById('fin-taxa').textContent     = taxa > 0 ? fmt(taxa) : 'Grátis';
        document.getElementById('fin-total').textContent    = fmt(total);
    } catch(e) {}
})();
</script>
