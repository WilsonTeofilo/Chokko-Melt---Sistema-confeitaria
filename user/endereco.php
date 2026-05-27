<?php
session_start();

// Segurança: Se não estiver logado, redireciona
if (!isset($_SESSION['idlogado'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/config.php';
require_once '../classes/Endereco.php';

$id_cliente = $_SESSION['idlogado'];
$enderecoCRUD = new Endereco(DATABASE, HOST, USER, PASS);

// ── BAIRROS PERMITIDOS (apenas Grajaú/SP) ──────────────────────────────────
// Somente esses bairros são aceitos. Qualquer outro valor é rejeitado.
$bairros_permitidos = [
    'Balsa',
    'BNH',
    'Cantinho do Ceu',
    'Chacara Cocaia',
    'Chacara do Conde',
    'Chacara do Sol',
    'Chacara Monte Sol',
    'Cidade Dutra',
    'Condomínio Palmares',
    'Conjunto Habitacional Faria Lima',
    'Jardim Almeida Prado',
    'Jardim Alpino',
    'Jardim Angelina',
    'Jardim Aristocrata',
    'Jardim Azano',
    'Jardim Beatriz',
    'Jardim Belcito',
    'Jardim Campinas',
    'Jardim Casa Grande',
    'Jardim Castro Alves',
    'Jardim Clipper',
    'Jardim Colibri',
    'Jardim Colonial',
    'Jardim das Embuias',
    'Jardim das Pedras',
    'Jardim dos Manacas',
    'Jardim Edda',
    'Jardim Edi',
    'Jardim Eliana',
    'Jardim Ellus',
    'Jardim Gaivotas',
    'Jardim Guanhembu',
    'Jardim Icarai',
    'Jardim Ideal',
    'Jardim Iporã',
    'Jardim Iporanga',
    'Jardim Itajai',
    'Jardim Itatiaia',
    'Jardim Kika',
    'Jardim Kioto',
    'Jardim Lallo',
    'Jardim Lucelia',
    'Jardim Malia',
    'Jardim Maria Amalia',
    'Jardim Maria Rita',
    'Jardim Marilda',
    'Jardim Monte Verde',
    'Jardim Moraes Prado',
    'Jardim Myrna',
    'Jardim Noronha',
    'Jardim Novo Horizonte',
    'Jardim Novo Jau',
    'Jardim Orbã',
    'Jardim Porto Velho',
    'Jardim Prainha',
    'Jardim Presidente',
    'Jardim Progresso',
    'Jardim Ramala',
    'Jardim Regis',
    'Jardim Reimberg',
    'Jardim Represa',
    'Jardim Sabia',
    'Jardim Santa Fe',
    'Jardim São Bernardo',
    'Jardim São Judas Tadeu',
    'Jardim São Pedro',
    'Jardim Sao Rafael',
    'Jardim Satélite',
    'Jardim Sete de Setembro',
    'Jardim Shangrila',
    'Jardim Sipramar',
    'Jardim Somara',
    'Jardim Tanay',
    'Jardim Toca',
    'Jardim Três Corações',
    'Jardim Varginha',
    'Jardim Zilda',
    'Jordanópolis',
    'Lago Azul',
    'Palmares',
    'Parque America',
    'Parque Brasil',
    'Parque Cocaia',
    'Parque das Arvores',
    'Parque Deizy',
    'Parque Grajau',
    'Parque Maria Fernandes',
    'Parque Novo Grajau',
    'Parque Planalto',
    'Parque Residencial Cocaia',
    'Parque Residencial dos Lagos',
    'Parque Santa Cecilia',
    'Parque São Jose',
    'Parque São Miguel',
    'Parque São Paulo',
    'Rio Bonito',
    'Terceira Divisão de Interlagos',
    'Vila Narciso',
    'Vila Nascente',
    'Vila Natal',
    'Vila Rubi',
    'Vila São Jose'
];
sort($bairros_permitidos); // Mantém em ordem alfabética

// Variáveis para preencher o formulário (em caso de edição ou erro)
$dados_form = [
    'id_endereco' => '',
    'cep' => '',
    'rua' => '',
    'numero' => '',
    'bairro' => '',
    'complemento' => '',
    'ponto_referencia' => ''
];

$erro = "";
$sucesso = "";
$modo_edicao = false;

// 1. PROCESSAR EXCLUSÃO (via GET)
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id_endereco = (int)$_GET['id'];
    try {
        $enderecoCRUD->excluir($id_endereco, $id_cliente);
        header("Location: perfil.php?msg=Endereço excluído com sucesso");
        exit;
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// 2. PROCESSAR CARREGAMENTO DE EDIÇÃO (via GET)
if (isset($_GET['id']) && empty($_POST)) {
    $id_endereco = (int)$_GET['id'];
    try {
        $end_existente = $enderecoCRUD->buscarPorId($id_endereco, $id_cliente);
        if ($end_existente) {
            $modo_edicao = true;
            $dados_form = $end_existente;
        } else {
            $erro = "Endereço não encontrado ou você não tem permissão para acessá-lo.";
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// 3. PROCESSAR FORMULÁRIO (POST - Cadastrar ou Editar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_endereco_post = isset($_POST['id_endereco']) ? (int)$_POST['id_endereco'] : 0;
    
    // Captura os dados
    $bairro_post = trim($_POST['bairro'] ?? '');

    $dados = [
        'cep'             => trim($_POST['cep'] ?? ''),
        'rua'             => trim($_POST['rua'] ?? ''),
        'numero'          => trim($_POST['numero'] ?? ''),
        'bairro'          => $bairro_post,
        'complemento'     => trim($_POST['complemento'] ?? ''),
        'ponto_referencia'=> trim($_POST['ponto_referencia'] ?? '')
    ];

    // Mantém os dados preenchidos em caso de erro
    $dados_form = $dados;
    $dados_form['id_endereco'] = $id_endereco_post;

    // Valida se o bairro enviado é um dos permitidos
    if (!in_array($bairro_post, $bairros_permitidos)) {
        $erro = "Bairro inválido. Por favor, selecione um bairro da lista.";
        if ($id_endereco_post > 0) $modo_edicao = true;
    }

    if (empty($erro) && !empty($dados['cep']) && !empty($dados['rua']) && !empty($dados['numero']) && !empty($dados['bairro'])) {
        try {
            if ($id_endereco_post > 0) {
                // EDIÇÃO
                $enderecoCRUD->editar($id_endereco_post, $id_cliente, $dados);
                $sucesso = "Endereço atualizado com sucesso!";
            } else {
                // CADASTRO
                $enderecoCRUD->cadastrar($id_cliente, $dados);
                $sucesso = "Endereço cadastrado com sucesso!";
            }
            // Redireciona de volta ao perfil após sucesso
            header("Location: perfil.php?msg=" . urlencode($sucesso));
            exit;
        } catch (Exception $e) {
            $erro = $e->getMessage();
            if ($id_endereco_post > 0) $modo_edicao = true;
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios.";
        if ($id_endereco_post > 0) $modo_edicao = true;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/endereco.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title><?= $modo_edicao ? 'Editar Endereço' : 'Cadastrar Endereço' ?> — Chokko Melt</title>
</head>
<body>

<!-- ── PAINEL ESQUERDO (só visível no desktop) ── -->
<div class="painel-esquerdo">
    <a href="index.php" class="logo-painel">Chokko<span> Melt</span></a>
    <i class="fa-solid fa-map-location-dot icone-mapa"></i>
    <h2><?= $modo_edicao ? 'Atualizar local<br>de entrega' : 'Onde entregamos<br>sua felicidade?' ?></h2>
    <p><?= $modo_edicao ? 'Mantenha seus dados atualizados para não atrasar a sua sobremesa.' : 'Cadastre um endereço de entrega e receba nossas delícias artesanais direto na sua porta. 🍫' ?></p>
</div>

<!-- ── PAINEL DIREITO com o formulário ── -->
<div class="painel-direito">
<main id="form-container">
    <div id="form-header">
        <h1 id="form-title"><?= $modo_edicao ? 'Editar Endereço' : 'Novo Endereço' ?></h1>
        <button type="button" id="btn-back" onclick="window.history.back()">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
    </div>
    
    <?php if(!empty($erro)): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em; border-left: 4px solid #c62828;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        <input type="hidden" name="id_endereco" value="<?= htmlspecialchars($dados_form['id_endereco']) ?>">
        
        <div id="input_container">
            
            <!-- CEP:-->
            <div class="input-box">
                <label for="cepJS" class="form-label">CEP</label>
                <div class="input-field"><i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="cep" id="cepJS" value="<?= htmlspecialchars($dados_form['cep']) ?>" placeholder="00000-000" maxlength="10" required autocomplete="off">
                </div>
            </div>

            <!-- Rua:-->
            <div class="input-box">
                <label for="ruaJS" class="form-label">Rua</label>
                <div class="input-field"> <i class="fa-solid fa-road"></i>
                    <input type="text" name="rua" id="ruaJS" value="<?= htmlspecialchars($dados_form['rua']) ?>" placeholder="Ex: Av. Paulista" maxlength="50" required autocomplete="off">
                </div>
            </div>

            <!-- Número e Bairro lado a lado -->
            <div class="input-row">
                <div class="input-box">
                    <label for="numeroJS" class="form-label">Número</label>
                    <div class="input-field"> <i class="fa-solid fa-hashtag"></i>
                        <input type="text" name="numero" id="numeroJS" value="<?= htmlspecialchars($dados_form['numero']) ?>" placeholder="Ex: 123" maxlength="10" required autocomplete="off">
                    </div>
                </div>

                <!-- Bairro (select fixo — apenas bairros do Grajaú/SP) -->
                <div class="input-box">
                    <label for="bairroJS" class="form-label">Bairro</label>
                    <div class="input-field input-field-select">
                        <i class="fa-solid fa-tree-city"></i>
                        <?php $bairro_valido = in_array($dados_form['bairro'], $bairros_permitidos); ?>
                        <select name="bairro" id="bairroJS" required>
                            <option value="" disabled <?= !$bairro_valido ? 'selected' : '' ?>>Selecione o bairro</option>
                            <?php foreach ($bairros_permitidos as $bairro_opt): ?>
                                <option value="<?= htmlspecialchars($bairro_opt) ?>"
                                    <?= ($dados_form['bairro'] === $bairro_opt) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($bairro_opt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <small class="bairro-hint"><i class="fa-solid fa-circle-info"></i> Apenas bairros do Grajaú/SP são atendidos</small>
                </div>
            </div>


            <!-- Complemento:-->
            <div class="input-box">
                <label for="complementoJS" class="form-label">Complemento (Opcional)</label>
                <div class="input-field">
                    <i class="fa-solid fa-building"></i>
                    <input type="text" name="complemento" id="complementoJS" value="<?= htmlspecialchars($dados_form['complemento']) ?>" placeholder="Ex: Apto 45, Bloco B" maxlength="50" autocomplete="off">
                </div>
            </div>

            <!-- Ponto de Referência:-->
            <div class="input-box">
                <label for="pontoReferenciaJS" class="form-label">Ponto de Referência (Opcional)</label>
                <div class="input-field">
                    <i class="fa-solid fa-location-crosshairs"></i>
                    <input type="text" name="ponto_referencia" id="pontoReferenciaJS" value="<?= htmlspecialchars($dados_form['ponto_referencia']) ?>" placeholder="Ex: Próximo ao mercado..." maxlength="120" autocomplete="off">
                </div>
            </div>

        </div>

        <!-- Enviar:-->
        <input type="submit" name="Salvar" id="btn-salvar-endereco" value="<?= $modo_edicao ? 'Atualizar Endereço' : 'Salvar Endereço' ?>">
    
    </form>
</main>
</div>

<!-- Lógica de máscaras e busca de CEP (ViaCEP) -->
<script src="assets/js/endereco.js"></script>
</body>
</html>
