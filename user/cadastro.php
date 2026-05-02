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
<?php
/*
 * Cadastro sem JS dedicado: o botão só redireciona (mock). Em produção:
 *   • form method="post" action="cadastro.php" ou api/cadastro_cliente.php
 *   • PHP: validar e-mail único, password_hash, INSERT em cliente, sessão ou redirect login
 *   • Não confie em validação só no HTML — repita no servidor.
 * Guia JS↔PHP: INTEGRACAO_JS_PHP.txt (na raiz do projeto).
 */
?>
<main id="form-container">
    <div id="form-header">
        <h1 id="form-title">
            Criar Conta
        </h1>
        <button class="btn-default" onclick="window.location.href='login.php'" >
            <i class="fa-solid fa-right-to-bracket"></i>
        </button>
    </div>
    <form action="" id="form">
        <div id="input_container">
            <div class="input-box">
                <label for="name" class="form-label">
                    Primeiro Nome
                </label>
                <div class="input-field">
                    <input type="text" name="name" id="name" class="form-control" placeholder="Guilherme">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
            <div class="input-box">
                <label for="last_name" class="form-label">
                    Segundo Nome
                </label>
                <div class="input-field">
                    <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Santos Veloso">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
            <div class="input-box">
                <label for="nascimento" class="form-label">
                    Nascimento
                </label>
                <div class="input-field">
                    <input type="date" name="nascimento" id="nascimento" class="form-control">
                </div>
            </div>
            <div class="input-box">
                <label for="email" class="form-label">
                    Email
                </label>
                <div class="input-field">
                    <input type="email" name="email" id="email" class="form-control" placeholder="exemplo@gmail.com">
                    <i class="fa-solid fa-envelope"></i>
                </div>
            </div>
            <div class="input-box">
                <label for="senha" class="form-label">
                    Senha
                </label>
                <div class="input-field">
                    <input type="password" name="senha" id="senha" class="form-control" placeholder="******">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>
            <div class="input-box">
                <label for="confirmar_senha" class="form-label">
                    Confirmar Senha
                </label>
                <div class="input-field">
                    <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" placeholder="******">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>
            <div class="radio-container">
                <label class="form-label">
                    Gênero
                </label>
                <div id="gender-inputs">
                    <div class="radio-box">
                        <input type="radio" name="gender" id="female" class="form-control" value="female">
                        <label for="female" class="form-label">Feminino</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" name="gender" id="male" class="form-control" value="male">
                        <label for="male" class="form-label">Masculino</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" name="gender" id="other" class="form-control" value="other">
                        <label for="other" class="form-label">Outro</label>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn-default" style="width:100%" onclick="window.location.href='perfil.php'">
            <i class="fa-solid fa-check"></i>
            Criar Conta
        </button>
    </form>
</main>
</body>
</html>
