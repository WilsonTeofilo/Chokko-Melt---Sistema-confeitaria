<?php 
session_start();
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}
include '../includes/user_header.php'; 
?>
<link rel="stylesheet" href="assets/css/pedidos.css">

<div class="page-header-wrap">
<header class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <a href="index.php" class="logo-mini" style="text-decoration: none; display: block;">Chokko<span> Melt</span></a>
        <p class="page-subtitle">Meus Pedidos</p>
    </div>
</header>
</div>

<div class="page-pad">

<?php
// Carrega conexão e busca pedidos do usuário logado
require_once '../config/config.php';

$id_cliente = $_SESSION['idlogado'];

// Busca todos os pedidos do cliente ordenados pelo mais recente
$todosPedidos = [];
try {
    $sql = "
        SELECT p.id_pedido, p.data_hora, p.valor_total, p.tipo_entrega, p.id_status_pedido,
               sp.descricao AS status_descricao
        FROM pedido p
        INNER JOIN status_pedido sp ON p.id_status_pedido = sp.id_status_pedido
        WHERE p.id_cliente = :id_cliente
        ORDER BY p.data_hora DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_cliente' => $id_cliente]);
    $todosPedidos = $stmt->fetchAll();
} catch (Exception $e) {
    // Silencioso em caso de erro na query
}

// Prepara consulta para buscar itens de cada pedido
$ativos = [];
$historico = [];

if (!empty($todosPedidos)) {
    try {
        $stmtItems = $conn->prepare("
            SELECT ip.quantidade, p.nome
            FROM item_pedido ip
            INNER JOIN produto p ON ip.id_produto = p.id_produto
            WHERE ip.id_pedido = :id_pedido
        ");

        foreach ($todosPedidos as $pedido) {
            $stmtItems->execute(['id_pedido' => $pedido['id_pedido']]);
            $pedido['itens'] = $stmtItems->fetchAll();

            $status = $pedido['status_descricao'];
            if (in_array($status, ['ENTREGUE', 'CANCELADO_CLIENTE', 'CANCELADO_LOJA'])) {
                $historico[] = $pedido;
            } else {
                $ativos[] = $pedido;
            }
        }
    } catch (Exception $e) {
        // Silencioso
    }
}

// Função auxiliar para formatar status de exibição
function obterStatusFormatado($status_db) {
    switch ($status_db) {
        case 'PENDENTE':
            return ['class' => 'aguardando', 'texto' => 'Aguardando'];
        case 'ACEITO':
            return ['class' => 'em-preparo', 'texto' => 'Aceito pela Loja'];
        case 'EM_PREPARO':
            return ['class' => 'em-preparo', 'texto' => 'Em Preparo'];
        case 'ENVIADO':
            return ['class' => 'saiu-entrega', 'texto' => 'Saiu para Entrega'];
        case 'ENTREGUE':
            return ['class' => 'entregue', 'texto' => 'Entregue'];
        case 'CANCELADO_CLIENTE':
        case 'CANCELADO_LOJA':
            return ['class' => 'cancelado', 'texto' => 'Cancelado'];
        default:
            return ['class' => 'aguardando', 'texto' => $status_db];
    }
}
?>

    <!-- Estado vazio se não houver pedidos -->
    <?php if (empty($todosPedidos)): ?>
        <div class="orders-empty" id="orders-empty">
            <i class="fa-solid fa-clipboard-list"></i>
            <p>Você ainda não fez nenhum pedido.</p>
            <a href="index.php" class="btn btn-primary btn-auto btn-auto-pad">
                Ver cardápio
            </a>
        </div>
    <?php endif; ?>

    <!-- ══ EM ANDAMENTO ══ -->
    <?php if (!empty($ativos)): ?>
        <p class="orders-section-label">Em Andamento</p>
        
        <?php foreach ($ativos as $pedido): 
            $statusInfo = obterStatusFormatado($pedido['status_descricao']);
            $data_formatada = date('d/m/Y H:i', strtotime($pedido['data_hora']));
        ?>
            <div class="order-card order-card--active" data-pedido-id="<?= $pedido['id_pedido'] ?>">
                <div class="order-card-header">
                    <div class="order-card-info">
                        <span class="order-number">Pedido #<?= $pedido['id_pedido'] ?></span>
                        <span class="order-date"><?= $data_formatada ?></span>
                    </div>
                    <span class="order-status <?= $statusInfo['class'] ?>"><?= $statusInfo['texto'] ?></span>
                </div>

                <ul class="order-items-preview">
                    <?php foreach ($pedido['itens'] as $item): ?>
                        <li><i class="fa-solid fa-circle-dot"></i> <?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="order-card-footer">
                    <span class="order-total">R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
                    <a href="detalhes_pedido.php?id=<?= $pedido['id_pedido'] ?>" class="btn-detalhes">
                        Ver detalhes <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ══ HISTÓRICO ══ -->
    <?php if (!empty($historico)): ?>
        <p class="orders-section-label mt-24">Histórico</p>

        <?php foreach ($historico as $pedido): 
            $statusInfo = obterStatusFormatado($pedido['status_descricao']);
            $data_formatada = date('d/m/Y H:i', strtotime($pedido['data_hora']));
            $cancelado = in_array($pedido['status_descricao'], ['CANCELADO_CLIENTE', 'CANCELADO_LOJA']);
        ?>
            <div class="order-card <?= $cancelado ? 'order-card--cancelled' : '' ?>" data-pedido-id="<?= $pedido['id_pedido'] ?>">
                <div class="order-card-header">
                    <div class="order-card-info">
                        <span class="order-number">Pedido #<?= $pedido['id_pedido'] ?></span>
                        <span class="order-date"><?= $data_formatada ?></span>
                    </div>
                    <span class="order-status <?= $statusInfo['class'] ?>"><?= $statusInfo['texto'] ?></span>
                </div>

                <ul class="order-items-preview">
                    <?php foreach ($pedido['itens'] as $item): ?>
                        <li><i class="fa-solid fa-circle-dot"></i> <?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="order-card-footer">
                    <span class="order-total" style="<?= $cancelado ? 'text-decoration: line-through; color: var(--cinza-medio);' : '' ?>">
                        R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?>
                    </span>
                    <div class="order-card-actions">
                        <a href="detalhes_pedido.php?id=<?= $pedido['id_pedido'] ?>" class="btn-detalhes">
                            Detalhes <i class="fa-solid fa-chevron-right"></i>
                        </a>
                        <form action="src/carrinho_acao.php" method="POST" style="display:inline;">
                            <input type="hidden" name="acao" value="repetir_pedido">
                            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                            <button type="submit" class="btn-repetir" style="background: none; border: 1px solid var(--marrom); color: var(--marrom); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold; display: flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-rotate-right"></i> Repetir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include '../includes/user_footer.php'; ?>
<script src="assets/js/pedidos.js"></script>
