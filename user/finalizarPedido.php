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
    <div class="aviso-conta">
        <i class="fa-solid fa-circle-user icon-user"></i>
        <div>
            <p class="conta-text">Fazendo pedido como Maria Silva</p>
            <!-- NOTA BACKEND: exibir o nome do $_SESSION['usuario_nome'] -->
            <p class="conta-subtext">Não é você? <a href="login.php" class="conta-link">Trocar de conta</a></p>
        </div>
    </div>

    <!-- ENDEREÇO DE ENTREGA -->
    <h3 class="fin-section-title">Endereço de Entrega</h3>
    <div class="card mb-20">
        <div class="flex-between">
            <div class="flex-gap-12">
                <i class="fa-solid fa-location-dot end-icon"></i>
                <div>
                    <!-- NOTA BACKEND: exibir o endereço padrão do usuário logado -->
                    <p id="end-apelido" class="pay-text">Casa</p>
                    <p id="end-linha1" style="font-size: .75rem; color: var(--cinza-medio); line-height: 1.4; margin-top: 2px;">Rua das Flores, 123</p>
                    <p id="end-linha2" style="font-size: .75rem; color: var(--cinza-medio);">Jardim Primavera · Grajaú</p>
                </div>
            </div>
            <!-- Botão Trocar abre o bottom sheet de endereços -->
            <button class="btn-trocar-end" onclick="abrirSheetEnderecos()">Trocar</button>
        </div>
    </div>

    <!-- FORMA DE PAGAMENTO -->
    <h3 class="fin-section-title">Pagamento na Entrega</h3>
    <div class="card mb-20">
        <!-- NOTA BACKEND: Os values dos radios devem ser capturados no POST -->
        <label class="pay-option">
            <div class="flex-gap-12">
                <i class="fa-brands fa-pix pay-icon pay-icon-pix"></i>
                <span class="pay-text">Pix</span>
            </div>
            <input type="radio" name="pagamento" value="pix" class="radio-marrom">
        </label>

        <label class="pay-option">
            <div class="flex-gap-12">
                <i class="fa-regular fa-credit-card pay-icon pay-icon-card"></i>
                <span class="pay-text">Cartão de Crédito</span>
            </div>
            <input type="radio" name="pagamento" value="credito" class="radio-marrom" checked>
        </label>

        <label class="pay-option pay-option-last">
            <div class="flex-gap-12">
                <i class="fa-solid fa-money-bill-wave pay-icon pay-icon-money"></i>
                <span class="pay-text">Dinheiro</span>
            </div>
            <input type="radio" name="pagamento" value="dinheiro" class="radio-marrom">
        </label>
    </div>

    <!-- CPF NA NOTA E OBSERVAÇÃO -->
    <div class="card mb-20">
        <label class="label-form">CPF na Nota (Opcional)</label>
        <input type="text" class="form-control mb-15" placeholder="000.000.000-00">

        <label class="label-form">Observação para o Restaurante</label>
        <textarea class="form-control" rows="3" placeholder="Ex: Tocar o interfone, sem cebola, etc..."></textarea>
    </div>

    <!-- RESUMO -->
    <div class="card mb-20">
        <div class="resumo-row">
            <span>Subtotal</span>
            <!-- NOTA BACKEND: calcular do carrinho real -->
            <span id="fin-subtotal">R$ 26,00</span>
        </div>
        <div class="resumo-row-last">
            <span>Taxa de Entrega</span>
            <span id="fin-taxa">R$ 5,00</span>
        </div>
        <div class="resumo-total">
            <span>Total a pagar</span>
            <span id="fin-total">R$ 31,00</span>
        </div>
    </div>

    <!-- NOTA BACKEND: O botão deve enviar via fetch ou form POST para api/gravar_pedido.php -->
    <button id="btn-fazer-pedido" class="btn btn-success btn-fazer-pedido">
        Fazer Pedido
    </button>
    <p class="termos-text">
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

<script src="assets/js/finalizar_pedido.js"></script>
