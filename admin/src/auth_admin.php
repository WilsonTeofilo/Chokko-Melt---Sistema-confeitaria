<?php 
session_start();
include('../../config/config.php'); 

// Modal de alerta seguindo seu padrão UI
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

// Vetores vazios que vão ser preenchidos ao carregar pelo BD pra conferência
$logins = array();
$number = array();

// 1. Puxa todos da tabela CLIENTE
$sqlCliente = "SELECT * FROM cliente";
$resCliente = $conn->query($sqlCliente);
if($resCliente && $resCliente->num_rows > 0){
   while($row = $resCliente->fetch_assoc()){
      array_push($logins, $row['email']);
      array_push($number, $row['telefone']);
   }
}

// 2. Puxa todos da tabela USUARIO (Admins/Funcionários)
// O @ evita Warning se a tabela não existir ainda na hora de testar
$sqlUsuario = "SELECT * FROM usuario";
$resUsuario = @$conn->query($sqlUsuario);
if($resUsuario && $resUsuario->num_rows > 0){
   while($row = $resUsuario->fetch_assoc()){
      array_push($logins, $row['email']);
      array_push($number, $row['telefone']);
   }
}


switch (@$_REQUEST['acao']){

    //----- CADASTRAR NOVO USUÁRIO ADMIN:
    case "CadastrarUsuario":
        $nome = $_POST['usr_nome'];
        $email = $_POST['usr_email'];
        // Limpar telefone mantendo só números se vier máscara, 
        // ou você pode salvar com a máscara, usando o padrão que preferir.
        $telefone = preg_replace('/[^0-9]/', '', $_POST['usr_telefone']);
        $senha = password_hash($_POST['usr_senha'], PASSWORD_DEFAULT);
        $tipo = $_POST['usr_tipo'];
        
        // Pega os checkboxes marcados e transforma em string (só pro nosso controle interno)
        $permissoes = isset($_POST['permissoes']) ? $_POST['permissoes'] : array();
        
        // Na sua tabela não tem a coluna 'permissoes', mas tem a coluna 'root'
        // Se for ADMIN, ele é root.
        $is_root = ($tipo === 'ADMIN') ? 1 : 0;

        // Validação MÁXIMA: O e-mail e telefone não podem existir nem em cliente nem em usuário!
        if(!in_array($email, $logins) && !in_array($telefone, $number)){
            
        // O INSERT agora inclui a coluna permissoes
        $permissoes_string = ($tipo === 'ADMIN') ? 'pedidos,extrato,produtos,usuarios,config' : 'pedidos,produtos';
        $sql = "INSERT INTO usuario (nome, email, telefone, senha, tipo_usuario, root, permissoes) VALUES 
        ('$nome', '$email', '$telefone', '$senha', '$tipo', $is_root, '$permissoes_string')";
        
        $res = @$conn->query($sql);

            if ($res) {
                exibirModalEVoltar('Sucesso!', 'Usuário administrativo cadastrado com sucesso!', '../usuarios.php', 'fa-check-circle', '#2ecc71');
            } else {
                exibirModalEVoltar('Erro SQL', 'Verifique a tabela: ' . addslashes($conn->error), 'javascript:window.history.back()');
            }  
        } else {
            exibirModalEVoltar('Conflito de Dados', 'Esse e-mail ou número de telefone já pertence a uma conta (pode ser de um cliente ou de outro administrador).', 'javascript:window.history.back()');
        }
        break;


    //----- LOGAR ADMIN:
    case "LogarAdmin":
        $logar = $_POST['email'];
        $pass = $_POST['senha'];
      
        // Mesma lógica de vetores separados que você usa:
        $emailsAdmin = array();
        $senhasAdmin = array();
        $idsAdmin = array();
        $tiposAdmin = array();
        $permsAdmin = array();
        $nomesAdmin = array();

        // Como já foi consultado lá em cima e consumido, precisamos dar um data_seek ou rodar de novo:
        if($resUsuario && $resUsuario->num_rows > 0){
            $resUsuario->data_seek(0);
            while($rowAdmin = $resUsuario->fetch_assoc()){
                array_push($emailsAdmin, $rowAdmin['email']);
                array_push($senhasAdmin, $rowAdmin['senha']);
                array_push($idsAdmin, $rowAdmin['id_usuario']); 
                array_push($tiposAdmin, $rowAdmin['tipo_usuario']);
                array_push($nomesAdmin, $rowAdmin['nome']);
                
                // Pra fingir as permissões já que não tem a coluna, a gente checa se é root
                $permString = ($rowAdmin['tipo_usuario'] === 'ADMIN' || $rowAdmin['root'] == 1) 
                    ? 'pedidos,extrato,produtos,usuarios,config' 
                    : 'pedidos,produtos';
                array_push($permsAdmin, $permString);
            }
        }
      
        if(in_array($logar, $emailsAdmin)){
            $index = array_search($logar, $emailsAdmin);
            if ($index !== false && $index >= 0){
                if(password_verify($pass, $senhasAdmin[$index])){
                    // Sucesso!
                    $_SESSION['adminlogado'] = true;
                    $_SESSION['admin_id'] = $idsAdmin[$index];
                    $_SESSION['admin_nome'] = $nomesAdmin[$index];
                    $_SESSION['admin_tipo'] = $tiposAdmin[$index];
                    $_SESSION['admin_permissoes'] = $permsAdmin[$index];

                    header("Location: ../index.php");
                    exit;
                } else {
                    exibirModalEVoltar('Acesso Negado', 'Senha inválida.', 'javascript:window.history.back()');
                }
            }
        } else {
            exibirModalEVoltar('Atenção', 'Administrador não encontrado na base de dados.', 'javascript:window.history.back()');
        }
        break;

    case "ExcluirUsuario":
        if (!isset($_GET['id'])) {
            exibirModalEVoltar('Erro', 'ID do usuário não fornecido.', 'javascript:window.history.back()');
            exit;
        }

        $idDeletar = intval($_GET['id']);
        
        // Proteção pra não excluir a si mesmo
        if (isset($_SESSION['admin_id']) && $idDeletar == $_SESSION['admin_id']) {
            exibirModalEVoltar('Atenção', 'Você não pode excluir a sua própria conta!', 'javascript:window.history.back()');
        } else {
            $sqlDelete = "DELETE FROM usuario WHERE id_usuario = $idDeletar";
            if ($conn->query($sqlDelete)) {
                exibirModalEVoltar('Sucesso', 'Usuário excluído com sucesso do banco de dados.', '../usuarios.php', 'fa-check-circle', '#2ecc71');
            } else {
                exibirModalEVoltar('Erro', 'Não foi possível excluir o usuário.', 'javascript:window.history.back()');
            }
        }
        break;

    case "AtualizarPermissoes":
        $idAtualizar  = intval($_POST['id_usuario']);
        $novoTipo     = $_POST['tipo_usuario'] === 'ADMIN' ? 'ADMIN' : 'FUNCIONARIO';
        $novoRoot     = ($novoTipo === 'ADMIN') ? 1 : 0;
        $novasPerms   = isset($_POST['permissoes']) ? $_POST['permissoes'] : '';

        $sqlUpdate = "UPDATE usuario SET tipo_usuario = '$novoTipo', root = $novoRoot, permissoes = '$novasPerms' WHERE id_usuario = $idAtualizar";
        if ($conn->query($sqlUpdate)) {
            exibirModalEVoltar('Sucesso', 'Permissoes atualizadas com sucesso!', '../usuarios.php', 'fa-check-circle', '#2ecc71');
        } else {
            exibirModalEVoltar('Erro', 'Nao foi possivel atualizar as permissoes.', 'javascript:window.history.back()');
        }
        break;
}
?>
