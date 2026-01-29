<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChokkoMelt | Extrato Financeiro</title>
    
    <link rel="stylesheet" href="assets/css/extrato.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lily+Script+One&display=swap" rel="stylesheet">
</head>
<body>

    <header class="top-header">
        <div class="logo-area">
            <img src="assets/photos/Chokko logo.png" alt="Logo" class="admin-logo">
            <span class="admin-title">Chokko Melt</span>
        </div>
        <nav class="top-nav">
            <a href="logout.php">Sair <i class="fa-solid fa-right-from-bracket"></i></a>
        </nav>
    </header>

    <div class="admin-layout">
        
        <aside class="sidebar">
            <nav>
                <a href="index.php" class="side-link">
                    <i class="fa-solid fa-receipt"></i> <span>Pedidos</span>
                </a>
                <a href="extrato.php" class="side-link active">
                    <i class="fa-solid fa-chart-line"></i> <span>Extrato Financeiro</span>
                </a>
                <a href="produtos.php" class="side-link">
                    <i class="fa-solid fa-box"></i> <span>Gerenciar Produtos</span>
                </a>
                <a href="usuarios.php" class="side-link">
                    <i class="fa-solid fa-users"></i> <span>Usuários</span>
                </a>
                <a href="config.php" class="side-link">
                    <i class="fa-solid fa-gear"></i> <span>Configurações</span>
                </a>
            </nav>
        </aside>

        <main class="admin-content">
            <section class="welcome-area">
                <h1>Extrato de Faturamento</h1>
                <p>Confira o desempenho das suas vendas por período.</p>
            </section>

            <section class="filters-container">
                <div class="quick-filters">
                    <button class="btn-filter">Hoje</button>
                    <button class="btn-filter">Semana</button>
                    <button class="btn-filter active">Mês</button>
                </div>
                
                <div class="date-picker-box">
                    <input type="date" class="input-date">
                    <span>até</span>
                    <input type="date" class="input-date">
                    <button class="btn-filter-icon"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </section>

            <section class="metrics-container">
                <div class="m-card blue">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <div class="m-info">
                        <strong>R$ 1.540,00</strong>
                        <span>Bruto Total</span>
                    </div>
                </div>

                <div class="m-card green">
                    <i class="fa-solid fa-qrcode"></i>
                    <div class="m-info">
                        <strong>R$ 890,00</strong>
                        <span>Total via Pix</span>
                    </div>
                </div>

                <div class="m-card orange">
                    <i class="fa-solid fa-credit-card"></i>
                    <div class="m-info">
                        <strong>R$ 420,00</strong>
                        <span>Total Cartão</span>
                    </div>
                </div>

                <div class="m-card brown">
                    <i class="fa-solid fa-money-bill-1-wave"></i>
                    <div class="m-info">
                        <strong>R$ 230,00</strong>
                        <span>Total Dinheiro</span>
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
                        <tr>
                            <td>27/01/2026</td>
                            <td>Ana Oliveira</td>
                            <td><i class="fa-solid fa-qrcode"></i> Pix</td>
                            <td>R$ 45,00</td>
                        </tr>
                        <tr>
                            <td>27/01/2026</td>
                            <td>Ricardo Silva</td>
                            <td><i class="fa-solid fa-money-bill-1-wave"></i> Dinheiro</td>
                            <td>R$ 12,00</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>
</html>