<?php 
require_once '../config/config.php';
include '../includes/admin_header.php'; 

// Carrega os pedidos reais do banco de dados
try {
    $sql = "
        SELECT p.id_pedido, p.data_hora, p.valor_total, p.subtotal, p.taxa_entrega, p.observacao AS obs_pedido, p.tipo_entrega, p.cpf_nota,
               p.motivo_cancelamento, p.cancelado_por, p.id_status_pedido,
               sp.descricao AS status_descricao,
               c.nome AS nome_cliente, c.email AS email_cliente, c.telefone AS telefone_cliente,
               e.rua, e.numero, e.complemento, e.bairro, e.cep, e.ponto_referencia,
               pg.forma_pagamento, pg.valor_entregue, pg.troco
        FROM pedido p
        INNER JOIN status_pedido sp ON p.id_status_pedido = sp.id_status_pedido
        LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
        LEFT JOIN endereco e ON p.id_endereco = e.id_endereco
        LEFT JOIN pagamento pg ON pg.id_pedido = p.id_pedido
        ORDER BY p.id_pedido DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca itens de cada pedido
    foreach ($pedidos as &$p) {
        $stmtItens = $conn->prepare("
            SELECT ip.id_item_pedido, ip.quantidade, ip.preco_unitario, ip.observacao AS obs_item,
                   COALESCE(prod.nome, 'Produto Indisponível') AS nome_produto
            FROM item_pedido ip
            LEFT JOIN produto prod ON ip.id_produto = prod.id_produto
            WHERE ip.id_pedido = :id_pedido
        ");
        $stmtItens->execute(['id_pedido' => $p['id_pedido']]);
        $itensPedido = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

        foreach ($itensPedido as &$it) {
            // Busca os adicionais deste item do pedido
            $stmtAd = $conn->prepare("
                SELECT nome_snapshot, preco_unitario_snapshot
                FROM item_pedido_adicional
                WHERE id_item_pedido = :id_item
            ");
            $stmtAd->execute(['id_item' => $it['id_item_pedido']]);
            $it['adicionais'] = $stmtAd->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($it);

        $p['itens'] = $itensPedido;
    }
    unset($p);
} catch (Exception $e) {
    $pedidos = [];
}
?>
<link rel="stylesheet" href="assets/css/pedidos.css?v=<?= time() ?>">
<link rel="stylesheet" href="assets/css/cupom_termico.css?v=<?= time() ?>">

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
            <?php foreach ($pedidos as $p): 
                // Mapeia status legível
                $status = $p['status_descricao'];
                
                // Mapeia ícones e tipos
                $icone_tipo = 'fa-solid fa-motorcycle';
                if ($p['tipo_entrega'] === 'RETIRADA') {
                    $icone_tipo = 'fa-solid fa-bag-shopping';
                } elseif ($p['tipo_entrega'] === 'LOCAL') {
                    $icone_tipo = 'fa-solid fa-store';
                }

                // Ajusta a classe do badge
                $badge_class = 'badge-pendente';
                $status_legivel = 'PENDENTE';
                if ($status === 'ACEITO' || $status === 'EM_PREPARO') {
                    $badge_class = 'badge-preparo';
                    $status_legivel = 'EM PREPARO';
                    $status = 'EM_PREPARO'; // Unifica para o filtro do JS
                } elseif ($status === 'ENVIADO') {
                    $badge_class = 'badge-enviado';
                    if ($p['tipo_entrega'] === 'RETIRADA') {
                        $status_legivel = 'PRONTO P/ RETIRADA';
                    } elseif ($p['tipo_entrega'] === 'LOCAL') {
                        $status_legivel = 'SERVIDO';
                    } else {
                        $status_legivel = 'ENVIADO';
                    }
                } elseif ($status === 'ENTREGUE') {
                    $badge_class = 'badge-entregue';
                    $status_legivel = 'ENTREGUE';
                } elseif (strpos($status, 'CANCELADO') === 0) {
                    $badge_class = 'badge-cancelado';
                    $status_legivel = 'CANCELADO';
                    $status = 'CANCELADO'; // Unifica para o filtro do JS
                }

                // Endereço completo formatado
                $end_formatado = '';
                if ($p['tipo_entrega'] === 'DELIVERY' && !empty($p['rua'])) {
                    $end_formatado = $p['rua'] . ', ' . $p['numero'];
                    if (!empty($p['complemento'])) $end_formatado .= ' - ' . $p['complemento'];
                    $end_formatado .= ', ' . $p['bairro'] . ' - CEP ' . $p['cep'];
                    if (!empty($p['ponto_referencia'])) $end_formatado .= ' (Ref: ' . $p['ponto_referencia'] . ')';
                } else {
                    $end_formatado = ($p['tipo_entrega'] === 'RETIRADA') ? 'Retirar no Balcão' : 'Consumir no Local';
                }
                
                if (!empty($p['obs_pedido'])) {
                    $end_formatado .= ' | OBS: "' . $p['obs_pedido'] . '"';
                }
            ?>
            <tr data-status="<?= htmlspecialchars($status) ?>"
                data-endereco="<?= htmlspecialchars($end_formatado) ?>"
                data-itens='<?= htmlspecialchars(json_encode($p['itens']), ENT_QUOTES, 'UTF-8') ?>'
                data-tipo-entrega="<?= htmlspecialchars($p['tipo_entrega']) ?>">
                <td>#<?= $p['id_pedido'] ?></td>
                <td><?= htmlspecialchars($p['nome_cliente'] ?? 'Cliente Excluído') ?></td>
                <td><i class="<?= $icone_tipo ?>"></i> <?= htmlspecialchars($p['tipo_entrega']) ?></td>
                <td>
                    <?= htmlspecialchars($p['forma_pagamento']) ?>
                    <?php if ($p['forma_pagamento'] === 'DINHEIRO' && $p['troco'] > 0): ?>
                        <br><small style="color: #666;">Troco: R$ <?= number_format($p['troco'], 2, ',', '.') ?></small>
                    <?php endif; ?>
                </td>
                <td class="bold-text">R$ <?= number_format($p['valor_total'], 2, ',', '.') ?></td>
                <td><span class="badge <?= $badge_class ?>"><?= $status_legivel ?></span></td>
                <td class="acoes-pedido">
                    <button class="btn-action-accept btn-detalhes" onclick="verDetalhesPedido(this)" title="Ver Detalhes">
                        <i class="fa-solid fa-eye"></i> Detalhes
                    </button>
                    
                    <?php if ($status === 'PENDENTE'): ?>
                        <button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, 'EM_PREPARO')" title="Aceitar pedido"><i class="fa-solid fa-check"></i> Aceitar</button>
                        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>
                    <?php elseif ($status === 'EM_PREPARO' || $status === 'ACEITO'): ?>
                        <?php if ($p['tipo_entrega'] === 'DELIVERY'): ?>
                            <button class="btn-action-accept btn-enviar" onclick="atualizarStatusPedido(this, 'ENVIADO')" title="Marcar como enviado"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
                        <?php elseif ($p['tipo_entrega'] === 'RETIRADA'): ?>
                            <button class="btn-action-accept btn-enviar btn-pronto-retirar" onclick="atualizarStatusPedido(this, 'ENVIADO')" title="Marcar como pronto para retirada"><i class="fa-solid fa-box"></i> Pronto p/ Retirada</button>
                        <?php else: /* LOCAL */ ?>
                            <button class="btn-action-accept btn-enviar btn-pronto-local" onclick="atualizarStatusPedido(this, 'ENVIADO')" title="Marcar como servido"><i class="fa-solid fa-utensils"></i> Servido</button>
                        <?php endif; ?>
                        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>
                    <?php elseif ($status === 'ENVIADO'): ?>
                        <button class="btn-action-accept btn-entregar" onclick="atualizarStatusPedido(this, 'ENTREGUE')" title="Confirmar entrega"><i class="fa-solid fa-flag-checkered"></i> Entregue</button>
                        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>
                    <?php elseif ($status === 'ENTREGUE'): ?>
                        <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar"><i class="fa-solid fa-xmark"></i> Cancelar</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
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
