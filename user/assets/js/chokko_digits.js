// ============================================================
// chokko_digits.js — Funções de telefone
// ============================================================
// Esse arquivo tem 3 funções de telefone que outros arquivos usam:
//
//   ChokkoSomenteDigitos(texto)      → tira tudo que não é número
//   ChokkoFormatTelBR(texto)         → formata como (11) 98765-4321
//   ChokkoMascaraTelefoneInput(input)→ aplica a máscara enquanto o usuário digita
//
// ============================================================


// Recebe qualquer texto e devolve só os números.
// Exemplo: "(11) 98765-4321" vira "11987654321"
function ChokkoSomenteDigitos(texto) {
    // Se vier null ou undefined, trata como string vazia
    if (texto == null) {
        texto = '';
    }

    // .replace(/\D/g, '') apaga tudo que NÃO é dígito
    // \D significa "qualquer coisa que não seja número"
    // o 'g' significa "faz isso em todo o texto, não só no primeiro"
    return String(texto).replace(/\D/g, '');
}


// Recebe uma string de dígitos e coloca a formatação brasileira.
// Exemplos:
//   "11987654321"  →  "(11) 98765-4321"   (celular, 11 dígitos)
//   "1133334444"   →  "(11) 3333-4444"    (fixo, 10 dígitos)
function ChokkoFormatTelBR(texto) {
    // Primeiro garante que só tem números e limita em 11 dígitos
    var digitos = ChokkoSomenteDigitos(texto).slice(0, 11);

    // Celular: 11 dígitos → (DDD) 9XXXX-XXXX
    if (digitos.length === 11) {
        return digitos.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }

    // Fixo: 10 dígitos → (DDD) XXXX-XXXX
    if (digitos.length === 10) {
        return digitos.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    }

    // Se ainda tiver digitando (menos de 10 dígitos), devolve sem formatação
    return digitos;
}


// Essa função é chamada pelo oninput do campo de telefone no HTML.
// Ela pega o que o usuário digitou, formata e coloca de volta no campo.
function ChokkoMascaraTelefoneInput(input) {
    // Segurança: se não receber um input válido, não faz nada
    if (!input) {
        return;
    }

    // Aplica a formatação no valor atual do campo
    input.value = ChokkoFormatTelBR(input.value);
}
