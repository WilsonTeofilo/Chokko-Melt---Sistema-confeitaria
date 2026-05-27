<?php 
session_start();
require_once '../../config/config.php'; 
require_once '../../classes/Auth.php';

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

$auth = new Auth(DATABASE, HOST, USER, PASS);
switch (isset($_REQUEST['acao']) ? $_REQUEST['acao'] : ''){

    //----- CADASTRAR NOVO USUÁRIO ADMIN:
    case "CadastrarUsuario":
        $nome = isset($_POST['usr_nome']) ? trim($_POST['usr_nome']) : '';
        $email = isset($_POST['usr_email']) ? strtolower(trim($_POST['usr_email'])) : '';
        $telefone = isset($_POST['usr_telefone']) ? preg_replace('/[^0-9]/', '', $_POST['usr_telefone']) : '';
        $senha = isset($_POST['usr_senha']) ? $_POST['usr_senha'] : '';
        $tipo = isset($_POST['usr_tipo']) ? $_POST['usr_tipo'] : '';
        
        try {
            $auth->cadastrarAdmin($nome, $email, $telefone, $senha, $tipo);
            exibirModalEVoltar('Sucesso!', 'Usuário administrativo cadastrado com sucesso!', '../usuarios.php', 'fa-check-circle', '#2ecc71');
        } catch (Exception $e) {
            exibirModalEVoltar('Conflito de Dados', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;


    //----- LOGAR ADMIN (LEGADO / RETIDO PARA COMPATIBILIDADE SE HOUVER ACESSO DIRETO):
    case "LogarAdmin":
        $logar = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $pass = isset($_POST['senha']) ? $_POST['senha'] : '';
      
        try {
            $admin = $auth->buscarAdminPorEmail($logar);
            if ($admin && password_verify($pass, $admin['senha'])) {
                $_SESSION['adminlogado'] = true;
                $_SESSION['admin_id'] = $admin['id_usuario'];
                $_SESSION['admin_nome'] = $admin['nome'];
                $_SESSION['admin_tipo'] = $admin['tipo_usuario'];
                
                $permString = !empty($admin['permissoes']) 
                    ? $admin['permissoes'] 
                    : (($admin['tipo_usuario'] === 'ADMIN' || $admin['root'] == 1) 
                        ? 'pedidos,extrato,produtos,usuarios,config' 
                        : 'pedidos,produtos');
                    
                $_SESSION['admin_permissoes'] = $permString;
    
                header("Location: ../index.php");
                exit;
            } else {
                exibirModalEVoltar('Acesso Negado', 'E-mail ou senha inválidos.', 'javascript:window.history.back()');
            }
        } catch(Exception $e) {
            exibirModalEVoltar('Atenção', $e->getMessage(), 'javascript:window.history.back()');
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
            try {
                $auth->excluirAdmin($idDeletar);
                exibirModalEVoltar('Sucesso', 'Usuário excluído com sucesso do banco de dados.', '../usuarios.php', 'fa-check-circle', '#2ecc71');
            } catch(Exception $e) {
                exibirModalEVoltar('Erro', 'Não foi possível excluir o usuário: ' . $e->getMessage(), 'javascript:window.history.back()');
            }
        }
        break;

    case "AtualizarPermissoes":
        $idAtualizar  = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
        $novoTipo     = (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] === 'ADMIN') ? 'ADMIN' : 'FUNCIONARIO';
        $novasPerms   = isset($_POST['permissoes']) ? $_POST['permissoes'] : '';

        try {
            $auth->atualizarPermissoesAdmin($idAtualizar, $novoTipo, $novasPerms);
            exibirModalEVoltar('Sucesso', 'Permissões atualizadas com sucesso!', '../usuarios.php', 'fa-check-circle', '#2ecc71');
        } catch(Exception $e) {
            exibirModalEVoltar('Erro', 'Não foi possível atualizar as permissões: ' . $e->getMessage(), 'javascript:window.history.back()');
        }
        break;
}
?>
