// ============================================================
// login.js — Lógica da tela de login
// ============================================================
// NOTA PARA O PHP:
// Quando o backend estiver pronto, esse arquivo vai ficar quase
// vazio. O formulário vai submeter normalmente via POST para o
// PHP, e o PHP vai redirecionar o usuário conforme o perfil dele.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // Botão de login com Google (ainda não implementado no backend)
    var btnGoogle = document.getElementById('btn-google');
    if (btnGoogle) {
        btnGoogle.addEventListener('click', function () {
            loginGoogle();
        });
    }
});

// Placeholder para o login com Google
function loginGoogle() {
    alert('Login com Google estará disponível em breve!');
}

