<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chokko Melt | Cardápio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Lily+Script+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header class="main-header">
        <div class="logo">Chokko<span> Melt</span></div>
        <div class="status-badge">Loja Aberta</div>
    </header>

    <main class="content">
        <section class="hero-banner">
            <h1>Cardápio Digital</h1>
        </section>

        <nav class="categories">
            <button class="cat-btn active">Todos</button>
            <button class="cat-btn">Bolos</button>
            <button class="cat-btn">Doces</button>
            <button class="cat-btn">Bebidas</button>
        </nav>

        <section class="products-grid">
            
            <article class="product-card">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400" alt="Bolo">
                </div>
                <div class="product-info">
                    <h3>Bolo de Chocolate</h3>
                    <span class="product-price">R$ 25,00</span>
                    <button class="add-btn">+ Adicionar</button>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=400" alt="Doce">
                </div>
                <div class="product-info">
                    <h3>Brigadeiro Gourmet</h3>
                    <span class="product-price">R$ 5,00</span>
                    <button class="add-btn">+ Adicionar</button>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400" alt="Doce">
                </div>
                <div class="product-info">
                    <h3>Cheesecake Morango</h3>
                    <span class="product-price">R$ 18,00</span>
                    <button class="add-btn">+ Adicionar</button>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400" alt="Bebida">
                </div>
                <div class="product-info">
                    <h3>Milkshake Nutella</h3>
                    <span class="product-price">R$ 15,00</span>
                    <button class="add-btn">+ Adicionar</button>
                </div>
            </article>

        </section>
    </main>

    <footer class="bottom-nav">
        <a href="index.php" class="nav-item active"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="pedidos.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span></a>
        
        <a href="carrinho.php" class="nav-item"> <i class="fa-solid fa-bag-shopping"></i><span>Sacola</span></a>
        <a href="perfil.php" class="nav-item"><i class="fa-solid fa-user"></i><span>Perfil</span></a>
    </footer>

</body>
</html>