/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS — login.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnLogin = document.getElementById('btn-login');
    if (btnLogin) {
        btnLogin.addEventListener('click', fazerLogin);
    }

    const btnGoogle = document.getElementById('btn-google');
    if (btnGoogle) {
        btnGoogle.addEventListener('click', loginGoogle);
    }
});

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
    if (email.toLowerCase().includes('admin')) {
        window.location.href = '../admin/index.php';
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
