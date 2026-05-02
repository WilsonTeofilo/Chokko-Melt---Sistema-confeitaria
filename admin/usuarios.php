<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/usuarios.css">

<section class="welcome-area">
    <h1>Gestão de Usuários</h1>
    <p>Cadastre e gerencie funcionários e administradores do sistema.</p>
</section>

<section class="action-bar">
    <button class="btn-action-accept btn-icon" onclick="abrirModalNovoUsuario()">
        <i class="fa-solid fa-plus"></i> Novo Usuário
    </button>
</section>

<?php
// Mocking DB data fetching (Clean Architecture -> Repository -> Controller -> View)
$usuariosMock = [
    [
        'id' => 1,
        'nome' => 'Admin Principal',
        'email' => 'admin@chokkomelt.com',
        'tipo' => 'ADMIN'
    ],
    [
        'id' => 2,
        'nome' => 'João Balconista',
        'email' => 'joao@chokkomelt.com',
        'tipo' => 'FUNCIONARIO'
    ]
];
?>
<section class="table-wrapper">
    <h2>Usuários Cadastrados</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOME</th>
                <th>EMAIL</th>
                <th>TIPO</th>
                <th>AÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuariosMock as $usr): ?>
            <tr data-row-id="<?= $usr['id'] ?>">
                <td>#<?= $usr['id'] ?></td>
                <td><?= htmlspecialchars($usr['nome']) ?></td>
                <td><?= htmlspecialchars($usr['email']) ?></td>
                <td>
                    <span class="badge <?= $usr['tipo'] === 'ADMIN' ? 'badge-admin' : 'badge-func' ?>" id="badge-tipo-<?= $usr['id'] ?>">
                        <?= $usr['tipo'] ?>
                    </span>
                </td>
                <td>
                    <button class="btn-action-accept btn-permission" onclick="abrirModalPermissoes('<?= htmlspecialchars($usr['nome']) ?>', '<?= $usr['tipo'] ?>', <?= $usr['id'] ?>)"><i class="fa-solid fa-pen"></i> Permissões</button>
                    <button class="btn-action-accept btn-del" onclick="excluirUsuario(this)"><i class="fa-solid fa-trash"></i> Excluir</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- MODAL DE PERMISSÕES (Apenas Front-end) -->
<div id="modal-permissoes" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Permissões de Usuário</h2>
            <button onclick="fecharModalPermissoes()" class="btn-close-modal">&times;</button>
        </div>
        
        <div class="modal-user-info">
            <p><strong>Usuário:</strong> <span id="modal-nome-user">João Balconista</span></p>
            <p class="mt-10">
                <strong>Nível de Acesso:</strong>
                <select id="modal-tipo-user" class="form-control select-inline" onchange="atualizarCheckboxesPermissao(this.value)">
                    <option value="ADMIN">Administrador</option>
                    <option value="FUNCIONARIO">Funcionário</option>
                </select>
            </p>
        </div>

        <h3 class="modal-subtitle">O que este usuário pode fazer?</h3>
        
        <div class="permissions-list" id="lista-permissoes-checkboxes">
            <label class="permission-item">
                <input type="checkbox" value="pedidos" checked>
                <span>Gerenciar Pedidos (Aceitar/Cancelar)</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="extrato">
                <span>Acessar Extrato e Financeiro</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="produtos" checked>
                <span>Adicionar/Editar Produtos</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="config">
                <span>Configurações da Loja</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="usuarios">
                <span>Gerenciar Outros Usuários</span>
            </label>
        </div>

        <div class="modal-footer">
            <button onclick="fecharModalPermissoes()" class="btn-cancel">Cancelar</button>
            <button onclick="salvarPermissoes()" class="btn-action-accept btn-save">
                <i class="fa-solid fa-save"></i> Salvar
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE NOVO/EDITAR USUÁRIO -->
<div id="modal-novo-usuario" class="modal-overlay">
    <div class="modal-content modal-usuario">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-novo-usuario-title">Novo Usuário</h2>
            <button onclick="fecharModalNovoUsuario()" class="btn-close-modal">&times;</button>
        </div>
        
        <form>
            <div class="form-group">
                <label class="form-label">Nome Completo <span class="required">*</span></label>
                <input type="text" id="usr-nome" class="form-control" placeholder="Nome do funcionário ou admin" required autocomplete="off">
            </div>

            <div class="form-row-usuario">
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="usr-email" class="form-control" placeholder="exemplo@chokko.com" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone <span class="required">*</span></label>
                    <input type="tel" id="usr-telefone" class="form-control" placeholder="(00) 00000-0000" maxlength="15" oninput="mascaraTelefone(this)" required>
                </div>
            </div>

            <div class="form-row-usuario">
                <div class="form-group">
                    <label class="form-label">Senha <span class="required">*</span></label>
                    <input type="password" id="usr-senha" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Usuário</label>
                    <select id="usr-tipo" class="form-control">
                        <option value="FUNCIONARIO">Funcionário</option>
                        <option value="ADMIN">Administrador</option>
                    </select>
                </div>
            </div>

            <div class="root-warning">
                <label class="root-label">
                    <input type="checkbox" id="usr-root" onchange="toggleRoot(this)">
                    <div>
                        <strong>Acesso Root (Super Administrador)</strong>
                        <span>Força tipo ADMIN e dá permissão total ao sistema.</span>
                    </div>
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="fecharModalNovoUsuario()" class="btn-cancel">Cancelar</button>
                <button type="button" class="btn-action-accept btn-save" onclick="salvarNovoUsuario(event)">
                    <i class="fa-solid fa-save"></i> Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../user/assets/js/chokko_digits.js"></script>
<!-- usuarios.js: modais; criar/editar usuário admin só no PHP com password_hash. INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/usuarios.js"></script>

<?php include '../includes/admin_footer.php'; ?>
