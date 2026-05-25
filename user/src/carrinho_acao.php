<?php
// ============================================================
// carrinho_acao.php — Recebe ações do carrinho e redireciona
// ============================================================
// Este arquivo NÃO exibe HTML. Ele só mexe na $_SESSION e
// manda o usuário de volta para a página certa.
// ============================================================

session_start();

// Garante que o carrinho existe na sessão
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Só processa se veio um POST com uma ação definida
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['acao'])) {
    header('Location: ../index.php');
    exit;
}

$acao = $_POST['acao'];

// ── AÇÃO: Adicionar produto ao carrinho ───────────────────────
if ($acao === 'adicionar') {

    $id_produto     = intval($_POST['id_produto']     ?? 0);
    $nome           = $_POST['nome']                  ?? '';
    $imagem         = $_POST['imagem']                ?? '';
    $preco_unitario = floatval($_POST['preco_unitario'] ?? 0);
    $quantidade     = intval($_POST['quantidade']     ?? 1);
    $observacao     = trim($_POST['observacao']       ?? '');

    // Cria uma chave única para o item.
    // Serve para separar o "mesmo produto com observações diferentes".
    // Ex: bolo sem granulado ≠ bolo com granulado
    $chave = $id_produto . '|' . $observacao;

    // Verifica se já existe esse item no carrinho
    $itemEncontrado = false;
    foreach ($_SESSION['carrinho'] as $idx => $item) {
        if ($item['chave'] === $chave) {
            // Já existe: só soma a quantidade
            $_SESSION['carrinho'][$idx]['quantidade'] += $quantidade;
            $itemEncontrado = true;
            break;
        }
    }

    // Se não encontrou, adiciona como item novo
    if (!$itemEncontrado) {
        $_SESSION['carrinho'][] = [
            'chave'          => $chave,
            'id_produto'     => $id_produto,
            'nome'           => $nome,
            'imagem'         => $imagem,
            'preco_unitario' => $preco_unitario,
            'quantidade'     => $quantidade,
            'observacao'     => $observacao,
        ];
    }

    // Volta pra vitrine
    header('Location: ../index.php');
    exit;
}

// ── AÇÃO: Alterar quantidade (+1 ou -1) ───────────────────────
if ($acao === 'alterar_quantidade') {

    $idx   = intval($_POST['idx']   ?? -1);
    $delta = intval($_POST['delta'] ?? 0); // +1 ou -1

    if (isset($_SESSION['carrinho'][$idx])) {
        $_SESSION['carrinho'][$idx]['quantidade'] += $delta;

        // Se zerou ou foi pra negativo, remove o item
        if ($_SESSION['carrinho'][$idx]['quantidade'] <= 0) {
            unset($_SESSION['carrinho'][$idx]);
            // Reindexa o array para não ficar com buracos (0, 2, 3...)
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
        }
    }

    header('Location: ../carrinho.php');
    exit;
}

// ── AÇÃO: Remover item do carrinho ────────────────────────────
if ($acao === 'remover') {

    $idx = intval($_POST['idx'] ?? -1);

    if (isset($_SESSION['carrinho'][$idx])) {
        unset($_SESSION['carrinho'][$idx]);
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
    }

    header('Location: ../carrinho.php');
    exit;
}

// Se caiu aqui, a ação não foi reconhecida — volta pro início
header('Location: ../index.php');
exit;
?>
