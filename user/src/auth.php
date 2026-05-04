<?php 
session_start();
include('../../config/config.php'); 

//modal bonitinho pra substituir o window alert feio.
function exibirModalEVoltar($titulo, $mensagem, $url, $icone = 'fa-circle-exclamation', $corIcone = '#e74c3c') {
    echo '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    </head>
    <body style="margin: 0; padding: 0; font-family: \'Inter\', sans-serif; background: #fdfaf6;">
        <div style="z-index: 99999; display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); justify-content: center; align-items: center; padding: 20px;">
            <div style="background: white; width: 380px; max-width: 100%; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <i class="fa-solid '.$icone.'" style="font-size: 3.5rem; color: '.$corIcone.'; margin-bottom: 15px;"></i>
                <h2 style="color: #3b2313; margin-top: 0; margin-bottom: 10px; font-size: 1.4rem;">'.$titulo.'</h2>
                <p style="color: #555; margin-bottom: 25px; line-height: 1.4; font-size: 0.95rem;">'.$mensagem.'</p>
                <button onclick="window.location.href=\''.$url.'\'" style="background: #3b2313; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;">OK, Entendi</button>
            </div>
        </div>
    </body>
    </html>
    ';
    exit;
}

        // 1. Vetores para Clientes
        $id=array();
        $logins=array();
        $number=array();
        $passwords=array();
        $nomes=array();

        $sqlSelect = "SELECT * FROM cliente";
        $resSelect = $conn->query($sqlSelect);
        if($resSelect && $resSelect->num_rows > 0){
           while($rowSelect = $resSelect->fetch_assoc()){
              array_push($id,$rowSelect['id_cliente']);
              array_push($logins, strtolower($rowSelect['email']));
              array_push($number,$rowSelect['telefone']);
              array_push($passwords,$rowSelect['senha']);
              array_push($nomes,$rowSelect['nome_cliente']);
           }
        }

        // 2. Vetores para Usuarios (Admins/Funcionarios)
        $admin_id=array();
        $admin_logins=array();
        $admin_passwords=array();
        $admin_nomes=array();
        $admin_tipos=array();
        $admin_roots=array();
        $admin_permissoes=array();

        $sqlAdmin = "SELECT * FROM usuario";
        $resAdmin = @$conn->query($sqlAdmin);
        if($resAdmin && $resAdmin->num_rows > 0){
           while($rowAdmin = $resAdmin->fetch_assoc()){
              array_push($admin_id, $rowAdmin['id_usuario']);
              array_push($admin_logins, strtolower($rowAdmin['email']));
              array_push($admin_passwords, $rowAdmin['senha']);
              array_push($admin_nomes, $rowAdmin['nome']);
              array_push($admin_tipos, $rowAdmin['tipo_usuario']);
              array_push($admin_roots, $rowAdmin['root']);
              // Usa a coluna permissoes do banco; se vazia, deriva do tipo como fallback
              $perm = isset($rowAdmin['permissoes']) && !empty($rowAdmin['permissoes'])
                  ? $rowAdmin['permissoes']
                  : (($rowAdmin['tipo_usuario'] === 'ADMIN' || $rowAdmin['root'] == 1)
                      ? 'pedidos,extrato,produtos,usuarios,config'
                      : 'pedidos,produtos');
              array_push($admin_permissoes, $perm);
           }
        }

     //----- CADASTRAR OFICIALMENTE:
switch (@$_REQUEST['acao']){
    case "Cadastrar":
        $nome     = $_POST['names'];
        $email    = $_POST['emails'];
        $telefone = $_POST['telefone'];
        $senha    = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        // Para onde ir depois (vem do redirect do carrinho, ou vazio = index)
        $redirectDestino = !empty($_POST['redirect']) ? htmlspecialchars_decode($_POST['redirect']) : 'index.php';

        //----- valida se email ou telefone ja existem
        if(!in_array($email,$logins) && !in_array($telefone,$number)){
            $sql = "INSERT INTO cliente (nome_cliente, email, senha, telefone) VALUES 
            ('$nome', '$email', '$senha', '$telefone')";
            $res = $conn->query($sql);

            if ($res) {
                // Pega o ID do cliente recém-criado e já loga direto
                $novoId = $conn->insert_id;

                $_SESSION['userlogado']  = $nome;
                $_SESSION['idlogado']    = $novoId;
                $_SESSION['idemail']     = $email;
                $_SESSION['idtelefone']  = $telefone;

                // Vai direto pro destino sem passar pelo login
                header("Location: ../" . $redirectDestino);
                exit;
            } else {
                exibirModalEVoltar('Erro', 'Erro no banco de dados: ' . addslashes($conn->error), 'javascript:window.history.back()');
            }  
        } else {
           exibirModalEVoltar('Atenção', 'Usuário já cadastrado na base de dados.', 'javascript:window.history.back()');
        }
        break;
        
    //LOGIN:
   case "Logar":
    $logar = strtolower(trim($_POST['email'])); // normaliza pra minusculo
    $pass = $_POST['senhaL'];
  
    // Primeiro tenta logar como CLIENTE
    if(in_array($logar,$logins)){
        $index = array_search($logar,$logins);
        if ($index>=0){
            if (password_verify($pass,$passwords[$index])){
                $_SESSION['userlogado']= $nomes[$index];
                $_SESSION['idlogado']= $id[$index];
                $_SESSION['idemail'] = $logins[$index];
                $_SESSION['idtelefone'] =$number[$index];
                
                $redirecionar = $_POST['redirect'];
                if($redirecionar == ""){ $redirecionar = "index.php"; }
                header("Location: ../" . $redirecionar);
                exit;
            }else{
                exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o cliente.', 'javascript:window.history.back()');
            }
        }
    } 
    // Se não achou em cliente, tenta logar como ADMIN / FUNCIONARIO
    elseif (in_array($logar, $admin_logins)) {
        $indexAdmin = array_search($logar, $admin_logins);
        if ($indexAdmin >= 0) {
            if (password_verify($pass, $admin_passwords[$indexAdmin])){
                $_SESSION['admin_nome'] = $admin_nomes[$indexAdmin];
                $_SESSION['admin_id']   = $admin_id[$indexAdmin];
                $_SESSION['admin_tipo'] = $admin_tipos[$indexAdmin]; // ADMIN ou FUNCIONARIO

                // Usa diretamente a coluna permissoes do banco (ja com fallback na carga)
                $_SESSION['admin_permissoes'] = $admin_permissoes[$indexAdmin];

                header("Location: ../../admin/index.php");
                exit;
            } else {
                exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o administrador.', 'javascript:window.history.back()');
            }
        }
    } 
    // Se não achou em lugar nenhum
    else {
         exibirModalEVoltar('Atenção', 'Conta não cadastrada na base de dados.', '../cadastro.php');
    }
    break; 
}
?>