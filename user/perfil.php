<?php 

session_start();

// Segurança: se não estiver logado, chuta pro login
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}

include '../includes/user_header.php'; 
include '../config/config.php';
include '../includes/modal.php'; // Função central de modais
require_once '../classes/Endereco.php'; // Incluindo a nova classe

$id_cliente = $_SESSION['idlogado'];
$enderecoCRUD = new Endereco(DATABASE, HOST, USER, PASS);

// --- 1. BUSCANDO OS DADOS DO CLIENTE PARA EXIBIR NA TELA ---
try {
    $sqlCliente = "SELECT email, telefone FROM cliente WHERE id_cliente = :id_cliente LIMIT 1";
    $stmtCliente = $conn->prepare($sqlCliente);
    $stmtCliente->execute(['id_cliente' => $id_cliente]);

    if ($stmtCliente->rowCount() > 0) {
        $cliente = $stmtCliente->fetch();
        $emailCliente = $cliente['email'];
        $telefoneCliente = $cliente['telefone'];
    } else {
        $emailCliente = "Email não cadastrado";
        $telefoneCliente = "Telefone não cadastrado";
    }

    // --- 2. BUSCANDO ENDERECOS PELA CLASSE:
    $listaEnderecos = $enderecoCRUD->listarPorCliente($id_cliente);

    if (empty($listaEnderecos)) {
        // Exibe o modal de endereço não cadastrado usando a função centralizada
        exibirModalOverlay(
            'modal-no-address',
            'Você não tem endereço cadastrado',
            'Para receber suas delícias em casa, precisamos que você cadastre pelo menos um endereço de entrega.',
            'endereco.php',
            'Cadastrar Endereço',
            'fa-map-location-dot',
            '#3b2313'
        );
    }
} catch(Exception $e) {
    // Erro silencioso ou log
    $emailCliente = "Erro ao carregar";
    $telefoneCliente = "Erro ao carregar";
    $listaEnderecos = array();
}
?>
<link rel="stylesheet" href="assets/css/perfil.css">
<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <p class="page-subtitle">Meu Perfil</p>
    </div>
</header>
</div>


<div class="screen">
    <main class="container">
        <div class="content">
            
            <?php if(isset($_GET['msg'])): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em; border-left: 4px solid #2e7d32;">
                    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

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
                        <span class="address-count"><?= count($listaEnderecos) ?>/3 endereço(s)</span>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <?php foreach($listaEnderecos as $index => $end): ?>
                            <div class="card-branco" style="margin-bottom: 15px; border: 1px solid #e0e0e0; padding: 20px; box-shadow: none;">
                                <div class="address-header">
                                    <strong><i class="fa-solid fa-map-pin" style="color: #d32f2f;"></i> Endereço <?= $index + 1 ?></strong>
                                    <?php if($index == 0): ?>
                                        <span class="badge-default">✔ Endereço padrão</span>
                                    <?php endif; ?>
                                </div>
                                <div class="address-body" style="line-height: 1.5; margin-top: 10px;">
                                    <?= htmlspecialchars($end['rua'] . ', ' . $end['numero']) ?><br>
                                    <?php if(!empty($end['complemento'])) echo htmlspecialchars($end['complemento']) . '<br>'; ?>
                                    <?= htmlspecialchars($end['bairro']) ?><br>
                                    CEP: <?= htmlspecialchars($end['cep']) ?>
                                </div>
                                <div class="address-footer">
                                    <button class="btn-outline" onclick="window.location.href='endereco.php?id=<?= $end['id_endereco'] ?>'">Editar</button>
                                    <button class="btn-outline-danger" onclick="if(confirm('Tem certeza que deseja excluir este endereço?')) window.location.href='endereco.php?acao=excluir&id=<?= $end['id_endereco'] ?>'">Excluir</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(count($listaEnderecos) < 3): ?>
                        <button class="btn-add-address" onclick="window.location.href='endereco.php'"> 
                            + Adicionar novo endereço
                        </button>
                    <?php else: ?>
                        <p style="text-align: center; color: #888; font-size: 0.9em; margin-top: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px;">
                            Você atingiu o limite máximo de 3 endereços.
                        </p>
                    <?php endif; ?>
                </section>
            <button class="btn-outline-danger btn-logout" onclick="window.location.href='src/auth/logout.php'">
                Sair da Conta
            </button>
        </div>
    </main>
</div>



<script src="assets/js/chokko_digits.js"></script>
<script src="assets/js/perfil.js"></script>

<?php include '../includes/user_footer.php'; ?>
