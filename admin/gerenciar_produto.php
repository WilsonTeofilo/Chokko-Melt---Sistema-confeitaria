<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos - Chokko Melt</title>
    <link rel="stylesheet" href="assets/css/style-produtos.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header class="topbar">
        <div class="brand"><h1>Chokko Melt</h1> <span class="badge-admin">Admin</span></div>
        <button class="btn-logout"><span class="material-icons">logout</span> Sair</button>
    </header>   

    <div class="container">
        <aside class="sidebar">
            <nav>
                <a href="#" class="nav-item"><span class="material-icons">description</span> Pedidos</a>
                <a href="#" class="nav-item"><span class="material-icons">request_quote</span> Extrato</a>
                <a href="#" class="nav-item active"><span class="material-icons">inventory_2</span> Gerenciar Produtos</a>
            </nav>
        </aside>

        <main class="content">
            <div class="page-header">
                <h2>Gerenciar Produtos</h2>
                <p>5 produtos ativos • 1 inativos</p>
            </div>

            <div class="toolbar">
                <button class="btn-main"><span class="material-icons">add</span> Novo Produto</button>
                <div class="search-box">
                    <span class="material-icons">search</span>
                    <input type="text" placeholder="Buscar produto pelo nome...">
                </div>
                <select><option>Todas as categorias</option></select>
                <button class="btn-secondary"><span class="material-icons">visibility_off</span> Mostrar inativos</button>
            </div>

            <div class="product-grid">
                <div class="card-product">
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1ea345?w=120" alt="Bolo">
                    <div class="info">
                        <h3>Bolo de Chocolate</h3>
                        <p class="cat">Bolos</p>
                        <div class="tags">
                            <span class="tag tag-green">Ativo</span>
                            <span class="tag tag-blue">Em estoque</span>
                        </div>
                        <div class="actions">
                            <button><span class="material-icons">edit</span> Editar</button>
                            <button><span class="material-icons">power_settings_new</span> Desativar</button>
                        </div>
                    </div>
                    <div class="price-side">
                        <span class="price">R$ 25,00</span>
                        <button class="btn-del"><span class="material-icons">delete</span></button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>