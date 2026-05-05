// ═══════════════════════════════════════════════════════
//  endereco.js — Lógica da tela de cadastro de endereço
//  - Máscara de CEP (00000-000)
//  - Auto-preenchimento via ViaCEP API
// ═══════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    const inputCep   = document.getElementById('cepJS');
    const inputRua   = document.getElementById('ruaJS');
    const inputBairro = document.getElementById('bairroJS');

    // ── 1. MÁSCARA DO CEP (digita 12345678 → vira 12345-678) ──
    inputCep.addEventListener('input', () => {
        let v = inputCep.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
        inputCep.value = v;

        // Dispara busca ao completar 8 dígitos (com ou sem hífen)
        if (v.replace('-', '').length === 8) {
            buscarCep(v.replace('-', ''));
        }
    });

    // ── 2. BUSCA NA API VIACEP ──
    async function buscarCep(cepLimpo) {
        // Feedback visual: ícone de loading no campo CEP
        const iconeCep = inputCep.closest('.input-field').querySelector('i');
        iconeCep.className = 'fa-solid fa-spinner fa-spin';
        inputRua.value   = '';
        inputBairro.value = '';

        try {
            const res  = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            const data = await res.json();

            if (data.erro) {
                // CEP não encontrado
                iconeCep.className = 'fa-solid fa-circle-xmark';
                iconeCep.style.color = '#e74c3c';
                inputCep.style.borderColor = '#e74c3c';
                mostrarErroCep('CEP não encontrado. Verifique e tente novamente.');
                return;
            }

            // Preenchimento automático
            inputRua.value    = data.logradouro || '';
            inputBairro.value = data.bairro     || '';

            // Ícone de sucesso
            iconeCep.className  = 'fa-solid fa-circle-check';
            iconeCep.style.color = '#4CAF50';

            // Foca no campo número para o usuário completar
            document.getElementById('numeroJS').focus();

            limparErroCep();

        } catch (err) {
            iconeCep.className  = 'fa-solid fa-circle-xmark';
            iconeCep.style.color = '#e74c3c';
            mostrarErroCep('Erro de conexão. Verifique sua internet.');
        }
    }

    // ── 3. MENSAGEM DE ERRO INLINE ABAIXO DO CEP ──
    function mostrarErroCep(msg) {
        let span = document.getElementById('erro-cep');
        if (!span) {
            span = document.createElement('span');
            span.id = 'erro-cep';
            span.style.cssText = 'display:block;font-size:0.78rem;color:#e74c3c;margin-top:4px;';
            inputCep.closest('.input-box').appendChild(span);
        }
        span.textContent = msg;
    }

    function limparErroCep() {
        const span = document.getElementById('erro-cep');
        if (span) span.textContent = '';
    }

    // ── 4. Reset do ícone quando o usuário apaga o CEP ──
    inputCep.addEventListener('blur', () => {
        const iconeCep = inputCep.closest('.input-field').querySelector('i');
        const cepLimpo = inputCep.value.replace(/\D/g, '');
        // Se estiver incompleto, volta ao ícone padrão
        if (cepLimpo.length < 8) {
            iconeCep.className  = 'fa-solid fa-location-dot';
            iconeCep.style.color = '';
        }
    });

});
