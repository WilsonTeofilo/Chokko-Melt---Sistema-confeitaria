<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/financeiro.css">

<section class="welcome-area">
    <h1>Financeiro da Loja</h1>
    <p>Acompanhe o faturamento bruto e o lucro líquido do seu negócio.</p>
</section>

<section class="filters-container">
    <div class="quick-filters">
        <button class="btn-filter" onclick="filtrarPeriodo('hoje', this)">Hoje</button>
        <button class="btn-filter" onclick="filtrarPeriodo('semana', this)">Semana</button>
        <button class="btn-filter active" onclick="filtrarPeriodo('mes', this)">Mês</button>
    </div>
    
    <div class="date-picker-box">
        <input type="date" id="data-inicio" class="input-date">
        <span>até</span>
        <input type="date" id="data-fim" class="input-date">
        <button class="btn-filter-icon" onclick="filtrarPorData()">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
</section>

<section class="metrics-container">
    <!-- MODELO FOREACH METRICAS (puxar do PHP via SUM/COUNT no banco) -->
    <div class="m-card blue">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <div class="m-info">
            <strong>R$ [faturamento_bruto]</strong>
            <span>Faturamento Bruto</span>
        </div>
    </div>
    <div class="m-card lucro-card">
        <i class="fa-solid fa-piggy-bank"></i>
        <div class="m-info">
            <strong>R$ [lucro_liquido]</strong>
            <span>Lucro Líquido</span>
        </div>
    </div>
    <div class="m-card green">
        <i class="fa-solid fa-qrcode"></i>
        <div class="m-info">
            <strong>R$ [total_pix]</strong>
            <span>Pix (Bruto)</span>
        </div>
    </div>
    <div class="m-card orange">
        <i class="fa-solid fa-credit-card"></i>
        <div class="m-info">
            <strong>R$ [total_cartao]</strong>
            <span>Cartão (Bruto)</span>
        </div>
    </div>
    <div class="m-card brown">
        <i class="fa-solid fa-money-bill-1-wave"></i>
        <div class="m-info">
            <strong>R$ [total_dinheiro]</strong>
            <span>Dinheiro (Bruto)</span>
        </div>
    </div>
</section>

<section class="table-wrapper">
    <h2>Detalhamento de Entradas</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>DATA</th>
                <th>CLIENTE</th>
                <th>FORMA DE PAGAMENTO</th>
                <th>VALOR BRUTO</th>
            </tr>
        </thead>
        <tbody>
            <!-- MODELO PARA FOREACH PHP -->
            <tr>
                <td>[data_pedido_formatada]</td>
                <td>[nome_do_cliente]</td>
                <td><i class="[icone_pagamento]"></i> [forma_pagamento]</td>
                <td class="bold-text">R$ [valor_bruto]</td>
            </tr>
            <!-- FIM MODELO PHP -->
        </tbody>
    </table>
</section>

<!-- financeiro.js: abas e UX; totais reais vêm do PHP (consultas a pedido/pagamento). INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/financeiro.js"></script>

<?php include '../includes/admin_footer.php'; ?>