<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChokkoMelt | Painel Administrativo</title>
    
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/adicionais.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lily+Script+One&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <header class="top-header">
        <div class="logo-area">
            <span class="admin-title" style="font-family: 'Lily Script One', cursive; font-size: 24px;">
                Chokko<span style="color: var(--rosa-chokko);">Melt</span>
            </span>
        </div>
        <nav class="top-nav">
            <a href="../user/login.php">Sair <i class="fa-solid fa-right-from-bracket"></i></a>
        </nav>
    </header>

    <div class="admin-layout">
        <aside class="sidebar">
            <nav>
                <a href="pedidos.php" class="side-link <?= ($current_page == 'pedidos.php' || $current_page == 'index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt"></i> <span>Pedidos</span>
                </a>
                <a href="financeiro.php" class="side-link <?= $current_page == 'financeiro.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> <span>Financeiro</span>
                </a>
                <a href="produtos.php" class="side-link <?= $current_page == 'produtos.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-box"></i> <span>Produtos</span>
                </a>
                <a href="usuarios.php" class="side-link <?= $current_page == 'usuarios.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> <span>Usuários</span>
                </a>
                <a href="config.php" class="side-link <?= $current_page == 'config.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i> <span>Config</span>
                </a>
            </nav>
        </aside>

        <main class="admin-content">
