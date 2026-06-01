<?php 
require_once '../config/config.php';
require_once '../classes/Produto.php';
require_once '../classes/Categoria.php';
include '../includes/user_header.php';
?>
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
    <p>Confeitaria artesanal no Grajaú · Delivery das <span id="store-hours-text"><?php echo isset($horario['horario']) ? htmlspecialchars($horario['horario']) : '15:00 às 22:00'; ?></span></p>
</div>

<!-- ── FILTROS DE CATEGORIA ── -->
<div class="categories-wrap">
    <button class="cat-btn active" data-cat="todos">Todos</button>
    <?php
    try {
        $catObj = new Categoria(DATABASE, HOST, USER, PASS);
        $todasCats = $catObj->listarTodas();
        foreach ($todasCats as $c) {
            echo '<button class="cat-btn" data-cat="' . $c['id_categoria'] . '">' . htmlspecialchars($c['nome']) . '</button>';
        }
    } catch (Exception $e) {
        // Sem categorias ou erro
    }
    ?>
</div>

<p class="section-title">Mais Pedidos ✨</p>

<!-- ── GRID DE PRODUTOS ── -->
<!-- Cada article tem data-* com as infos do produto que o JS vai ler para o modal -->
<div class="products-grid" id="products-grid">

    <?php
    try {
        $prodObj = new Produto(DATABASE, HOST, USER, PASS);
        $produtosAtivos = $prodObj->listarAtivos();
        foreach ($produtosAtivos as $p):
            $adicionais = $prodObj->obterAdicionais($p['id_produto']);
            $addonsJson = json_encode($adicionais);
            
            $imgSrc = $p['imagem'];
            if (empty($imgSrc)) {
                $imgSrc = 'https://placehold.co/150x150/f5e6d0/8B4513?text=Foto';
            } else {
                if (strpos($imgSrc, 'uploads/') === 0) {
                    $imgSrc = '../admin/' . $imgSrc;
                }
            }
            
            $descCurta = $p['descricao'];
            if (strlen($descCurta) > 70) {
                $descCurta = substr($descCurta, 0, 67) . '...';
            }
    ?>
    <article class="product-card" data-cat="<?php echo $p['id_categoria']; ?>"
        data-id="<?php echo $p['id_produto']; ?>"
        data-name="<?php echo htmlspecialchars($p['nome']); ?>"
        data-desc="<?php echo htmlspecialchars($p['descricao']); ?>"
        data-price="<?php echo $p['preco']; ?>"
        data-img="<?php echo $imgSrc; ?>"
        data-addons='<?php echo $addonsJson; ?>'>
        <div class="product-img-wrap">
            <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>">
        </div>
        <div class="product-info">
            <span class="produto-categoria-badge" style="font-size: 0.75rem; background: #ffe4c4; color: #8b4513; padding: 2px 8px; border-radius: 10px; margin-bottom: 5px; display: inline-block;"><?php echo isset($p['nome_categoria']) ? htmlspecialchars($p['nome_categoria']) : 'Sem Categoria'; ?></span>
            <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
            <p class="product-desc"><?php echo htmlspecialchars($descCurta); ?></p>
            <div class="product-footer">
                <span class="product-price">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></span>
                <button class="add-btn" title="Ver detalhes e adicionar"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>
    </article>
    <?php 
        endforeach;
    } catch (Exception $e) {
        echo '<p style="grid-column: 1/-1; text-align: center; color: #777;">Nenhum produto disponível na vitrine no momento.</p>';
    }
    ?>

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







