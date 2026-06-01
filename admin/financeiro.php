<?php
require_once '../config/config.php';
include '../includes/admin_header.php';

// ── Período selecionado (GET) ──────────────────────────────────
$periodo = filter_input(INPUT_GET, 'periodo', FILTER_DEFAULT);
$periodo = $periodo ? trim($periodo) : 'mes';

$data_inicio_raw = filter_input(INPUT_GET, 'data_inicio', FILTER_DEFAULT);
$data_fim_raw    = filter_input(INPUT_GET, 'data_fim',    FILTER_DEFAULT);

$hoje = date('Y-m-d');

if ($data_inicio_raw && $data_fim_raw) {
    $data_inicio = $data_inicio_raw;
    $data_fim    = $data_fim_raw;
    $periodo     = 'custom';
} elseif ($periodo === 'hoje') {
    $data_inicio = $hoje;
    $data_fim    = $hoje;
} elseif ($periodo === 'semana') {
    $data_inicio = date('Y-m-d', strtotime('monday this week'));
    $data_fim    = $hoje;
} else {
    // mes (padrão)
    $periodo     = 'mes';
    $data_inicio = date('Y-m-01');
    $data_fim    = $hoje;
}

// ── Query de totais (apenas pedidos ENTREGUE, id_status = 5) ──
try {
    $sqlTotais = "
        SELECT
            COALESCE(SUM(p.valor_total), 0) AS faturamento_bruto,
            COALESCE(SUM(p.lucro), 0) AS lucro_total,
            COALESCE(SUM(CASE WHEN pg.forma_pagamento = 'PIX'      THEN p.valor_total ELSE 0 END), 0) AS total_pix,
            COALESCE(SUM(CASE WHEN pg.forma_pagamento IN ('CREDITO', 'DEBITO') THEN p.valor_total ELSE 0 END), 0) AS total_cartao,
            COALESCE(SUM(CASE WHEN pg.forma_pagamento = 'DINHEIRO' THEN p.valor_total ELSE 0 END), 0) AS total_dinheiro,
            COUNT(*) AS total_pedidos
        FROM pedido p
        LEFT JOIN pagamento pg ON pg.id_pedido = p.id_pedido
        WHERE p.id_status_pedido = 5
          AND DATE(p.data_hora) BETWEEN :inicio AND :fim
    ";
    $stmtTotais = $conn->prepare($sqlTotais);
    $stmtTotais->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $totais = ['faturamento_bruto' => 0, 'lucro_total' => 0, 'total_pix' => 0, 'total_cartao' => 0, 'total_dinheiro' => 0, 'total_pedidos' => 0];
}

// ── Query de detalhamento (lista de pedidos entregues) ─────────
try {
    $sqlLista = "
        SELECT p.id_pedido, p.data_hora, p.valor_total, p.lucro,
               c.nome AS nome_cliente,
               pg.forma_pagamento
        FROM pedido p
        INNER JOIN cliente c ON c.id_cliente = p.id_cliente
        LEFT JOIN pagamento pg ON pg.id_pedido = p.id_pedido
        WHERE p.id_status_pedido = 5
          AND DATE(p.data_hora) BETWEEN :inicio AND :fim
        ORDER BY p.data_hora DESC
    ";
    $stmtLista = $conn->prepare($sqlLista);
    $stmtLista->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $lista = $stmtLista->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $lista = [];
}

// ── Ícones de pagamento ────────────────────────────────────────
function iconePagamento($forma) {
    switch ($forma) {
        case 'PIX':      return 'fa-brands fa-pix';
        case 'DINHEIRO': return 'fa-solid fa-money-bill-1-wave';
        default:         return 'fa-regular fa-credit-card';
    }
}
?>
<link rel="stylesheet" href="assets/css/financeiro.css">

<section class="welcome-area">
    <h1>Financeiro da Loja</h1>
    <p>Faturamento bruto dos pedidos entregues. Cancelamentos não são contabilizados.</p>
</section>

<!-- Filtros -->
<section class="filters-container">
    <div class="quick-filters">
        <button class="btn-filter <?= $periodo === 'hoje'  ? 'active' : '' ?>" onclick="filtrarPeriodo('hoje',  this)">Hoje</button>
        <button class="btn-filter <?= $periodo === 'semana'? 'active' : '' ?>" onclick="filtrarPeriodo('semana',this)">Semana</button>
        <button class="btn-filter <?= $periodo === 'mes'   ? 'active' : '' ?>" onclick="filtrarPeriodo('mes',   this)">Mês</button>
    </div>

    <div class="date-picker-box">
        <input type="date" id="data-inicio" class="input-date" value="<?= htmlspecialchars($data_inicio) ?>">
        <span>até</span>
        <input type="date" id="data-fim"    class="input-date" value="<?= htmlspecialchars($data_fim) ?>">
        <button class="btn-filter-icon" onclick="filtrarPorData()">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
</section>

<!-- Cards de métricas -->
<section class="metrics-container">
    <div class="m-card blue">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <div class="m-info">
            <strong>R$ <?= number_format($totais['faturamento_bruto'], 2, ',', '.') ?></strong>
            <span>Faturamento Bruto</span>
        </div>
    </div>
    <div class="m-card lucro-card">
        <i class="fa-solid fa-chart-line"></i>
        <div class="m-info">
            <strong>R$ <?= number_format($totais['lucro_total'], 2, ',', '.') ?></strong>
            <span>Lucro Líquido</span>
        </div>
    </div>
    <div class="m-card green">
        <i class="fa-brands fa-pix"></i>
        <div class="m-info">
            <strong>R$ <?= number_format($totais['total_pix'], 2, ',', '.') ?></strong>
            <span>Pix (Bruto)</span>
        </div>
    </div>
    <div class="m-card orange">
        <i class="fa-regular fa-credit-card"></i>
        <div class="m-info">
            <strong>R$ <?= number_format($totais['total_cartao'], 2, ',', '.') ?></strong>
            <span>Cartão (Bruto)</span>
        </div>
    </div>
    <div class="m-card brown">
        <i class="fa-solid fa-money-bill-1-wave"></i>
        <div class="m-info">
            <strong>R$ <?= number_format($totais['total_dinheiro'], 2, ',', '.') ?></strong>
            <span>Dinheiro (Bruto)</span>
        </div>
    </div>
    <div class="m-card fin-pedidos-card">
        <i class="fa-solid fa-bag-shopping"></i>
        <div class="m-info">
            <strong><?= intval($totais['total_pedidos']) ?></strong>
            <span>Pedidos Entregues</span>
        </div>
    </div>
</section>

<!-- Tabela de detalhamento -->
<section class="table-wrapper">
    <h2>Detalhamento de Entradas
        <small style="font-size: .8rem; font-weight: 400; color: #888; margin-left: 8px;">
            <?= date('d/m/Y', strtotime($data_inicio)) ?> até <?= date('d/m/Y', strtotime($data_fim)) ?>
        </small>
    </h2>

    <?php if (empty($lista)): ?>
        <p style="color: #888; margin-top: 20px; text-align: center;">Nenhum pedido entregue no período selecionado.</p>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>DATA</th>
                <th>CLIENTE</th>
                <th>PAGAMENTO</th>
                <th>VALOR BRUTO</th>
                <th>LUCRO LÍQUIDO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista as $row): ?>
            <tr>
                <td>#<?= intval($row['id_pedido']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                <td><i class="<?= iconePagamento($row['forma_pagamento']) ?>"></i> <?= htmlspecialchars($row['forma_pagamento'] ?? '—') ?></td>
                <td class="bold-text">R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                <td class="bold-text" style="color: #2E7D32;">R$ <?= number_format($row['lucro'] ?? 0, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<script src="assets/js/financeiro.js"></script>

<?php include '../includes/admin_footer.php'; ?>