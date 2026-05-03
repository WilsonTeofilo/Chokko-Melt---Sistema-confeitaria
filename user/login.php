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
            <button type="button" class="login" id="btn-login">
                Entrar
            </button>

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
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
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
