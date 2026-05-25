<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/cardapio.css">
<link rel="stylesheet" href="assets/css/produto-modal.css">

<!-- ── HEADER PRINCIPAL ── -->
<header class="main-header">
    <a href="index.php" class="logo" style="text-decoration: none;">Chokko<span> Melt</span></a>
    <div class="status-badge" id="store-status-badge">Loja Aberta</div>
</header>

<!-- ── BANNER ── -->
<div class="hero-banner">
    <h2>Cardápio Digital 🍫</h2>
    <p>Confeitaria artesanal no Grajaú · Delivery das <span id="store-hours-text">15:00 às 22:00</span></p>
</div>

<!-- ── FILTROS DE CATEGORIA ── -->
<div class="categories-wrap">
    <button class="cat-btn active" data-cat="todos">Todos</button>
    <button class="cat-btn" data-cat="bolos-pote">Bolos de Pote</button>
    <button class="cat-btn" data-cat="tortas-pote">Tortas de Pote</button>
    <button class="cat-btn" data-cat="brigadeiros">Brigadeiros</button>
    <button class="cat-btn" data-cat="salgados">Salgados</button>
    <button class="cat-btn" data-cat="bebidas">Bebidas</button>
    <button class="cat-btn" data-cat="kits">Kits &amp; Bolos</button>
</div>

<p class="section-title">Mais Pedidos ✨</p>

<!-- ── GRID DE PRODUTOS ── -->
<!-- Cada article tem data-* com as infos do produto que o JS vai ler para o modal -->
<div class="products-grid" id="products-grid">

    <!-- MODELO PARA FOREACH PHP -->
    <article class="product-card" data-cat="[categoria]"
        data-id="[id_produto]"
        data-name="[nome_do_produto]"
        data-desc="[descricao_do_produto]"
        data-price="[preco_do_produto]"
        data-img="[url_da_imagem]"
        data-addons='[json_dos_adicionais_ou_vazio]'>
        <div class="product-img-wrap">
            <img src="[url_da_imagem]" alt="[nome_do_produto]">
            <!-- Descomente e use se houver badge -->
            <!-- <span class="product-badge">[badge]</span> -->
        </div>
        <div class="product-info">
            <h3>[nome_do_produto]</h3>
            <p class="product-desc">[descricao_curta_do_produto]</p>
            <div class="product-footer">
                <span class="product-price">R$ [preco_do_produto]</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>
    <!-- FIM MODELO PHP -->

</div>

<!-- ══════════════════════════════════════════
     MODAL DE DETALHES DO PRODUTO
     ══════════════════════════════════════════ -->
<div class="modal-overlay" id="productModal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-sheet" id="modalSheet">
        <div class="modal-handle"></div>

        <img class="modal-img" id="modalImg" src="" alt="">

        <div class="modal-body">
            <h2 class="modal-product-name" id="modal-title"></h2>
            <p class="modal-product-desc" id="modalDesc"></p>
            <span class="modal-product-price" id="modalPriceDisplay"></span>

            <!-- Acompanhamentos (gerados via JS) -->
            <div id="modalAddonsSection" class="hide">
                <div class="modal-section-title">Acompanhamentos</div>
                <p class="modal-section-sub">Escolha os extras (opcional)</p>
                <div id="modalAddonsList"></div>
            </div>

            <!-- Observação -->
            <label class="modal-obs-label" for="modalObs">Alguma observação?</label>
            <textarea class="modal-obs-input" id="modalObs" rows="2" placeholder="Ex: sem cobertura, pote separado..."></textarea>
        </div>

        <!-- Rodapé: quantidade + botão adicionar -->
        <div class="modal-footer">
            <div class="qty-stepper">
                <button id="modalQtyMinus" aria-label="Diminuir">−</button>
                <span class="qty-num" id="modalQty">1</span>
                <button id="modalQtyPlus" aria-label="Aumentar">+</button>
            </div>
            <button class="btn-add-modal" id="modalAddBtn">
                <span>Adicionar</span>
                <span id="modalBtnPrice">R$ 0,00</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast de confirmação -->
<div class="toast-cart" id="toastCart">
    <i class="fa-solid fa-check-circle icon-success"></i>
    <span id="toastMsg">Item adicionado!</span>
</div>

<?php include '../includes/user_footer.php'; ?>
<!-- Ordem: main.js (footer) depois cardapio.js — não inverta sem motivo. -->
<!-- INTEGRACAO_JS_PHP.txt: PHP deve gerar os .product-card com data-* reais. -->
<script src="assets/js/cardapio.js"></script>







<!-- WILSON BACK-END -->

<?php 
include ("../config/config.php");
switch(@$_REQUEST['page']) {
   
    case 'carrinhoUS':
        break;

    case 'CadastroUS':
        include ("src/auth/auth.php");
        break;
}
?>