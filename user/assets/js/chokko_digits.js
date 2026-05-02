/**
 * Telefone/WhatsApp: ao enviar ao backend use só dígitos (ChokkoSomenteDigitos).
 * Na tela pode continuar mascarado com ChokkoFormatTelBR.
 */
(function () {
    window.ChokkoSomenteDigitos = function (v) {
        return String(v == null ? '' : v).replace(/\D/g, '');
    };

    window.ChokkoFormatTelBR = function (digits) {
        let v = window.ChokkoSomenteDigitos(digits).slice(0, 11);
        if (v.length <= 10) {
            return v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3').trim();
        }
        return v.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3').trim();
    };

    window.ChokkoMascaraTelefoneInput = function (input) {
        if (!input) return;
        const v = window.ChokkoSomenteDigitos(input.value).slice(0, 11);
        input.value = window.ChokkoFormatTelBR(v);
    };
})();
