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
    <!-- Campos usados: $pedido['id'], $pedido['status'], $pedido['total'], $pedido['criado_em'], $pedido['itens_resumo'] -->

    <div class="order-card order-card--active" data-pedido-id="1024">
        <div class="order-card-header">
            <div class="order-card-info">
                <!-- NOTA BACKEND: exibir 'Pedido #' . $pedido['id'] -->
                <span class="order-number">Pedido #1024</span>
                <!-- NOTA BACKEND: exibir date('d M Y · H:i', strtotime($pedido['criado_em'])) -->
                <span class="order-date">Hoje às 19h30</span>
            </div>
            <!-- NOTA BACKEND: classe CSS do badge = $pedido['status'] (ex: 'em-producao', 'aguardando') -->
            <span class="order-status em-producao">Em Produção</span>
        </div>

        <!-- NOTA BACKEND: itens_resumo vem do GROUP_CONCAT da query acima -->
        <!-- Cada <li> = um item. Gerar um li por produto com quantidade x nome -->
        <ul class="order-items-preview">
            <li><i class="fa-solid fa-circle-dot"></i> 1x Bolo de Chocolate no Pote</li>
            <li><i class="fa-solid fa-circle-dot"></i> 1x Brigadeiro Gourmet Kit</li>
        </ul>

        <div class="order-card-footer">
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['total'], 2, ',', '.') -->
            <span class="order-total">R$ 31,00</span>
            <!-- NOTA BACKEND: href="detalhes_pedido.php?id=" . $pedido['id'] -->
            <a href="detalhes_pedido.php?id=1024" class="btn-detalhes">
                Ver detalhes <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
    <!-- NOTA BACKEND: fim do foreach ($ativos as $pedido) -->

    <!-- ══ HISTÓRICO ══ -->
    <!-- NOTA BACKEND: exibir esta secao e seu label apenas se $historico nao estiver vazio -->
    <p class="orders-section-label mt-24">Histórico</p>

    <!-- NOTA BACKEND: foreach ($historico as $pedido) — gerar um .order-card para cada pedido finalizado/cancelado -->

    <div class="order-card" data-pedido-id="1023">
        <div class="order-card-header">
            <div class="order-card-info">
                <span class="order-number">Pedido #1023</span>
                <span class="order-date">30 Abr · 14h22</span>
            </div>
            <span class="order-status entregue">Finalizado</span>
        </div>

        <ul class="order-items-preview">
            <li><i class="fa-solid fa-circle-dot"></i> 2x Torta de Limão no Pote</li>
            <li><i class="fa-solid fa-circle-dot"></i> 1x Refrigerante Lata</li>
        </ul>

        <div class="order-card-footer">
            <span class="order-total">R$ 25,00</span>
            <div class="order-card-actions">
                <!-- NOTA BACKEND: href="detalhes_pedido.php?id=" . $pedido['id'] -->
                <a href="detalhes_pedido.php?id=1023" class="btn-detalhes">
                    Detalhes <i class="fa-solid fa-chevron-right"></i>
                </a>
                <!-- NOTA BACKEND: onclick passa o id real: repetirPedido($pedido['id']) -->
                <!-- Ao clicar: JS busca itens via fetch, repopula localStorage e redireciona ao carrinho -->
                <button class="btn-repetir btn-repetir-pedido" data-pedido-id="1023">
                    <i class="fa-solid fa-rotate-right"></i> Repetir
                </button>
            </div>
        </div>
    </div>

    <div class="order-card order-card--cancelado" data-pedido-id="1020">
        <div class="order-card-header">
            <div class="order-card-info">
                <span class="order-number">Pedido #1020</span>
                <span class="order-date">22 Abr · 11h05</span>
            </div>
            <span class="order-status cancelado">Cancelado</span>
        </div>

        <ul class="order-items-preview">
            <li><i class="fa-solid fa-circle-dot"></i> 3x Pastel Frito</li>
        </ul>

        <!-- NOTA BACKEND: exibir motivo do cancelamento se $pedido['motivo_cancelamento'] nao for vazio -->
        <p class="order-cancel-reason">
            <i class="fa-solid fa-circle-info"></i> Produto fora de estoque no momento
        </p>

        <div class="order-card-footer">
            <span class="order-total total-riscado">R$ 36,00</span>
            <a href="detalhes_pedido.php?id=1020" class="btn-detalhes">
                Detalhes <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
    <!-- NOTA BACKEND: fim do foreach ($historico as $pedido) -->

</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/pedidos.js"></script>
