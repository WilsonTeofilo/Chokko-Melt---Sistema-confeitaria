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
            <tr data-status="PENDENTE">
                <td>#1024</td>
                <td>Juliana Souza</td>
                <td><i class="fa-solid fa-motorcycle"></i> Delivery</td>
                <td>Pix</td>
                <td class="bold-text">R$ 45,00</td>
                <td><span class="badge badge-pendente">PENDENTE</span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                    <button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, 'EM_PREPARO')" title="Aceitar pedido">
                        <i class="fa-solid fa-check"></i> Aceitar
                    </button>
                    <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar pedido">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                </td>
            </tr>
            <tr data-status="EM_PREPARO">
                <td>#1023</td>
                <td>Carlos Pereira</td>
                <td><i class="fa-solid fa-store"></i> Retirada</td>
                <td>Dinheiro</td>
                <td class="bold-text">R$ 22,00</td>
                <td><span class="badge badge-preparo">EM PREPARO</span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                    <button class="btn-action-accept btn-enviar" onclick="atualizarStatusPedido(this, 'ENVIADO')" title="Marcar como enviado">
                        <i class="fa-solid fa-paper-plane"></i> Enviar
                    </button>
                    <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar pedido">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                </td>
            </tr>
            <tr data-status="ENVIADO">
                <td>#1022</td>
                <td>Ana Oliveira</td>
                <td><i class="fa-solid fa-motorcycle"></i> Delivery</td>
                <td>Cartão</td>
                <td class="bold-text">R$ 67,50</td>
                <td><span class="badge badge-enviado">ENVIADO</span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                    <button class="btn-action-accept btn-entregar" onclick="atualizarStatusPedido(this, 'ENTREGUE')" title="Confirmar entrega">
                        <i class="fa-solid fa-flag-checkered"></i> Entregue
                    </button>
                </td>
            </tr>
            <tr data-status="ENTREGUE">
                <td>#1021</td>
                <td>Marcos Lima</td>
                <td><i class="fa-solid fa-motorcycle"></i> Delivery</td>
                <td>Pix</td>
                <td class="bold-text">R$ 30,00</td>
                <td><span class="badge badge-entregue">ENTREGUE</span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</section>

<!-- MODAL DETALHES DO PEDIDO -->
<div id="modal-detalhes-pedido" class="modal-overlay" style="z-index: 2005; display: none;">
    <div class="modal-content" style="width: 500px; max-width: 90%;">
        <div class="modal-header">
            <h2 class="modal-title">Detalhes do Pedido <span id="detalhe-id" style="color: var(--marrom);">#0000</span></h2>
            <button onclick="fecharModalDetalhes()" class="btn-close-modal">&times;</button>
        </div>
        <div class="detalhes-body" style="margin-bottom: 20px;">
            <p style="margin-bottom: 5px;"><strong>Cliente:</strong> <span id="detalhe-cliente">...</span></p>
            <p style="margin-bottom: 5px;"><strong>Pagamento:</strong> <span id="detalhe-pagamento">...</span></p>
            <p style="margin-bottom: 5px;"><strong>Tipo:</strong> <span id="detalhe-tipo">...</span></p>
            <p style="margin-bottom: 5px;"><strong>Endereço / Observação:</strong> <span id="detalhe-endereco">Rua Fictícia, 123 - Centro</span></p>
            
            <h3 style="margin-top: 15px; margin-bottom: 10px; font-size: 1.1rem; color: var(--marrom);">Itens do Pedido</h3>
            <ul id="detalhe-itens" style="list-style: none; padding: 0; margin: 0; background: #f9f9f9; border-radius: 8px; padding: 10px;">
                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #ddd; padding: 8px 0;">
                    <span>2x Bolo de Cenoura com Chocolate</span>
                    <strong>R$ 30,00</strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>1x Torta de Morango</span>
                    <strong>R$ 15,00</strong>
                </li>
            </ul>
            <div style="display: flex; justify-content: flex-end; margin-top: 15px; font-size: 1.2rem;">
                <strong>Total: <span id="detalhe-total" style="color: var(--rosa-chokko);">R$ 45,00</span></strong>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
            <button type="button" id="btn-imprimir-comanda" class="btn-action-accept" style="background: #1976D2; padding: 10px 20px; font-weight: bold; border-radius: 6px; color: white;" onclick="abrirModalImpressaoCupom()"><i class="fa-solid fa-print"></i> Imprimir comanda</button>
            <button type="button" onclick="fecharModalDetalhes()" style="padding: 10px 20px; background: #eee; border:none; border-radius: 6px; cursor: pointer; font-weight: bold; color: #555;">Fechar</button>
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
<div id="modal-cancelar-pedido" class="modal-overlay" style="z-index: 2010; display: none;">
    <div class="modal-content" style="width: 400px; max-width: 90%;">
        <div class="modal-header">
            <h2 class="modal-title" style="color: #E53935;">Cancelar Pedido</h2>
            <button onclick="document.getElementById('modal-cancelar-pedido').style.display='none'" class="btn-close-modal">&times;</button>
        </div>
        <div class="detalhes-body" style="margin-bottom: 20px;">
            <p style="margin-bottom: 10px;">Informe o motivo do cancelamento para o cliente (obrigatório):</p>
            <textarea id="motivo-cancelamento-admin" style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; resize: none;" maxlength="250" placeholder="Ex: Produto indisponível, Fora do horário..."></textarea>
            <small style="color: #777; float: right; margin-top: 5px;"><span id="contador-cancelamento-admin">0</span>/250</small>
            <!-- Variável oculta pra guardar qual tr ou botão iniciou o cancelamento -->
            <input type="hidden" id="btn-cancelar-ref">
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
            <button type="button" class="btn-action-accept" style="background: #E53935; padding: 10px 20px; font-weight: bold; border-radius: 6px; color: white;" onclick="confirmarCancelamentoAdmin()">Confirmar Cancelamento</button>
            <button type="button" onclick="document.getElementById('modal-cancelar-pedido').style.display='none'" style="padding: 10px 20px; background: #eee; border:none; border-radius: 6px; cursor: pointer; font-weight: bold; color: #555;">Voltar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCESSO -->
<div id="modal-sucesso" class="modal-overlay" style="z-index: 9999; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; padding: 20px;">
    <div class="modal-content" style="background: white; width: 350px; max-width: 100%; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <i class="fa-solid fa-circle-check" style="font-size: 3.5rem; color: #43A047; margin-bottom: 15px;"></i>
        <h2 style="color: var(--marrom); margin-bottom: 10px; font-size: 1.4rem;">Ação Concluída</h2>
        <p id="modal-sucesso-msg" style="color: #666; margin-bottom: 20px; line-height: 1.4;"></p>
        <button onclick="document.getElementById('modal-sucesso').style.display='none'" style="background: var(--marrom); color: white; border: none; padding: 10px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;">Entendi</button>
    </div>
</div>

<!-- pedidos.js: status, cupom térmico, modais; cada ação deve ter endpoint PHP (ex.: api/atualizar_status_pedido.php). INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/pedidos.js"></script>

<?php include '../includes/admin_footer.php'; ?>
