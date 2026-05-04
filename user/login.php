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
    <!-- FORMULÁRIO :-->

<?php
// Prepara o terreno pra saber pra onde ir depois do login
$redirectUrl = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'index.php';
?>
        <form action="src/auth.php" method="POST">
            <input type="hidden" name="acao" value="Logar">
            <input type="hidden" name="redirect" value="<?= $redirectUrl ?>">
            <h1>Fazer Login</h1>
            <div class="input-box">
                <input placeholder="E-mail" type="email" name="email" required autocomplete="on">
                <i class="bx bxs-user"></i></div>

                <!-- Senha :-->
            <div class="input-box">
                <input placeholder="Senha" type="password" name="senhaL" required>
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
     <input type="submit" value="Entrar" class="login" id="btn-login">
            <div class="divider">
                <hr>
                <span>OU</span>
                <hr>
            </div>

            <button type="button" class="btn-google" id="btn-google">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
                CONECTAR COM GOOGLE
            </button>

            <div class="register-link">
                <p>Não tem uma conta? <a href="cadastro.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>">Cadastre-se</a></p>
            </div>

            <!-- Link para voltar ao cardápio SEM login -->
            <div class="guest-link-container">
                <a href="index.php" class="guest-link">
                    Continuar sem entrar → Ver cardápio
                </a>
            </div>
        </form>
    </main>

<script src="assets/js/login.js"></script>
</body>
</html>
