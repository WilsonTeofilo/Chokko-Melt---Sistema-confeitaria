<?php
session_start();
include('../../../config/config.php');
include('../../../includes/modal.php'); // exibirModalEVoltar() centralizada
$ARrua=array();
$id_cliente = $_SESSION['idlogado'];

if(isset($_POST['Salvar'])){
    $rua         = $_POST['rua'];
    $numero      = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $bairro      = $_POST['bairro'];
    $cep         = $_POST['cep'];
    $pontref     = $_POST['ponto_referencia'];

    // 1. Verifica quantos endereços o cliente já tem ANTES de inserir
    $sqlSelect = "SELECT id_endereco FROM endereco WHERE id_cliente = '$id_cliente'";
    $selectEndereco = $conn->query($sqlSelect);

    if($selectEndereco->num_rows >= 3) {
        // Já tem 3 endereços, não deixa cadastrar mais
        exibirModalEVoltar(
            'Limite Atingido',
            'Você já atingiu o limite de 3 endereços cadastrados. Exclua um endereço antigo antes de cadastrar um novo.',
            '../../perfil.php',
            'Voltar ao Perfil',
            'fa-circle-exclamation',
            '#f39c12' // Laranja pra warning
        );
    } else {
        // 2. Se tiver menos de 3, prossegue com o INSERT
        $sqlEndereco = "INSERT INTO endereco (rua, numero, complemento, bairro, cep, ponto_referencia, id_cliente)
                        VALUES ('$rua', '$numero', '$complemento', '$bairro', '$cep', '$pontref', '$id_cliente')";
        $resEndereco = $conn->query($sqlEndereco);

        if($resEndereco){
            exibirModalEVoltar(
                'Endereço Cadastrado!',
                'Seu endereço de entrega foi salvo com sucesso. Agora é só fazer seu pedido! 🍫',
                '../../perfil.php',
                'Voltar ao Perfil',
                'fa-circle-check',
                '#4CAF50'
            );
        } else {
            exibirModalEVoltar(
                'Erro ao Salvar',
                'Não foi possível salvar o endereço. Tente novamente.',
                'javascript:window.history.back()',
                'Tentar Novamente'
            );
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/endereco.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Cadastrar Endereço — Chokko Melt</title>
</head>
<body>

<!-- ── PAINEL ESQUERDO (só visível no desktop) ── -->
<div class="painel-esquerdo">
    <a href="../../index.php" class="logo-painel">Chokko<span> Melt</span></a>
    <i class="fa-solid fa-map-location-dot icone-mapa"></i>
    <h2>Onde entregamos<br>sua felicidade?</h2>
    <p>Cadastre um endereço de entrega e receba nossas delícias artesanais direto na sua porta. 🍫</p>
</div>

<!-- ── PAINEL DIREITO com o formulário ── -->
<div class="painel-direito">
<main id="form-container">
    <div id="form-header">
        <h1 id="form-title">Novo Endereço</h1>
        <button type="button" id="btn-back" onclick="window.history.back()">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
    </div>
    
    <!-- FORMULÁRIO -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        <input type="hidden" name="acao" value="CadastrarEndereco">
        <div id="input_container">
            
            <!-- CEP:-->
            <div class="input-box">
                <label for="cepJS" class="form-label">CEP</label>
                <div class="input-field"><i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="cep" id="cepJS" placeholder="00000-000" maxlength="10" required autocomplete="off">
                </div>
            </div>

            <!-- Rua:-->
            <div class="input-box">
                <label for="ruaJS" class="form-label">Rua</label>
                <div class="input-field"> <i class="fa-solid fa-road"></i>
                    <input type="text" name="rua" id="ruaJS" placeholder="Ex: Av. Paulista" maxlength="50" required autocomplete="off">
                </div>
            </div>

            <!-- Número e Bairro lado a lado -->
            <div class="input-row">
                <div class="input-box">
                    <label for="numeroJS" class="form-label">Número</label>
                    <div class="input-field"> <i class="fa-solid fa-hashtag"></i>
                        <input type="text" name="numero" id="numeroJS" placeholder="Ex: 123" maxlength="10" required autocomplete="off">
                    </div>
                </div>

                <!-- Bairro:-->
                <div class="input-box">
                    <label for="bairroJS" class="form-label">Bairro</label>
                    <div class="input-field">  <i class="fa-solid fa-tree-city"></i>
                        <input type="text" name="bairro" id="bairroJS" placeholder="Ex: Jardim Shangrilá" maxlength="29" required autocomplete="off">
                    </div>
                </div>
            </div>


            <!-- Complemento:-->
            <div class="input-box">
                <label for="complementoJS" class="form-label">Complemento (Opcional)</label>
                <div class="input-field">
                    <i class="fa-solid fa-building"></i>
                    <input type="text" name="complemento" id="complementoJS" placeholder="Ex: Apto 45, Bloco B" maxlength="50" autocomplete="off">
                </div>
            </div>

            <!-- Ponto de Referência:-->
            <div class="input-box">
                <label for="pontoReferenciaJS" class="form-label">Ponto de Referência (Opcional)</label>
                <div class="input-field">
                    <i class="fa-solid fa-location-crosshairs"></i>
                    <input type="text" name="ponto_referencia" id="pontoReferenciaJS" placeholder="Ex: Próximo ao mercado..." maxlength="120" autocomplete="off">
                </div>
            </div>

        </div>

        <!-- Enviar:-->
        <input type="submit" name="Salvar" id="btn-salvar-endereco" value="Salvar Endereço">
    
    </form>
</main>
</div>

<!-- Lógica de máscaras e busca de CEP (ViaCEP) -->
<script src="../../assets/js/endereco.js"></script>
</body>
</html>