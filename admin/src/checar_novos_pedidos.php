<?php
session_start();
require_once '../../config/config.php';

header('Content-Type: application/json');

// Segurança: Se não tiver sessão de admin, impede o acesso
if (!isset($_SESSION['admin_nome']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado.']);
    exit;
}

$ultimo_id = (int)filter_input(INPUT_GET, 'ultimo_id', FILTER_SANITIZE_NUMBER_INT);

try {
    // Busca se existe algum pedido com ID maior que $ultimo_id e status PENDENTE (1)
    $stmt = $conn->prepare("SELECT id_pedido FROM pedido WHERE id_pedido > :ultimo_id AND id_status_pedido = 1 ORDER BY id_pedido DESC LIMIT 1");
    $stmt->execute(['ultimo_id' => $ultimo_id]);
    $novo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($novo) {
        echo json_encode([
            'sucesso' => true,
            'tem_novo' => true,
            'novo_id' => (int)$novo['id_pedido']
        ]);
    } else {
        echo json_encode([
            'sucesso' => true,
            'tem_novo' => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}
?>
