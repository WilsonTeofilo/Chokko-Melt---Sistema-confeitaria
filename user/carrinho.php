<?php
/*
 * ══════════════════════════════════════════════════════════════
 *  NOTA BACKEND — carrinho.php
 * ══════════════════════════════════════════════════════════════
 *
 *  PASSO 1 — Processar ações do carrinho (antes de renderizar):
 *
 *  session_start(); // já feito pelo header incluído
 *  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_carrinho'])) {
 *      $idx = intval($_POST['item_idx'] ?? -1);
 *      switch ($_POST['acao_carrinho']) {
 *          case 'alterar_qtd':
 *              $delta = intval($_POST['delta'] ?? 0);
 *              if (isset($_SESSION['carrinho'][$idx])) {
 *                  $_SESSION['carrinho'][$idx]['qty'] += $delta;
 *                  if ($_SESSION['carrinho'][$idx]['qty'] < 1) {
 *                      array_splice($_SESSION['carrinho'], $idx, 1);
 *                  } elseif ($_SESSION['carrinho'][$idx]['qty'] > 10) {
 *                      $_SESSION['carrinho'][$idx]['qty'] = 10;
 *                  }
 *              }
 *              break;
 *          case 'remover_item':
 *              if (isset($_SESSION['carrinho'][$idx])) {
 *                  array_splice($_SESSION['carrinho'], $idx, 1);
 *              }
 *              break;
 *      }
 *      header('Location: carrinho.php'); exit;
 *  }
 *
 *  PASSO 2 — Ler o carrinho da sessão:
 *
 *  $carrinho      = $_SESSION['carrinho'] ?? [];
 *  $taxa_entrega  = 5.00; // pode vir do banco/config
 *
 *  PASSO 3 — Ao receber o form de checkout (finalizarPedido.php):
 *
 *  $_POST['acao']    → 'finalizar' | 'continuar'
 *  $_POST['entrega'] → 'delivery'  | 'retirada'  | 'local'
 *  O carrinho vem de $_SESSION['carrinho'] — não precisa de JSON.
 * ══════════════════════════════════════════════════════════════
 */

// Mockup: carrinho vazio até o backend preencher $_SESSION['carrinho']
$carrinho     = $_SESSION['carrinho'] ?? [];
$taxa_entrega = 5.00;
$subtotal     = 0;
foreach ($carrinho as $item) {
    $subtotal += ($item['unitPrice'] ?? 0) * ($item['qty'] ?? 1);
}
$total = $subtotal + $taxa_entrega;
?>
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

    <!-- ── ITENS DO CARRINHO (renderizado pelo PHP via $_SESSION['carrinho']) ── -->
    <div class="card" id="cart-container">

        <?php if (empty($carrinho)): ?>
        <!-- Estado vazio -->
        <div class="cart-empty" id="cart-empty">
            <i class="fa-solid fa-bag-shopping"></i>
            <p>Sua sacola está vazia.</p>
            <a href="index.php" class="btn btn-primary btn-auto btn-auto-pad">
                Ver cardápio
            </a>
        </div>

        <?php else: ?>
        <!-- Lista de itens -->
        <div id="cart-items-list">
            <?php foreach ($carrinho as $idx => $item): ?>
            <div class="cart-item" data-idx="<?= $idx ?>">

                <div class="cart-item-main">
                    <img class="cart-item-img"
                         src="<?= htmlspecialchars($item['img'] ?? '') ?>"
                         alt="<?= htmlspecialchars($item['name'] ?? '') ?>">
                    <div class="cart-item-info">
                        <h4><?= htmlspecialchars($item['name'] ?? '') ?></h4>
                        <?php if (!empty($item['addons'])): ?>
                        <p class="item-addons">+ <?= htmlspecialchars(implode(', ', array_column($item['addons'], 'name'))) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['obs'])): ?>
                        <p class="item-obs">"<?= htmlspecialchars($item['obs']) ?>"</p>
                        <?php endif; ?>
                        <p class="item-price">R$ <?= number_format(($item['unitPrice'] ?? 0) * ($item['qty'] ?? 1), 2, ',', '.') ?></p>
                    </div>
                </div>

                <!-- Controles de quantidade (mini-forms PHP) -->
                <div class="qty-ctrl">
                    <form method="POST" action="carrinho.php" style="display:inline;">
                        <input type="hidden" name="acao_carrinho" value="alterar_qtd">
                        <input type="hidden" name="item_idx" value="<?= $idx ?>">
                        <input type="hidden" name="delta" value="-1">
                        <button type="submit">−</button>
                    </form>
                    <span class="qty-value"><?= intval($item['qty'] ?? 1) ?></span>
                    <form method="POST" action="carrinho.php" style="display:inline;">
                        <input type="hidden" name="acao_carrinho" value="alterar_qtd">
                        <input type="hidden" name="item_idx" value="<?= $idx ?>">
                        <input type="hidden" name="delta" value="1">
                        <button type="submit">+</button>
                    </form>
                </div>

                <!-- Remover item (mini-form PHP) -->
                <form method="POST" action="carrinho.php" style="display:inline;">
                    <input type="hidden" name="acao_carrinho" value="remover_item">
                    <input type="hidden" name="item_idx" value="<?= $idx ?>">
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
                <span id="sum-subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
            </div>
            <div class="summary-row" id="row-entrega">
                <span>Taxa de entrega</span>
                <span id="sum-entrega">R$ <?= number_format($taxa_entrega, 2, ',', '.') ?></span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span id="sum-total">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?php if (!empty($carrinho)): ?>

    <!-- ── FORMA DE RECEBIMENTO ── -->
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

    <!-- ── FORMULÁRIO DE CHECKOUT (Preparado para PHP) ── -->
    <form id="form-carrinho" action="finalizarPedido.php" method="POST">
        <!--
            NOTA BACKEND: ao receber este POST em finalizarPedido.php:
              $_POST['acao']    → 'finalizar' | 'continuar'
              $_POST['entrega'] → 'delivery'  | 'retirada'  | 'local'
              O carrinho vem de $_SESSION['carrinho'] — não precisa de JSON.

            Se acao === 'continuar':
              header('Location: index.php'); exit;
            Se acao === 'finalizar':
              Verificar sessão → montar tela de confirmação do pedido.
        -->
        <input type="hidden" name="entrega" id="hidden-entrega" value="delivery">

        <!-- ── BOTÕES DE AÇÃO ── -->
        <div id="cart-actions">
            <button type="submit" name="acao" value="finalizar" class="btn btn-success" id="btn-finalizar">
                <i class="fa-solid fa-check"></i>
                <span>Finalizar Pedido</span>
                <span id="btn-total-label">· R$ <?= number_format($total, 2, ',', '.') ?></span>
            </button>
            <button type="submit" name="acao" value="continuar" class="btn btn-ghost btn-auto-pad">
                Continuar comprando
            </button>
        </div>

    </form>

    <?php endif; ?>

</div>

<?php include '../includes/user_footer.php'; ?>
<!-- carrinho.js: UI de entrega + verificação de login. PHP gerencia o carrinho via $_SESSION. -->
<script src="assets/js/carrinho.js"></script>