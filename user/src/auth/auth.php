<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../classes/Auth.php';
require_once '../../../classes/Carrinho.php';
require_once '../../../includes/modal.php'; // exibirModalEVoltar() centralizada

$auth = new Auth(DATABASE, HOST, USER, PASS);

switch (isset($_REQUEST['acao']) ? $_REQUEST['acao'] : '') {
    case 'Cadastrar':
        $nome            = filter_input(INPUT_POST, 'names', FILTER_DEFAULT);
        $nome            = $nome !== null ? trim($nome) : '';
        
        $email           = filter_input(INPUT_POST, 'emails', FILTER_SANITIZE_EMAIL);
        $email           = $email !== null ? strtolower(trim($email)) : '';
        
        $telefone        = filter_input(INPUT_POST, 'telefone', FILTER_DEFAULT);
        $telefone        = $telefone !== null ? preg_replace('/[^0-9]/', '', $telefone) : '';
        
        $senha           = filter_input(INPUT_POST, 'senha', FILTER_DEFAULT);
        $senha           = $senha !== null ? $senha : '';
        
        $confirmar_senha = filter_input(INPUT_POST, 'confirmar_senha', FILTER_DEFAULT);
        $confirmar_senha = $confirmar_senha !== null ? $confirmar_senha : '';

        // Validações básicas de backend (Clean Code e Segurança)
        if (empty($nome)) {
            exibirModalEVoltar('Atenção', 'O nome completo é obrigatório.', 'javascript:window.history.back()');
        }
        if (strlen($nome) > 50) {
            exibirModalEVoltar('Atenção', 'O nome completo deve ter no máximo 50 caracteres.', 'javascript:window.history.back()');
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            exibirModalEVoltar('Atenção', 'Digite um e-mail válido.', 'javascript:window.history.back()');
        }
        if (strlen($email) > 50) {
            exibirModalEVoltar('Atenção', 'O e-mail deve ter no máximo 50 caracteres.', 'javascript:window.history.back()');
        }
        if (empty($telefone)) {
            exibirModalEVoltar('Atenção', 'O telefone é obrigatório.', 'javascript:window.history.back()');
        }
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            exibirModalEVoltar('Atenção', 'O telefone deve conter DDD e número de 9 dígitos (10 ou 11 números).', 'javascript:window.history.back()');
        }
        if (empty($senha)) {
            exibirModalEVoltar('Atenção', 'A senha é obrigatória.', 'javascript:window.history.back()');
        }
        if (strlen($senha) < 8) {
            exibirModalEVoltar('Atenção', 'A senha deve ter no mínimo 8 caracteres.', 'javascript:window.history.back()');
        }
        if ($senha !== $confirmar_senha) {
            exibirModalEVoltar('Atenção', 'A confirmação de senha não confere.', 'javascript:window.history.back()');
        }

        // Destino do redirecionamento
        $redirectDestino = filter_input(INPUT_POST, 'redirect', FILTER_DEFAULT);
        $redirectDestino = $redirectDestino !== null ? htmlspecialchars_decode($redirectDestino) : 'index.php';

        try {
            $novoId = $auth->cadastrarCliente($nome, $email, $telefone, $senha);
            if ($novoId) {
                // Autentica automaticamente o cliente cadastrado
                $_SESSION['userlogado']  = $nome;
                $_SESSION['idlogado']    = $novoId;
                $_SESSION['idemail']     = $email;
                $_SESSION['idtelefone']  = $telefone;

                // Sincroniza o carrinho da sessão com o banco de dados
                $carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
                $carrinhoObj->sincronizarSessaoParaBanco($novoId);

                header("Location: ../../" . $redirectDestino);
                exit;
            }
        } catch (Exception $e) {
            exibirModalEVoltar('Atenção', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;

    case 'Logar':
        $loginInput = filter_input(INPUT_POST, 'email', FILTER_DEFAULT);
        $loginInput = $loginInput !== null ? trim($loginInput) : '';

        $senha = filter_input(INPUT_POST, 'senhaL', FILTER_DEFAULT);
        $senha = $senha !== null ? $senha : '';

        $redirectDestino = filter_input(INPUT_POST, 'redirect', FILTER_DEFAULT);
        $redirectDestino = $redirectDestino !== null ? htmlspecialchars_decode($redirectDestino) : 'index.php';

        if (empty($loginInput) || empty($senha)) {
            exibirModalEVoltar('Acesso Negado', 'Preencha todos os campos para fazer login.', 'javascript:window.history.back()');
        }

        try {
            // 1. Tenta buscar cliente (E-mail ou Telefone)
            $cliente = $auth->buscarClientePorEmailOuTelefone($loginInput);
            if ($cliente) {
                if (password_verify($senha, $cliente['senha'])) {
                    // Setando sessões do cliente
                    $_SESSION['userlogado']  = $cliente['nome'];
                    $_SESSION['idlogado']    = $cliente['id_cliente'];
                    $_SESSION['idemail']     = $cliente['email'];
                    $_SESSION['idtelefone']  = $cliente['telefone'];

                    // Sincroniza o carrinho da sessão com o banco de dados
                    $carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
                    $carrinhoObj->sincronizarSessaoParaBanco($cliente['id_cliente']);

                    header("Location: ../../" . $redirectDestino);
                    exit;
                } else {
                    exibirModalEVoltar('Acesso Negado', 'Senha incorreta para o cliente.', 'javascript:window.history.back()');
                }
            } else {
                // 2. Tenta buscar admin/funcionário (Apenas por E-mail)
                if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                    $admin = $auth->buscarAdminPorEmail(strtolower($loginInput));
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
                        exibirModalEVoltar('Atenção', 'Conta não cadastrada na base de dados.', '../../cadastro.php');
                    }
                } else {
                    exibirModalEVoltar('Atenção', 'Conta não cadastrada na base de dados.', '../../cadastro.php');
                }
            }
        } catch (Exception $e) {
            exibirModalEVoltar('Erro', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;

    case 'EditarPerfil':
        if (!isset($_SESSION['idlogado'])) {
            header("Location: ../../login.php");
            exit;
        }

        $id_cliente = intval($_SESSION['idlogado']);
        
        $nome = filter_input(INPUT_POST, 'nome', FILTER_DEFAULT);
        $nome = $nome !== null ? trim($nome) : '';
        
        $telefone = filter_input(INPUT_POST, 'telefone', FILTER_DEFAULT);
        $telefone = $telefone !== null ? preg_replace('/[^0-9]/', '', $telefone) : '';
        
        $nova_senha = filter_input(INPUT_POST, 'nova_senha', FILTER_DEFAULT);
        $nova_senha = $nova_senha !== null ? $nova_senha : '';

        if (empty($nome) || empty($telefone)) {
            exibirModalEVoltar('Atenção', 'Nome e telefone são campos obrigatórios.', 'javascript:window.history.back()');
            exit;
        }

        try {
            $senha_atualizar = !empty($nova_senha) ? $nova_senha : null;

            $auth->atualizarPerfilCliente($id_cliente, $nome, $telefone, $senha_atualizar);

            // Atualiza as sessões correspondentes com os novos dados salvos
            $_SESSION['userlogado'] = $nome;
            $_SESSION['idtelefone'] = $telefone;

            header("Location: ../../perfil.php?msg=" . urlencode("Seus dados cadastrais foram atualizados com sucesso!"));
            exit;
        } catch (Exception $e) {
            exibirModalEVoltar('Erro ao Atualizar', $e->getMessage(), 'javascript:window.history.back()');
        }
        break;
}
