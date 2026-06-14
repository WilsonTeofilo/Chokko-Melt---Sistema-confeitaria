<?php 
session_start();
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/config.php';

$id_cliente = intval($_SESSION['idlogado']);
$pedido_id  = intval($_GET['id'] ?? 0);

// Valida: ID do pedido precisa existir na URL
if ($pedido_id <= 0) {
    header("Location: pedidos.php");
    exit;
}

// Busca o pedido garantindo que pertence ao cliente logado
try {
    $sqlPedido = "
        SELECT p.id_pedido, p.data_hora, p.valor_total, p.subtotal, p.taxa_entrega,
               p.observacao, p.tipo_entrega, p.cpf_nota,
               p.motivo_cancelamento, p.cancelado_por,
               sp.descricao AS status_descricao,
               e.rua, e.numero, e.complemento, e.bairro, e.cep,
               pg.forma_pagamento, pg.valor_entregue, pg.troco
        FROM pedido p
        INNER JOIN status_pedido sp ON p.id_status_pedido = sp.id_status_pedido
        LEFT JOIN endereco e ON p.id_endereco = e.id_endereco
        LEFT JOIN pagamento pg ON pg.id_pedido = p.id_pedido
        WHERE p.id_pedido = :id_pedido AND p.id_cliente = :id_cliente
        LIMIT 1
    ";
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->execute(['id_pedido' => $pedido_id, 'id_cliente' => $id_cliente]);
    $pedido = $stmtPedido->fetch();

    // Se pedido não existe ou não pertence a esse cliente, manda para a lista
    if (!$pedido) {
        header("Location: pedidos.php");
        exit;
    }

    // Busca os itens do pedido
    $sqlItens = "
        SELECT ip.id_item_pedido, ip.quantidade, ip.preco_unitario, ip.observacao,
               COALESCE(p.nome, 'Produto Indisponível') AS nome,
               COALESCE(p.imagem, '') AS imagem
        FROM item_pedido ip
        LEFT JOIN produto p ON ip.id_produto = p.id_produto
        WHERE ip.id_pedido = :id_pedido
    ";
    $stmtItens = $conn->prepare($sqlItens);
    $stmtItens->execute(['id_pedido' => $pedido_id]);
    $itens = $stmtItens->fetchAll();

} catch (Exception $e) {
    header("Location: pedidos.php");
    exit;
}

$status = $pedido['status_descricao'];
$is_finalizado = in_array($status, ['ENTREGUE', 'CANCELADO_CLIENTE', 'CANCELADO_LOJA']);
$pode_cancelar = ($status === 'PENDENTE');

// Monta texto e classe do badge de status
function statusInfo($status, $tipo_entrega = 'DELIVERY') {
    switch ($status) {
        case 'PENDENTE':    return ['texto' => 'Aguardando',         'classe' => 'aguardando'];
        case 'ACEITO':      return ['texto' => 'Aceito pela Loja',   'classe' => 'em-preparo'];
        case 'EM_PREPARO':  return ['texto' => 'Em Produção',        'classe' => 'em-preparo'];
        case 'ENVIADO':
            if ($tipo_entrega === 'RETIRADA') {
                return ['texto' => 'Pronto para Retirada', 'classe' => 'saiu-entrega'];
            } elseif ($tipo_entrega === 'LOCAL') {
                return ['texto' => 'Pronto', 'classe' => 'saiu-entrega'];
            } else {
                return ['texto' => 'Saiu para Entrega',  'classe' => 'saiu-entrega'];
            }
        case 'ENTREGUE':
            if ($tipo_entrega === 'LOCAL') {
                return ['texto' => 'Consumido / Servido', 'classe' => 'entregue'];
            } else {
                return ['texto' => 'Entregue',           'classe' => 'entregue'];
            }
        default:            return ['texto' => 'Cancelado',          'classe' => 'cancelado'];
    }
}

// Ícone e título da forma de entrega
function entregaInfo($tipo) {
    switch ($tipo) {
        case 'RETIRADA': return ['icone' => 'fa-solid fa-bag-shopping', 'titulo' => 'Retirar no Balcão',   'desc' => 'Sem taxa de entrega'];
        case 'LOCAL':    return ['icone' => 'fa-solid fa-store',        'titulo' => 'Consumir no Local',   'desc' => 'Sem taxa de entrega'];
        default:         return ['icone' => 'fa-solid fa-motorcycle',   'titulo' => 'Delivery',             'desc' => ''];
    }
}

// Ícone e título da forma de pagamento
function pagamentoInfo($forma) {
    switch ($forma) {
        case 'PIX':      return ['icone' => 'fa-brands fa-pix',              'titulo' => 'Pix',                    'desc' => 'Pagamento via Pix'];
        case 'DINHEIRO': return ['icone' => 'fa-solid fa-money-bill-wave',   'titulo' => 'Dinheiro',               'desc' => 'Pagamento em dinheiro na entrega'];
        default:         return ['icone' => 'fa-regular fa-credit-card',     'titulo' => 'Cartão de Crédito/Débito','desc' => 'Pagamento na entrega'];
    }
}

$si = statusInfo($status, $pedido['tipo_entrega']);
$ei = entregaInfo($pedido['tipo_entrega']);
$pi = pagamentoInfo($pedido['forma_pagamento'] ?? '');

include '../includes/user_header.php'; 
?>
<link rel="stylesheet" href="assets/css/pedidos.css?v=<?= time() ?>">

<div class="page-header-wrap">
<header class="page-header">
    <a href="pedidos.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <p class="page-subtitle">Pedido #<?= $pedido_id ?></p>
    </div>
</header>
</div>

<div class="page-pad">
<div class="det-panel">

    <!-- ══ CABEÇALHO DO PEDIDO ══ -->
    <div class="det-order-head">
        <div>
            <h2 class="det-order-title">Pedido #<?= $pedido_id ?></h2>
            <p class="det-order-date">Em <?= date('d/m/Y \à\s H:i', strtotime($pedido['data_hora'])) ?></p>
        </div>
        <?php if ($status === 'ENTREGUE'): ?>
            <form action="src/carrinho_acao.php" method="POST" style="display:inline;">
                <input type="hidden" name="acao" value="repetir_pedido">
                <input type="hidden" name="id_pedido" value="<?= $pedido_id ?>">
                <button type="submit" class="btn-repetir">
                    <i class="fa-solid fa-rotate-right"></i> Repetir pedido
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- ══ STATUS / BADGE ══ -->
    <div class="det-section-label">Status</div>
    <span class="order-status <?= $si['classe'] ?> det-status-badge"><?= $si['texto'] ?></span>

    <?php if (in_array($status, ['CANCELADO_CLIENTE', 'CANCELADO_LOJA'])): 
        $titulo_cancel = ($status === 'CANCELADO_LOJA') ? 'Pedido Cancelado pela Confeitaria' : 'Pedido Cancelado por Você';
        $motivo_cancel = !empty($pedido['motivo_cancelamento']) ? $pedido['motivo_cancelamento'] : (($status === 'CANCELADO_LOJA') ? 'Nenhum motivo informado pela loja.' : 'Cancelamento solicitado pelo cliente.');
    ?>
        <div class="det-cancel-banner">
            <i class="fa-solid fa-circle-xmark"></i>
            <div class="det-cancel-banner-content">
                <h4 class="det-cancel-banner-title"><?= htmlspecialchars($titulo_cancel) ?></h4>
                <p class="det-cancel-banner-reason"><strong>Motivo:</strong> <?= htmlspecialchars($motivo_cancel) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ TIMELINE ══ -->
    <ul class="det-timeline" style="margin-top: 16px;">
        <?php
        $statusOrdem = ['PENDENTE' => 1, 'ACEITO' => 2, 'EM_PREPARO' => 2, 'ENVIADO' => 3, 'ENTREGUE' => 4];
        $nivel = $statusOrdem[$status] ?? 0;
        ?>
        <li class="det-tl-step <?= $nivel >= 1 ? 'done' : '' ?>">
            <span class="det-tl-dot"><i class="fa-solid fa-check"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido realizado</span>
                <span class="det-tl-time"><?= date('H:i', strtotime($pedido['data_hora'])) ?></span>
            </div>
        </li>
        <li class="det-tl-step <?= $nivel >= 2 ? 'done' : '' ?>">
            <span class="det-tl-dot"><i class="fa-solid fa-fire-burner"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido em produção</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
        <?php if ($pedido['tipo_entrega'] === 'DELIVERY'): ?>
        <li class="det-tl-step <?= $nivel >= 3 ? 'done' : '' ?>">
            <span class="det-tl-dot"><i class="fa-solid fa-motorcycle"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Saiu para entrega</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
        <?php endif; ?>
        <li class="det-tl-step <?= $nivel >= 4 ? 'done' : '' ?>">
            <span class="det-tl-dot"><i class="fa-solid fa-flag-checkered"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido finalizado</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
    </ul>

    <div class="divider"></div>

    <!-- ══ FORMA DE ENTREGA ══ -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de entrega</div>
        <div class="det-info-row">
            <i class="<?= $ei['icone'] ?>"></i>
            <div>
                <p class="det-info-title"><?= $ei['titulo'] ?></p>
                <?php if ($pedido['tipo_entrega'] === 'DELIVERY' && !empty($pedido['rua'])): ?>
                    <p class="det-info-sub">
                        <?= htmlspecialchars($pedido['rua'] . ', ' . $pedido['numero']) ?><br>
                        <?= htmlspecialchars($pedido['bairro'] . ($pedido['cep'] ? ' · CEP ' . $pedido['cep'] : '')) ?>
                    </p>
                <?php else: ?>
                    <p class="det-info-sub"><?= $ei['desc'] ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ FORMA DE PAGAMENTO ══ -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de pagamento</div>
        <div class="det-info-row">
            <i class="<?= $pi['icone'] ?>"></i>
            <div>
                <p class="det-info-title"><?= $pi['titulo'] ?></p>
                <p class="det-info-sub">
                    <?= $pi['desc'] ?>
                    <?php if (!empty($pedido['troco']) && $pedido['troco'] > 0): ?>
                        · Troco: R$ <?= number_format($pedido['troco'], 2, ',', '.') ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ ITENS DO PEDIDO ══ -->
    <div class="det-section-label">Itens do pedido</div>

    <div class="det-items-list">
        <?php foreach ($itens as $item):
            $imgSrc = $item['imagem'];
            if (empty($imgSrc)) {
                $imgSrc = 'https://placehold.co/150x150/f5e6d0/8B4513?text=Foto';
            } elseif (strpos($imgSrc, 'uploads/') === 0) {
                $imgSrc = '../admin/' . $imgSrc;
            }
        ?>
            <?php
            // Busca adicionais deste item do pedido
            $stmtAd = $conn->prepare("
                SELECT nome_snapshot, preco_unitario_snapshot 
                FROM item_pedido_adicional 
                WHERE id_item_pedido = :id_item
            ");
            $stmtAd->execute(['id_item' => $item['id_item_pedido']]);
            $adicionais = $stmtAd->fetchAll();

            $precoTotalItem = $item['preco_unitario'];
            if (!empty($adicionais)) {
                foreach ($adicionais as $ad) {
                    $precoTotalItem += (float)$ad['preco_unitario_snapshot'];
                }
            }
            $precoTotalItem *= $item['quantidade'];
            ?>
            <div class="det-item">
                <img class="det-item-img" src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                <div class="det-item-info">
                    <p class="det-item-name"><?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?></p>
                    <?php if (!empty($adicionais)): ?>
                        <p style="font-size: 0.8rem; color: #757575; margin-top: 2px;">
                            <span style="font-weight: 600;">Adicionais:</span>
                            <?php 
                            $addonsList = [];
                            foreach ($adicionais as $ad) {
                                $addonsList[] = htmlspecialchars($ad['nome_snapshot']) . ' (+ R$ ' . number_format($ad['preco_unitario_snapshot'], 2, ',', '.') . ')';
                            }
                            echo implode(', ', $addonsList);
                            ?>
                        </p>
                    <?php endif; ?>
                    <p class="det-item-price" style="margin-top: 4px;">R$ <?= number_format($precoTotalItem, 2, ',', '.') ?></p>
                </div>
            </div>
            <?php if (!empty($item['observacao'])): ?>
                <div class="det-obs-box">
                    <i class="fa-solid fa-note-sticky"></i>
                    <span><?= htmlspecialchars($item['observacao']) ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($pedido['observacao'])): ?>
            <div class="det-obs-box" style="margin-top: 10px;">
                <i class="fa-solid fa-comment-dots"></i>
                <span>Obs. do pedido: <?= htmlspecialchars($pedido['observacao']) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- ══ RESUMO FINANCEIRO ══ -->
    <div class="det-summary">
        <div class="det-summary-row">
            <span>Subtotal</span>
            <span>R$ <?= number_format($pedido['subtotal'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div class="det-summary-row">
            <span>Taxa de entrega</span>
            <span><?= $pedido['taxa_entrega'] > 0 ? 'R$ ' . number_format($pedido['taxa_entrega'], 2, ',', '.') : 'Grátis' ?></span>
        </div>
        <div class="det-summary-row det-summary-total">
            <span>Total</span>
            <span>R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
        </div>
    </div>

    <!-- ══ AÇÕES ══ -->
    <div class="det-actions">
        <?php if (!$is_finalizado): ?>
            <?php
            $whatsapp_loja = '';
            try {
                $stmtW = $conn->prepare("SELECT whatsapp FROM config_loja WHERE id_config = 1 LIMIT 1");
                $stmtW->execute();
                $cfg = $stmtW->fetch();
                if ($cfg && !empty($cfg['whatsapp'])) {
                    // Limpa caracteres especiais do telefone
                    $whatsapp_loja = preg_replace('/[^0-9]/', '', $cfg['whatsapp']);
                }
            } catch (Exception $e) {}
            
            if (!empty($whatsapp_loja)):
                $texto_wa = urlencode("Olá! Gostaria de falar sobre o meu Pedido #" . $pedido_id);
            ?>
                <a href="https://wa.me/55<?= $whatsapp_loja ?>?text=<?= $texto_wa ?>" target="_blank" class="btn btn-outline" style="text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-headset"></i> Falar com o Estabelecimento
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline" onclick="alert('Nenhum número de WhatsApp está cadastrado pelo estabelecimento nas configurações da loja.')">
                    <i class="fa-solid fa-headset"></i> Falar com o Estabelecimento
                </button>
            <?php endif; ?>
            <?php if ($pode_cancelar): ?>
                <button class="btn btn-outline btn-outline-danger btn-cancelar-pedido" data-pedido-id="<?= $pedido_id ?>">
                    <i class="fa-solid fa-ban"></i> Cancelar Pedido
                </button>
            <?php endif; ?>
        <?php elseif ($status === 'ENTREGUE'): ?>
            <form action="src/carrinho_acao.php" method="POST" style="display:inline;">
                <input type="hidden" name="acao" value="repetir_pedido">
                <input type="hidden" name="id_pedido" value="<?= $pedido_id ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-rotate-right"></i> Repetir este pedido
                </button>
            </form>
        <?php endif; ?>
        <button class="btn btn-outline btn-imprimir" onclick="imprimirComprovante()">
            <i class="fa-solid fa-print"></i> Imprimir Comprovante
        </button>
        <button class="btn btn-ghost" onclick="window.location.href='pedidos.php'">
            Voltar aos meus pedidos
        </button>
    </div>

</div><!-- /det-panel -->
</div><!-- /page-pad -->

<!-- MODAL CANCELAR PEDIDO -->
<div id="modal-cancelar-cliente" class="modal-overlay">
    <div class="modal-content-box">
        <form action="src/pedido_acao.php" method="POST">
            <input type="hidden" name="acao" value="cancelar_pedido">
            <input type="hidden" name="id_pedido" value="<?= $pedido_id ?>">
            <div class="modal-header-box">
                <h2 class="modal-title">Cancelar Pedido</h2>
                <button type="button" class="btn-close-modal btn-close-modal-cancelar">&times;</button>
            </div>
            <div class="modal-body-box">
                <p>Por qual motivo você deseja cancelar seu pedido?</p>
                <select name="motivo" id="motivo-cancelamento-cliente" class="modal-select" required>
                    <option value="" disabled selected>Selecione um motivo...</option>
                    <option value="Demorou muito para ser aceito">Demorou muito para ser aceito</option>
                    <option value="Fiz o pedido errado">Fiz o pedido errado</option>
                    <option value="Desisti da compra">Desisti da compra</option>
                    <option value="Endereço incorreto">Endereço incorreto</option>
                    <option value="Outro motivo">Outro motivo</option>
                </select>
            </div>
            <div class="modal-footer-box">
                <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                <button type="button" class="btn btn-cancel btn-close-modal btn-close-modal-cancelar">Voltar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SUCESSO -->
<div id="modal-sucesso-cliente" class="modal-overlay z-high <?= isset($_GET['cancelado']) && $_GET['cancelado'] === '1' ? 'show' : '' ?>">
    <div class="modal-content-box success-box">
        <i class="fa-solid fa-circle-check success-icon"></i>
        <h2 class="success-title">Pedido Cancelado</h2>
        <p id="modal-sucesso-cliente-msg" class="success-msg">Seu pedido foi cancelado com sucesso no nosso sistema.</p>
        <button id="btn-reload-page" class="btn-success-back" onclick="window.location.href='pedidos.php'">Voltar aos Meus Pedidos</button>
    </div>
</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/detalhes_pedido.js"></script>

<!-- ══ COMPROVANTE DE IMPRESSÃO (oculto na tela, exibido no print) ══ -->
<div id="comprovante-cliente">
    <p class="cv-titulo">CHOKKO MELT</p>
    <p class="cv-sub">Comprovante de Pedido</p>
    <hr class="cv-hr">
    <p class="cv-linha"><b>Pedido:</b> #<?= $pedido_id ?></p>
    <p class="cv-linha"><b>Data:</b> <?= date('d/m/Y H:i', strtotime($pedido['data_hora'])) ?></p>
    <p class="cv-linha"><b>Status:</b> <?= htmlspecialchars($si['texto']) ?></p>
    <hr class="cv-hr">
    <p class="cv-linha"><b>Entrega:</b> <?= htmlspecialchars($ei['titulo']) ?></p>
    <?php if ($pedido['tipo_entrega'] === 'DELIVERY' && !empty($pedido['rua'])): ?>
    <p class="cv-linha"><?= htmlspecialchars($pedido['rua'] . ', ' . $pedido['numero']) ?>, <?= htmlspecialchars($pedido['bairro']) ?></p>
    <?php endif; ?>
    <p class="cv-linha"><b>Pagamento:</b> <?= htmlspecialchars($pedido['forma_pagamento'] ?? '—') ?></p>
    <?php if (!empty($pedido['troco']) && $pedido['troco'] > 0): ?>
    <p class="cv-linha">Troco: R$ <?= number_format($pedido['troco'], 2, ',', '.') ?></p>
    <?php endif; ?>
    <hr class="cv-hr">
    <table class="cv-tabela">
        <thead><tr><th class="cv-desc">Item</th><th class="cv-val">Valor</th></tr></thead>
        <tbody>
        <?php foreach ($itens as $cvItem):
            $stmtCvAd = $conn->prepare("SELECT nome_snapshot, preco_unitario_snapshot FROM item_pedido_adicional WHERE id_item_pedido = :id");
            $stmtCvAd->execute(['id' => $cvItem['id_item_pedido']]);
            $cvAdicionais = $stmtCvAd->fetchAll();
            $cvPrecoItem = $cvItem['preco_unitario'];
            foreach ($cvAdicionais as $ad) { $cvPrecoItem += (float)$ad['preco_unitario_snapshot']; }
            $cvPrecoItem *= $cvItem['quantidade'];
        ?>
        <tr>
            <td class="cv-desc">
                <?= $cvItem['quantidade'] ?>x <?= htmlspecialchars($cvItem['nome']) ?>
                <?php if (!empty($cvAdicionais)): ?>
                <br><small>+ <?php $nomes = []; foreach($cvAdicionais as $ad) { $nomes[] = htmlspecialchars($ad['nome_snapshot']); } echo implode(', ', $nomes); ?></small>
                <?php endif; ?>
                <?php if (!empty($cvItem['observacao'])): ?>
                <br><small><i>Obs: <?= htmlspecialchars($cvItem['observacao']) ?></i></small>
                <?php endif; ?>
            </td>
            <td class="cv-val">R$ <?= number_format($cvPrecoItem, 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <hr class="cv-hr">
    <?php if (!empty($pedido['observacao'])): ?>
    <p class="cv-linha"><i>Obs. pedido: <?= htmlspecialchars($pedido['observacao']) ?></i></p>
    <?php endif; ?>
    <div class="cv-total">
        <span>Subtotal</span>
        <span>R$ <?= number_format($pedido['subtotal'] ?? 0, 2, ',', '.') ?></span>
    </div>
    <?php if ($pedido['taxa_entrega'] > 0): ?>
    <div class="cv-total" style="font-weight:normal; font-size:10px;">
        <span>Taxa de entrega</span>
        <span>R$ <?= number_format($pedido['taxa_entrega'], 2, ',', '.') ?></span>
    </div>
    <?php endif; ?>
    <div class="cv-total cv-total-final">
        <span>TOTAL</span>
        <span>R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
    </div>
    <hr class="cv-hr">
    <p class="cv-centro">Obrigado pela preferência! — Chokko Melt</p>
</div>
