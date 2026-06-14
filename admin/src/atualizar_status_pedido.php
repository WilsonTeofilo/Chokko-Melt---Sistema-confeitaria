<?php
session_start();
require_once '../../config/config.php';

// Resposta JSON padrão
header('Content-Type: application/json');

// Segurança: Se não tiver sessão de admin, impede o acesso
if (!isset($_SESSION['admin_nome']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido.']);
    exit;
}

// Sanitização robusta com filter_input a pedido do usuário
$id_pedido = (int)filter_input(INPUT_POST, 'id_pedido', FILTER_SANITIZE_NUMBER_INT);
$novo_status = filter_input(INPUT_POST, 'novo_status', FILTER_DEFAULT);
$novo_status = $novo_status !== null ? trim($novo_status) : '';

$motivo = filter_input(INPUT_POST, 'motivo', FILTER_DEFAULT);
$motivo = $motivo !== null ? trim($motivo) : '';

if ($id_pedido <= 0 || empty($novo_status)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos.']);
    exit;
}

// Mapeia o novo status de string para o ID correspondente da tabela status_pedido
$id_status_pedido = 0;
if ($novo_status === 'EM_PREPARO') {
    $id_status_pedido = 3;
} elseif ($novo_status === 'ENVIADO') {
    $id_status_pedido = 4;
} elseif ($novo_status === 'ENTREGUE') {
    $id_status_pedido = 5;
} elseif ($novo_status === 'CANCELADO') {
    $id_status_pedido = 7; // CANCELADO_LOJA (Admin cancelou)
}

if ($id_status_pedido === 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Status inválido.']);
    exit;
}

try {
    $conn->beginTransaction();

    if ($id_status_pedido === 7) {
        // Cancelado pelo admin
        $sql = "
            UPDATE pedido 
            SET id_status_pedido = :status, 
                motivo_cancelamento = :motivo, 
                cancelado_por = 'ADMIN', 
                cancelado_em = NOW() 
            WHERE id_pedido = :id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'status' => $id_status_pedido,
            'motivo' => htmlspecialchars($motivo),
            'id' => $id_pedido
        ]);

        // Atualiza pagamento para cancelado (status 3)
        $stmtPag = $conn->prepare("UPDATE pagamento SET id_status_pagamento = 3 WHERE id_pedido = :id");
        $stmtPag->execute(['id' => $id_pedido]);
    } else {
        // Outros status (EM_PREPARO, ENVIADO, ENTREGUE)
        $sql = "UPDATE pedido SET id_status_pedido = :status";
        
        // Atualiza os timestamps correspondentes a cada etapa
        if ($id_status_pedido === 3) {
            $sql .= ", aceito_em = COALESCE(aceito_em, NOW())";
        } elseif ($id_status_pedido === 4) {
            $sql .= ", enviado_em = COALESCE(enviado_em, NOW())";
        } elseif ($id_status_pedido === 5) {
            $sql .= ", entregue_em = COALESCE(entregue_em, NOW())";
        }
        
        $sql .= " WHERE id_pedido = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'status' => $id_status_pedido,
            'id' => $id_pedido
        ]);

        if ($id_status_pedido === 5) {
            // Se entregue, atualiza o pagamento para PAGO (status 2)
            $stmtPag = $conn->prepare("UPDATE pagamento SET id_status_pagamento = 2 WHERE id_pedido = :id");
            $stmtPag->execute(['id' => $id_pedido]);
        }
    }

    $conn->commit();
    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>
