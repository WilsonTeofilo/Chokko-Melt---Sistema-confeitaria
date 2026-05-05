<?php 
session_start();
include('../../../config/config.php');
include('../../../includes/modal.php'); // exibirModalEVoltar() centralizada

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
                header("Location: ../../" . $redirectDestino);
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
                header("Location: ../../" . $redirecionar);
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

                header("Location: ../../../admin/index.php");
                exit;
            } else {
                exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o administrador.', 'javascript:window.history.back()');
            }
        }
    } 
    // Se não achou em lugar nenhum
    else {
         exibirModalEVoltar('Atenção', 'Conta não cadastrada na base de dados.', '../../cadastro.php');
    }
    break; 
}
?>