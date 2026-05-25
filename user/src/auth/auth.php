<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../classes/Auth.php';
require_once '../../../includes/modal.php'; // exibirModalEVoltar() centralizada

$auth = new Auth(DATABASE, HOST, USER, PASS);

switch (@$_REQUEST['acao']) {
    case 'Cadastrar':
        $nome     = $_POST['names'];
        $email    = strtolower(trim($_POST['emails']));
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $senha    = $_POST['senha'];

        // Destino do redirecionamento
        $redirectDestino = !empty($_POST['redirect']) ? htmlspecialchars_decode($_POST['redirect']) : 'index.php';

        try {
            $novoId = $auth->cadastrarCliente($nome, $email, $telefone, $senha);
            if ($novoId) {
                // Autentica automaticamente o cliente cadastrado
                $_SESSION['userlogado']  = $nome;
                $_SESSION['idlogado']    = $novoId;
                $_SESSION['idemail']     = $email;
                $_SESSION['idtelefone']  = $telefone;

                header("Location: ../../" . $redirectDestino);
                exit;
            }
        } catch (Exception $e) {
            exibirModalEVoltar('Atenção', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;

    case 'Logar':
        $email = strtolower(trim($_POST['email']));
        $senha = $_POST['senhaL'];
        $redirectDestino = !empty($_POST['redirect']) ? htmlspecialchars_decode($_POST['redirect']) : 'index.php';

        try {
            // 1. Tenta buscar cliente (LoginUsuario)
            $cliente = $auth->buscarClientePorEmail($email);
            if ($cliente) {
                if (password_verify($senha, $cliente['senha'])) {
                    // Setando sessões do cliente
                    $_SESSION['userlogado']  = $cliente['nome'];
                    $_SESSION['idlogado']    = $cliente['id_cliente'];
                    $_SESSION['idemail']     = $cliente['email'];
                    $_SESSION['idtelefone']  = $cliente['telefone'];

                    header("Location: ../../" . $redirectDestino);
                    exit;
                } else {
                    exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o cliente.', 'javascript:window.history.back()');
                }
            } else {
                // 2. Tenta buscar admin/funcionário (LoginAdmin)
                $admin = $auth->buscarAdminPorEmail($email);
                if ($admin) {
                    if (password_verify($senha, $admin['senha'])) {
                        // Setando sessões do admin/funcionário
                        $_SESSION['admin_nome'] = $admin['nome'];
                        $_SESSION['admin_id']   = $admin['id_usuario'];
                        $_SESSION['admin_tipo'] = $admin['tipo_usuario'];

                        // Permissões: usa a coluna do banco se preenchida, caso contrário deriva do tipo
                        $permString = !empty($admin['permissoes']) 
                            ? $admin['permissoes'] 
                            : (($admin['tipo_usuario'] === 'ADMIN' || $admin['root'] == 1) 
                                ? 'pedidos,extrato,produtos,usuarios,config' 
                                : 'pedidos,produtos');
                        
                        $_SESSION['admin_permissoes'] = $permString;
                        $_SESSION['adminlogado'] = true;

                        header("Location: ../../../admin/index.php");
                        exit;
                    } else {
                        exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o administrador.', 'javascript:window.history.back()');
                    }
                } else {
                    // Não foi encontrado em nenhuma tabela
                    exibirModalEVoltar('Atenção', 'Conta não cadastrada na base de dados.', '../../cadastro.php');
                }
            }
        } catch (Exception $e) {
            exibirModalEVoltar('Erro', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;
}
