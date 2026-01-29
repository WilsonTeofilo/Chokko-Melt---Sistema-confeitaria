<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChokkoMelt | Painel Administrativo</title>
    
    <link rel="stylesheet" href="assets/css/admin.css">
    
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
                <a href="index.php" class="side-link active">
                    <i class="fa-solid fa-receipt"></i> <span>Pedidos</span>
                </a>
                <a href="extrato.php" class="side-link">
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
                <h1>Painel de Pedidos</h1>
                <p>Monitore e gerencie as solicitações em tempo real.</p>
            </section>

            <section class="metrics-container">
                <div class="m-card orange">
                    <i class="fa-solid fa-clock"></i>
                    <div class="m-info">
                        <strong>12</strong>
                        <span>Pendentes</span>
                    </div>
                </div>

                <div class="m-card green">
                    <i class="fa-solid fa-circle-check"></i>
                    <div class="m-info">
                        <strong>08</strong>
                        <span>Aceitos</span>
                    </div>
                </div>

                <div class="m-card red">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div class="m-info">
                        <strong>02</strong>
                        <span>Cancelados</span>
                    </div>
                </div>

                <div class="m-card blue">
                    <i class="fa-solid fa-flag-checkered"></i>
                    <div class="m-info">
                        <strong>45</strong>
                        <span>Finalizados</span>
                    </div>
                </div>
            </section>

            <section class="table-wrapper">
                <h2>Pedidos em Aberto</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CLIENTE</th>
                            <th>PAGAMENTO</th>
                            <th>STATUS</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1024</td>
                            <td>Juliana Souza</td>
                            <td>Pix</td>
                            <td><span class="badge pendente">Pendente</span></td>
                            <td>
                                <button class="btn-action-accept">Aceitar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>
</html>