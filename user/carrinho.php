<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/carrinho.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <p class="logo-mini">Chokko<span> Melt</span></p>
        <p class="page-subtitle">Sacola</p>
    </div>
</header>
</div>

<div class="page-pad">

    <!-- ── ITENS DO CARRINHO (preenchido pelo JS) ── -->
    <div class="card" id="cart-container">

        <!-- Estado vazio (oculto se tiver itens) -->
        <div class="cart-empty" id="cart-empty">
            <i class="fa-solid fa-bag-shopping"></i>
            <p>Sua sacola está vazia.</p>
            <a href="index.php" class="btn btn-primary btn-auto" style="margin-top:16px;padding:10px 24px;">
                Ver cardápio
            </a>
        </div>

        <!-- Lista de itens (gerado pelo JS) -->
        <div id="cart-items-list"></div>

        <!-- Resumo de valores (oculto enquanto vazio) -->
        <div class="cart-summary" id="cart-summary" style="display:none;">
            <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">R$ 0,00</span></div>
            <div class="summary-row" id="row-entrega"><span>Taxa de entrega</span><span id="sum-entrega">R$ 5,00</span></div>
            <div class="summary-row total"><span>Total</span><span id="sum-total">R$ 0,00</span></div>
        </div>

    </div>

    <!-- ── FORMA DE RECEBIMENTO (Radio) ── -->
    <div class="card mb-14" id="card-entrega" style="display:none;">
        <h3 class="entrega-title">
            <i class="fa-solid fa-truck"></i> Forma de Recebimento
        </h3>

        <div class="delivery-options-list">

            <label class="delivery-option active" id="opt-delivery">
                <input type="radio" name="entrega" value="delivery" checked>
                <i class="fa-solid fa-motorcycle"></i>
                <div class="delivery-option-text">
                    <strong>Delivery</strong>
                    <p>Receba em casa · Grajaú/SP</p>
                </div>
                <span class="delivery-check-icon"><i class="fa-solid fa-circle-check"></i></span>
            </label>

            <label class="delivery-option" id="opt-retirada">
                <input type="radio" name="entrega" value="retirada">
                <i class="fa-solid fa-bag-shopping"></i>
                <div class="delivery-option-text">
                    <strong>Retirar no Balcão</strong>
                    <p>Retire e leve · Sem taxa</p>
                </div>
                <span class="delivery-check-icon"><i class="fa-solid fa-circle-check"></i></span>
            </label>

            <label class="delivery-option" id="opt-local">
                <input type="radio" name="entrega" value="local">
                <i class="fa-solid fa-store"></i>
                <div class="delivery-option-text">
                    <strong>Consumir no Local</strong>
                    <p>Fique por aqui e aproveite</p>
                </div>
                <span class="delivery-check-icon"><i class="fa-solid fa-circle-check"></i></span>
            </label>

        </div>
    </div>

    <!-- ── BOTÕES DE AÇÃO ── -->
    <div id="cart-actions" style="display:none;">
        <button class="btn btn-success" id="btn-finalizar">
            <i class="fa-solid fa-check"></i>
            <span>Finalizar Pedido</span>
            <span id="btn-total-label">· R$ 0,00</span>
        </button>
        <button class="btn btn-ghost" style="margin-top:10px;" onclick="window.location.href='index.php'">
            Continuar comprando
        </button>
    </div>

    <!-- Editar complementos / observação do item na sacola -->
    <div id="cart-edit-modal" class="cart-edit-modal" aria-hidden="true">
        <div class="cart-edit-backdrop" id="cart-edit-backdrop"></div>
        <div class="cart-edit-sheet" role="dialog" aria-labelledby="cart-edit-title">
            <div class="cart-edit-handle"></div>
            <button type="button" class="cart-edit-close" id="cart-edit-close" aria-label="Fechar">&times;</button>
            <h3 id="cart-edit-title" class="cart-edit-heading"></h3>
            <p class="cart-edit-lead">Ajuste complementos ou observação e salve.</p>
            <div id="cart-edit-addons" class="cart-edit-addons-wrap"></div>
            <label class="cart-edit-label" for="cart-edit-obs">Observação</label>
            <textarea id="cart-edit-obs" class="cart-edit-textarea" rows="2" maxlength="300" placeholder="Ex.: sem glitter, menos doce…"></textarea>
            <button type="button" class="btn btn-primary btn-auto cart-edit-save" id="cart-edit-save">
                Salvar alterações
            </button>
        </div>
    </div>

</div>

<?php include '../includes/user_footer.php'; ?>
<!-- carrinho.js: lê localStorage; PHP entra no fluxo no fetch gravar_pedido + sessão. Ver INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/carrinho.js"></script>
