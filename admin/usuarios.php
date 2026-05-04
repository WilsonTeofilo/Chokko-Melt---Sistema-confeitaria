<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/usuarios.css">

<section class="welcome-area">
    <h1>Gestao de Usuarios</h1>
    <p>Cadastre e gerencie funcionarios e administradores do sistema.</p>
</section>

<section class="action-bar">
    <button class="btn-action-accept btn-icon" onclick="abrirModalNovoUsuario()">
        <i class="fa-solid fa-plus"></i> Novo Usuario
    </button>
</section>

<?php
include '../config/config.php';
$usuariosBD = [];

// Busca os usuarios reais no banco
$sqlBusca = "SELECT * FROM usuario";
$resBusca = @$conn->query($sqlBusca);
if ($resBusca && $resBusca->num_rows > 0) {
    while($row = $resBusca->fetch_assoc()) {
        $usuariosBD[] = $row;
    }
}
?>
<section class="table-wrapper">
    <h2>Usuarios Cadastrados</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOME</th>
                <th>EMAIL</th>
                <th>TIPO</th>
                <th>ACOES</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($usuariosBD)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Nenhum usuario administrativo encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($usuariosBD as $usr): 
                    // Pega o id independente de como a coluna se chame no banco
                    $usrId = isset($usr['id_usuario']) ? $usr['id_usuario'] : $usr['id'];
                ?>
                <tr>
                    <td>#<?= $usrId ?></td>
                    <td><?= htmlspecialchars($usr['nome']) ?></td>
                    <td><?= htmlspecialchars($usr['email']) ?></td>
                    <td>
                        <span class="badge <?= $usr['tipo_usuario'] === 'ADMIN' ? 'badge-admin' : 'badge-func' ?>" id="badge-tipo-<?= $usrId ?>">
                            <?= htmlspecialchars($usr['tipo_usuario']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-action-accept btn-permission" onclick="abrirModalPermissoes('<?= htmlspecialchars($usr['nome']) ?>', '<?= $usr['tipo_usuario'] ?>', <?= $usrId ?>, '<?= htmlspecialchars(isset($usr['permissoes']) ? $usr['permissoes'] : '') ?>')"><i class="fa-solid fa-pen"></i> Permissoes</button>
                        <button class="btn-action-accept btn-del" onclick="excluirUsuario(this, <?= $usrId ?>)"><i class="fa-solid fa-trash"></i> Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<!-- MODAL DE PERMISSOES (Apenas Front-end) -->
<div id="modal-permissoes" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Permissoes de Acesso</h2>
            <button onclick="fecharModalPermissoes()" class="btn-close-modal">&times;</button>
        </div>

        <div class="modal-user-info">
            <strong id="modal-nome-user">Nome do Usuario</strong>
            <div class="mt-10">
                <span>Tipo:</span>
                <select id="modal-tipo-user" class="select-inline">
                    <option value="FUNCIONARIO">Funcionario</option>
                    <option value="ADMIN">Administrador</option>
                </select>
            </div>
        </div>

        <h3 class="modal-subtitle">Modulos Liberados</h3>
        <div id="lista-permissoes-checkboxes" class="permissions-list">
            <label class="permission-item">
                <input type="checkbox" value="pedidos" checked>
                <span>Gerenciar Pedidos</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="extrato">
                <span>Ver Extrato Financeiro</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="produtos" checked>
                <span>Adicionar/Editar Produtos</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="config">
                <span>Configuracoes da Loja</span>
            </label>
            <label class="permission-item">
                <input type="checkbox" value="usuarios">
                <span>Gerenciar Outros Usuarios</span>
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

<!-- MODAL DE NOVO/EDITAR USUARIO -->
<div id="modal-novo-usuario" class="modal-overlay">
    <div class="modal-content modal-usuario">
        <div class="modal-header">
            <h2 class="modal-title" id="modal-novo-usuario-title">Novo Usuario</h2>
            <button onclick="fecharModalNovoUsuario()" class="btn-close-modal">&times;</button>
        </div>
        
        <form action="src/auth_admin.php" method="POST">
            <input type="hidden" name="acao" value="CadastrarUsuario">
            
            <div class="form-group">
                <label class="form-label">Nome Completo <span class="required">*</span></label>
                <input type="text" name="usr_nome" class="form-control" placeholder="Nome do funcionario ou admin" required autocomplete="off">
            </div>

            <div class="form-row-usuario">
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="usr_email" class="form-control" placeholder="exemplo@chokko.com" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone <span class="required">*</span></label>
                    <input type="tel" name="usr_telefone" class="form-control" placeholder="(00) 00000-0000" maxlength="15" oninput="mascaraTelefone(this)" required>
                </div>
            </div>

            <div class="form-row-usuario">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Senha <span class="required">*</span></label>
                    <input type="password" id="usr_senha" name="usr_senha" class="form-control" placeholder="Minimo 8 caracteres" required autocomplete="new-password">
                    
                    <!-- Medidor de forca da senha -->
                    <div id="senha-strength-wrap" class="hidden">
                        <div id="senha-strength-bar">
                            <span class="strength-seg" id="seg1"></span>
                            <span class="strength-seg" id="seg2"></span>
                            <span class="strength-seg" id="seg3"></span>
                            <span class="strength-seg" id="seg4"></span>
                        </div>
                        <ul id="senha-checklist">
                            <li id="chk-len"><i class="fa-solid fa-circle-xmark"></i> Minimo 8 caracteres</li>
                            <li id="chk-upper"><i class="fa-solid fa-circle-xmark"></i> 1 letra maiuscula</li>
                            <li id="chk-special"><i class="fa-solid fa-circle-xmark"></i> 1 caractere especial (!@#$...)</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Confirmar Senha <span class="required">*</span></label>
                    <input type="password" id="usr_senha_confirmar" name="usr_senha_confirmar" class="form-control" placeholder="Repita a senha" required autocomplete="new-password">
                    <span id="erro-senhas" class="erro-senhas-txt"></span>
                </div>
            </div>

            <div class="form-row-usuario">
                <div class="form-group" style="width: 50%;">
                    <label class="form-label">Tipo de Usuario</label>
                    <select name="usr_tipo" id="usr-tipo" class="form-control">
                        <option value="FUNCIONARIO">Funcionario</option>
                        <option value="ADMIN">Administrador</option>
                    </select>
                </div>
            </div>

            <!-- Permissoes customizadas para quando for funcionario -->
            <div class="form-group" style="margin-top: 15px;">
                <label class="form-label">Permissoes de Acesso (Marque as opcoes liberadas)</label>
                <div class="permissions-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label><input type="checkbox" name="permissoes[]" value="pedidos" checked> Pedidos</label>
                    <label><input type="checkbox" name="permissoes[]" value="extrato"> Financeiro/Extrato</label>
                    <label><input type="checkbox" name="permissoes[]" value="produtos"> Produtos</label>
                    <label><input type="checkbox" name="permissoes[]" value="usuarios"> Usuarios</label>
                    <label><input type="checkbox" name="permissoes[]" value="config"> Configuracoes</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="fecharModalNovoUsuario()" class="btn-cancel">Cancelar</button>
                <button type="submit" class="btn-action-accept btn-save">
                    <i class="fa-solid fa-save"></i> Salvar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../user/assets/js/chokko_digits.js"></script>
<script src="assets/js/usuarios.js"></script>

<?php include '../includes/admin_footer.php'; ?>