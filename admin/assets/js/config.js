// ============================================================
// config.js — Admin / Configurações da loja
// ============================================================
// NOTA BACKEND:
// Hoje esse arquivo salva no localStorage para testar.
// Quando o banco de dados estiver pronto, o admin deve enviar o formulário
// (POST) e salvar isso na tabela de configurações.
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Ao abrir a página, preenche com os dados falsos do localStorage
    var abre = localStorage.getItem('chokko_hora_abre');
    var fecha = localStorage.getItem('chokko_hora_fecha');
    var waDig = localStorage.getItem('chokko_whatsapp_digits');

    var elAbre = document.getElementById('config-hora-abre');
    var elFecha = document.getElementById('config-hora-fecha');
    var waEl = document.getElementById('config-whatsapp');

    if (abre && elAbre) {
        elAbre.value = abre;
    }
    if (fecha && elFecha) {
        elFecha.value = fecha;
    }

    // Se tiver função de máscara, aplica no WhatsApp salvo
    if (waDig && waEl && typeof window.ChokkoFormatTelBR === 'function') {
        waEl.value = window.ChokkoFormatTelBR(waDig);
    }
});

function salvarConfig(evento) {
    if (evento) {
        evento.preventDefault();
    }

    var elNomeLoja = document.getElementById('config-nome-loja');
    var elWhatsapp = document.getElementById('config-whatsapp');
    var elHoraAbre = document.getElementById('config-hora-abre');
    var elHoraFecha = document.getElementById('config-hora-fecha');

    var nomeLoja = elNomeLoja ? elNomeLoja.value : '';
    var whatsapp = elWhatsapp ? elWhatsapp.value : '';
    var horaAbre = elHoraAbre ? elHoraAbre.value : '';
    var horaFecha = elHoraFecha ? elHoraFecha.value : '';

    // Verifica se os campos importantes foram preenchidos
    if (!nomeLoja || !whatsapp) {
        alert('Preencha o Nome da Loja e o WhatsApp antes de salvar.');
        return;
    }

    if (!horaAbre || !horaFecha) {
        alert('Os horários de funcionamento são obrigatórios.');
        return;
    }

    // Tira os símbolos do whatsapp para salvar só números
    var waSomenteNumeros = whatsapp.replace(/\D/g, '');

    // Salva no localStorage (mockup)
    localStorage.setItem('chokko_hora_abre', horaAbre);
    localStorage.setItem('chokko_hora_fecha', horaFecha);
    localStorage.setItem('chokko_whatsapp_digits', waSomenteNumeros);

    // Reaplica a máscara no campo
    if (elWhatsapp && typeof window.ChokkoFormatTelBR === 'function') {
        elWhatsapp.value = window.ChokkoFormatTelBR(waSomenteNumeros);
    }

    // NOTA BACKEND: em vez de dar só esse alert, você faria o submit do form aqui.
    alert('Configurações salvas!\n• Horário definido: ' + horaAbre + ' às ' + horaFecha);
}
