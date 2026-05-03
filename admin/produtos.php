<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/produtos.css">

<section class="welcome-area">
    <h1>Gerenciar Produtos</h1>
    <p>Adicione, edite ou remova produtos do cardápio.</p>
</section>

<section class="action-bar">
    <button class="btn-action-accept btn-icon" onclick="abrirModalProduto(false)">
        <i class="fa-solid fa-plus"></i> Novo Produto
    </button>
    <div class="search-bar">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Buscar produto pelo nome...">
    </div>
    <select class="filter-select">
        <option>Todas as categorias</option>
        <option>Bolos de Pote</option>
        <option>Bebidas</option>
    </select>
</section>

<!-- Tabs de Status -->
<div class="status-tabs" id="produtos-tabs">
    <button class="tab-btn active" onclick="filtrarProdutos('todos', this)">Todos</button>
    <button class="tab-btn" onclick="filtrarProdutos('ativo', this)">Ativos</button>
    <button class="tab-btn" onclick="filtrarProdutos('congelado', this)">Congelados</button>
</div>

<section class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>PRODUTO</th>
                <th>CATEGORIA</th>
                <th>PREÇO</th>
                <th>STATUS</th>
                <th>AÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <tr data-produto-id="1" data-status="ativo">
                <td>#1</td>
                <td class="prod-info-cell">
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1ea345?w=50" alt="Bolo">
                    <span>Bolo de Chocolate</span>
                </td>
                <td>Bolos de Pote</td>
                <td class="bold-text">R$ 25,00</td>
                <td><span class="badge badge-ativo">Ativo</span></td>
                <td>
                    <button type="button" class="btn-action-accept btn-edit" title="Editar" onclick="abrirModalProduto(true, this)"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="btn-action-accept btn-freeze" title="Congelar item" onclick="congelarProduto(this)"><i class="fa-solid fa-snowflake"></i></button>
                    <button type="button" class="btn-action-accept btn-del" title="Excluir" onclick="excluirProduto(this)"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <tr data-produto-id="2" data-status="congelado">
                <td>#2</td>
                <td class="prod-info-cell">
                    <img src="https://images.unsplash.com/photo-1551024506-0bccd828d307?w=50" alt="Sobremesa">
                    <span>Torta de Limão</span>
                </td>
                <td>Tortas de Pote</td>
                <td class="bold-text">R$ 22,00</td>
                <td><span class="badge badge-congelado">Congelado</span></td>
                <td>
                    <button type="button" class="btn-action-accept btn-edit" title="Editar" onclick="abrirModalProduto(true, this)"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="btn-action-accept btn-unfreeze" title="Ativar item" onclick="descongelarProduto(this)"><i class="fa-solid fa-fire"></i></button>
                    <button type="button" class="btn-action-accept btn-del" title="Excluir" onclick="excluirProduto(this)"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</section>

<!-- ═══════════════════════════════════════════
     MODAL DE PRODUTO (Criar/Editar)
     ═══════════════════════════════════════════ -->
<div id="modal-produto" class="modal-overlay">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-produto-title">Novo Produto</h2>
            <button onclick="fecharModalProduto()" class="btn-close-modal">&times;</button>
        </div>
        
        <form>
            <input type="hidden" id="prod-edit-id" value="">
            <div class="form-row">
                <div class="form-group flex-2">
                    <label class="form-label">Nome do Produto <span class="required">*</span></label>
                    <input type="text" id="prod-nome" class="form-control" placeholder="Ex: Bolo de Cenoura" required autocomplete="off">
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Preço Venda (R$) <span class="required">*</span></label>
                    <input type="text" id="prod-preco" class="form-control"
                           placeholder="0,00" required
                           oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace('.', ',')">
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Custo Compra (R$)</label>
                    <input type="text" id="prod-custo" class="form-control"
                           placeholder="0,00"
                           oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace('.', ',')">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label class="form-label">Categoria</label>
                    <select id="prod-categoria" class="form-control" onchange="verificarNovaCategoria(this)">
                        <option value="Bolos de Pote">Bolos de Pote</option>
                        <option value="Tortas de Pote">Tortas de Pote</option>
                        <option value="Bebidas">Bebidas</option>
                        <option value="nova_categoria" class="opt-nova-categoria">+ Criar nova categoria...</option>
                    </select>
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Status</label>
                    <select id="prod-status" class="form-control">
                        <option value="ativo">Ativo (Visível no cardápio)</option>
                        <option value="congelado">Congelado (Oculto/Pausado)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Imagem do Produto</label>
                <div class="img-preview-wrap">
                    <img id="img-preview" src="" alt="Preview" class="img-preview">
                    <input type="file" id="imagem_produto" class="form-control" accept="image/*" onchange="previewImagem(event)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Descrição / Ingredientes</label>
                <textarea class="form-control" rows="3" placeholder="Descreva o produto..."></textarea>
            </div>

            <h3 class="modal-subtitle mt-20">Adicionais / Complementos</h3>
            <div class="adicionais-list" id="adicionais-list">
                <label class="adicional-item">
                    <input type="checkbox" class="checkbox-marrom">
                    <span>Calda de Chocolate extra (+ R$ 3,00)</span>
                </label>
                <label class="adicional-item">
                    <input type="checkbox" class="checkbox-marrom">
                    <span>Morango Pedaços (+ R$ 4,50)</span>
                </label>
                <button type="button" class="btn-text-add" onclick="abrirModalAdicional()">+ Criar novo adicional</button>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="fecharModalProduto()" class="btn-cancel">Cancelar</button>
                <button type="button" class="btn-action-accept btn-save" onclick="salvarProduto(event)">
                    <i class="fa-solid fa-save"></i> Salvar Produto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL NOVO ADICIONAL -->
<div id="modal-novo-adicional" class="modal-overlay modal-overlay-custom">
    <div class="modal-content modal-content-sm">
        <div class="modal-header">
            <h2 class="modal-title">Novo Adicional</h2>
            <button onclick="fecharModalAdicional()" class="btn-close-modal">&times;</button>
        </div>
        <form>
            <div class="form-group">
                <label class="form-label">Nome do Adicional <span class="required">*</span></label>
                <input type="text" id="add-nome" class="form-control" placeholder="Ex: Granulado">
            </div>
            <div class="form-group">
                <label class="form-label">Preço (Opcional)</label>
                <input type="number" id="add-preco" class="form-control" placeholder="0.00" step="0.01" min="0">
            </div>
            <div class="modal-footer">
                <button type="button" onclick="fecharModalAdicional()" class="btn-cancel">Cancelar</button>
                <button type="button" class="btn-action-accept btn-save" onclick="salvarNovoAdicional()">
                    <i class="fa-solid fa-plus"></i> Adicionar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- produtos.js: CRUD de UI; persistência em produto/categoria/adicional via POST PHP. INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/produtos.js"></script>

<?php include '../includes/admin_footer.php'; ?>
