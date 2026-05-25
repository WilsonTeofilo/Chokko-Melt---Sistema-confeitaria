<?php 
require_once '../config/config.php';
require_once '../classes/Produto.php';
require_once '../classes/Categoria.php';

$listarProd = new Produto(DATABASE, HOST, USER, PASS);
$produtoEditar = null;

// Se existe acao e acao === excluir e existe id_produto no GET, deleta do banco e atualiza a pagina
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id_produto'])) {
    $id_del = filter_input(INPUT_GET, 'id_produto', FILTER_SANITIZE_NUMBER_INT);
    if ($id_del) {
        try {
            $listarProd->excluir(intval($id_del));
            header("Location: produtos.php");
            exit;
        } catch (Exception $e) {
            // Se houver erro, apenas continua para renderizar
        }
    }
}

// Se existe acao e acao === editar e existe id_produto no GET, busca os dados do banco para preencher o form
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id_produto'])) {
    $id_edit = filter_input(INPUT_GET, 'id_produto', FILTER_SANITIZE_NUMBER_INT);
    if ($id_edit) {
        try {
            $produtoEditar = $listarProd->buscarPorId(intval($id_edit));
        } catch (Exception $e) {
            // Se houver erro ou não achar o produto, o modal não abre
        }
    }
}

include '../includes/admin_header.php'; 
?>
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
        <?php 
        try {
            $catObj = new Categoria(DATABASE, HOST, USER, PASS);
            $todasCats = $catObj->listarTodas();
            foreach ($todasCats as $c) {
                echo '<option value="' . htmlspecialchars($c['nome']) . '">' . htmlspecialchars($c['nome']) . '</option>';
            }
        } catch (Exception $e) {
            // Em caso de erro, cai nos estáticos
            echo '<option>Bolos de Pote</option>';
            echo '<option>Bebidas</option>';
        }
        ?>
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
             <?php $listarProd = new Produto(DATABASE,HOST,USER,PASS);
            try {
                $Produtos = $listarProd->listarTodos();
                foreach($Produtos as $prods):
                    $status_produto = $prods['disponibilidade'] == 1 ? 'ativo' : 'congelado';
                    $classe_badge_status = $prods['disponibilidade'] == 1 ? 'badge-ativo' : 'badge-congelado';
                    $status_legivel = $prods['disponibilidade'] == 1 ? 'Ativo' : 'Congelado';
            ?>
            <tr data-produto-id="<?php echo $prods['id_produto']; ?>" data-status="<?php echo $status_produto; ?>" data-custo="<?php echo $prods['custo_compra'] ? number_format($prods['custo_compra'], 2, ',', '') : ''; ?>" data-descricao="<?php echo htmlspecialchars($prods['descricao']); ?>">
                <td><?php echo $prods['id_produto']; ?></td>
                <td class="prod-info-cell">
                    <img src="<?php echo $prods['imagem']; ?>" alt="<?php echo $prods['nome']; ?>">
                    <span><?php echo $prods['nome']; ?></span>
                </td>
                <td><?php echo $prods['nome_categoria']; ?></td>
                <td class="bold-text">R$ <?php echo number_format($prods['preco'], 2, ',', '.'); ?></td>
                <td><span class="badge <?php echo $classe_badge_status; ?>"><?php echo $status_legivel; ?></span></td>
                <td>
                    <a href="produtos.php?acao=editar&id_produto=<?php echo $prods['id_produto']; ?>" class="btn-action-accept btn-edit" title="Editar" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fa-solid fa-pen"></i></a>
                    <?php if ($prods['disponibilidade'] == 1): ?>
                        <button type="button" class="btn-action-accept btn-freeze" title="Congelar" onclick="congelarProduto(this)"><i class="fa-solid fa-snowflake"></i></button>
                    <?php else: ?>
                        <button type="button" class="btn-action-accept btn-unfreeze" title="Descongelar" onclick="descongelarProduto(this)"><i class="fa-solid fa-fire"></i></button>
                    <?php endif; ?>
                    <button type="button" class="btn-action-accept btn-del" title="Excluir" onclick="excluirProduto(this)"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <?php 
                endforeach; 
            } catch (Exception $e) {
                echo '<tr><td colspan="6" style="text-align:center;">' . $e->getMessage() . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</section>

<!-- ═══════════════════════════════════════════
     MODAL DE PRODUTO (Criar/Editar)
     ═══════════════════════════════════════════ -->
<div id="modal-produto" class="modal-overlay" <?php echo $produtoEditar ? 'style="display: flex;"' : ''; ?>>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-produto-title"><?php echo $produtoEditar ? 'Editar Produto' : 'Novo Produto'; ?></h2>
            <button onclick="fecharModalProduto()" class="btn-close-modal">&times;</button>
        </div>
        
        <form action="src/produtos_controller.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id_produto" id="prod-edit-id" value="<?php echo $produtoEditar ? $produtoEditar['id_produto'] : ''; ?>">
            
            <div class="form-row">
                <div class="form-group flex-2">
                    <label class="form-label">Nome do Produto <span class="required">*</span></label>
                    <input type="text" id="prod-nome" name="nome" class="form-control" placeholder="Ex: Bolo de Cenoura" required autocomplete="off" value="<?php echo $produtoEditar ? htmlspecialchars($produtoEditar['nome']) : ''; ?>">
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Preço Venda (R$) <span class="required">*</span></label>
                    <input type="text" id="prod-preco" name="preco" class="form-control"
                           placeholder="0,00" required
                           oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace('.', ',')"
                           value="<?php echo $produtoEditar ? number_format($produtoEditar['preco'], 2, ',', '.') : ''; ?>">
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Custo Compra (R$)</label>
                    <input type="text" id="prod-custo" name="custo_compra" class="form-control"
                           placeholder="0,00"
                           oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace('.', ',')"
                           value="<?php echo $produtoEditar && $produtoEditar['custo_compra'] ? number_format($produtoEditar['custo_compra'], 2, ',', '.') : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label class="form-label">Categoria</label>
                    <select id="prod-categoria" name="categoria" class="form-control" onchange="verificarNovaCategoria(this)">
                        <?php 
                        try {
                            $catObj = new Categoria(DATABASE, HOST, USER, PASS);
                            $todasCats = $catObj->listarTodas();
                            foreach ($todasCats as $c) {
                                $selected = '';
                                if ($produtoEditar && $produtoEditar['id_categoria'] == $c['id_categoria']) {
                                    $selected = 'selected';
                                }
                                echo '<option value="' . htmlspecialchars($c['nome']) . '" ' . $selected . '>' . htmlspecialchars($c['nome']) . '</option>';
                            }
                        } catch (Exception $e) {
                            echo '<option value="Bolos de Pote">Bolos de Pote</option>';
                        }
                        ?>
                        <option value="nova_categoria" class="opt-nova-categoria">+ Criar nova categoria...</option>
                    </select>
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">Status</label>
                    <select id="prod-status" name="disponibilidade" class="form-control">
                        <option value="ativo" <?php echo $produtoEditar && $produtoEditar['disponibilidade'] == 1 ? 'selected' : ''; ?>>Ativo (Visível no cardápio)</option>
                        <option value="congelado" <?php echo $produtoEditar && $produtoEditar['disponibilidade'] == 0 ? 'selected' : ''; ?>>Congelado (Oculto/Pausado)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Imagem do Produto</label>
                <div class="img-preview-wrap">
                    <?php 
                    $imgStyle = 'display: none;';
                    $imgUrl = '';
                    if ($produtoEditar && !empty($produtoEditar['imagem'])) {
                        $imgStyle = 'display: block;';
                        $imgUrl = $produtoEditar['imagem'];
                    }
                    ?>
                    <img id="img-preview" src="<?php echo $imgUrl; ?>" alt="Preview" class="img-preview" style="<?php echo $imgStyle; ?>">
                    <input type="file" id="imagem_produto" name="imagem_produto" class="form-control" accept="image/*" onchange="previewImagem(event)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Descrição / Ingredientes</label>
                <textarea class="form-control" name="descricao" id="prod-descricao" rows="3" placeholder="Descreva o produto..."><?php echo $produtoEditar ? htmlspecialchars($produtoEditar['descricao']) : ''; ?></textarea>
            </div>

            <h3 class="modal-subtitle mt-20">Adicionais / Complementos</h3>
            <div class="adicionais-list" id="adicionais-list">
                <button type="button" class="btn-text-add" onclick="abrirModalAdicional()">+ Criar novo adicional</button>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="fecharModalProduto()" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-action-accept btn-save">
                    <i class="fa-solid fa-save"></i> <?php echo $produtoEditar ? 'Terminar Edição' : 'Salvar Produto'; ?>
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
