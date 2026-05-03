/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS — cadastro.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnBackToLogin = document.getElementById('btn-back-login');
    if (btnBackToLogin) {
        btnBackToLogin.addEventListener('click', () => {
            window.location.href = 'login.php';
        });
    }

    const btnCriarConta = document.getElementById('btn-criar-conta');
    if (btnCriarConta) {
        btnCriarConta.addEventListener('click', () => {
            // Em produção: Validação e envio do formulário (POST /api/cadastro_cliente.php)
            window.location.href = 'perfil.php';
        });
    }
});
