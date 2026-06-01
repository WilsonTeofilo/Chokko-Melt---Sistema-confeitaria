// ============================================================
// financeiro.js — Admin / Financeiro
// ============================================================

function filtrarPeriodo(periodo, btn) {
    var botoes = document.querySelectorAll('.btn-filter');
    for (var i = 0; i < botoes.length; i++) {
        botoes[i].classList.remove('active');
    }
    if (btn) btn.classList.add('active');

    window.location.href = 'financeiro.php?periodo=' + periodo;
}

function filtrarPorData() {
    var inicio = document.getElementById('data-inicio').value;
    var fim    = document.getElementById('data-fim').value;

    if (!inicio || !fim) {
        alert('Preencha a data de início e fim.');
        return;
    }
    if (inicio > fim) {
        alert('A data de início não pode ser maior que a data de fim.');
        return;
    }

    window.location.href = 'financeiro.php?data_inicio=' + inicio + '&data_fim=' + fim;
}
