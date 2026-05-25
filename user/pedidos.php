<?php 
session_start();
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}
include '../includes/user_header.php'; 
?>
<link rel="stylesheet" href="assets/css/pedidos.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <p class="page-subtitle">Meus Pedidos</p>
    </div>
</header>
</div>

<div class="page-pad">

<?php
/*
 * ══════════════════════════════════════════════════════════════
 *  NOTA BACKEND — INTEGRAÇÃO PHP/MYSQL
 * ══════════════════════════════════════════════════════════════
 *
 *  O bloco abaixo é MOCKUP FRONT-END apenas.
 *  Quando o backend estiver pronto:
 *
 *  1. Buscar pedidos do usuário logado:
 *     SELECT p.id, p.status, p.total, p.criado_em,
 *            GROUP_CONCAT(prod.nome, ' x ', pi.quantidade SEPARATOR ', ') AS itens_resumo
 *     FROM pedidos p
 *     INNER JOIN pedido_itens pi ON pi.pedido_id = p.id
 *     INNER JOIN produtos prod ON prod.id = pi.produto_id
 *     WHERE p.usuario_id = $_SESSION['usuario_id']
 *     GROUP BY p.id
 *     ORDER BY p.criado_em DESC;
 *
 *  2. Status possíveis: 'aguardando', 'em_producao', 'saiu_entrega', 'entregue', 'cancelado'
 *
 *  3. Separar ativos dos finalizados:
 *     $ativos    = array_filter($pedidos, fn($p) => !in_array($p['status'], ['entregue','cancelado']));
 *     $historico = array_filter($pedidos, fn($p) =>  in_array($p['status'], ['entregue','cancelado']));
 *
 *  4. Substituir cada card de mockup por um foreach gerando o HTML dinamicamente.
 * ══════════════════════════════════════════════════════════════
 */
?>

    <!-- Estado vazio: NOTA BACKEND — exibir esta div apenas se $pedidos estiver vazio -->
    <div class="orders-empty hide" id="orders-empty">
        <i class="fa-solid fa-clipboard-list"></i>
        <p>Você ainda não fez nenhum pedido.</p>
        <a href="index.php" class="btn btn-primary btn-auto btn-auto-pad">
            Ver cardápio
        </a>
    </div>

    <!-- ══ EM ANDAMENTO ══ -->
    <!-- NOTA BACKEND: exibir esta secao e seu label apenas se $ativos nao estiver vazio -->
    <p class="orders-section-label">Em Andamento</p>

    <!-- NOTA BACKEND: foreach ($ativos as $pedido) — gerar um .order-card para cada pedido ativo -->
    <!-- MODELO PARA FOREACH PHP (ATIVOS) -->
    <div class="order-card order-card--active" data-pedido-id="[id_pedido]">
        <div class="order-card-header">
            <div class="order-card-info">
                <span class="order-number">Pedido #[id_pedido]</span>
                <span class="order-date">[data_e_hora_formatada]</span>
            </div>
            <!-- NOTA BACKEND: classe CSS do badge = $pedido['status'] (ex: 'em-producao', 'aguardando') -->
            <span class="order-status [classe_status]">[status_legivel]</span>
        </div>

        <ul class="order-items-preview">
            <!-- MODELO FOREACH ITENS DO PEDIDO -->
            <li><i class="fa-solid fa-circle-dot"></i> [quantidade]x [nome_do_produto]</li>
        </ul>

        <div class="order-card-footer">
            <span class="order-total">R$ [total_do_pedido]</span>
            <a href="detalhes_pedido.php?id=[id_pedido]" class="btn-detalhes">
                Ver detalhes <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
    <!-- FIM MODELO PHP -->

    <!-- ══ HISTÓRICO ══ -->
    <!-- NOTA BACKEND: exibir esta secao e seu label apenas se $historico nao estiver vazio -->
    <p class="orders-section-label mt-24">Histórico</p>

    <!-- NOTA BACKEND: foreach ($historico as $pedido) — gerar um .order-card para cada pedido finalizado/cancelado -->
    <!-- MODELO PARA FOREACH PHP (HISTORICO) -->
    <div class="order-card [classe_extra_se_cancelado]" data-pedido-id="[id_pedido]">
        <div class="order-card-header">
            <div class="order-card-info">
                <span class="order-number">Pedido #[id_pedido]</span>
                <span class="order-date">[data_e_hora_formatada]</span>
            </div>
            <span class="order-status [classe_status]">[status_legivel]</span>
        </div>

        <ul class="order-items-preview">
            <!-- MODELO FOREACH ITENS -->
            <li><i class="fa-solid fa-circle-dot"></i> [quantidade]x [nome_do_produto]</li>
        </ul>

        <!-- Exibir apenas se cancelado e com motivo -->
        <!--
        <p class="order-cancel-reason">
            <i class="fa-solid fa-circle-info"></i> [motivo_cancelamento]
        </p>
        -->

        <div class="order-card-footer">
            <span class="order-total [classe_riscado_se_cancelado]">R$ [total_do_pedido]</span>
            <div class="order-card-actions">
                <a href="detalhes_pedido.php?id=[id_pedido]" class="btn-detalhes">
                    Detalhes <i class="fa-solid fa-chevron-right"></i>
                </a>
                <!-- Exibir botao repetir apenas se entregue -->
                <button class="btn-repetir btn-repetir-pedido" data-pedido-id="[id_pedido]">
                    <i class="fa-solid fa-rotate-right"></i> Repetir
                </button>
            </div>
        </div>
    </div>
    <!-- FIM MODELO PHP -->

</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/pedidos.js"></script>
