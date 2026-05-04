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

        //vetores vazios que vão ser preenchido ao carregar a pagina pelo banco de dados atual
        $id=array();
        $logins=array();
        $number=array();
        $passwords=array();
        $nomes=array();

        //queries seleção geral para validar se banco de dados ou uma conta.
        $sqlSelect = "SELECT * FROM cliente";
        $resSelect = $conn->query($sqlSelect);
        $Select = $resSelect->num_rows;
        
        
        if($Select>0){
           while( $rowSelect = $resSelect->fetch_assoc()){
              array_push($id,$rowSelect['id_cliente']);
              array_push($logins,$rowSelect['email']);
              array_push($number,$rowSelect['telefone']);
              array_push($passwords,$rowSelect['senha']);
              array_push($nomes,$rowSelect['nome_cliente']);
             
           }
        }

     //----- CADASTRAR OFICIALMENTE:
switch (@$_REQUEST['acao']){
    case "Cadastrar":
        $nome = $_POST['names'];
        $email = $_POST['emails'];
        $telefone = $_POST['telefone'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        

       
        
        //----- validação pra ver se o que digitou em cadastro ja existe
        if(!in_array($email,$logins)&&!in_array($telefone,$number)){
            $sql = "INSERT INTO cliente (nome_cliente, email, senha, telefone) VALUES 
            ('$nome', '$email', '$senha', '$telefone')";
            $res = $conn->query($sql);

            if ($res) {
                exibirModalEVoltar('Sucesso!', 'Cadastro realizado com sucesso!', '../login.php', 'fa-check-circle', '#2ecc71');
            } else {
                exibirModalEVoltar('Erro', 'Erro no banco de dados: ' . addslashes($conn->error), 'javascript:window.history.back()');
            }  
        }else{
           exibirModalEVoltar('Atenção', 'Usuário já cadastrado na base de dados.', '../login.php');
        }
        break;
        
    //LOGIN:
   case "Logar":
    $logar = $_POST['email'];
    $pass = $_POST['senhaL'];
  
    if(in_array($logar,$logins)){
$index = array_search($logar,$logins);

if ($index>=0){
    if (password_verify($pass,$passwords[$index])){
        $_SESSION['userlogado']= $nomes[$index];
        $_SESSION['idlogado']= $id[$index];
        $_SESSION['idemail'] = $logins[$index];
        $_SESSION['idtelefone'] =$number[$index];
        header("Location:../carrinho.php");
        exit;
    }else{
         exibirModalEVoltar('Acesso Negado', 'Usuário ou senha inválido(s).', '../login.php');
    }

}
    }else{
         exibirModalEVoltar('Atenção', 'Cliente não cadastrado na base de dados.', '../cadastro.php');
    }
    break; 
}
?>