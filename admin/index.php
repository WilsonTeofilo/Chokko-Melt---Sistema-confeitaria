<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/pedidos.css">
<link rel="stylesheet" href="assets/css/cupom_termico.css">

<section class="welcome-area">
    <h1>Gerenciar Pedidos</h1>
    <p>Acompanhe e gerencie todos os pedidos em tempo real.</p>
</section>

<!-- Tabs de status dos pedidos -->
<div class="status-tabs" id="pedidos-tabs">
    <button class="tab-btn active" onclick="filtrarPedidos('todos', this)">Todos</button>
    <button class="tab-btn" onclick="filtrarPedidos('PENDENTE', this)">Pendentes</button>
    <button class="tab-btn" onclick="filtrarPedidos('EM_PREPARO', this)">Em Preparo</button>
    <button class="tab-btn" onclick="filtrarPedidos('ENVIADO', this)">Enviados</button>
    <button class="tab-btn" onclick="filtrarPedidos('ENTREGUE', this)">Entregues</button>
    <button class="tab-btn" onclick="filtrarPedidos('CANCELADO', this)">Cancelados</button>
</div>

<section class="table-wrapper">
    <table class="admin-table" id="tabela-pedidos">
        <thead>
            <tr>
                <th>ID</th>
                <th>CLIENTE</th>
                <th>TIPO</th>
                <th>PAGAMENTO</th>
                <th>VALOR</th>
                <th>STATUS</th>
                <th>AÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <!-- MODELO PARA FOREACH PHP -->
            <tr data-status="[STATUS_DO_PEDIDO]">
                <td>#[id_pedido]</td>
                <td>[nome_do_cliente]</td>
                <td><i class="[icone_tipo_entrega]"></i> [tipo_entrega]</td>
                <td>[forma_pagamento]</td>
                <td class="bold-text">R$ [valor_total]</td>
                <td><span class="badge [classe_badge_status]">[status_legivel]</span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                    <!-- Exibir os botões abaixo conforme o status do pedido -->
                    <!-- PENDENTE: Aceitar + Cancelar -->
                    <!-- EM_PREPARO: Enviar + Cancelar -->
                    <!-- ENVIADO: Entregue -->
                    <!-- ENTREGUE: apenas Detalhes -->
                </td>
            </tr>
            <!-- FIM MODELO PHP -->
        </tbody>
    </table>
</section>

<!-- MODAL DETALHES DO PEDIDO -->
<div id="modal-detalhes-pedido" class="modal-overlay modal-detalhes-overlay">
    <div class="modal-content modal-detalhes-content">
        <div class="modal-header">
            <h2 class="modal-title">Detalhes do Pedido <span id="detalhe-id" class="detalhe-id-text">#0000</span></h2>
            <button onclick="fecharModalDetalhes()" class="btn-close-modal">&times;</button>
        </div>
        <div class="detalhes-body detalhe-body-wrap">
            <p class="detalhe-p"><strong>Cliente:</strong> <span id="detalhe-cliente">...</span></p>
            <p class="detalhe-p"><strong>Pagamento:</strong> <span id="detalhe-pagamento">...</span></p>
            <p class="detalhe-p"><strong>Tipo:</strong> <span id="detalhe-tipo">...</span></p>
            <p class="detalhe-p"><strong>Endereço / Observação:</strong> <span id="detalhe-endereco">Rua Fictícia, 123 - Centro</span></p>
            
            <h3 class="detalhe-h3">Itens do Pedido</h3>
            <ul id="detalhe-itens" class="detalhe-ul">
                <li class="detalhe-li-border">
                    <span>2x Bolo de Cenoura com Chocolate</span>
                    <strong>R$ 30,00</strong>
                </li>
                <li class="detalhe-li">
                    <span>1x Torta de Morango</span>
                    <strong>R$ 15,00</strong>
                </li>
            </ul>
            <div class="detalhe-total-wrap">
                <strong>Total: <span id="detalhe-total" class="detalhe-total-text">R$ 45,00</span></strong>
            </div>
        </div>
        <div class="modal-footer modal-footer-wrap">
            <button type="button" id="btn-imprimir-comanda" class="btn-action-accept btn-print-comanda" onclick="abrirModalImpressaoCupom()"><i class="fa-solid fa-print"></i> Imprimir comanda</button>
            <button type="button" onclick="fecharModalDetalhes()" class="btn-fechar-modal">Fechar</button>
        </div>
    </div>
</div>

<!-- MODAL pré-visualização + impressão térmica (58mm / 80mm) -->
<div id="print-cupom-modal" aria-hidden="true">
    <div class="print-cupom-dialog">
        <div class="print-cupom-toolbar">
            <label for="print-cupom-largura">Largura do papel</label>
            <select id="print-cupom-largura" aria-label="Largura do papel térmico">
                <option value="80" selected>80 mm (bobina larga)</option>
                <option value="58">58 mm (bobina estreita)</option>
                <option value="a4">A4 / PDF — texto grande (impressora comum)</option>
            </select>
            <div class="print-cupom-actions">
                <button type="button" class="btn-print-go" onclick="executarImpressaoCupom()"><i class="fa-solid fa-print"></i> Imprimir</button>
                <button type="button" class="btn-print-close" onclick="fecharModalImpressaoCupom()">Fechar</button>
            </div>
        </div>
        <div class="print-cupom-body">
            <div id="print-cupom-mount"></div>
        </div>
    </div>
</div>

<!-- MODAL CANCELAR PEDIDO (ADMIN) -->
<div id="modal-cancelar-pedido" class="modal-overlay modal-cancel-overlay">
    <div class="modal-content modal-cancel-content">
        <div class="modal-header">
            <h2 class="modal-title cancel-title">Cancelar Pedido</h2>
            <button onclick="document.getElementById('modal-cancelar-pedido').style.display='none'" class="btn-close-modal">&times;</button>
        </div>
        <div class="detalhes-body detalhe-body-wrap">
            <p class="cancel-p">Informe o motivo do cancelamento para o cliente (obrigatório):</p>
            <textarea id="motivo-cancelamento-admin" class="cancel-textarea" maxlength="250" placeholder="Ex: Produto indisponível, Fora do horário..."></textarea>
            <small class="cancel-counter"><span id="contador-cancelamento-admin">0</span>/250</small>
            <!-- Variável oculta pra guardar qual tr ou botão iniciou o cancelamento -->
            <input type="hidden" id="btn-cancelar-ref">
        </div>
        <div class="modal-footer modal-footer-wrap">
            <button type="button" class="btn-action-accept btn-confirm-cancel" onclick="confirmarCancelamentoAdmin()">Confirmar Cancelamento</button>
            <button type="button" onclick="document.getElementById('modal-cancelar-pedido').style.display='none'" class="btn-fechar-modal">Voltar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCESSO -->
<div id="modal-sucesso" class="modal-overlay modal-success-overlay">
    <div class="modal-content modal-success-content">
        <i class="fa-solid fa-circle-check success-icon"></i>
        <h2 class="success-title">Ação Concluída</h2>
        <p id="modal-sucesso-msg" class="success-msg"></p>
        <button onclick="document.getElementById('modal-sucesso').style.display='none'" class="btn-success-ok">Entendi</button>
    </div>
</div>

<!-- pedidos.js: status, cupom térmico, modais; cada ação deve ter endpoint PHP (ex.: api/atualizar_status_pedido.php). INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/pedidos.js"></script>

<?php include '../includes/admin_footer.php'; ?>
