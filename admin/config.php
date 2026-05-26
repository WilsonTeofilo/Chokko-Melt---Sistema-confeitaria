<?php
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensagem = '';
$tipo_mensagem = '';

// Se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hora_abre    = $_POST['hora_abre'] ?? '15:00';
    $hora_fecha   = $_POST['hora_fecha'] ?? '22:00';
    $whatsapp     = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');
    
    // Converte a taxa de entrega de "X,XX" para "X.XX"
    $taxa_raw     = $_POST['taxa_entrega'] ?? '5,00';
    $taxa_raw     = str_replace('.', '', $taxa_raw);
    $taxa_raw     = str_replace(',', '.', $taxa_raw);
    $taxa_entrega = floatval($taxa_raw);

    try {
        $sql = "UPDATE config_loja SET 
                    hora_abre = :hora_abre, 
                    hora_fecha = :hora_fecha, 
                    whatsapp = :whatsapp, 
                    taxa_entrega_padrao = :taxa_entrega 
                WHERE id_config = 1";
        
        $stmt_update = $conn->prepare($sql);
        $stmt_update->execute([
            ':hora_abre' => $hora_abre,
            ':hora_fecha' => $hora_fecha,
            ':whatsapp' => empty($whatsapp) ? null : $whatsapp,
            ':taxa_entrega' => $taxa_entrega
        ]);
        
        $mensagem = "Configurações salvas com sucesso!";
        $tipo_mensagem = "sucesso";
    } catch (Exception $e) {
        $mensagem = "Erro ao salvar as configurações: " . $e->getMessage();
        $tipo_mensagem = "erro";
    }
}

// Busca as configurações atuais do banco
try {
    $stmt = $conn->prepare("SELECT * FROM config_loja WHERE id_config = 1");
    $stmt->execute();
    $config = $stmt->fetch();
    if (!$config) {
        // Inicializa registro caso não exista
        $conn->query("INSERT INTO config_loja (id_config, hora_abre, hora_fecha, whatsapp, taxa_entrega_padrao) VALUES (1, '15:00:00', '22:00:00', NULL, 5.00)");
        $stmt->execute();
        $config = $stmt->fetch();
    }
} catch (Exception $e) {
    die("Erro ao carregar configurações: " . $e->getMessage());
}

// Formata o WhatsApp para exibição no input (ex: (11) 99999-9999)
$whatsapp_formatado = '';
if (!empty($config['whatsapp'])) {
    $w = $config['whatsapp'];
    if (strlen($w) === 11) {
        $whatsapp_formatado = '(' . substr($w, 0, 2) . ') ' . substr($w, 2, 5) . '-' . substr($w, 7);
    } elseif (strlen($w) === 10) {
        $whatsapp_formatado = '(' . substr($w, 0, 2) . ') ' . substr($w, 2, 4) . '-' . substr($w, 6);
    } else {
        $whatsapp_formatado = $w;
    }
}

include '../includes/admin_header.php';
?>
<link rel="stylesheet" href="assets/css/config.css">

<section class="welcome-area">
    <h1>Configurações da Loja</h1>
    <p>Ajuste os parâmetros de funcionamento do sistema.</p>
</section>

<section class="table-wrapper config-wrapper">
    
    <?php if (!empty($mensagem)): ?>
        <div style="background: <?= $tipo_mensagem === 'sucesso' ? '#e8f5e9' : '#ffebee' ?>; 
                    color: <?= $tipo_mensagem === 'sucesso' ? '#2e7d32' : '#c62828' ?>; 
                    padding: 15px; 
                    border-radius: 8px; 
                    margin-bottom: 20px; 
                    border: 1px solid <?= $tipo_mensagem === 'sucesso' ? '#a5d6a7' : '#ffcdd2' ?>; 
                    font-weight: 500;">
            <i class="fa-solid <?= $tipo_mensagem === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>" style="margin-right: 8px;"></i>
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="config.php">
        <div class="config-section">
            <h3 class="config-title">Informações Básicas</h3>
            
            <div class="form-group">
                <label class="form-label">Nome da Loja</label>
                <input type="text" name="nome_loja" id="config-nome-loja" value="Chokko Melt" class="form-control" readonly style="background-color: #f8f8f8; cursor: not-allowed;" title="O nome da loja é fixo do sistema.">
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp para Contato</label>
                <input type="text" name="whatsapp" id="config-whatsapp" value="<?= htmlspecialchars($whatsapp_formatado) ?>" class="form-control" maxlength="16"
                    oninput="typeof ChokkoMascaraTelefoneInput==='function'&&ChokkoMascaraTelefoneInput(this)">
            </div>
        </div>

        <div class="config-section">
            <h3 class="config-title">Operação e Delivery</h3>
            
            <div class="form-group flex-gap-15">
                <div class="flex-1">
                    <label class="form-label">Horário de Abertura</label>
                    <input type="time" name="hora_abre" id="config-hora-abre" value="<?= date('H:i', strtotime($config['hora_abre'])) ?>" class="form-control" required>
                </div>
                <div class="flex-1">
                    <label class="form-label">Horário de Fechamento</label>
                    <input type="time" name="hora_fecha" id="config-hora-fecha" value="<?= date('H:i', strtotime($config['hora_fecha'])) ?>" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Taxa de Entrega Padrão (R$)</label>
                <input type="text" name="taxa_entrega" id="config-taxa-entrega" value="<?= number_format($config['taxa_entrega_padrao'], 2, ',', '.') ?>" class="form-control config-short-input" required>
            </div>
        </div>

        <div class="config-footer">
            <button type="submit" class="btn-action-accept config-save-btn">
                <i class="fa-solid fa-save"></i> Salvar Configurações
            </button>
        </div>
    </form>
</section>

<script src="../user/assets/js/chokko_digits.js"></script>

<?php include '../includes/admin_footer.php'; ?>
