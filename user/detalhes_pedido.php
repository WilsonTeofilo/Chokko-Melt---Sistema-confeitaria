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
        <li class="det-tl-step [classe_se_realizado]">
            <span class="det-tl-dot"><i class="fa-solid fa-check"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido realizado</span>
                <span class="det-tl-time">[hora_realizado_ou_traco]</span>
            </div>
        </li>
        <li class="det-tl-step [classe_se_producao]">
            <span class="det-tl-dot"><i class="fa-solid fa-fire-burner"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido em produção</span>
                <span class="det-tl-time">[hora_producao_ou_traco]</span>
            </div>
        </li>
        <li class="det-tl-step [classe_se_saiu_entrega]">
            <span class="det-tl-dot"><i class="fa-solid fa-motorcycle"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Saiu para entrega</span>
                <span class="det-tl-time">[hora_saiu_ou_traco]</span>
            </div>
        </li>
        <li class="det-tl-step [classe_se_entregue]">
            <span class="det-tl-dot"><i class="fa-solid fa-flag-checkered"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido finalizado</span>
                <span class="det-tl-time">[hora_entregue_ou_traco]</span>
            </div>
        </li>
    </ul>

    <div class="divider"></div>

    <!-- ══ FORMA DE ENTREGA ══ -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de entrega</div>
        <div class="det-info-row">
            <i class="[icone_forma_entrega]"></i>
            <div>
                <p class="det-info-title">[titulo_forma_entrega]</p>
                <!-- Exibir endereco ou retirar no local -->
                <p class="det-info-sub">[descricao_endereco_ou_local]</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ FORMA DE PAGAMENTO ══ -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de pagamento</div>
        <div class="det-info-row">
            <i class="[icone_forma_pagamento]"></i>
            <div>
                <p class="det-info-title">[titulo_forma_pagamento]</p>
                <p class="det-info-sub">[descricao_forma_pagamento]</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ ITENS DO PEDIDO ══ -->
    <div class="det-section-label">Itens do pedido</div>

    <div class="det-items-list">
        <!-- MODELO FOREACH ITENS -->
        <div class="det-item">
            <img class="det-item-img"
                 src="[url_imagem_produto]"
                 alt="[nome_produto]">
            <div class="det-item-info">
                <p class="det-item-name">[quantidade]x [nome_produto]</p>
                <!-- Exibir apenas se tiver adicionais -->
                <p class="det-item-addons">+ [lista_de_adicionais_formatada]</p>
                <p class="det-item-price">R$ [preco_unitario_vezes_qtd]</p>
            </div>
        </div>

        <!-- Exibir .det-obs-box apenas se item tiver observação -->
        <!--
        <div class="det-obs-box">
            <i class="fa-solid fa-note-sticky"></i>
            <span>[observacao_do_item]</span>
        </div>
        -->
        <!-- FIM MODELO FOREACH ITENS -->
    </div>

    <div class="divider"></div>

    <!-- ══ RESUMO FINANCEIRO ══ -->
    <div class="det-summary">
        <div class="det-summary-row">
            <span>Subtotal</span>
            <span>R$ [subtotal]</span>
        </div>
        <div class="det-summary-row">
            <span>Taxa de entrega</span>
            <span>R$ [taxa_entrega_ou_gratis]</span>
        </div>
        
        <!-- Exibir somente se houver desconto -->
        <!--
        <div class="det-summary-row det-summary-desconto">
            <span>Desconto</span>
            <span>— R$ [desconto]</span>
        </div>
        -->

        <div class="det-summary-row det-summary-total">
            <span>Total</span>
            <span>R$ [total_final]</span>
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
