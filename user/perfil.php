<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/perfil.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <p class="logo-mini">Chokko<span> Melt</span></p>
        <p class="page-subtitle">Meu Perfil</p>
    </div>
</header>
</div>

<?php
// Mock DB Data (Clean Architecture: Controller passing Data to View)
$usuarioLogado = [
    'nome' => 'Maria Silva',
    'membro_desde' => '2024',
    'email' => 'maria.silva@email.com',
    'telefone' => '(11) 98765-4321',
    'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150'
];

$enderecosMock = [
    [
        'id' => 1,
        'apelido' => 'Casa',
        'logradouro' => 'Rua das Flores, 123',
        'bairro' => 'Jardim Primavera, São Paulo - SP',
        'cep' => '01234-567',
        'is_default' => true,
        'icon' => '📍'
    ]
];
?>
<div class="screen">
    <main class="container">
        <div class="content">
            <section class="user-card">
                <div class="profile-info">
                    <img src="<?= htmlspecialchars($usuarioLogado['avatar_url']) ?>" alt="Avatar" class="avatar">
                    <div class="user-details">
                        <h2><?= htmlspecialchars($usuarioLogado['nome']) ?></h2>
                        <p class="member-since">Cliente desde <?= htmlspecialchars($usuarioLogado['membro_desde']) ?></p>
                        <div class="contact-row"><span>✉</span> <?= htmlspecialchars($usuarioLogado['email']) ?></div>
                        <div class="contact-row"><span>📞</span> <?= htmlspecialchars($usuarioLogado['telefone']) ?></div>
                        <button class="btn-edit-info" style="background:#d7ccc8; border:none; padding:5px 10px; border-radius:5px; color:#4a362d; margin-top:10px;">📝 Editar informações</button>
                    </div>
                </div>
            </section>
            
            <div class="card-branco">
                <section class="addresses-section">
                    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h3>Meus Endereços</h3>
                        <span style="font-size:12px; color:#999;"><?= count($enderecosMock) ?> endereço(s)</span>
                    </div>
                    
                    <button class="btn-add-address">+ Adicionar novo endereço</button>

                    <?php foreach ($enderecosMock as $end): ?>
                    <div class="address-card" data-endereco-id="<?= $end['id'] ?>">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= htmlspecialchars($end['icon']) ?> <?= htmlspecialchars($end['apelido']) ?></strong>
                            <?php if ($end['is_default']): ?>
                            <span class="badge-default">✓ Endereço padrão</span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#555;">
                            <p><?= htmlspecialchars($end['logradouro']) ?></p>
                            <p><?= htmlspecialchars($end['bairro']) ?></p>
                            <p>CEP: <?= htmlspecialchars($end['cep']) ?></p>
                        </div>
                        <div class="address-footer">
                            <button class="btn-outline" onclick="editarEndereco(this)">Editar</button>
                            <button class="btn-outline-danger" onclick="excluirEndereco(this)">Excluir</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </section>
            </div>
            
            <!-- NOTA BACKEND: ao clicar em Sair, destruir a sessão PHP: session_destroy() e redirecionar para index.php -->
            <button class="btn-outline-danger" style="width: 100%; padding: 15px; margin-top: 10px;"
                onclick="localStorage.removeItem('chokko_usuario_id'); window.location.href='index.php'">
                Sair da Conta
            </button>
        </div>
    </main>
</div>

<!-- Modal Overlay -->
<div id="modal-overlay" class="modal-overlay">
    <div class="modal-card">
        <h3 id="modal-title">Editar Informações</h3>
        <div id="modal-body"></div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="fecharModal()">Cancelar</button>
            <button class="btn-save" id="btn-salvar-modal">Salvar Alterações</button>
        </div>
    </div>
</div>

<script src="assets/js/chokko_digits.js"></script>
<script src="assets/js/perfil.js"></script>

<?php include '../includes/user_footer.php'; ?>
