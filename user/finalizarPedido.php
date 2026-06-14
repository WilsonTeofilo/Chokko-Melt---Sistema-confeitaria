<?php 
session_start();
require_once '../config/config.php';
require_once '../classes/Carrinho.php';
require_once '../classes/Endereco.php';

// 1. Se não estiver logado, obriga a logar antes de ver essa página
if (!isset($_SESSION['idlogado'])) {
    // Guarda na sessão qual entrega ele escolheu pra não perder
    if (isset($_POST['entrega'])) {
        $_SESSION['forma_entrega_pendente'] = $_POST['entrega'];
    }
    // Vai pro login com a indicação de voltar pra cá depois
    header("Location: login.php?redirect=finalizarPedido.php");
    exit;
}

// 2. Carrega o carrinho e calcula valores
$carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
$itens = $carrinhoObj->listarItens();
$subtotal = $carrinhoObj->calcularSubtotal();

// Se o carrinho estiver vazio, volta pro início
if (empty($itens)) {
    header("Location: index.php");
    exit;
}

// 3. Recupera forma de recebimento (delivery, retirada, local)
$forma_entrega = $_POST['entrega'] ?? $_SESSION['forma_entrega_pendente'] ?? 'delivery';

// 4. Puxa a taxa de entrega padrão da tabela config_loja se for delivery
$taxa_entrega = 0.00;
if ($forma_entrega === 'delivery') {
    try {
        $stmt = $conn->prepare("SELECT taxa_entrega_padrao FROM config_loja WHERE id_config = 1");
        $stmt->execute();
        $config_loja = $stmt->fetch();
        if ($config_loja) {
            $taxa_entrega = floatval($config_loja['taxa_entrega_padrao']);
        }
    } catch (Exception $e) {
        $taxa_entrega = 5.00; // fallback
    }
}

$total = $subtotal + $taxa_entrega;

// 5. Carrega os endereços do cliente
$enderecoObj = new Endereco(DATABASE, HOST, USER, PASS);
$enderecos = $enderecoObj->listarPorCliente($_SESSION['idlogado']);

// Pré-seleciona o primeiro endereço se for delivery
$endereco_selecionado = null;
if ($forma_entrega === 'delivery' && !empty($enderecos)) {
    $endereco_selecionado = $enderecos[0];
}

// Verifica se pode finalizar (se for delivery, exige endereço cadastrado)
$pode_finalizar = true;
if ($forma_entrega === 'delivery' && empty($enderecos)) {
    $pode_finalizar = false;
}

include '../includes/user_header.php'; 
?>
<link rel="stylesheet" href="assets/css/carrinho.css">
<link rel="stylesheet" href="assets/css/finalizar.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="carrinho.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <p class="page-subtitle">Finalizar Pedido</p>
    </div>
</header>
</div>

<div class="page-pad">

    <!-- FORMULÁRIO DE CHECKOUT -->
    <form action="src/pedido_acao.php" method="POST" id="checkoutForm">
        <input type="hidden" name="tipo_entrega" value="<?= htmlspecialchars($forma_entrega) ?>">
        <input type="hidden" name="id_endereco" id="id_endereco_input" value="<?= htmlspecialchars($endereco_selecionado['id_endereco'] ?? '') ?>">

        <?php if (isset($_SESSION['erro_checkout'])): ?>
            <div class="checkout-error-banner" style="background: #FFCDD2; color: #B71C1C; padding: 12px 15px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($_SESSION['erro_checkout']) ?></span>
                <?php unset($_SESSION['erro_checkout']); ?>
            </div>
        <?php endif; ?>

        <!-- AVISO DE CONTA -->
        <div class="aviso-conta">
            <i class="fa-solid fa-circle-user icon-user"></i>
            <div>
                <p class="conta-text">Fazendo pedido como <?= htmlspecialchars($_SESSION['userlogado']) ?></p>
                <p class="conta-subtext">Não é você? <a href="login.php" class="conta-link">Trocar de conta</a></p>
            </div>
        </div>

        <!-- MODO DE RECEBIMENTO OU ENDEREÇO DE ENTREGA -->
        <?php if ($forma_entrega !== 'delivery'): ?>
            <h3 class="fin-section-title">Forma de Recebimento</h3>
            <div class="card mb-20">
                <div class="flex-between">
                    <div class="flex-gap-12">
                        <i class="fa-solid <?= $forma_entrega === 'retirada' ? 'fa-bag-shopping' : 'fa-store' ?>" style="font-size: 1.5rem; color: var(--marrom);"></i>
                        <div>
                            <p class="pay-text" style="font-weight: bold;">
                                <?= $forma_entrega === 'retirada' ? 'Retirar no Balcão' : 'Consumir no Local' ?>
                            </p>
                            <p style="font-size: .75rem; color: var(--cinza-medio); line-height: 1.4; margin-top: 2px;">
                                <?= $forma_entrega === 'retirada' ? 'Sem taxa de entrega. Retire seu pedido no balcão da confeitaria.' : 'Sem taxa de entrega. Seu pedido será servido na mesa.' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <h3 class="fin-section-title">Endereço de Entrega</h3>
            <div class="card mb-20">
                <?php if (empty($enderecos)): ?>
                    <div style="text-align: center; padding: 10px 0;">
                        <p style="font-size: .85rem; color: var(--cinza-medio); margin-bottom: 10px;">Nenhum endereço cadastrado.</p>
                        <a href="perfil.php" class="btn btn-outline btn-auto-pad" style="font-size: 0.8rem; display: inline-block;">
                            <i class="fa-solid fa-plus"></i> Cadastrar Endereço
                        </a>
                    </div>
                <?php else: 
                    $apelido_inicial = !empty($endereco_selecionado['complemento']) ? $endereco_selecionado['complemento'] : 'Endereço 1';
                ?>
                    <div class="flex-between">
                        <div class="flex-gap-12">
                            <i class="fa-solid fa-location-dot end-icon"></i>
                            <div>
                                <p id="end-apelido" class="pay-text"><?= htmlspecialchars($apelido_inicial) ?></p>
                                <p id="end-linha1" style="font-size: .75rem; color: var(--cinza-medio); line-height: 1.4; margin-top: 2px;">
                                    <?= htmlspecialchars($endereco_selecionado['rua'] . ', ' . $endereco_selecionado['numero']) ?>
                                </p>
                                <p id="end-linha2" style="font-size: .75rem; color: var(--cinza-medio);">
                                    <?= htmlspecialchars($endereco_selecionado['bairro'] . ($endereco_selecionado['cep'] ? ' · ' . $endereco_selecionado['cep'] : '')) ?>
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-trocar-end" onclick="abrirSheetEnderecos()">Trocar</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- FORMA DE PAGAMENTO -->
        <h3 class="fin-section-title">Pagamento na Entrega</h3>
        <div class="card mb-20">
            <label class="pay-option">
                <div class="flex-gap-12">
                    <i class="fa-brands fa-pix pay-icon pay-icon-pix"></i>
                    <span class="pay-text">Pix</span>
                </div>
                <input type="radio" name="pagamento" value="pix" class="radio-marrom" required>
            </label>

            <label class="pay-option">
                <div class="flex-gap-12">
                    <i class="fa-regular fa-credit-card pay-icon pay-icon-card"></i>
                    <span class="pay-text">Cartão de Crédito/Débito</span>
                </div>
                <input type="radio" name="pagamento" value="credito" class="radio-marrom" checked required>
            </label>

            <label class="pay-option pay-option-last">
                <div class="flex-gap-12">
                    <i class="fa-solid fa-money-bill-wave pay-icon pay-icon-money"></i>
                    <span class="pay-text">Dinheiro</span>
                </div>
                <input type="radio" name="pagamento" value="dinheiro" class="radio-marrom" required>
            </label>
            
            <!-- Campo de Troco Dinâmico -->
            <div id="troco-wrapper" style="display: none; padding: 15px; background: #fdfaf6; border-top: 1px dashed #ddd; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <label class="label-form" style="font-weight: bold; color: #3b2313; font-size: 0.85rem; display: block; margin-bottom: 5px;">Troco para quanto?</label>
                <div style="position: relative; display: flex; align-items: center; max-width: 150px;">
                    <span style="position: absolute; left: 12px; font-weight: bold; color: #3b2313; font-size: 0.9rem;">R$</span>
                    <input type="text" name="valor_pago_dinheiro" id="valor_pago_dinheiro" class="form-control" placeholder="Ex: 50,00" style="padding-left: 35px; width: 100%; border-radius: 8px; border: 1px solid #ccc; height: 38px; box-sizing: border-box;" autocomplete="off">
                </div>
                <p style="font-size: 0.75rem; color: #888; margin-top: 5px;">Se não precisar de troco, basta digitar o valor exato da compra.</p>
            </div>
        </div>

        <!-- CPF NA NOTA E OBSERVAÇÃO -->
        <div class="card mb-20">
            <label class="label-form">CPF na Nota (Opcional)</label>
            <input type="text" name="cpf_nota" class="form-control mb-15" placeholder="000.000.000-00">

            <label class="label-form">Observação para o Restaurante</label>
            <textarea name="observacao" class="form-control" rows="3" placeholder="Ex: Tocar o interfone, sem cebola, etc..."></textarea>
        </div>

        <!-- RESUMO -->
        <div class="card mb-20">
            <div class="resumo-row">
                <span>Subtotal</span>
                <span id="fin-subtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
            </div>
            <div class="resumo-row-last">
                <span>Taxa de Entrega</span>
                <span id="fin-taxa"><?= $taxa_entrega > 0 ? 'R$ ' . number_format($taxa_entrega, 2, ',', '.') : 'Grátis' ?></span>
            </div>
            <div class="resumo-total">
                <span>Total a pagar</span>
                <span id="fin-total">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
        </div>

        <button type="submit" id="btn-fazer-pedido" class="btn btn-success btn-fazer-pedido" <?= !$pode_finalizar ? 'disabled style="opacity: 0.6; cursor: not-allowed;"' : '' ?>>
            Fazer Pedido
        </button>

        <?php if (!$pode_finalizar): ?>
            <p style="color: #c62828; font-size: 0.8rem; text-align: center; margin-top: 10px; font-weight: 500;">
                <i class="fa-solid fa-circle-exclamation"></i> Você precisa cadastrar um endereço no seu perfil para receber por Delivery.
            </p>
        <?php endif; ?>

        <p class="termos-text">
            Ao fazer o pedido você concorda com nossos termos.
        </p>
    </form>

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
        <?php if (!empty($enderecos)): ?>
            <?php foreach ($enderecos as $idx => $end): 
                $apelido = !empty($end['complemento']) ? $end['complemento'] : 'Endereço ' . ($idx + 1);
                $linha1 = htmlspecialchars($end['rua'] . ', ' . $end['numero']);
                $linha2 = htmlspecialchars($end['bairro'] . ($end['cep'] ? ' · ' . $end['cep'] : ''));
                $selectedClass = ($idx === 0) ? 'addr-option--selected' : '';
            ?>
                <div class="addr-option <?= $selectedClass ?>" id="addr-<?= $end['id_endereco'] ?>"
                     onclick="selecionarEndereco(this, '<?= htmlspecialchars($apelido) ?>', '<?= $linha1 ?>', '<?= $linha2 ?>', <?= $end['id_endereco'] ?>)">
                    <div class="addr-icon">🏠</div>
                    <div class="addr-info">
                        <p class="addr-apelido"><?= htmlspecialchars($apelido) ?></p>
                        <p class="addr-rua"><?= $linha1 ?></p>
                        <p class="addr-bairro"><?= $linha2 ?></p>
                    </div>
                    <i class="fa-solid fa-circle-check addr-check"></i>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="sheet-footer">
        <a href="perfil.php" class="btn btn-outline" style="width:100%; text-align:center;">
            <i class="fa-solid fa-plus"></i> Adicionar novo endereço
        </a>
    </div>
</div>

<?php include '../includes/user_footer.php'; ?>

<script src="assets/js/finalizar_pedido.js"></script>
