/**
 * ═══════════════════════════════════════════════════════════════════════════
 * pedidos.js — Admin / Pedidos
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia geral na raiz do projeto: INTEGRACAO_JS_PHP.txt
 *
 * Carregado em: admin/pedidos.php
 *
 * Hoje: a tabela de pedidos é HTML estático; o JS só muda badge e botões no
 *       navegador (MOCK). Impressão do cupom usa dados já mostrados no modal.
 *
 * O que VOCÊ (PHP) deve fazer:
 *   1) pedidos.php: gerar <tbody> com while/foreach no SELECT de pedido + cliente.
 *   2) Ao aceitar/cancelar/enviar: em vez de só mudar o DOM, chamar
 *      fetch('admin/api/atualizar_status_pedido.php', { method:'POST', body: JSON })
 *      com id_pedido e novo_status. O PHP faz session_start(), verifica se o
 *      usuario é admin, UPDATE pedido SET id_status_pedido = ...
 *   3) Cancelamento: enviar também motivo_cancelamento para gravar no pedido.
 *   4) Detalhes do pedido: endpoint GET admin/api/pedido_detalhes.php?id= que
 *      devolve JSON (itens, endereço) para o JS preencher o modal (ou renderize
 *      tudo em PHP e use JS só para abrir/fechar).
 *
 * Sessão: páginas admin devem incluir no topo um require que redireciona se
 *         não existir $_SESSION['usuario_id'] (ou o nome que você usar).
 * ═══════════════════════════════════════════════════════════════════════════
 */

const BADGE_MAP = {
    'PENDENTE':   { label: 'PENDENTE',   css: 'badge-pendente' },
    'EM_PREPARO': { label: 'EM PREPARO', css: 'badge-preparo' },
    'ENVIADO':    { label: 'ENVIADO',    css: 'badge-enviado' },
    'ENTREGUE':   { label: 'ENTREGUE',   css: 'badge-entregue' },
    'CANCELADO':  { label: 'CANCELADO',  css: 'badge-cancelado' },
};

let _btnCancelarTemp = null;

// BANCO DE DADOS FAKE PARA TESTE FRONTEND
const MOCK_PEDIDOS = {
    '#1024': {
        endereco: 'Rua das Flores, 123 - Grajaú\nObs: Entregar na portaria',
        itens: [
            { desc: '2x Bolo de Cenoura com Chocolate', preco: 'R$ 30,00' },
            { desc: '1x Torta de Morango', preco: 'R$ 15,00' }
        ]
    },
    '#1023': {
        endereco: 'Cliente vem retirar na loja\nObs: Nenhuma',
        itens: [
            { desc: '1x Bolo de Pote Ninho', preco: 'R$ 12,00' },
            { desc: '1x Torta Salgada de Frango', preco: 'R$ 10,00' }
        ]
    },
    '#1022': {
        endereco: 'Av. Dona Belmira Marin, 450 - Grajaú\nObs: Sem troco, já pago no cartão',
        itens: [
            { desc: '1x Kit Festa 2 (Bolo + Salgados + Refri)', preco: 'R$ 67,50' }
        ]
    },
    '#1021': {
        endereco: 'Rua Jequirituba, 980 - Pq América\nObs: Tocar o interfone 4',
        itens: [
            { desc: '2x Torta Holandesa de Pote', preco: 'R$ 20,00' },
            { desc: '1x Guaraná Antarctica 2L', preco: 'R$ 10,00' }
        ]
    }
};

const ACOES_MAP = {
    'PENDENTE': `
        <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
            <i class="fa-solid fa-eye"></i> Detalhes
        </button>
        <button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, 'EM_PREPARO')" title="Aceitar pedido">
            <i class="fa-solid fa-check"></i> Aceitar
        </button>
        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </button>`,
    'EM_PREPARO': `
        <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
            <i class="fa-solid fa-eye"></i> Detalhes
        </button>
        <button class="btn-action-accept btn-enviar" onclick="atualizarStatusPedido(this, 'ENVIADO')" title="Marcar como enviado">
            <i class="fa-solid fa-paper-plane"></i> Enviar
        </button>
        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </button>`,
    'ENVIADO': `
        <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
            <i class="fa-solid fa-eye"></i> Detalhes
        </button>
        <button class="btn-action-accept btn-entregar" onclick="atualizarStatusPedido(this, 'ENTREGUE')" title="Confirmar entrega">
            <i class="fa-solid fa-flag-checkered"></i> Entregue
        </button>`,
    'ENTREGUE': `
        <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
            <i class="fa-solid fa-eye"></i> Detalhes
        </button>`,
    'CANCELADO': `
        <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
            <i class="fa-solid fa-eye"></i> Detalhes
        </button>`,
};

function verDetalhesPedido(btn) {
    const tr = btn.closest('tr');
    const id = tr.cells[0].innerText;
    const cliente = tr.cells[1].innerText;
    const tipo = tr.cells[2].innerText;
    const pagamento = tr.cells[3].innerText;
    const total = tr.cells[4].innerText;
    const status = tr.dataset.status;
    
    document.getElementById('detalhe-id').innerText = id;
    document.getElementById('detalhe-cliente').innerText = cliente;
    document.getElementById('detalhe-tipo').innerText = tipo;
    document.getElementById('detalhe-pagamento').innerText = pagamento;
    document.getElementById('detalhe-total').innerText = total;

    // Popula itens fakes e endereço
    const fakeData = MOCK_PEDIDOS[id] || { 
        endereco: 'Endereço não informado', 
        itens: [{ desc: '1x Produto Genérico', preco: total }] 
    };
    
    document.getElementById('detalhe-endereco').innerText = fakeData.endereco;
    
    const ulItens = document.getElementById('detalhe-itens');
    ulItens.innerHTML = '';
    fakeData.itens.forEach(item => {
        ulItens.innerHTML += `
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #ddd; padding: 8px 0;">
                <span>${item.desc}</span>
                <strong>${item.preco}</strong>
            </li>
        `;
    });

    const btnImprimir = document.getElementById('btn-imprimir-comanda');
    if (status === 'PENDENTE' || status === 'CANCELADO') {
        btnImprimir.style.display = 'none';
    } else {
        btnImprimir.style.display = 'inline-block';
    }

    document.getElementById('modal-detalhes-pedido').style.display = 'flex';
}

function fecharModalDetalhes() {
    document.getElementById('modal-detalhes-pedido').style.display = 'none';
}

/* ── Cupom térmico: modal + impressão nativa (@media print + cupom_termico.css) ── */

function escHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function coletarDadosCupomDoModalDetalhes() {
    const id = document.getElementById('detalhe-id').innerText.trim();
    const cliente = document.getElementById('detalhe-cliente').innerText.trim();
    const tipo = document.getElementById('detalhe-tipo').innerText.trim();
    const pagamento = document.getElementById('detalhe-pagamento').innerText.trim();
    const endereco = document.getElementById('detalhe-endereco').innerText.trim();
    const total = document.getElementById('detalhe-total').innerText.trim();

    const itens = [];
    document.getElementById('detalhe-itens').querySelectorAll('li').forEach(li => {
        const span = li.querySelector('span');
        const strong = li.querySelector('strong');
        if (!span) return;
        itens.push({
            desc: span.innerText.trim(),
            preco: strong ? strong.innerText.trim() : '',
        });
    });

    const dataAtual = new Date().toLocaleString('pt-BR', { hour12: false });

    return { id, cliente, tipo, pagamento, endereco, total, dataAtual, itens };
}

function escHtmlNl(text) {
    if (text == null) return '';
    return String(text)
        .split('\n')
        .map(line => escHtml(line))
        .join('<br />');
}

function montarHtmlCupomTermico(dados, largura) {
    const w = largura === '58' ? '58' : largura === 'a4' ? 'a4' : '80';

    let rows = '';
    (dados.itens || []).forEach(item => {
        rows += `<tr><td class="col-desc">${escHtml(item.desc)}</td><td class="col-val">${escHtml(item.preco)}</td></tr>`;
    });
    if (!rows) {
        rows = `<tr><td class="col-desc" colspan="2">Sem itens listados.</td></tr>`;
    }

    return `
<div class="cupom-termico-sheet" data-largura="${w}">
  <p class="cupom-titulo">CHOKKO MELT</p>
  <p class="cupom-subtitulo">Comanda interna • Documento sem valor fiscal<br>Não válido como cupom fiscal / NFC-e</p>
  <hr class="cupom-divisor" />
  <p class="cupom-linha"><strong>Pedido</strong> ${escHtml(dados.id)}</p>
  <p class="cupom-linha"><strong>Data</strong> ${escHtml(dados.dataAtual)}</p>
  <p class="cupom-linha"><strong>Cliente</strong> ${escHtml(dados.cliente)}</p>
  <hr class="cupom-divisor" />
  <table class="cupom-itens-termico" data-largura="${w}">
    <thead>
      <tr><th class="col-desc">Item</th><th class="col-val">Valor</th></tr>
    </thead>
    <tbody>${rows}</tbody>
  </table>
  <div class="cupom-total-row">
    <span>TOTAL</span>
    <span>${escHtml(dados.total)}</span>
  </div>
  <hr class="cupom-divisor" />
  <p class="cupom-linha"><strong>Recebimento</strong> ${escHtml(dados.tipo)}</p>
  <p class="cupom-linha"><strong>Pagamento</strong> ${escHtml(dados.pagamento)}</p>
  <p class="cupom-linha cupom-obs-multi"><strong>Endereço / obs.</strong><br />${escHtmlNl(dados.endereco)}</p>
  <hr class="cupom-divisor" />
  <p class="cupom-central">Obrigado pela preferência.</p>
</div>`;
}

function obterLarguraCupomMm() {
    const sel = document.getElementById('print-cupom-largura');
    if (!sel) return '80';
    if (sel.value === '58') return '58';
    if (sel.value === 'a4') return 'a4';
    return '80';
}

function renderizarCupomNoModal(dados, largura) {
    const mount = document.getElementById('print-cupom-mount');
    if (mount) mount.innerHTML = montarHtmlCupomTermico(dados, largura);
}

/** Abre pré-visualização (sempre a partir dos dados já carregados no modal de detalhes). */
function abrirModalImpressaoCupom() {
    const dados = coletarDadosCupomDoModalDetalhes();
    const largura = obterLarguraCupomMm();
    renderizarCupomNoModal(dados, largura);

    const modal = document.getElementById('print-cupom-modal');
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function fecharModalImpressaoCupom() {
    const modal = document.getElementById('print-cupom-modal');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

/** Dispara impressão do sistema usando o mesmo HTML visto na pré-visualização. */
function executarImpressaoCupom() {
    const dados = coletarDadosCupomDoModalDetalhes();
    const largura = obterLarguraCupomMm();
    renderizarCupomNoModal(dados, largura);

    document.body.classList.remove('print-sheet-80', 'print-sheet-58', 'print-sheet-a4');
    if (largura === '58') document.body.classList.add('print-sheet-58');
    else if (largura === 'a4') document.body.classList.add('print-sheet-a4');
    else document.body.classList.add('print-sheet-80');

    const aoSairImpressao = () => {
        document.body.classList.remove('print-sheet-80', 'print-sheet-58', 'print-sheet-a4');
        window.removeEventListener('afterprint', aoSairImpressao);
    };
    window.addEventListener('afterprint', aoSairImpressao);

    window.print();
}

function atualizarStatusPedido(btn, novoStatus) {
    if (novoStatus === 'CANCELADO') {
        // Abre o modal de cancelamento em vez de fazer direto
        document.getElementById('motivo-cancelamento-admin').value = '';
        document.getElementById('contador-cancelamento-admin').innerText = '0';
        document.getElementById('modal-cancelar-pedido').style.display = 'flex';
        // Guarda qual botão iniciou o cancelamento (evita global no window)
        _btnCancelarTemp = btn;
        return;
    }

    executarMudancaStatus(btn, novoStatus);
}

function confirmarCancelamentoAdmin() {
    const motivo = document.getElementById('motivo-cancelamento-admin').value.trim();
    if (!motivo) {
        alert('O motivo do cancelamento é obrigatório.');
        return;
    }
    
    // NOTA BACKEND: Enviar o motivo no POST da API
    // Ex: POST { id_pedido, novo_status: 'CANCELADO', motivo_cancelamento: motivo }
    
    document.getElementById('modal-cancelar-pedido').style.display = 'none';
    if (_btnCancelarTemp) executarMudancaStatus(_btnCancelarTemp, 'CANCELADO');
    
    // Troca o alert pelo modal bonito
    document.getElementById('modal-sucesso-msg').innerText = 'O pedido foi cancelado com sucesso. Motivo registrado: ' + motivo;
    document.getElementById('modal-sucesso').style.display = 'flex';
}

function executarMudancaStatus(btn, novoStatus) {
    if (!btn) return;
    const tr = btn.closest('tr');
    if (!tr) return;
    const badgeCell = tr.querySelector('.badge');
    const acoesCell = tr.querySelector('.acoes-pedido');

    // Atualiza data-status na linha para o filtro funcionar
    tr.dataset.status = novoStatus;

    // Limpa badges anteriores e aplica o novo
    const info = BADGE_MAP[novoStatus] || { label: String(novoStatus || '—'), css: 'badge-pendente' };
    if (badgeCell) {
        badgeCell.className = `badge ${info.css}`;
        badgeCell.textContent = info.label;
    }

    // Atualiza botões de ação conforme o novo status
    if (acoesCell) acoesCell.innerHTML = ACOES_MAP[novoStatus] || '—';
}

// Contador de caracteres do textarea
document.addEventListener('DOMContentLoaded', () => {
    const txtAdmin = document.getElementById('motivo-cancelamento-admin');
    if (txtAdmin) {
        txtAdmin.addEventListener('input', function() {
            document.getElementById('contador-cancelamento-admin').innerText = this.value.length;
        });
    }

    /** Modal de cupom vai para o document.body (@media print + visibility herdada de ancestrais). */
    const modalPrint = document.getElementById('print-cupom-modal');
    if (modalPrint && modalPrint.parentElement !== document.body) {
        document.body.appendChild(modalPrint);
    }
    if (modalPrint) {
        modalPrint.addEventListener('click', (e) => {
            if (e.target === modalPrint) fecharModalImpressaoCupom();
        });
    }

    const selLarg = document.getElementById('print-cupom-largura');
    if (selLarg) {
        selLarg.addEventListener('change', () => {
            const open = modalPrint && modalPrint.classList.contains('is-open');
            if (!open) return;
            renderizarCupomNoModal(coletarDadosCupomDoModalDetalhes(), obterLarguraCupomMm());
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalPrint && modalPrint.classList.contains('is-open')) {
            fecharModalImpressaoCupom();
        }
    });
});

function filtrarPedidos(status, btn) {
    // Atualiza a tab ativa visualmente de forma limpa
    document.querySelectorAll('#pedidos-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Filtra as linhas da tabela
    document.querySelectorAll('#tabela-pedidos tbody tr').forEach(tr => {
        if (status === 'todos' || tr.dataset.status === status) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}
