<?php
// ============================================================
// carrinho_acao.php — Recebe ações do carrinho e redireciona
// ============================================================
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
    $id_produto = intval($_POST['id_produto'] ?? 0);
    $quantidade = intval($_POST['quantidade'] ?? 1);
    $observacao = trim($_POST['observacao'] ?? '');

    $carrinho->adicionar($id_produto, $quantidade, $observacao);

    // Volta pra vitrine
    header('Location: ../index.php');
    exit;
}

// ── AÇÃO: Alterar quantidade (+1 ou -1) ───────────────────────
if ($acao === 'alterar_quantidade') {
    $chave = $_POST['chave'] ?? '';
    $delta = intval($_POST['delta'] ?? 0); // +1 ou -1

    if (!empty($chave)) {
        $carrinho->alterarQuantidade($chave, $delta);
    }

    header('Location: ../carrinho.php');
    exit;
}

// ── AÇÃO: Remover item do carrinho ────────────────────────────
if ($acao === 'remover') {
    $chave = $_POST['chave'] ?? '';

    if (!empty($chave)) {
        $carrinho->remover($chave);
    }

    header('Location: ../carrinho.php');
    exit;
}

// Se caiu aqui, a ação não foi reconhecida — volta pro início
header('Location: ../index.php');
exit;
?>
