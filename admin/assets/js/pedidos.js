// ============================================================
// pedidos.js — Admin / Pedidos
// ============================================================

var _btnCancelarTemp = null;

var ACOES_MAP = {
    'PENDENTE':
        '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button> ' +
        '<button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, \'EM_PREPARO\')" title="Aceitar pedido"><i class="fa-solid fa-check"></i> Aceitar</button> ' +
        '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>',
    'EM_PREPARO':
        '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button> ' +
        '<button class="btn-action-accept btn-enviar" onclick="atualizarStatusPedido(this, \'ENVIADO\')" title="Marcar como enviado"><i class="fa-solid fa-paper-plane"></i> Enviar</button> ' +
        '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>',
    'ENVIADO':
        '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button> ' +
        '<button class="btn-action-accept btn-entregar" onclick="atualizarStatusPedido(this, \'ENTREGUE\')" title="Confirmar entrega"><i class="fa-solid fa-flag-checkered"></i> Entregue</button>',
    'ENTREGUE':
        '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button>',
    'CANCELADO':
        '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button>'
};

// ── TABS ──

function filtrarPedidos(status, btn) {
    var botoes = document.querySelectorAll('#pedidos-tabs .tab-btn');
    for (var i = 0; i < botoes.length; i++) {
        botoes[i].classList.remove('active');
    }
    if (btn) btn.classList.add('active');

    var trs = document.querySelectorAll('#tabela-pedidos tbody tr');
    for (var j = 0; j < trs.length; j++) {
        if (status === 'todos' || trs[j].dataset.status === status) {
            trs[j].style.display = '';
        } else {
            trs[j].style.display = 'none';
        }
    }
}

// ── MODAL DETALHES ──

function verDetalhesPedido(btn) {
    var tr = btn.closest('tr');
    var id = tr.cells[0].innerText;
    var cliente = tr.cells[1].innerText;
    var tipo = tr.cells[2].innerText;
    var pagamento = tr.cells[3].innerText;
    var total = tr.cells[4].innerText;
    var status = tr.dataset.status;

    document.getElementById('detalhe-id').innerText = id;
    document.getElementById('detalhe-cliente').innerText = cliente;
    document.getElementById('detalhe-tipo').innerText = tipo;
    document.getElementById('detalhe-pagamento').innerText = pagamento;
    document.getElementById('detalhe-total').innerText = total;
    document.getElementById('detalhe-endereco').innerText = 'Rua do Cliente Fictício, 123';

    var ulItens = document.getElementById('detalhe-itens');
    ulItens.innerHTML =
        '<li class="detalhe-li-border">' +
            '<span>1x Produto Exemplo</span>' +
            '<strong>' + total + '</strong>' +
        '</li>';

    var btnImprimir = document.getElementById('btn-imprimir-comanda');
    if (btnImprimir) {
        btnImprimir.style.display = (status === 'PENDENTE' || status === 'CANCELADO') ? 'none' : 'inline-block';
    }

    document.getElementById('modal-detalhes-pedido').style.display = 'flex';
}

function fecharModalDetalhes() {
    document.getElementById('modal-detalhes-pedido').style.display = 'none';
}

// ── IMPRESSÃO TÉRMICA ──
// Estratégia: o driver da POS-58 já sabe que o papel é 58mm.
// O browser só precisa enviar o conteúdo limpo — sem forçar tamanho de página.
// O @media print no CSS esconde tudo e exibe só o cupom a 100% da largura.

function coletarDadosCupom() {
    var itens = [];
    var lis = document.getElementById('detalhe-itens').querySelectorAll('li');
    for (var i = 0; i < lis.length; i++) {
        var span = lis[i].querySelector('span');
        var strong = lis[i].querySelector('strong');
        if (span) {
            itens.push({
                desc: span.innerText.trim(),
                preco: strong ? strong.innerText.trim() : ''
            });
        }
    }

    var agora = new Date();
    var data = agora.getDate() + '/' + (agora.getMonth() + 1) + '/' + agora.getFullYear()
        + ' ' + agora.getHours() + ':' + String(agora.getMinutes()).padStart(2, '0');

    return {
        id:        document.getElementById('detalhe-id').innerText.trim(),
        cliente:   document.getElementById('detalhe-cliente').innerText.trim(),
        tipo:      document.getElementById('detalhe-tipo').innerText.trim(),
        pagamento: document.getElementById('detalhe-pagamento').innerText.trim(),
        endereco:  document.getElementById('detalhe-endereco').innerText.trim(),
        total:     document.getElementById('detalhe-total').innerText.trim(),
        data:      data,
        itens:     itens
    };
}

function montarHtmlCupom(dados) {
    var linhas = '';
    for (var i = 0; i < dados.itens.length; i++) {
        linhas += '<tr><td class="col-desc">' + dados.itens[i].desc + '</td>'
                + '<td class="col-val">' + dados.itens[i].preco + '</td></tr>';
    }
    if (!linhas) {
        linhas = '<tr><td colspan="2">Sem itens listados.</td></tr>';
    }

    return '<div id="cupom-para-impressao">'
        + '<p class="c-titulo">CHOKKO MELT</p>'
        + '<p class="c-sub">Comanda interna &bull; Sem valor fiscal</p>'
        + '<hr class="c-hr">'
        + '<p class="c-linha"><b>Pedido:</b> ' + dados.id + '</p>'
        + '<p class="c-linha"><b>Data:</b> ' + dados.data + '</p>'
        + '<p class="c-linha"><b>Cliente:</b> ' + dados.cliente + '</p>'
        + '<hr class="c-hr">'
        + '<table class="c-tabela">'
        +   '<thead><tr><th class="col-desc">Item</th><th class="col-val">Valor</th></tr></thead>'
        +   '<tbody>' + linhas + '</tbody>'
        + '</table>'
        + '<div class="c-total"><span>TOTAL</span><span>' + dados.total + '</span></div>'
        + '<hr class="c-hr">'
        + '<p class="c-linha"><b>Retirada/Entrega:</b> ' + dados.tipo + '</p>'
        + '<p class="c-linha"><b>Pagamento:</b> ' + dados.pagamento + '</p>'
        + '<p class="c-linha"><b>Endereço:</b> ' + dados.endereco + '</p>'
        + '<hr class="c-hr">'
        + '<p class="c-centro">Obrigado pela preferência!</p>'
        + '</div>';
}

function imprimirCupom() {
    var dados = coletarDadosCupom();

    // Injeta o cupom direto no body (fora de qualquer modal)
    // para que o @media print encontre ele limpo e isolado
    var wrapper = document.getElementById('_print-wrapper');
    if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.id = '_print-wrapper';
        document.body.appendChild(wrapper);
    }
    wrapper.innerHTML = montarHtmlCupom(dados);

    window.print();
}

// ── MODAL DE IMPRESSÃO (pré-visualização) ──

function abrirModalImpressaoCupom() {
    var dados = coletarDadosCupom();
    var mount = document.getElementById('print-cupom-mount');
    if (mount) mount.innerHTML = montarHtmlCupom(dados);

    var modal = document.getElementById('print-cupom-modal');
    if (modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function fecharModalImpressaoCupom() {
    var modal = document.getElementById('print-cupom-modal');
    if (modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }
}

function executarImpressaoCupom() {
    var dados = coletarDadosCupom();
    
    // Atualiza a pré-visualização
    var mount = document.getElementById('print-cupom-mount');
    if (mount) mount.innerHTML = montarHtmlCupom(dados);

    // CRÍTICO: Injetar o #_print-wrapper que o @media print usa
    var wrapper = document.getElementById('_print-wrapper');
    if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.id = '_print-wrapper';
        document.body.appendChild(wrapper);
    }
    wrapper.innerHTML = montarHtmlCupom(dados);

    // O browser é teimoso e tenta forçar A4.
    // Vamos injetar a regra @page de forma permanente antes do window.print()
    var largura = document.getElementById('print-cupom-largura');
    var wStr = largura && largura.value === '80' ? '80' : '58';
    var w = wStr + 'mm';
    
    // Adiciona a classe no body para que o CSS de 48mm / 72mm dispare
    document.body.classList.remove('print-sheet-58', 'print-sheet-80');
    document.body.classList.add('print-sheet-' + wStr);
    
    var styleId = 'dynamic-print-page-size';
    var styleEl = document.getElementById(styleId);
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = styleId;
        document.head.appendChild(styleEl);
    }
    styleEl.innerHTML = '@media print { @page { size: ' + w + ' auto !important; margin: 0 !important; } }';

    window.print();
}

// Ocultar a opção A4 via JS para não tocar no PHP
document.addEventListener('DOMContentLoaded', function() {
    var a4Opt = document.querySelector('#print-cupom-largura option[value="a4"]');
    if (a4Opt) a4Opt.remove();
});

// ── STATUS DOS PEDIDOS ──

function atualizarStatusPedido(btn, novoStatus) {
    if (novoStatus === 'CANCELADO') {
        document.getElementById('motivo-cancelamento-admin').value = '';
        document.getElementById('contador-cancelamento-admin').innerText = '0';
        document.getElementById('modal-cancelar-pedido').style.display = 'flex';
        _btnCancelarTemp = btn;
        return;
    }
    executarMudancaStatus(btn, novoStatus);
}

function confirmarCancelamentoAdmin() {
    var motivo = document.getElementById('motivo-cancelamento-admin').value.trim();
    if (!motivo) {
        alert('O motivo do cancelamento é obrigatório.');
        return;
    }
    document.getElementById('modal-cancelar-pedido').style.display = 'none';
    if (_btnCancelarTemp) executarMudancaStatus(_btnCancelarTemp, 'CANCELADO');

    document.getElementById('modal-sucesso-msg').innerText =
        'Pedido cancelado. Motivo: ' + motivo;
    document.getElementById('modal-sucesso').style.display = 'flex';
}

function executarMudancaStatus(btn, novoStatus) {
    if (!btn) return;
    var tr = btn.closest('tr');
    if (!tr) return;

    tr.dataset.status = novoStatus;

    var badgeMap = {
        'EM_PREPARO': ['badge-preparo', 'EM PREPARO'],
        'ENVIADO':    ['badge-enviado', 'ENVIADO'],
        'ENTREGUE':   ['badge-entregue', 'ENTREGUE'],
        'CANCELADO':  ['badge-cancelado', 'CANCELADO'],
        'PENDENTE':   ['badge-pendente', 'PENDENTE']
    };

    var info = badgeMap[novoStatus] || ['badge-pendente', 'PENDENTE'];
    var badge = tr.querySelector('.badge');
    if (badge) {
        badge.className = 'badge ' + info[0];
        badge.textContent = info[1];
    }

    var acoes = tr.querySelector('.acoes-pedido');
    if (acoes) acoes.innerHTML = ACOES_MAP[novoStatus] || '';
}

// ── INIT ──

document.addEventListener('DOMContentLoaded', function () {
    // Contador do textarea de cancelamento
    var txtAdmin = document.getElementById('motivo-cancelamento-admin');
    if (txtAdmin) {
        txtAdmin.addEventListener('input', function () {
            document.getElementById('contador-cancelamento-admin').innerText = this.value.length;
        });
    }
});
