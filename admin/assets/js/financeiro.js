// ============================================================
// financeiro.js — Admin / Relatórios e Financeiro
// ============================================================

function filtrarPeriodo(periodoSelecionado, botaoClicado) {
    // Remove o destaque de todos os botões de filtro
    var botoes = document.querySelectorAll('.btn-filter');
    for (var i = 0; i < botoes.length; i++) {
        botoes[i].classList.remove('active');
    }

    // Coloca destaque apenas no botão que foi clicado
    if (botaoClicado) {
        botaoClicado.classList.add('active');
    }

    // NOTA BACKEND: Aqui você redirecionaria para a página com o filtro na URL.
    // Exemplo: window.location.href = 'financeiro.php?periodo=' + periodoSelecionado;
}

function filtrarPorData() {
    var dataInicio = document.getElementById('data-inicio').value;
    var dataFim = document.getElementById('data-fim').value;

    if (!dataInicio || !dataFim) {
        alert("Preencha a data de início e fim.");
        return;
    }
    
    if (dataInicio > dataFim) {
        alert("A data de início não pode ser maior que a data de fim.");
        return;
    }

    // NOTA BACKEND: Aqui você redirecionaria com as datas.
    // Exemplo: window.location.href = 'financeiro.php?data_inicio=' + dataInicio + '&data_fim=' + dataFim;
    alert("Filtro por data ainda não está conectado ao banco de dados.");
}
