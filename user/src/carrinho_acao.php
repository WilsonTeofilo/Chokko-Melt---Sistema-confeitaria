<?php
// ============================================================
// carrinho_acao.php — Recebe ações do carrinho e redireciona
// Este arquivo NÃO exibe HTML. Ele só mexe no Carrinho e
// manda o usuário de volta para a página certa.
// ============================================================

session_start();
require_once '../../config/config.php';
require_once '../../classes/Carrinho.php';

// Só processa se veio um POST com uma ação definida
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['acao'])) {
    header('Location: ../index.php');
    exit;
}

$carrinho = new Carrinho(DATABASE, HOST, USER, PASS);
$acao = $_POST['acao'];

// ── AÇÃO: Adicionar produto ao carrinho ───────────────────────
if ($acao === 'adicionar') {
    $id_produto = (int)filter_input(INPUT_POST, 'id_produto', FILTER_SANITIZE_NUMBER_INT);
    $quantidade = (int)filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);
    
    $observacao = filter_input(INPUT_POST, 'observacao', FILTER_DEFAULT);
    $observacao = $observacao !== null ? trim($observacao) : '';

    $adicionais_raw = filter_input(INPUT_POST, 'adicionais', FILTER_DEFAULT);
    $adicionais_raw = $adicionais_raw !== null ? trim($adicionais_raw) : '';

    $adicionais = [];
    if (!empty($adicionais_raw)) {
        $adicionais = array_map('intval', explode(',', $adicionais_raw));
    }

    if ($quantidade <= 0) {
        $quantidade = 1;
    }

    $carrinho->adicionar($id_produto, $quantidade, $observacao, $adicionais);

    // Volta pra vitrine
    header('Location: ../index.php');
    exit;
}

// ── AÇÃO: Alterar quantidade (+1 ou -1) ───────────────────────
if ($acao === 'alterar_quantidade') {
    $chave = filter_input(INPUT_POST, 'chave', FILTER_DEFAULT);
    $chave = $chave !== null ? trim($chave) : '';
    
    $delta = (int)filter_input(INPUT_POST, 'delta', FILTER_SANITIZE_NUMBER_INT);

    if (!empty($chave)) {
        $carrinho->alterarQuantidade($chave, $delta);
    }

    header('Location: ../carrinho.php');
    exit;
}

// ── AÇÃO: Remover item do carrinho ────────────────────────────
if ($acao === 'remover') {
    $chave = filter_input(INPUT_POST, 'chave', FILTER_DEFAULT);
    $chave = $chave !== null ? trim($chave) : '';

    if (!empty($chave)) {
        $carrinho->remover($chave);
    }

    header('Location: ../carrinho.php');
    exit;
}

// ── AÇÃO: Repetir pedido anterior ────────────────────────
if ($acao === 'repetir_pedido') {
    $id_pedido = (int)filter_input(INPUT_POST, 'id_pedido', FILTER_SANITIZE_NUMBER_INT);

    if ($id_pedido > 0 && isset($_SESSION['idlogado'])) {
        $id_cliente = (int)$_SESSION['idlogado'];

        try {
            // $conn vem do config.php já incluído acima
            // Valida que o pedido pertence ao cliente logado
            $sql = "
                SELECT ip.id_item_pedido, ip.id_produto, ip.quantidade, ip.observacao
                FROM item_pedido ip
                INNER JOIN pedido p ON p.id_pedido = ip.id_pedido
                WHERE ip.id_pedido = :id_pedido
                  AND p.id_cliente = :id_cliente
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id_pedido' => $id_pedido, 'id_cliente' => $id_cliente]);
            $itens = $stmt->fetchAll();

            foreach ($itens as $item) {
                // Busca adicionais associados a este item de pedido anterior
                $stmtAds = $conn->prepare("
                    SELECT id_adicional 
                    FROM item_pedido_adicional 
                    WHERE id_item_pedido = :id_item
                ");
                $stmtAds->execute(['id_item' => $item['id_item_pedido']]);
                // Pega apenas a coluna id_adicional como array de inteiros
                $ads = $stmtAds->fetchAll(PDO::FETCH_COLUMN);

                $carrinho->adicionar(
                    (int)$item['id_produto'],
                    (int)$item['quantidade'],
                    $item['observacao'] !== null ? trim($item['observacao']) : '',
                    $ads ? $ads : []
                );
            }
        } catch (Exception $e) {
            // Se der erro, mantém o que já foi adicionado
        }
    }

    header('Location: ../carrinho.php');
    exit;
}

// Se caiu aqui, a ação não foi reconhecida — volta pro início
header('Location: ../index.php');
exit;
?>
