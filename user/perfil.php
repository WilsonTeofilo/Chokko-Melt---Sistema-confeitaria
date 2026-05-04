<?php 
session_start();
include '../includes/user_header.php'; 
include '../config/config.php';

// Segurança: se não estiver logado, chuta pro login
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}

$id_cliente = $_SESSION['idlogado'];

// --- 1. BUSCANDO OS DADOS DO CLIENTE PARA EXIBIR NA TELA ---
$sqlCliente = "SELECT email, telefone FROM cliente WHERE id_cliente = '$id_cliente'";
$resCliente = $conn->query($sqlCliente);
if ($resCliente && $resCliente->num_rows > 0) {
    $cliente = $resCliente->fetch_assoc();
    $emailCliente = $cliente['email'];
    $telefoneCliente = $cliente['telefone'];
} else {
    $emailCliente = "Email não cadastrado";
    $telefoneCliente = "Telefone não cadastrado";
}

// --- 2. TERRENO PREPARADO PARA OS ENDEREÇOS ---
// TODO: Escreva aqui a lógica (SELECT) para buscar os endereços desse cliente no banco!
// Por enquanto, vou criar o array vazio só pra página não quebrar de erro.
$enderecosMock = []; 

?>
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


<div class="screen">
    <main class="container">
        <div class="content">
            <section class="user-card">
                <div class="profile-info">
                    <img src="https://img.freepik.com/vetores-premium/desenho-de-bolo-de-aniversario-ilustracao-de-vetor-de-alimentos-premium_1080480-131970.jpg?semt=ais_hybrid&w=740&q=80" alt="Avatar" class="avatar">
                    <div class="user-details">
                       
                        <div class="contact-row"><span>✉</span> <?= htmlspecialchars($emailCliente) ?></div>
                        <div class="contact-row"><span>📞</span> <?= htmlspecialchars($telefoneCliente) ?></div>
                        <button class="btn-edit-info">📝 Editar informações</button>
                    </div>
                </div>
            </section>
            
            <div class="card-branco">
                <section class="addresses-section">
                    <div class="section-header">
                        <h3>Meus Endereços</h3>
                        <span class="address-count"><?= count($enderecosMock) ?> endereço(s)</span>
                    </div>
                    
                    <button class="btn-add-address">+ Adicionar novo endereço</button>

                    <?php foreach ($enderecosMock as $end): ?>
                    <div class="address-card" data-endereco-id="<?= $end['id'] ?>">
                        <div class="address-header">
                            <strong><?= htmlspecialchars($end['icon']) ?> <?= htmlspecialchars($end['apelido']) ?></strong>
                            <?php if ($end['is_default']): ?>
                            <span class="badge-default">✓ Endereço padrão</span>
                            <?php endif; ?>
                        </div>
                        <div class="address-body">
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
            
            <button class="btn-outline-danger btn-logout" onclick="window.location.href='src/logout.php'">
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
            <button class="btn-cancel">Cancelar</button>
            <button class="btn-save" id="btn-salvar-modal">Salvar Alterações</button>
        </div>
    </div>
</div>

<script src="assets/js/chokko_digits.js"></script>
<script src="assets/js/perfil.js"></script>

<?php include '../includes/user_footer.php'; ?>
