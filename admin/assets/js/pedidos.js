// ============================================================
// pedidos.js — Admin / Pedidos (Com modais mantidos e código simples)
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

    document.getElementById('detalhe-endereco').innerText = "Rua do Cliente Fictício, 123";
    
    var ulItens = document.getElementById('detalhe-itens');
    ulItens.innerHTML = 
        '<li style="display: flex; justify-content: space-between; border-bottom: 1px solid #ddd; padding: 8px 0;">' +
            '<span>1x Produto Exemplo</span>' +
            '<strong>' + total + '</strong>' +
        '</li>';

    var btnImprimir = document.getElementById('btn-imprimir-comanda');
    if (btnImprimir) {
        if (status === 'PENDENTE' || status === 'CANCELADO') {
            btnImprimir.style.display = 'none';
        } else {
            btnImprimir.style.display = 'inline-block';
        }
    }

    document.getElementById('modal-detalhes-pedido').style.display = 'flex';
}

function fecharModalDetalhes() {
    document.getElementById('modal-detalhes-pedido').style.display = 'none';
}

// ── IMPRESSÃO DO CUPOM TÉRMICO (Mantida mas simplificada sem map/reduce/const) ──

function coletarDadosCupomDoModalDetalhes() {
    var itensExtras = [];
    var liElements = document.getElementById('detalhe-itens').querySelectorAll('li');
    for (var i = 0; i < liElements.length; i++) {
        var span = liElements[i].querySelector('span');
        var strong = liElements[i].querySelector('strong');
        if (span) {
            itensExtras.push({
                desc: span.innerText.trim(),
                preco: strong ? strong.innerText.trim() : ''
            });
        }
    }

    var hoje = new Date();
    var dataString = hoje.getDate() + '/' + (hoje.getMonth() + 1) + '/' + hoje.getFullYear() + ' ' + hoje.getHours() + ':' + hoje.getMinutes();

    return {
        id: document.getElementById('detalhe-id').innerText.trim(),
        cliente: document.getElementById('detalhe-cliente').innerText.trim(),
        tipo: document.getElementById('detalhe-tipo').innerText.trim(),
        pagamento: document.getElementById('detalhe-pagamento').innerText.trim(),
        endereco: document.getElementById('detalhe-endereco').innerText.trim(),
        total: document.getElementById('detalhe-total').innerText.trim(),
        dataAtual: dataString,
        itens: itensExtras
    };
}

function montarHtmlCupomTermico(dados, largura) {
    var w = (largura === '58' || largura === 'a4') ? largura : '80';
    var rows = '';

    for (var i = 0; i < dados.itens.length; i++) {
        rows = rows + '<tr><td class="col-desc">' + dados.itens[i].desc + '</td><td class="col-val">' + dados.itens[i].preco + '</td></tr>';
    }
    if (rows === '') {
        rows = '<tr><td class="col-desc" colspan="2">Sem itens listados.</td></tr>';
    }

    return '' +
        '<div class="cupom-termico-sheet" data-largura="' + w + '">' +
            '<p class="cupom-titulo">CHOKKO MELT</p>' +
            '<p class="cupom-subtitulo">Comanda interna • Documento sem valor fiscal<br>Não válido como cupom fiscal</p>' +
            '<hr class="cupom-divisor" />' +
            '<p class="cupom-linha"><strong>Pedido</strong> ' + dados.id + '</p>' +
            '<p class="cupom-linha"><strong>Data</strong> ' + dados.dataAtual + '</p>' +
            '<p class="cupom-linha"><strong>Cliente</strong> ' + dados.cliente + '</p>' +
            '<hr class="cupom-divisor" />' +
            '<table class="cupom-itens-termico" data-largura="' + w + '">' +
                '<thead><tr><th class="col-desc">Item</th><th class="col-val">Valor</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
            '<div class="cupom-total-row"><span>TOTAL</span><span>' + dados.total + '</span></div>' +
            '<hr class="cupom-divisor" />' +
            '<p class="cupom-linha"><strong>Recebimento</strong> ' + dados.tipo + '</p>' +
            '<p class="cupom-linha"><strong>Pagamento</strong> ' + dados.pagamento + '</p>' +
            '<p class="cupom-linha cupom-obs-multi"><strong>Endereço / obs.</strong><br />' + dados.endereco + '</p>' +
            '<hr class="cupom-divisor" />' +
            '<p class="cupom-central">Obrigado pela preferência.</p>' +
        '</div>';
}

function obterLarguraCupomMm() {
    var sel = document.getElementById('print-cupom-largura');
    if (sel && sel.value) return sel.value;
    return '80';
}

function renderizarCupomNoModal(dados, largura) {
    var mount = document.getElementById('print-cupom-mount');
    if (mount) {
        mount.innerHTML = montarHtmlCupomTermico(dados, largura);
    }
}

function abrirModalImpressaoCupom() {
    var dados = coletarDadosCupomDoModalDetalhes();
    var largura = obterLarguraCupomMm();
    renderizarCupomNoModal(dados, largura);

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
    var dados = coletarDadosCupomDoModalDetalhes();
    var largura = obterLarguraCupomMm();
    renderizarCupomNoModal(dados, largura);

    document.body.classList.remove('print-sheet-80');
    document.body.classList.remove('print-sheet-58');
    document.body.classList.remove('print-sheet-a4');
    
    document.body.classList.add('print-sheet-' + largura);

    window.print();
}

// ── Mudanças de status e abas ──

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
    if (_btnCancelarTemp) {
        executarMudancaStatus(_btnCancelarTemp, 'CANCELADO');
    }
    
    document.getElementById('modal-sucesso-msg').innerText = 'O pedido foi cancelado com sucesso. Motivo registrado: ' + motivo;
    document.getElementById('modal-sucesso').style.display = 'flex';
}

function executarMudancaStatus(btn, novoStatus) {
    if (!btn) return;
    var tr = btn.closest('tr');
    if (!tr) return;
    
    var badgeCell = tr.querySelector('.badge');
    var acoesCell = tr.querySelector('.acoes-pedido');

    tr.dataset.status = novoStatus;

    var css = '';
    var label = '';
    if (novoStatus === 'EM_PREPARO') { css = 'badge-preparo'; label = 'EM PREPARO'; }
    else if (novoStatus === 'ENVIADO') { css = 'badge-enviado'; label = 'ENVIADO'; }
    else if (novoStatus === 'ENTREGUE') { css = 'badge-entregue'; label = 'ENTREGUE'; }
    else if (novoStatus === 'CANCELADO') { css = 'badge-cancelado'; label = 'CANCELADO'; }
    else { css = 'badge-pendente'; label = 'PENDENTE'; }

    if (badgeCell) {
        badgeCell.className = 'badge ' + css;
        badgeCell.textContent = label;
    }

    if (acoesCell) {
        acoesCell.innerHTML = ACOES_MAP[novoStatus] || '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var txtAdmin = document.getElementById('motivo-cancelamento-admin');
    if (txtAdmin) {
        txtAdmin.addEventListener('input', function() {
            document.getElementById('contador-cancelamento-admin').innerText = this.value.length;
        });
    }

    var modalPrint = document.getElementById('print-cupom-modal');
    if (modalPrint && modalPrint.parentElement !== document.body) {
        document.body.appendChild(modalPrint);
    }
    
    var selLarg = document.getElementById('print-cupom-largura');
    if (selLarg) {
        selLarg.addEventListener('change', function() {
            if (modalPrint && modalPrint.classList.contains('is-open')) {
                renderizarCupomNoModal(coletarDadosCupomDoModalDetalhes(), obterLarguraCupomMm());
            }
        });
    }
});

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
