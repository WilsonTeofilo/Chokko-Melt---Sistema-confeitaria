// ============================================================
// pedidos.js — Admin / Pedidos
// ============================================================

var _btnCancelarTemp = null;

function obterHtmlAcoes(status, tipoEntrega) {
    var botoes = '<button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes"><i class="fa-solid fa-eye"></i> Detalhes</button> ';
    
    if (status === 'PENDENTE') {
        botoes += '<button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, \'EM_PREPARO\')" title="Aceitar pedido"><i class="fa-solid fa-check"></i> Aceitar</button> ' +
                  '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>';
    } else if (status === 'EM_PREPARO' || status === 'ACEITO') {
        if (tipoEntrega === 'DELIVERY') {
            botoes += '<button class="btn-action-accept btn-enviar" onclick="atualizarStatusPedido(this, \'ENVIADO\')" title="Marcar como enviado"><i class="fa-solid fa-paper-plane"></i> Enviar</button> ';
        } else if (tipoEntrega === 'RETIRADA') {
            botoes += '<button class="btn-action-accept btn-enviar btn-pronto-retirar" onclick="atualizarStatusPedido(this, \'ENVIADO\')" title="Marcar como pronto para retirada"><i class="fa-solid fa-box"></i> Pronto p/ Retirada</button> ';
        } else { // LOCAL
            botoes += '<button class="btn-action-accept btn-enviar btn-pronto-local" onclick="atualizarStatusPedido(this, \'ENVIADO\')" title="Marcar como servido"><i class="fa-solid fa-utensils"></i> Servido</button> ';
        }
        botoes += '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>';
    } else if (status === 'ENVIADO') {
        botoes += '<button class="btn-action-accept btn-entregar" onclick="atualizarStatusPedido(this, \'ENTREGUE\')" title="Confirmar entrega"><i class="fa-solid fa-flag-checkered"></i> Entregue</button> ' +
                  '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>';
    } else if (status === 'ENTREGUE') {
        botoes += '<button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, \'CANCELADO\')" title="Estornar pedido"><i class="fa-solid fa-arrow-rotate-left"></i> Estornar</button>';
    }
    
    return botoes;
}

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

function formatarDataHora(str) {
    if (!str) return '';
    var partes = str.split(' ');
    if (partes.length < 2) return str;
    var dataPartes = partes[0].split('-');
    var horaPartes = partes[1].split(':');
    if (dataPartes.length < 3 || horaPartes.length < 2) return str;
    return dataPartes[2] + '/' + dataPartes[1] + '/' + dataPartes[0] + ' ' + horaPartes[0] + ':' + horaPartes[1];
}

function verDetalhesPedido(btn) {
    var tr = btn.closest('tr');
    var id = tr.cells[0].innerText;
    var cliente = tr.cells[1].innerText;
    var tipo = tr.cells[2].innerText;
    var pagamento = tr.cells[3].innerText;
    var total = tr.cells[4].innerText;
    var status = tr.dataset.status;
    var dataHora = tr.dataset.dataHora || '';

    document.getElementById('detalhe-id').innerText = id;
    document.getElementById('detalhe-cliente').innerText = cliente;
    document.getElementById('detalhe-data-hora').innerText = formatarDataHora(dataHora);
    document.getElementById('detalhe-tipo').innerText = tipo;
    document.getElementById('detalhe-pagamento').innerText = pagamento;
    document.getElementById('detalhe-total').innerText = total;
    
    // Puxa endereço real do data-attribute
    document.getElementById('detalhe-endereco').innerText = tr.dataset.endereco || 'Retirada ou Consumo Local';

    // Monta itens reais do data-attribute
    var ulItens = document.getElementById('detalhe-itens');
    ulItens.innerHTML = '';
    
    if (tr.dataset.itens) {
        try {
            var itens = JSON.parse(tr.dataset.itens);
            itens.forEach(function(item) {
                var li = document.createElement('li');
                li.className = 'detalhe-li-border';
                
                var qtd = item.quantidade || 1;
                var precoUnit = parseFloat(item.preco_unitario || 0);
                
                var precoAdicionais = 0;
                var htmlAdicionais = '';
                if (item.adicionais && item.adicionais.length > 0) {
                    var nomesAd = [];
                    item.adicionais.forEach(function(ad) {
                        precoAdicionais += parseFloat(ad.preco_unitario_snapshot);
                        nomesAd.push(ad.nome_snapshot + ' (+R$ ' + parseFloat(ad.preco_unitario_snapshot).toFixed(2).replace('.', ',') + ')');
                    });
                    htmlAdicionais = '<br><span style="font-size: 0.8rem; color: #757575;">Adicionais: ' + nomesAd.join(', ') + '</span>';
                }

                var htmlObs = '';
                if (item.obs_item && item.obs_item.trim() !== '') {
                    htmlObs = '<br><span style="font-size: 0.8rem; font-style: italic; color: #d32f2f; font-weight: bold;">Obs: "' + item.obs_item.trim() + '"</span>';
                }
                
                var sub = qtd * (precoUnit + precoAdicionais);
                
                li.innerHTML = '<div><span>' + qtd + 'x ' + item.nome_produto + '</span>' + htmlAdicionais + htmlObs + '</div>' +
                               '<strong>R$ ' + sub.toFixed(2).replace('.', ',') + '</strong>';
                ulItens.appendChild(li);
            });
        } catch (e) {
            ulItens.innerHTML = '<li class="detalhe-li">Erro ao processar itens.</li>';
        }
    } else {
        ulItens.innerHTML = '<li class="detalhe-li">Sem itens para exibir.</li>';
    }

    var btnImprimir = document.getElementById('btn-imprimir-comanda');
    if (btnImprimir) {
        btnImprimir.style.display = 'inline-block';
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
        var strong = lis[i].querySelector('strong');
        var divDesc = lis[i].querySelector('div');
        if (divDesc) {
            itens.push({
                desc: divDesc.innerText.trim().replace(/\n/g, ' '),
                preco: strong ? strong.innerText.trim() : ''
            });
        } else {
            var span = lis[i].querySelector('span');
            if (span) {
                itens.push({
                    desc: span.innerText.trim(),
                    preco: strong ? strong.innerText.trim() : ''
                });
            }
        }
    }

    var data = document.getElementById('detalhe-data-hora').innerText.trim();

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

    // Ajusta o tamanho da folha fisicamente para 58mm (padrão POS-58)
    document.body.classList.remove('print-sheet-58', 'print-sheet-80');
    document.body.classList.add('print-sheet-58');
    
    var styleId = 'dynamic-print-page-size';
    var styleEl = document.getElementById(styleId);
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = styleId;
        document.head.appendChild(styleEl);
    }
    styleEl.innerHTML = '@media print { @page { margin: 0 !important; } }';

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
    styleEl.innerHTML = '@media print { @page { margin: 0 !important; } }';

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
        var tr = btn.closest('tr');
        var statusAtual = tr ? tr.dataset.status : '';
        var msgConfirm = (statusAtual === 'ENTREGUE') 
            ? 'Deseja realmente estornar este pedido?' 
            : 'Deseja realmente cancelar este pedido?';

        if (!confirm(msgConfirm)) {
            return;
        }

        // Ajusta os textos do modal conforme statusAtual
        var modalTitle = document.querySelector('.cancel-title');
        var modalText = document.querySelector('.cancel-p');
        var modalBtn = document.querySelector('.btn-confirm-cancel');
        
        if (statusAtual === 'ENTREGUE') {
            if (modalTitle) modalTitle.innerText = 'Estornar Pedido';
            if (modalText) modalText.innerText = 'Informe o motivo do estorno para o cliente (obrigatório):';
            if (modalBtn) modalBtn.innerText = 'Confirmar Estorno';
        } else {
            if (modalTitle) modalTitle.innerText = 'Cancelar Pedido';
            if (modalText) modalText.innerText = 'Informe o motivo do cancelamento para o cliente (obrigatório):';
            if (modalBtn) modalBtn.innerText = 'Confirmar Cancelamento';
        }

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
        var modalTitle = document.querySelector('.cancel-title');
        var isEstorno = modalTitle && modalTitle.innerText.indexOf('Estornar') !== -1;
        alert(isEstorno ? 'O motivo do estorno é obrigatório.' : 'O motivo do cancelamento é obrigatório.');
        return;
    }
    if (_btnCancelarTemp) {
        executarMudancaStatus(_btnCancelarTemp, 'CANCELADO');
    }
}

function executarMudancaStatus(btn, novoStatus) {
    if (!btn) return;
    var tr = btn.closest('tr');
    if (!tr) return;

    var id_pedido = tr.cells[0].innerText.replace('#', '').trim();
    var motivo = '';
    if (novoStatus === 'CANCELADO') {
        motivo = document.getElementById('motivo-cancelamento-admin').value.trim();
    }

    var formData = new FormData();
    formData.append('id_pedido', id_pedido);
    formData.append('novo_status', novoStatus);
    formData.append('motivo', motivo);

    // Faz chamada Fetch AJAX para o backend atualizar o banco
    fetch('src/atualizar_status_pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            tr.dataset.status = novoStatus;

            var textoBadgeEnviado = 'ENVIADO';
            var tipoEntrega = tr.dataset.tipoEntrega || 'DELIVERY';
            if (tipoEntrega === 'RETIRADA') {
                textoBadgeEnviado = 'PRONTO P/ RETIRADA';
            } else if (tipoEntrega === 'LOCAL') {
                textoBadgeEnviado = 'SERVIDO';
            }

            var badgeMap = {
                'ACEITO':     ['badge-preparo', 'EM PREPARO'],
                'EM_PREPARO': ['badge-preparo', 'EM PREPARO'],
                'ENVIADO':    ['badge-enviado', textoBadgeEnviado],
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
            if (acoes) acoes.innerHTML = obterHtmlAcoes(novoStatus, tr.dataset.tipoEntrega);

            // Se for cancelamento, fecha o modal e exibe a justificativa no modal de sucesso
            if (novoStatus === 'CANCELADO') {
                var modalTitle = document.querySelector('.cancel-title');
                var isEstorno = modalTitle && modalTitle.innerText.indexOf('Estornar') !== -1;
                document.getElementById('modal-cancelar-pedido').style.display = 'none';
                
                if (isEstorno) {
                    document.getElementById('modal-sucesso-msg').innerText =
                        'Pedido #' + id_pedido + ' estornado. Motivo: ' + motivo;
                } else {
                    document.getElementById('modal-sucesso-msg').innerText =
                        'Pedido #' + id_pedido + ' cancelado. Motivo: ' + motivo;
                }
            } else {
                document.getElementById('modal-sucesso-msg').innerText =
                    'Pedido #' + id_pedido + ' atualizado para ' + info[1] + ' com sucesso!';
            }
            document.getElementById('modal-sucesso').style.display = 'flex';
        } else {
            alert('Erro ao atualizar no banco: ' + data.erro);
        }
    })
    .catch(error => {
        console.error('Erro na requisição AJAX:', error);
        alert('Erro ao conectar ao servidor para atualizar o status do pedido.');
    });
}

// ── INIT ──

var _ultimoIdPedido = 0;
var _audioNotification = null;

function iniciarChecagemNovosPedidos() {
    // Acha o maior ID de pedido na tabela
    var trs = document.querySelectorAll('#tabela-pedidos tbody tr');
    for (var i = 0; i < trs.length; i++) {
        var idText = trs[i].cells[0].innerText.replace('#', '').trim();
        var idVal = parseInt(idText);
        if (idVal > _ultimoIdPedido) {
            _ultimoIdPedido = idVal;
        }
    }

    // Cria o elemento de áudio
    _audioNotification = new Audio('assets/notification%20fah.mp3');

    // Checa a cada 10 segundos
    setInterval(function() {
        fetch('src/checar_novos_pedidos.php?ultimo_id=' + _ultimoIdPedido)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.sucesso && data.tem_novo) {
                    _ultimoIdPedido = data.novo_id;
                    
                    // Toca a notificação sonora
                    if (_audioNotification) {
                        _audioNotification.play().catch(function(e) {
                            console.log('Autoplay do som de notificação bloqueado pelo navegador. Interaja com a página primeiro.', e);
                        });
                    }
                    
                    // Exibe aviso visual
                    var welcome = document.querySelector('.welcome-area');
                    if (welcome) {
                        var alertDiv = document.createElement('div');
                        alertDiv.style.background = '#43A047';
                        alertDiv.style.color = '#fff';
                        alertDiv.style.padding = '12px 20px';
                        alertDiv.style.borderRadius = '8px';
                        alertDiv.style.marginTop = '15px';
                        alertDiv.style.fontWeight = 'bold';
                        alertDiv.className = 'new-order-alert';
                        alertDiv.innerHTML = '<i class="fa-solid fa-bell fa-shake"></i> Novo pedido recebido! Recarregando painel...';
                        welcome.appendChild(alertDiv);
                    }
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                }
            })
            .catch(function(err) {
                console.error('Erro ao verificar novos pedidos:', err);
            });
    }, 10000);
}

document.addEventListener('DOMContentLoaded', function () {
    // Contador do textarea de cancelamento
    var txtAdmin = document.getElementById('motivo-cancelamento-admin');
    if (txtAdmin) {
        txtAdmin.addEventListener('input', function () {
            document.getElementById('contador-cancelamento-admin').innerText = this.value.length;
        });
    }
    
    // Inicia a verificação de novos pedidos
    iniciarChecagemNovosPedidos();
});
