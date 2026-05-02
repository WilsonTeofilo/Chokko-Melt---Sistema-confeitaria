<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/pedidos.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <p class="logo-mini">Chokko<span> Melt</span></p>
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
    <div class="orders-empty" id="orders-empty" style="display:none;">
        <i class="fa-solid fa-clipboard-list"></i>
        <p>Você ainda não fez nenhum pedido.</p>
        <a href="index.php" class="btn btn-primary btn-auto" style="padding:10px 24px;margin-top:16px;">
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
    <p class="orders-section-label" style="margin-top:24px;">Histórico</p>

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
                <button class="btn-repetir" onclick="repetirPedido(1023)">
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
            <span class="order-total" style="color:var(--cinza-medio);text-decoration:line-through;">R$ 36,00</span>
            <a href="detalhes_pedido.php?id=1020" class="btn-detalhes">
                Detalhes <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
    <!-- NOTA BACKEND: fim do foreach ($historico as $pedido) -->

</div>

<?php include '../includes/user_footer.php'; ?>
<script>
/*
 * ═══════════════════════════════════════════════════════════════════════════
 * pedidos.php (cliente) — JS mínimo; lista deve vir do PHP (foreach pedidos).
 * Guia: INTEGRACAO_JS_PHP.txt
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * NOTA BACKEND — funcao repetirPedido()
 *
 * Quando o backend estiver pronto, criar o arquivo:
 *   /user/api/pedido_itens.php
 *
 * Ele deve receber GET ?id={pedidoId} e retornar JSON:
 *   [{ "id":1, "nome":"Bolo...", "img":"url", "preco":13.00, "qty":1 }, ...]
 *
 * O JS abaixo faz um fetch() nesse endpoint, monta o objeto do carrinho
 * e salva no localStorage antes de redirecionar para carrinho.php
 *
 * Query SQL sugerida para o endpoint:
 *   SELECT pi.quantidade AS qty, pi.preco_unitario AS preco,
 *          prod.id, prod.nome, prod.imagem AS img
 *   FROM pedido_itens pi
 *   INNER JOIN produtos prod ON prod.id = pi.produto_id
 *   WHERE pi.pedido_id = $_GET['id']
 *   AND EXISTS (SELECT 1 FROM pedidos p WHERE p.id = pi.pedido_id AND p.usuario_id = $_SESSION['usuario_id'])
 */
function repetirPedido(pedidoId) {
    /* NOTA BACKEND: quando o endpoint estiver pronto, descomente o fetch abaixo e remova o alert():

    fetch('api/pedido_itens.php?id=' + pedidoId)
        .then(function(r) { return r.json(); })
        .then(function(itens) {
            var cart = itens.map(function(i) {
                return {
                    key: i.id + '||',
                    id: i.id,
                    name: i.nome,
                    img: i.img,
                    basePrice: i.preco,
                    addons: [],
                    unitPrice: i.preco,
                    qty: i.qty,
                    obs: ''
                };
            });
            localStorage.setItem('chokko_cart', JSON.stringify(cart));
            window.location.href = 'carrinho.php';
        })
        .catch(function() {
            alert('Erro ao buscar itens do pedido. Tente novamente.');
        });
    */

    alert('Repetir pedido estará disponível quando o backend estiver conectado.');
}
</script>
