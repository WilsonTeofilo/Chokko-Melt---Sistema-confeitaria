<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chokko Melt | Sua Sacola</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Lily+Script+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/carrinho.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">Chokko<span>Melt</span></div>
        <div class="status-badge">Sacola</div>
    </header>

    <main class="cart-container" style="padding: 15px; max-width: 800px; margin: 0 auto;">
        <h2 style="color: var(--marrom); margin-bottom: 15px;">Meus Pedidos</h2>

        <div class="cart-item-card">
            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=100" alt="Bolo">
            <div class="cart-item-info">
                <h3>Bolo de Chocolate</h3>
                <p>R$ 25,00</p>
            </div>
            <div class="cart-item-actions">
                <button class="qty-btn">-</button>
                <span class="qty-number">1</span>
                <button class="qty-btn">+</button>
            </div>
        </div>

        <div class="cart-summary">
            <div class="summary-line">
                <span>Subtotal</span>
                <span>R$ 25,00</span>
            </div>
            <div class="summary-line">
                <span>Entrega</span>
                <span>R$ 5,00</span>
            </div>
            <div class="summary-line total">
                <span>Total</span>
                <span>R$ 30,00</span>
            </div>
            
            <button class="checkout-btn">Finalizar Pedido</button>
        </div>
    </main>

    <footer class="bottom-nav">
        <a href="index.php" class="nav-item"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="pedidos.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        <a href="carrinho.php" class="nav-item active"><i class="fa-solid fa-bag-shopping"></i><span>Sacola</span></a>
        <a href="perfil.php" class="nav-item"><i class="fa-solid fa-user"></i><span>Perfil</span></a>
    </footer>

</body>
</html>