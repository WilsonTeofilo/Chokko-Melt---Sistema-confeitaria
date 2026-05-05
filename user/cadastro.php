
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>CADASTRO</title>
</head>
<body>

<main id="form-container">
 <div id="form-header">
        <h1 id="form-title">
            Criar Conta
        </h1>
        <button class="btn-default" id="btn-back-login">
            <i class="fa-solid fa-right-to-bracket"></i>
        </button>
    </div>
    
<!-- FORMULÁRIO :-->
<?php $redirectUrl = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : ''; ?>
 <form action="src/auth/auth.php" method="POST">
        <input type="hidden" name="acao" value="Cadastrar">
        <input type="hidden" name="redirect" value="<?= $redirectUrl ?>">

        <!-- nome:-->
        <div id="input_container">
            <div class="input-box">
                <label for="nameJS" class="form-label"> Nome Completo </label>
                <div class="input-field">
                    <input type="text" name="names" id="nameJS" class="form-control" placeholder="Guilherme" maxlength="49" required autocomplete="off"> 
                    <i class="fa-solid fa-user" ></i>
                </div>
                    
               
   
            
        <!-- Email:-->
            <div class="input-box">
                <label for="emailJS" class="form-label">Email</label> 
                <div class="input-field">
                    <input type="email" name="emails" id="emailJS" class="form-control" placeholder="GuiVeloso@gmail.com" required autocomplete="off" maxlength="100">
                    <i class="fa-solid fa-envelope"></i>
                </div>
            </div>

        <!-- Telefone:-->
            <div class="input-box">
                <label for="telefoneJS" class="form-label">Telefone</label>
                <div class="input-field">
                    <input type="tel" name="telefone" id="telefoneJS" class="form-control" placeholder="(11) 98765-4321" maxlength="15" autocomplete="off" required
                        oninput="typeof ChokkoMascaraTelefoneInput==='function' && ChokkoMascaraTelefoneInput(this)">
                    <i class="fa-solid fa-phone"></i>
                </div>
            </div>

      <!-- Senha:-->
            <div class="input-box">
                <label for="senhaJS" class="form-label">Senha</label>
                <div class="input-field">
                    <input type="password" name="senha" id="senhaJS" class="form-control" placeholder="******" required maxlength="50" autocomplete="new-password">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <!-- Medidor de força da senha -->
                <div id="senha-strength-wrap">
                    <div id="senha-strength-bar">
                        <span class="strength-seg" id="seg1"></span>
                        <span class="strength-seg" id="seg2"></span>
                        <span class="strength-seg" id="seg3"></span>
                        <span class="strength-seg" id="seg4"></span>
                    </div>
                    <ul id="senha-checklist">
                        <li id="chk-len"><i class="fa-solid fa-circle-xmark"></i> Mínimo 8 caracteres</li>
                        <li id="chk-upper"><i class="fa-solid fa-circle-xmark"></i> 1 letra maiúscula</li>
                        <li id="chk-special"><i class="fa-solid fa-circle-xmark"></i> 1 caractere especial (!@#$...)</li>
                    </ul>
                </div>
            </div>

              <!-- Confirmar Senha:-->
            <div class="input-box">
                <label for="confirmar_senhaJS" class="form-label">Confirmar Senha</label>
                <div class="input-field">
                    <input type="password" name="confirmar_senha" id="confirmar_senhaJS" class="form-control" placeholder="******" required maxlength="50" autocomplete="new-password" onpaste="return false;" ondrop="return false;">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <span id="erro-senhas" class="erro-senhas-txt"></span>
            </div>

             <!-- enviar:-->
        <input type="submit" name="Registro" class="btn-default w-100" id="btn-criar-conta" value="Criar Conta" style="margin-top: 8px;">
    
    </form>
</main>
<script src="assets/js/chokko_digits.js"></script>
<script src="assets/js/cadastro.js"></script>
</body>
</html>


