<?php
session_start();
// Proteção básica: somente administradores logados
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../user/login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../classes/Produto.php';
require_once '../../classes/Categoria.php';

// Modal de alerta no padrão UI do TCC (cópia fiel de auth_admin.php para consistência visual)
function exibirModalEVoltar($titulo, $mensagem, $url, $icone = 'fa-circle-exclamation', $corIcone = '#e74c3c') {
    echo '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: "Inter", sans-serif; background: #fafafa; margin:0; height:100vh; display:flex; align-items:center; justify-content:center; }
            .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.55); backdrop-filter: blur(2px); display: flex; align-items: center; justify-content: center; padding: 20px; z-index: 99999; }
            .modal-content { width: 420px; max-width: 100%; background: #fff; border-radius: 14px; box-shadow: 0 12px 28px rgba(0,0,0,.25); padding: 22px; }
            .modal-header-flex { display: flex; gap: 12px; align-items: flex-start; }
            .icon-box { width: 42px; height: 42px; border-radius: 10px; background: #FAF7F5; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
            .icon-box i { color: '.$corIcone.'; font-size: 1.2rem; }
            .text-box { flex: 1; }
            .text-box h3 { margin: 0; color: #3b2313; font-size: 1.05rem; }
            .text-box p { margin: 8px 0 0; color: #555; line-height: 1.35; font-size: .92rem; white-space: pre-line; }
            .modal-footer { margin-top: 18px; display: flex; justify-content: flex-end; }
            .btn-ok { background: #3b2313; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        </style>
    </head>
    <body>
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-flex">
                    <div class="icon-box"><i class="fa-solid '.$icone.'"></i></div>
                    <div class="text-box">
                        <h3>'.$titulo.'</h3>
                        <p>'.$mensagem.'</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="'.$url.'" class="btn-ok">OK, Entendi</a>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

$prod = new Produto(DATABASE, HOST, USER, PASS);
$catClass = new Categoria(DATABASE, HOST, USER, PASS);

$acao = filter_input(INPUT_GET, 'acao', FILTER_DEFAULT);
if ($acao === null) {
    $acao = filter_input(INPUT_POST, 'acao', FILTER_DEFAULT);
}
$acao = $acao !== null ? trim($acao) : '';

switch ($acao) {
    case 'salvar':
        $id = filter_input(INPUT_POST, 'id_produto', FILTER_SANITIZE_NUMBER_INT);
        $id = $id !== null ? trim($id) : '';

        $nome = filter_input(INPUT_POST, 'nome', FILTER_DEFAULT);
        $nome = $nome !== null ? trim($nome) : '';

        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_DEFAULT);
        $descricao = $descricao !== null ? trim($descricao) : '';

        $disponibilidade = filter_input(INPUT_POST, 'disponibilidade', FILTER_DEFAULT);
        $dispo = ($disponibilidade === 'ativo') ? 1 : 0;
        
        // Conversão amigável de preço de Real (formato brasileiro 0,00) para Float
        $precoStr = filter_input(INPUT_POST, 'preco', FILTER_DEFAULT);
        $precoStr = $precoStr !== null ? trim($precoStr) : '';
        $precoStr = str_replace('.', '', $precoStr); // remove pontos de milhar
        $precoStr = str_replace(',', '.', $precoStr); // converte vírgula decimal para ponto
        $preco = floatval($precoStr);

        $custoStr = filter_input(INPUT_POST, 'custo_compra', FILTER_DEFAULT);
        $custoStr = $custoStr !== null ? trim($custoStr) : '';
        $custoStr = str_replace('.', '', $custoStr);
        $custoStr = str_replace(',', '.', $custoStr);
        $custo = floatval($custoStr);

        $categoriaNome = filter_input(INPUT_POST, 'categoria', FILTER_DEFAULT);
        $categoriaNome = $categoriaNome !== null ? trim($categoriaNome) : '';

        if (empty($nome)) {
            exibirModalEVoltar('Erro de Validação', 'O nome do produto é obrigatório.', 'javascript:window.history.back()');
        }
        if (strlen($nome) > 50) {
            exibirModalEVoltar('Erro de Validação', 'O nome do produto deve ter no máximo 50 caracteres.', 'javascript:window.history.back()');
        }
        if ($preco <= 0) {
            exibirModalEVoltar('Erro de Validação', 'O preço de venda do produto deve ser maior que zero.', 'javascript:window.history.back()');
        }
        if ($custo <= 0) {
            exibirModalEVoltar('Erro de Validação', 'O custo de compra do produto é obrigatório e deve ser maior que zero.', 'javascript:window.history.back()');
        }
        if (strlen($descricao) > 500) {
            exibirModalEVoltar('Erro de Validação', 'A descrição deve ter no máximo 500 caracteres.', 'javascript:window.history.back()');
        }

        // Resolvendo o ID da Categoria a partir do Nome da Categoria selecionada
        $id_categoria = null;
        if (!empty($categoriaNome)) {
            $todasCategorias = $catClass->listarTodas();
            foreach ($todasCategorias as $c) {
                if (strcasecmp($c['nome'], $categoriaNome) === 0) {
                    $id_categoria = $c['id_categoria'];
                    break;
                }
            }

            // Se for uma categoria nova, cadastra ela no banco e pega o ID recém-gerado
            if (empty($id_categoria)) {
                $id_categoria = $catClass->cadastrar($categoriaNome);
            }
        }

        // Upload de Imagem do Produto
        $imgLink = '';
        
        // Se for uma edição, buscamos o produto para saber qual é a foto antiga (se o usuário não trocar)
        if (!empty($id)) {
            try {
                $prodAtual = $prod->buscarPorId($id);
                if ($prodAtual) {
                    $imgLink = $prodAtual['imagem'];
                }
            } catch (Exception $e) {
                // Sem imagem prévia
            }
        }

        // Verifica se uma nova imagem foi enviada pelo formulário
        if (isset($_FILES['imagem_produto']) && $_FILES['imagem_produto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['imagem_produto']['tmp_name'];
            $fileName = $_FILES['imagem_produto']['name'];
            
            // Extrai a extensão e gera um nome de arquivo seguro/único
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExtension;
            
            // Pasta física onde as fotos serão salvas dentro do servidor
            // Como este arquivo roda em admin/src, o caminho da pasta admin/uploads/ é "../uploads/"
            $uploadFileDir = '../uploads/';
            
            // Se a pasta não existir no seu XAMPP, ela será criada automaticamente
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            // Transfere o arquivo para a pasta de uploads do painel admin
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Guardamos a URL relativa ("uploads/nome_da_foto.jpg") para que
                // tanto o painel admin quanto a área do cliente carreguem a foto
                $imgLink = 'uploads/' . $newFileName;
            }
        }

        try {
            if (empty($id)) {
                // Novo Produto
                $prod->Cadastrar($nome, $descricao, $dispo, $imgLink, $preco, $custo, $id_categoria);
                exibirModalEVoltar('Sucesso!', 'Produto cadastrado com sucesso!', '../produtos.php', 'fa-check-circle', '#2ecc71');
            } else {
                // Editar Produto Existente
                $prod->atualizar($id, $nome, $descricao, $dispo, $imgLink, $preco, $custo, $id_categoria);
                exibirModalEVoltar('Sucesso!', 'Produto atualizado com sucesso!', '../produtos.php', 'fa-check-circle', '#2ecc71');
            }
        } catch (Exception $e) {
            exibirModalEVoltar('Erro no Banco de Dados', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;

    case 'excluir':
        $id = (int)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id <= 0) {
            exibirModalEVoltar('Erro de Parâmetro', 'ID de produto inválido.', '../produtos.php');
        }

        try {
            $prod->excluir($id);
            exibirModalEVoltar('Sucesso!', 'Produto removido com sucesso!', '../produtos.php', 'fa-trash-can', '#2ecc71');
        } catch (Exception $e) {
            exibirModalEVoltar('Erro ao excluir', $e->getMessage(), '../produtos.php');
        }
        break;

    case 'alterarStatus':
        $id = (int)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $status = (int)filter_input(INPUT_GET, 'status', FILTER_SANITIZE_NUMBER_INT);
        if ($id <= 0) {
            exibirModalEVoltar('Erro de Parâmetro', 'ID de produto inválido.', '../produtos.php');
        }

        try {
            $prodAtual = $prod->buscarPorId($id);
            if ($prodAtual) {
                $prod->atualizar(
                    $id,
                    $prodAtual['nome'],
                    $prodAtual['descricao'],
                    $status,
                    $prodAtual['imagem'],
                    $prodAtual['preco'],
                    $prodAtual['custo_compra'],
                    $prodAtual['id_categoria']
                );
            }
            exibirModalEVoltar('Sucesso!', 'Status do produto atualizado!', '../produtos.php', 'fa-rotate', '#2ecc71');
        } catch (Exception $e) {
            exibirModalEVoltar('Erro ao atualizar status', $e->getMessage(), '../produtos.php');
        }
        break;

    default:
        header("Location: ../produtos.php");
        exit;
}
