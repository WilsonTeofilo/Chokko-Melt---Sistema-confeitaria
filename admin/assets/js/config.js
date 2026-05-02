/**
 * ═══════════════════════════════════════════════════════════════════════════
 * config.js — Admin / Configurações da loja
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt (raiz do projeto)
 *
 * Carregado em: admin/config.php
 *
 * Hoje: salvarConfig() grava horários no localStorage para o user/main.js
 *       simular loja aberta/fechada. Isso é só demonstração.
 *
 * O que VOCÊ (PHP) deve fazer:
 *   • No load da página: SELECT * FROM config_loja WHERE id=1 e preencha os
 *     <input> com value="<?= htmlspecialchars(...) ?>"
 *   • salvarConfig(): fetch POST admin/api/salvar_config.php com FormData ou JSON.
 *     PHP: session + UPDATE config_loja SET hora_abre=?, hora_fecha=?, ...
 *   • O site do cliente (main.js) deve passar a ler a mesma config via
 *     fetch('user/api/config_loja.php') em vez de só localStorage, senão cada
 *     navegador fica com horário diferente.
 * ═══════════════════════════════════════════════════════════════════════════
 */

// Ao carregar a página, se tiver no localStorage, preenche
document.addEventListener('DOMContentLoaded', () => {
    const abre = localStorage.getItem('chokko_hora_abre');
    const fecha = localStorage.getItem('chokko_hora_fecha');
    const elAbre = document.getElementById('config-hora-abre');
    const elFecha = document.getElementById('config-hora-fecha');

    if (abre && elAbre) elAbre.value = abre;
    if (fecha && elFecha) elFecha.value = fecha;

    const waDig = localStorage.getItem('chokko_whatsapp_digits');
    const waEl = document.getElementById('config-whatsapp');
    if (waDig && waEl && typeof window.ChokkoFormatTelBR === 'function') {
        waEl.value = window.ChokkoFormatTelBR(waDig);
    }
});

function salvarConfig(event) {
    if (event) event.preventDefault();

    const nomeLoja     = document.getElementById('config-nome-loja').value;
    const whatsappEl   = document.getElementById('config-whatsapp');
    const whatsapp     = whatsappEl ? whatsappEl.value : '';
    const horaAbre     = document.getElementById('config-hora-abre').value;
    const horaFecha    = document.getElementById('config-hora-fecha').value;
    const taxaEntrega  = document.getElementById('config-taxa-entrega').value;

    if (!nomeLoja || !whatsapp) {
        alert('Preencha o Nome da Loja e o WhatsApp antes de salvar.');
        return;
    }

    if (!horaAbre || !horaFecha) {
        alert('Os horários de funcionamento são obrigatórios.');
        return;
    }

    localStorage.setItem('chokko_hora_abre', horaAbre);
    localStorage.setItem('chokko_hora_fecha', horaFecha);

    const waDigits = typeof window.ChokkoSomenteDigitos === 'function'
        ? window.ChokkoSomenteDigitos(whatsapp)
        : whatsapp.replace(/\D/g, '');
    localStorage.setItem('chokko_whatsapp_digits', waDigits);
    if (whatsappEl && typeof window.ChokkoFormatTelBR === 'function') {
        whatsappEl.value = window.ChokkoFormatTelBR(waDigits);
    }

    // NOTA BACKEND: POST — enviar whatsapp apenas como waDigits (somente números).
    alert(`Configurações salvas!\n• Horário definido: ${horaAbre} às ${horaFecha}`);
}
