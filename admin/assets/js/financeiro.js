/**
 * ═══════════════════════════════════════════════════════════════════════════
 * financeiro.js — Admin / Financeiro (faturamento / lucro)
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt
 *
 * Carregado em: admin/financeiro.php
 *
 * Hoje: só dispara alert() simulando filtro.
 *
 * O que VOCÊ (PHP) deve fazer:
 *   • Criar admin/api/financeiro.php que recebe periodo ou data_inicio/fim.
 *   • SQL exemplo: somar pagamento.valor_pago ou pedido.valor_total JOIN
 *     status_pagamento onde descricao='PAGO', agrupar por forma_pagamento.
 *   • Para lucro: usar pedido.lucro ou somar (preco - custo) dos itens no período.
 *   • Devolver JSON { linhas: [...], totais: {...} } e o JS preenche a tabela
 *     (innerHTML ou createElement). Ou renderize tudo em PHP e use JS só para UX.
 * ═══════════════════════════════════════════════════════════════════════════
 */

function filtrarPeriodo(periodo, btn) {
    // Atualiza a tab ativa
    document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // NOTA BACKEND: fetch(api/financeiro.php?periodo=...) e repovoar a tabela — sem alert.
}

function filtrarPorData() {
    const dataInicio = document.getElementById('data-inicio').value;
    const dataFim    = document.getElementById('data-fim').value;

    if (!dataInicio || !dataFim) {
        return;
    }
    if (dataInicio > dataFim) {
        return;
    }

    // NOTA BACKEND: GET api/financeiro.php?data_inicio=...&data_fim=...
}
