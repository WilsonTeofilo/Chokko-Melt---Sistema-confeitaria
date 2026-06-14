<?php
require_once 'config/config.php';
require_once 'classes/Produto.php';

try {
    $prod = new Produto(DATABASE, HOST, USER, PASS);
    
    // Simulate a list of additionals containing both existing IDs and dynamic ones
    $adicionais = ['1', 'novo:Granulado Extra:1.50', 'novo:Calda de Chocolate:2.00'];
    
    $adicionais_ids = [];
    foreach ($adicionais as $ad) {
        if (strpos($ad, 'novo:') === 0) {
            $partes = explode(':', $ad);
            $nome_ad = isset($partes[1]) ? trim($partes[1]) : '';
            $preco_ad = isset($partes[2]) ? floatval($partes[2]) : 0.00;
            if (!empty($nome_ad)) {
                $id_novo = $prod->cadastrarNovoAdicional($nome_ad, $preco_ad, null);
                $adicionais_ids[] = $id_novo;
            }
        } else {
            $adicionais_ids[] = intval($ad);
        }
    }
    
    echo "Generated additional IDs: " . implode(', ', $adicionais_ids) . "\n";
    
    $id_produto = $prod->Cadastrar(
        "Bolo Pote Teste", 
        "Descricao de teste", 
        1, 
        "uploads/test.jpg", 
        15.50, 
        5.00, 
        1, 
        $adicionais_ids
    );
    
    echo "Inserted product ID: " . $id_produto . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
