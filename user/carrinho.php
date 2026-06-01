<?php
// ============================================================
// carrinho.php — Página da Sacola do cliente


session_start();
require_once '../config/config.php';
require_once '../classes/Carrinho.php';

// Garante que o carrinho existe na sessão
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Puxa a taxa de entrega padrão da tabela config_loja
$taxa_entrega = 5.00;
try {
    $stmt = $conn->prepare("SELECT taxa_entrega_padrao FROM config_loja WHERE id_config = 1");
    $stmt->execute();
    $config_loja = $stmt->fetch();
    if ($config_loja) {
        $taxa_entrega = floatval($config_loja['taxa_entrega_padrao']);
    }
} catch (Exception $e) {
    // Mantém o padrão em caso de erro
}

// Puxa os itens da sessão para uma variável local
$carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
$itens       = $carrinhoObj->listarItens();
$subtotal     = $carrinhoObj->calcularSubtotal();
$total        = $subtotal + $taxa_entrega;
?>
<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/carrinho.css">

<div class="page-header-wrap">
    <header class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
            <p class="page-subtitle">Sacola</p>
        </div>
    </header>
</div>

<div class="page-pad">

    <div class="card" id="cart-container">

        <?php if (empty($itens)): ?>

            <!-- Carrinho vazio -->
            <div class="cart-empty" id="cart-empty">
                <i class="fa-solid fa-bag-shopping"></i>
                <p>Sua sacola está vazia.</p>
                <a href="index.php" class="btn btn-primary btn-auto btn-auto-pad">
                    Ver cardápio
                </a>
            </div>

        <?php else: ?>

            <!-- Lista de itens do carrinho -->
            <div id="cart-items-list">
                <?php foreach ($itens as $item): 
                    $chave = $item['chave'];
                    
                    // Tratamento amigável da URL da imagem
                    $imgSrc = $item['imagem'];
                    if (empty($imgSrc)) {
                        $imgSrc = 'https://placehold.co/150x150/f5e6d0/8B4513?text=Foto';
                    } else if (strpos($imgSrc, 'uploads/') === 0) {
                        $imgSrc = '../admin/' . $imgSrc;
                    }
                ?>
                <div class="cart-item">

                    <!-- Imagem + informações do produto -->
                    <div class="cart-item-main">
                        <img class="cart-item-img"
                             src="<?= htmlspecialchars($imgSrc) ?>"
                             alt="<?= htmlspecialchars($item['nome']) ?>">
                        <div class="cart-item-info">
                            <h4><?= htmlspecialchars($item['nome']) ?></h4>

                            <?php if (!empty($item['observacao'])): ?>
                                <p class="item-obs">"<?= htmlspecialchars($item['observacao']) ?>"</p>
                            <?php endif; ?>

                            <?php if (!empty($item['adicionais'])): ?>
                                <div class="item-addons" style="font-size: 0.8rem; color: #757575; margin-top: 4px;">
                                    <span style="font-weight: 600; color: #5d4037;">Adicionais:</span>
                                    <?php 
                                    $addonsNomes = [];
                                    foreach ($item['adicionais'] as $ad) {
                                        $addonsNomes[] = htmlspecialchars($ad['nome']) . ' (+ R$ ' . number_format($ad['preco'], 2, ',', '.') . ')';
                                    }
                                    echo implode(', ', $addonsNomes);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php 
                            $precoTotalItem = $item['preco_unitario'];
                            if (!empty($item['adicionais'])) {
                                foreach ($item['adicionais'] as $ad) {
                                    $precoTotalItem += (float)$ad['preco'];
                                }
                            }
                            $precoTotalItem *= $item['quantidade'];
                            ?>
                            <p class="item-price" style="margin-top: 6px;">
                                R$ <?= number_format($precoTotalItem, 2, ',', '.') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Botões de quantidade: form PHP com ação alterar_quantidade -->
                    <div class="qty-ctrl">

                        <!-- Botão diminuir -->
                        <form method="POST" action="src/carrinho_acao.php" style="display:inline;">
                            <input type="hidden" name="acao"  value="alterar_quantidade">
                            <input type="hidden" name="chave" value="<?= htmlspecialchars($chave) ?>">
                            <input type="hidden" name="delta" value="-1">
                            <button type="submit">−</button>
                        </form>

                        <span class="qty-value"><?= $item['quantidade'] ?></span>

                        <!-- Botão aumentar -->
                        <form method="POST" action="src/carrinho_acao.php" style="display:inline;">
                            <input type="hidden" name="acao"  value="alterar_quantidade">
                            <input type="hidden" name="chave" value="<?= htmlspecialchars($chave) ?>">
                            <input type="hidden" name="delta" value="1">
                            <button type="submit">+</button>
                        </form>

                    </div>

                    <!-- Botão remover item -->
                    <form method="POST" action="src/carrinho_acao.php" style="display:inline;">
                        <input type="hidden" name="acao"  value="remover">
                        <input type="hidden" name="chave" value="<?= htmlspecialchars($chave) ?>">
                        <button type="submit" class="cart-item-remove" title="Remover">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>

                </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumo de valores -->
            <div class="cart-summary" id="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Taxa de entrega</span>
                    <span>R$ <?= number_format($taxa_entrega, 2, ',', '.') ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php if (!empty($itens)): ?>

    <!-- Forma de recebimento -->
    <div class="card mb-14" id="card-entrega">
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

    <!-- Botão finalizar pedido -->
    <form id="form-carrinho" action="finalizarPedido.php" method="POST">
        <input type="hidden" name="entrega" id="hidden-entrega" value="delivery">
        <div id="cart-actions">
            <button type="submit" name="acao" value="finalizar" class="btn btn-success" id="btn-finalizar">
                <i class="fa-solid fa-check"></i>
                <span>Finalizar Pedido</span>
                <span>· R$ <?= number_format($total, 2, ',', '.') ?></span>
            </button>
            <a href="index.php" class="btn btn-ghost btn-auto-pad">
                Continuar comprando
            </a>
        </div>
    </form>

    <?php endif; ?>

</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/carrinho.js"></script>