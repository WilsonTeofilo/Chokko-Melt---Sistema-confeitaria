<?php 
session_start();
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}
include '../includes/user_header.php'; 
?>
<link rel="stylesheet" href="assets/css/pedidos.css">

<?php
/*
 * ══════════════════════════════════════════════════════════════
 *  NOTA BACKEND — INTEGRAÇÃO PHP/MYSQL
 * ══════════════════════════════════════════════════════════════
 *
 *  1. Receber ID via GET e buscar pedido:
 *     $pedido_id = intval($_GET['id'] ?? 0);
 *
 *     SELECT p.id, p.status, p.total, p.subtotal, p.taxa_entrega,
 *            p.desconto, p.forma_entrega, p.forma_pagamento,
 *            p.criado_em, p.producao_em, p.saiu_em, p.entregue_em,
 *            e.logradouro, e.numero, e.bairro, e.complemento
 *     FROM pedidos p
 *     LEFT JOIN enderecos e ON e.id = p.endereco_id
 *     WHERE p.id = $pedido_id
 *     AND p.usuario_id = $_SESSION['usuario_id'];
 *
 *  2. Buscar itens do pedido:
 *     SELECT pi.quantidade, pi.preco_unitario, pi.obs,
 *            prod.nome, prod.imagem,
 *            GROUP_CONCAT(pa.nome SEPARATOR ', ') AS adicionais
 *     FROM pedido_itens pi
 *     INNER JOIN produtos prod ON prod.id = pi.produto_id
 *     LEFT JOIN pedido_item_adicionais pia ON pia.item_id = pi.id
 *     LEFT JOIN adicionais pa ON pa.id = pia.adicional_id
 *     WHERE pi.pedido_id = $pedido_id
 *     GROUP BY pi.id;
 *
 *  3. Status e seus labels para exibicao:
 *     'aguardando'   => badge class 'aguardando'
 *     'em_producao'  => badge class 'em-producao'
 *     'saiu_entrega' => badge class 'saiu-entrega'
 *     'entregue'     => badge class 'entregue'
 *     'cancelado'    => badge class 'cancelado'
 *
 *  4. Timeline: cada etapa recebe class 'done' se o timestamp correspondente
 *     no banco NAO for NULL.
 *     realizado_em   = p.criado_em      (sempre existe)
 *     producao_em    = p.producao_em
 *     saiu_em        = p.saiu_em
 *     entregue_em    = p.entregue_em
 * ══════════════════════════════════════════════════════════════
 */

// MOCKUP: remover quando o backend estiver conectado
$pedido_id     = $_GET['id'] ?? '1024';
$status_atual  = 'em_producao';
$is_finalizado = in_array($status_atual, ['entregue', 'cancelado']);
?>

<div class="page-header-wrap">
<header class="page-header">
    <a href="pedidos.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <!-- NOTA BACKEND: exibir 'Pedido #' . $pedido_id -->
        <p class="page-subtitle">Pedido #<?= htmlspecialchars($pedido_id) ?></p>
    </div>
</header>
</div>

<div class="page-pad">
<div class="det-panel">

    <!-- ══ CABEÇALHO DO PEDIDO ══ -->
    <div class="det-order-head">
        <div>
            <!-- NOTA BACKEND: 'Pedido #' . $pedido['id'] -->
            <h2 class="det-order-title">Pedido #<?= htmlspecialchars($pedido_id) ?></h2>
            <!-- NOTA BACKEND: date('d/m/Y \a\s H:i', strtotime($pedido['criado_em'])) -->
            <p class="det-order-date">Em 29/04/2026 às 19:30</p>
        </div>
        <!-- NOTA BACKEND: exibir o botao Repetir somente se $pedido['status'] == 'entregue' -->
        <?php if ($is_finalizado): ?>
        <button class="btn-repetir btn-repetir-pedido" data-pedido-id="<?= intval($pedido_id) ?>">
            <i class="fa-solid fa-rotate-right"></i> Repetir pedido
        </button>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- ══ STATUS / TIMELINE VERTICAL ══ -->
    <div class="det-section-label">Status</div>
    <!-- NOTA BACKEND: classe do badge = $pedido['status'] com tracos (ex: 'em-producao') -->
    <span class="order-status em-producao det-status-badge">Em Produção</span>

    <!--
        NOTA BACKEND: para cada etapa da timeline, adicionar a classe 'done' no li
        se o timestamp correspondente nao for NULL no banco:
          - Etapa 1 (realizado): sempre done
          - Etapa 2 (em producao): done se $pedido['producao_em'] != null
          - Etapa 3 (saiu entrega): done se $pedido['saiu_em'] != null
          - Etapa 4 (finalizado):   done se $pedido['entregue_em'] != null
        Horario da etapa: date('H:i', strtotime($pedido['producao_em'])) etc.
    -->
    <ul class="det-timeline">
        <li class="det-tl-step done">
            <span class="det-tl-dot"><i class="fa-solid fa-check"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido realizado</span>
                <span class="det-tl-time">19:30</span>
            </div>
        </li>
        <li class="det-tl-step done">
            <span class="det-tl-dot"><i class="fa-solid fa-fire-burner"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido em produção</span>
                <span class="det-tl-time">19:32</span>
            </div>
        </li>
        <li class="det-tl-step">
            <span class="det-tl-dot"><i class="fa-solid fa-motorcycle"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Saiu para entrega</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
        <li class="det-tl-step">
            <span class="det-tl-dot"><i class="fa-solid fa-flag-checkered"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido finalizado</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
    </ul>

    <div class="divider"></div>

    <!-- ══ FORMA DE ENTREGA ══ -->
    <!-- NOTA BACKEND: $pedido['forma_entrega'] = 'delivery' | 'retirada' | 'local' -->
    <!-- Icone e texto mudam conforme o valor do campo no banco -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de entrega</div>
        <div class="det-info-row">
            <i class="fa-solid fa-motorcycle"></i>
            <div>
                <p class="det-info-title">Entrega via delivery</p>
                <!-- NOTA BACKEND: exibir endereco se forma_entrega == 'delivery' -->
                <!-- $pedido['logradouro'] . ', ' . $pedido['numero'] . ' - ' . $pedido['bairro'] -->
                <p class="det-info-sub">Rua das Flores, 123 · Jardim Primavera, Grajaú</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ FORMA DE PAGAMENTO ══ -->
    <!-- NOTA BACKEND: $pedido['forma_pagamento'] = 'pix' | 'credito' | 'dinheiro' -->
    <!-- Icone muda: pix = fa-brands fa-pix (cor #32BCAD), credito = fa-regular fa-credit-card (azul), dinheiro = fa-solid fa-money-bill-wave (verde) -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de pagamento</div>
        <div class="det-info-row">
            <i class="fa-regular fa-credit-card icon-credit-card"></i>
            <div>
                <p class="det-info-title">Cartão de Crédito</p>
                <p class="det-info-sub">Pagamento na entrega</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ ITENS DO PEDIDO ══ -->
    <div class="det-section-label">Itens do pedido</div>

    <!--
        NOTA BACKEND: foreach ($itens as $item) — gerar um .det-item para cada item
        Campos: $item['quantidade'], $item['nome'], $item['imagem'],
                $item['preco_unitario'], $item['adicionais'], $item['obs']
    -->
    <div class="det-items-list">

        <div class="det-item">
            <!-- NOTA BACKEND: src = $item['imagem'] -->
            <img class="det-item-img"
                 src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160"
                 alt="Bolo de Chocolate">
            <div class="det-item-info">
                <!-- NOTA BACKEND: $item['quantidade'] . 'x ' . $item['nome'] -->
                <p class="det-item-name">1x Bolo de Chocolate no Pote</p>
                <!-- NOTA BACKEND: exibir apenas se $item['adicionais'] nao for vazio -->
                <p class="det-item-addons">+ Cobertura extra de brigadeiro</p>
                <!-- NOTA BACKEND: number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') -->
                <p class="det-item-price">R$ 15,00</p>
            </div>
        </div>

        <div class="det-item">
            <img class="det-item-img"
                 src="https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=160"
                 alt="Brigadeiro Gourmet">
            <div class="det-item-info">
                <p class="det-item-name">1x Brigadeiro Gourmet Kit</p>
                <!-- NOTA BACKEND: NAO exibir o p.det-item-addons se $item['adicionais'] for vazio -->
                <p class="det-item-price">R$ 13,00</p>
            </div>
        </div>

        <!-- NOTA BACKEND: exibir .det-obs-box apenas se $item['obs'] nao for vazio -->
        <div class="det-obs-box">
            <i class="fa-solid fa-note-sticky"></i>
            <!-- NOTA BACKEND: htmlspecialchars($item['obs']) -->
            <span>Sem cobertura extra no brigadeiro</span>
        </div>

    </div>
    <!-- NOTA BACKEND: fim do foreach ($itens as $item) -->

    <div class="divider"></div>

    <!-- ══ RESUMO FINANCEIRO ══ -->
    <!-- NOTA BACKEND: todos os valores vem da tabela pedidos -->
    <div class="det-summary">
        <div class="det-summary-row">
            <span>Subtotal</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['subtotal'], 2, ',', '.') -->
            <span>R$ 28,00</span>
        </div>
        <div class="det-summary-row">
            <span>Taxa de entrega</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['taxa_entrega'], 2, ',', '.') -->
            <!-- Se forma_entrega != 'delivery', exibir 'Gratis' e taxa = 0 -->
            <span>R$ 5,00</span>
        </div>
        <!-- NOTA BACKEND: exibir esta linha somente se $pedido['desconto'] > 0 -->
        <div class="det-summary-row det-summary-desconto">
            <span>Desconto</span>
            <!-- NOTA BACKEND: '- R$ ' . number_format($pedido['desconto'], 2, ',', '.') -->
            <span>— R$ 2,00</span>
        </div>
        <div class="det-summary-row det-summary-total">
            <span>Total</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['total'], 2, ',', '.') -->
            <span>R$ 31,00</span>
        </div>
    </div>

    <!-- ══ AÇÕES ══ -->
    <div class="det-actions">
        <?php if (!$is_finalizado): ?>
        <!-- NOTA BACKEND: exibir somente enquanto o pedido estiver ativo (nao finalizado/cancelado) -->
        <button class="btn btn-outline" id="btn-contato" data-pedido-id="<?= intval($pedido_id) ?>">
            <i class="fa-solid fa-headset"></i> Falar com o Estabelecimento
        </button>
        <?php if ($status_atual === 'aguardando'): ?>
        <!-- NOTA BACKEND: O cliente SÓ PODE CANCELAR se o pedido ainda não foi aceito pela loja -->
        <button class="btn btn-outline btn-outline-danger btn-cancelar-pedido" data-pedido-id="<?= intval($pedido_id) ?>">
            <i class="fa-solid fa-ban"></i> Cancelar Pedido
        </button>
        <?php endif; ?>
        <?php else: ?>
        <button class="btn btn-primary btn-repetir-pedido" data-pedido-id="<?= intval($pedido_id) ?>">
            <i class="fa-solid fa-rotate-right"></i> Repetir este pedido
        </button>
        <?php endif; ?>
        <button class="btn btn-ghost" onclick="window.location.href='pedidos.php'">
            Voltar aos meus pedidos
        </button>
    </div>

</div><!-- /det-panel -->
</div><!-- /page-pad -->

<!-- MODAL CANCELAR PEDIDO (CLIENTE) -->
<div id="modal-cancelar-cliente" class="modal-overlay">
    <div class="modal-content-box">
        <div class="modal-header-box">
            <h2 class="modal-title">Cancelar Pedido</h2>
            <button class="btn-close-modal">&times;</button>
        </div>
        <div class="modal-body-box">
            <p>Por qual motivo você deseja cancelar seu pedido?</p>
            <select id="motivo-cancelamento-cliente" class="modal-select">
                <option value="" disabled selected>Selecione um motivo...</option>
                <option value="Demorou muito para ser aceito">Demorou muito para ser aceito</option>
                <option value="Fiz o pedido errado">Fiz o pedido errado</option>
                <option value="Desisti da compra">Desisti da compra</option>
                <option value="Endereço incorreto">Endereço incorreto</option>
                <option value="Outro motivo">Outro motivo</option>
            </select>
        </div>
        <div class="modal-footer-box">
            <button type="button" class="btn btn-danger" id="btn-confirmar-cancelamento" data-pedido-id="<?= intval($pedido_id) ?>">Confirmar Cancelamento</button>
            <button type="button" class="btn btn-cancel btn-close-modal">Voltar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCESSO (CLIENTE) -->
<div id="modal-sucesso-cliente" class="modal-overlay z-high">
    <div class="modal-content-box success-box">
        <i class="fa-solid fa-circle-check success-icon"></i>
        <h2 class="success-title">Pedido Cancelado</h2>
        <p id="modal-sucesso-cliente-msg" class="success-msg"></p>
        <button id="btn-reload-page" class="btn-success-back">Voltar aos Meus Pedidos</button>
    </div>
</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/detalhes_pedido.js"></script>
