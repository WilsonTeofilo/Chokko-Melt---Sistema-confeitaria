<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Segurança extrema: Se não tiver sessão de admin, chuta pra tela de login
if (!isset($_SESSION['admin_nome']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../user/login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// Segurança: verifica o nível e permissões
$admin_tipo = $_SESSION['admin_tipo'] ?? 'FUNCIONARIO';
$admin_permissoes = isset($_SESSION['admin_permissoes']) ? explode(',', $_SESSION['admin_permissoes']) : array();

function temPermissao($modulo, $permissoes, $tipo) {
    if ($tipo === 'ADMIN') return true; // Super admin vê tudo
    return in_array($modulo, $permissoes);
}
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
            <a href="index.php" class="admin-title" style="text-decoration: none;">
                Chokko <span class="text-rosa-chokko">Melt</span>
            </a>
        </div>
        <nav class="top-nav">
            <a href="../user/login.php">Sair <i class="fa-solid fa-right-from-bracket"></i></a>
        </nav>
    </header>

    <div class="admin-layout">
        <aside class="sidebar">
            <nav>
                <?php if(temPermissao('pedidos', $admin_permissoes, $admin_tipo)): ?>
                <a href="index.php" class="side-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt"></i> <span>Pedidos</span>
                </a>
                <?php endif; ?>
                
                <?php if(temPermissao('extrato', $admin_permissoes, $admin_tipo)): ?>
                <a href="financeiro.php" class="side-link <?= $current_page == 'financeiro.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> <span>Financeiro</span>
                </a>
                <?php endif; ?>
                
                <?php if(temPermissao('produtos', $admin_permissoes, $admin_tipo)): ?>
                <a href="produtos.php" class="side-link <?= $current_page == 'produtos.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-box"></i> <span>Produtos</span>
                </a>
                <?php endif; ?>
                
                <?php if(temPermissao('usuarios', $admin_permissoes, $admin_tipo)): ?>
                <a href="usuarios.php" class="side-link <?= $current_page == 'usuarios.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> <span>Usuários</span>
                </a>
                <?php endif; ?>
                
                <?php if(temPermissao('config', $admin_permissoes, $admin_tipo)): ?>
                <a href="config.php" class="side-link <?= $current_page == 'config.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i> <span>Config</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="admin-content">
