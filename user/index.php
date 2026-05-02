<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/cardapio.css">
<link rel="stylesheet" href="assets/css/produto-modal.css">

<!-- ── HEADER PRINCIPAL ── -->
<header class="main-header">
    <div class="logo">Chokko<span> Melt</span></div>
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

    <article class="product-card" data-cat="bolos-pote"
        data-id="1"
        data-name="Bolo de Chocolate"
        data-desc="Pote 300ml · Massa fofinha com ganache de chocolate belga e cobertura de brigadeiro"
        data-price="13.00"
        data-img="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600"
        data-addons='[{"id":"a1","name":"Cobertura extra de brigadeiro","price":2.00},{"id":"a2","name":"Granulado de chocolate","price":1.00},{"id":"a3","name":"Chantilly","price":1.50}]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400" alt="Bolo de Chocolate">
            <span class="product-badge">Popular</span>
        </div>
        <div class="product-info">
            <h3>Bolo de Chocolate</h3>
            <p class="product-desc">Pote 300ml · Massa fofinha com ganache</p>
            <div class="product-footer">
                <span class="product-price">R$ 13,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

    <article class="product-card" data-cat="brigadeiros"
        data-id="2"
        data-name="Brigadeiro Gourmet"
        data-desc="Kit com 4 unidades · Sabores variados: tradicional, pistache, Ninho e Oreo"
        data-price="13.00"
        data-img="https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=600"
        data-addons='[{"id":"b1","name":"Caixa presente com laço","price":3.00},{"id":"b2","name":"Cartão de mensagem","price":0.50}]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=400" alt="Brigadeiro Gourmet">
        </div>
        <div class="product-info">
            <h3>Brigadeiro Gourmet</h3>
            <p class="product-desc">Kit com 4 unidades · Sabores variados</p>
            <div class="product-footer">
                <span class="product-price">R$ 13,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

    <article class="product-card" data-cat="tortas-pote"
        data-id="3"
        data-name="Torta de Limão"
        data-desc="Pote 300ml · Cremosa e refrescante, com base de biscoito amanteigado e merengue"
        data-price="10.00"
        data-img="https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600"
        data-addons='[{"id":"c1","name":"Raspas de limão extra","price":0.50},{"id":"c2","name":"Chantilly","price":1.50}]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400" alt="Torta de Limão">
            <span class="product-badge">Novidade</span>
        </div>
        <div class="product-info">
            <h3>Torta de Limão</h3>
            <p class="product-desc">Pote 300ml · Cremosa e refrescante</p>
            <div class="product-footer">
                <span class="product-price">R$ 10,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

    <article class="product-card" data-cat="bebidas"
        data-id="4"
        data-name="Refrigerante Lata"
        data-desc="300ml · Coca-Cola, Guaraná Antarctica, Fanta Laranja e Sprite"
        data-price="5.00"
        data-img="https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=600"
        data-addons='[]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400" alt="Refrigerante">
        </div>
        <div class="product-info">
            <h3>Refrigerante Lata</h3>
            <p class="product-desc">300ml · Coca-Cola, Guaraná e mais</p>
            <div class="product-footer">
                <span class="product-price">R$ 5,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

    <article class="product-card" data-cat="salgados"
        data-id="5"
        data-name="Pastel Frito"
        data-desc="Grande e crocante, feito na hora · Escolha o recheio: Frango com catupiry, Carne temperada ou Queijo"
        data-price="12.00"
        data-img="https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600"
        data-addons='[{"id":"d1","name":"Molho de pimenta","price":0.00},{"id":"d2","name":"Molho de alho","price":0.00},{"id":"d3","name":"Dobro de queijo","price":2.00}]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400" alt="Pastel Frito">
        </div>
        <div class="product-info">
            <h3>Pastel Frito</h3>
            <p class="product-desc">Frango, carne ou queijo</p>
            <div class="product-footer">
                <span class="product-price">R$ 12,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

    <article class="product-card" data-cat="kits"
        data-id="6"
        data-name="Kit Festa"
        data-desc="Bolo grande (1kg) + 2 refrigerantes lata + caixa com 12 brigadeiros gourmet. Perfeito para aniversários!"
        data-price="335.00"
        data-img="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=600"
        data-addons='[{"id":"e1","name":"Personalização do bolo (tema)","price":15.00},{"id":"e2","name":"Vela de aniversário","price":5.00},{"id":"e3","name":"Topos de bolo","price":10.00}]'>
        <div class="product-img-wrap">
            <img src="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=400" alt="Kit Festa">
            <span class="product-badge">Oferta</span>
        </div>
        <div class="product-info">
            <h3>Kit Festa</h3>
            <p class="product-desc">Bolo grande + 2 refris + brigadeiros</p>
            <div class="product-footer">
                <span class="product-price">R$ 335,00</span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>

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
            <div id="modalAddonsSection" style="display:none;">
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
    <i class="fa-solid fa-check-circle" style="margin-right:6px;"></i>
    <span id="toastMsg">Item adicionado!</span>
</div>

<?php include '../includes/user_footer.php'; ?>
<!-- Ordem: main.js (footer) depois cardapio.js — não inverta sem motivo. -->
<!-- INTEGRACAO_JS_PHP.txt: PHP deve gerar os .product-card com data-* reais. -->
<script src="assets/js/cardapio.js"></script>