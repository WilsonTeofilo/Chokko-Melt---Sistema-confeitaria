<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Login — Chokko Melt</title>
</head>
<body>
    <main class="container">
        <form>
            <h1>Fazer Login</h1>
            <div class="input-box">
                <input placeholder="E-mail" type="email" name="email">
                <i class="bx bxs-user"></i>
            </div>
            <div class="input-box">
                <input placeholder="Senha" type="password" name="senha">
                <i class="bx bxs-lock-alt"></i>
            </div>
            <div class="remember-forgot">
                <label>
                    <input type="checkbox"> Lembrar senha
                </label>
                <a href="">Esqueci a senha</a>
            </div>

            <!--
                NOTA BACKEND: ao clicar em "Entrar", validar email/senha no banco:
                SELECT id, nome, senha_hash FROM usuarios WHERE email = $_POST['email'];
                Verificar com password_verify($_POST['senha'], $row['senha_hash']);
                Se válido: $_SESSION['usuario_id'] = $row['id'];

                Verificar $_GET['retorno']:
                  Se 'finalizar' → header('Location: carrinho.php?retorno=finalizar');
                  Senão          → header('Location: index.php');
            -->
            <button type="button" class="login" id="btn-login" onclick="fazerLogin()">
                Entrar
            </button>

            <div style="display: flex; align-items: center; margin: 25px 0 15px 0;">
                <hr style="flex: 1; border: none; border-top: 1px solid #444;">
                <span style="padding: 0 10px; color: #888; font-size: 0.85rem; font-weight: bold;">OU</span>
                <hr style="flex: 1; border: none; border-top: 1px solid #444;">
            </div>

            <button type="button" onclick="loginGoogle()" style="background: white; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; color: #333; margin-bottom: 20px; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width: 18px;">
                CONECTAR COM GOOGLE
            </button>

            <div class="register-link">
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>

            <!-- Link para voltar ao cardápio SEM login -->
            <div style="text-align:center; margin-top:16px;">
                <a href="index.php" style="font-size:.85rem; color:#888; text-decoration:underline;">
                    Continuar sem entrar → Ver cardápio
                </a>
            </div>
        </form>
    </main>

<script>
/*
 * ═══════════════════════════════════════════════════════════════════════════
 * LOGIN — onde JS e PHP se encontram (leia INTEGRACAO_JS_PHP.txt na raiz)
 * ═══════════════════════════════════════════════════════════════════════════
 * Hoje: JS finge login com localStorage (só para o front andar sem banco).
 *
 * Forma certa em produção:
 *   • <form method="post" action="login.php"> ou action="api/login.php"
 *   • PHP: password_verify, session_start(), $_SESSION['cliente_id']
 *   • Google: botão redireciona para url OAuth do PHP; callback troca code por
 *     token e cria/atualiza linha em cliente (google_subject).
 *   • Após sucesso: header('Location: ...') — não dependa de JS para “estar logado”.
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * NOTA BACKEND: substituir esta função por um form action="api/login.php" method="POST"
 * quando o backend estiver pronto. Remover este bloco JS completamente.
 *
 * O endpoint PHP deve:
 *   1. Validar email e senha contra o banco
 *   2. Criar a sessão: $_SESSION['usuario_id'], $_SESSION['usuario_nome']
 *   3. Verificar $_GET['retorno'] para saber para onde redirecionar
 */
function loginGoogle() {
    alert('Autenticação com Google (OAuth) estará disponível quando o backend for conectado!');
}

function fazerLogin() {
    // SIMULAÇÃO FRONTEND — salva um ID mockup para o carrinho.js detectar
    // Remover quando o backend estiver conectado
    const email = document.querySelector('input[type="email"]').value;
    const senha = document.querySelector('input[type="password"]').value;

    if (!email || !senha) {
        alert('Preencha e-mail e senha.');
        return;
    }

    // Simula login bem-sucedido no frontend
    localStorage.setItem('chokko_usuario_id', '1'); // valor mockup

    // SIMULAÇÃO: Se o e-mail for de admin (contém 'admin' no e-mail), vai para o painel
    // NOTA BACKEND: O script PHP de login deve validar o 'tipo_usuario' do banco (ex: ADMIN, FUNCIONARIO ou CLIENTE)
    // Se for CLIENTE → user/index.php. Se for ADMIN/FUNCIONARIO → admin/pedidos.php
    if (email.toLowerCase().includes('admin')) {
        window.location.href = '../admin/pedidos.php';
        return;
    }

    // Verifica se veio do fluxo de finalizar pedido do cliente
    const params = new URLSearchParams(window.location.search);
    const retorno = params.get('retorno');
    if (retorno === 'finalizar') {
        window.location.href = 'carrinho.php?retorno=finalizar';
    } else {
        window.location.href = 'index.php';
    }
}
</script>
</body>
</html>
