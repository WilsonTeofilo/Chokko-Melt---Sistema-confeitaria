<?php
// ============================================================
// pedido_acao.php — Processa a gravação do pedido no banco
// ============================================================

session_start();
require_once '../../config/config.php';
require_once '../../classes/Carrinho.php';
require_once '../../classes/Endereco.php';

// Só aceita requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// 1. Garante que o cliente está logado
if (!isset($_SESSION['idlogado'])) {
    header("Location: ../login.php");
    exit;
}

$id_cliente = intval($_SESSION['idlogado']);

// Tratamento da Ação de Cancelar Pedido
$acao = filter_input(INPUT_POST, 'acao', FILTER_DEFAULT);
$acao = $acao !== null ? trim($acao) : '';

if ($acao === 'cancelar_pedido') {
    $id_pedido = (int)filter_input(INPUT_POST, 'id_pedido', FILTER_SANITIZE_NUMBER_INT);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_DEFAULT);
    $motivo = $motivo !== null ? trim($motivo) : '';

    if ($id_pedido <= 0 || empty($motivo)) {
        header("Location: ../pedidos.php");
        exit;
    }

    try {
        // Valida propriedade e se o status ainda é PENDENTE (1)
        $sqlCheck = "SELECT id_pedido FROM pedido WHERE id_pedido = :id_pedido AND id_cliente = :id_cliente AND id_status_pedido = 1 LIMIT 1";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute(['id_pedido' => $id_pedido, 'id_cliente' => $id_cliente]);
        $pedido = $stmtCheck->fetch();

        if ($pedido) {
            $sqlUpdate = "
                UPDATE pedido 
                SET id_status_pedido = 6, 
                    motivo_cancelamento = :motivo, 
                    cancelado_por = 'CLIENTE', 
                    cancelado_em = NOW() 
                WHERE id_pedido = :id_pedido
            ";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'motivo' => htmlspecialchars($motivo),
                'id_pedido' => $id_pedido
            ]);

            header("Location: ../detalhes_pedido.php?id=" . $id_pedido . "&cancelado=1");
            exit;
        } else {
            header("Location: ../detalhes_pedido.php?id=" . $id_pedido);
            exit;
        }
    } catch (Exception $e) {
        die("Erro ao cancelar o pedido: " . $e->getMessage());
    }
}

// 2. Carrega o carrinho do cliente
$carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
$itens = $carrinhoObj->listarItens();
$subtotal = $carrinhoObj->calcularSubtotal();

// Se o carrinho estiver vazio, volta para o cardápio
if (empty($itens)) {
    header("Location: ../index.php");
    exit;
}

// 3. Recebe e sanitiza os dados do formulário
$tipo_entrega = filter_input(INPUT_POST, 'tipo_entrega', FILTER_DEFAULT);
$tipo_entrega = $tipo_entrega !== null ? trim($tipo_entrega) : 'delivery';

$id_endereco = (int)filter_input(INPUT_POST, 'id_endereco', FILTER_SANITIZE_NUMBER_INT);

$pagamento = filter_input(INPUT_POST, 'pagamento', FILTER_DEFAULT);
$pagamento = $pagamento !== null ? trim($pagamento) : 'credito';

$cpf_nota = filter_input(INPUT_POST, 'cpf_nota', FILTER_DEFAULT);
$cpf_nota = $cpf_nota !== null ? trim($cpf_nota) : '';

$observacao_pedido = filter_input(INPUT_POST, 'observacao', FILTER_DEFAULT);
$observacao_pedido = $observacao_pedido !== null ? trim($observacao_pedido) : '';

// Higieniza o CPF para deixar apenas dígitos
$cpf_nota = preg_replace('/[^0-9]/', '', $cpf_nota);
if (strlen($cpf_nota) !== 11) {
    $cpf_nota = null; // CPF inválido ou em branco vira nulo
}

// 4. Validações e regras de negócio básicas
$tipo_entrega_db = 'DELIVERY';
if ($tipo_entrega === 'retirada') {
    $tipo_entrega_db = 'RETIRADA';
} elseif ($tipo_entrega === 'local') {
    $tipo_entrega_db = 'LOCAL';
}

$id_endereco_db = null;
$taxa_entrega = 0.00;

if ($tipo_entrega_db === 'DELIVERY') {
    // Valida se o endereço pertence mesmo ao cliente logado
    $enderecoObj = new Endereco(DATABASE, HOST, USER, PASS);
    $endereco = $enderecoObj->buscarPorId($id_endereco, $id_cliente);
    if (!$endereco) {
        die("Endereço inválido ou não selecionado.");
    }
    $id_endereco_db = $id_endereco;

    // Busca a taxa de entrega padrão da loja
    try {
        $stmtTaxa = $conn->prepare("SELECT taxa_entrega_padrao FROM config_loja WHERE id_config = 1");
        $stmtTaxa->execute();
        $config = $stmtTaxa->fetch();
        if ($config) {
            $taxa_entrega = floatval($config['taxa_entrega_padrao']);
        }
    } catch (Exception $e) {
        $taxa_entrega = 5.00;
    }
}

$total = $subtotal + $taxa_entrega;

// Valida forma de pagamento
$forma_pagamento_db = 'CREDITO';
if ($pagamento === 'pix') {
    $forma_pagamento_db = 'PIX';
} elseif ($pagamento === 'dinheiro') {
    $forma_pagamento_db = 'DINHEIRO';
}

// Trata troco se for pagamento em dinheiro
$valor_entregue = null;
$troco = 0.00;

if ($forma_pagamento_db === 'DINHEIRO') {
    $valor_pago_dinheiro_raw = filter_input(INPUT_POST, 'valor_pago_dinheiro', FILTER_DEFAULT);
    $valor_pago_dinheiro_raw = $valor_pago_dinheiro_raw !== null ? trim($valor_pago_dinheiro_raw) : '';
    if (!empty($valor_pago_dinheiro_raw)) {
        // Converte formato BR ("50,00") para Float
        $valor_pago_dinheiro_raw = str_replace('.', '', $valor_pago_dinheiro_raw);
        $valor_pago_dinheiro_raw = str_replace(',', '.', $valor_pago_dinheiro_raw);
        $valor_entregue = floatval($valor_pago_dinheiro_raw);

        if ($valor_entregue < $total) {
            $valor_entregue = $total;
        }
        $troco = $valor_entregue - $total;
    }
}

// 5. Inicia gravação com transação no banco para garantir consistência total
try {
    $conn->beginTransaction();

    // Calcula o custo total do pedido a partir do custo_compra de cada produto e de seus adicionais
    $custo_total = 0.00;
    $itens_detalhados = [];
    foreach ($itens as $item) {
        $stmtCusto = $conn->prepare("SELECT custo_compra FROM produto WHERE id_produto = :id LIMIT 1");
        $stmtCusto->execute(['id' => $item['id_produto']]);
        $prodInfo = $stmtCusto->fetch();
        $custo_unitario = $prodInfo && isset($prodInfo['custo_compra']) ? floatval($prodInfo['custo_compra']) : 0.00;
        
        $custo_item_acumulado = $custo_unitario;
        if (!empty($item['adicionais'])) {
            foreach ($item['adicionais'] as $ad) {
                $custo_item_acumulado += isset($ad['custo']) ? floatval($ad['custo']) : 0.00;
            }
        }
        
        $custo_total += $custo_item_acumulado * $item['quantidade'];
        
        $itens_detalhados[] = [
            'id_produto' => $item['id_produto'],
            'preco_unitario' => $item['preco_unitario'],
            'custo_unitario' => $custo_unitario,
            'quantidade' => $item['quantidade'],
            'observacao' => $item['observacao'],
            'adicionais' => $item['adicionais'] ?? []
        ];
    }
    
    // Lucro líquido do pedido = subtotal - custo de compra total
    $lucro = $subtotal - $custo_total;

    // A) Insere o pedido principal com informações de custo e lucro
    $sqlPedido = "
        INSERT INTO pedido (valor_total, subtotal, custo_total, lucro, observacao, tipo_entrega, taxa_entrega, cpf_nota, id_status_pedido, id_cliente, id_endereco)
        VALUES (:total, :subtotal, :custo_total, :lucro, :obs, :tipo, :taxa, :cpf, 1, :id_cliente, :id_endereco)
    ";
    $stmtPedido = $conn->prepare($sqlPedido);
    $stmtPedido->execute([
        ':total'        => $total,
        ':subtotal'     => $subtotal,
        ':custo_total'  => $custo_total,
        ':lucro'        => $lucro,
        ':obs'          => empty($observacao_pedido) ? null : htmlspecialchars($observacao_pedido),
        ':tipo'         => $tipo_entrega_db,
        ':taxa'         => $taxa_entrega,
        ':cpf'          => $cpf_nota,
        ':id_cliente'   => $id_cliente,
        ':id_endereco'  => $id_endereco_db
    ]);

    $id_pedido = $conn->lastInsertId();

    // B) Insere os itens do pedido com o custo_unitario capturado (snapshot de custo)
    $sqlItem = "
        INSERT INTO item_pedido (preco_unitario, quantidade, custo_unitario, observacao, id_produto, id_pedido)
        VALUES (:preco, :qtd, :custo, :obs, :id_prod, :id_ped)
    ";
    $stmtItem = $conn->prepare($sqlItem);

    foreach ($itens_detalhados as $item) {
        $stmtItem->execute([
            ':preco'   => $item['preco_unitario'],
            ':qtd'     => $item['quantidade'],
            ':custo'   => $item['custo_unitario'],
            ':obs'     => empty($item['observacao']) ? null : htmlspecialchars($item['observacao']),
            ':id_prod' => $item['id_produto'],
            ':id_ped'  => $id_pedido
        ]);
        $idItemPedido = $conn->lastInsertId();

        // Insere os adicionais deste item na tabela `item_pedido_adicional`
        if (!empty($item['adicionais'])) {
            foreach ($item['adicionais'] as $ad) {
                $stmtInsAd = $conn->prepare("
                    INSERT INTO item_pedido_adicional (id_item_pedido, id_adicional, nome_snapshot, preco_unitario_snapshot, custo_unitario_snapshot, quantidade)
                    VALUES (:id_item, :id_ad, :nome, :preco, :custo, 1)
                ");
                $stmtInsAd->execute([
                    'id_item' => $idItemPedido,
                    'id_ad'   => $ad['id_adicional'],
                    'nome'    => $ad['nome'],
                    'preco'   => $ad['preco'],
                    'custo'   => $ad['custo']
                ]);
            }
        }
    }

    // C) Insere o registro de pagamento (pendente na entrega)
    $sqlPagamento = "
        INSERT INTO pagamento (valor_pago, forma_pagamento, valor_entregue, troco, id_status_pagamento, id_pedido)
        VALUES (:total, :forma, :entregue, :troco, 1, :id_pedido)
    ";
    $stmtPagamento = $conn->prepare($sqlPagamento);
    $stmtPagamento->execute([
        ':total'    => $total,
        ':forma'    => $forma_pagamento_db,
        ':entregue' => $valor_entregue,
        ':troco'    => $troco,
        ':id_pedido'=> $id_pedido
    ]);

    // D) Esvazia a sacola do cliente no banco e na sessão
    $carrinhoObj->esvaziar();

    // Tudo certo! Confirma as alterações no banco
    $conn->commit();

    // Redireciona para os detalhes do pedido recém-criado
    header("Location: ../detalhes_pedido.php?id=" . $id_pedido);
    exit;

} catch (Exception $e) {
    // Se deu qualquer ruim, desfaz todas as inserções
    $conn->rollBack();
    die("Erro ao registrar seu pedido: " . $e->getMessage());
}
?>
