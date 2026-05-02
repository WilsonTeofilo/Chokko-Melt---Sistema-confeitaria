<?php include '../includes/user_header.php'; ?>
<link rel="stylesheet" href="assets/css/pedidos.css">

<?php
/*
 * ══════════════════════════════════════════════════════════════
 *  NOTA BACKEND — INTEGRAÇÃO PHP/MYSQL
 * ══════════════════════════════════════════════════════════════
 *
 *  1. Receber ID via GET e buscar pedido:
 *     $pedido_id = intval($_GET['id'] ?? 0);
 *
 *     SELECT p.id, p.status, p.total, p.subtotal, p.taxa_entrega,
 *            p.desconto, p.forma_entrega, p.forma_pagamento,
 *            p.criado_em, p.producao_em, p.saiu_em, p.entregue_em,
 *            e.logradouro, e.numero, e.bairro, e.complemento
 *     FROM pedidos p
 *     LEFT JOIN enderecos e ON e.id = p.endereco_id
 *     WHERE p.id = $pedido_id
 *     AND p.usuario_id = $_SESSION['usuario_id'];
 *
 *  2. Buscar itens do pedido:
 *     SELECT pi.quantidade, pi.preco_unitario, pi.obs,
 *            prod.nome, prod.imagem,
 *            GROUP_CONCAT(pa.nome SEPARATOR ', ') AS adicionais
 *     FROM pedido_itens pi
 *     INNER JOIN produtos prod ON prod.id = pi.produto_id
 *     LEFT JOIN pedido_item_adicionais pia ON pia.item_id = pi.id
 *     LEFT JOIN adicionais pa ON pa.id = pia.adicional_id
 *     WHERE pi.pedido_id = $pedido_id
 *     GROUP BY pi.id;
 *
 *  3. Status e seus labels para exibicao:
 *     'aguardando'   => badge class 'aguardando'
 *     'em_producao'  => badge class 'em-producao'
 *     'saiu_entrega' => badge class 'saiu-entrega'
 *     'entregue'     => badge class 'entregue'
 *     'cancelado'    => badge class 'cancelado'
 *
 *  4. Timeline: cada etapa recebe class 'done' se o timestamp correspondente
 *     no banco NAO for NULL.
 *     realizado_em   = p.criado_em      (sempre existe)
 *     producao_em    = p.producao_em
 *     saiu_em        = p.saiu_em
 *     entregue_em    = p.entregue_em
 * ══════════════════════════════════════════════════════════════
 */

// MOCKUP: remover quando o backend estiver conectado
$pedido_id     = $_GET['id'] ?? '1024';
$status_atual  = 'em_producao';
$is_finalizado = in_array($status_atual, ['entregue', 'cancelado']);
?>

<div class="page-header-wrap">
<header class="page-header">
    <a href="pedidos.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <p class="logo-mini">Chokko<span> Melt</span></p>
        <!-- NOTA BACKEND: exibir 'Pedido #' . $pedido_id -->
        <p class="page-subtitle">Pedido #<?= htmlspecialchars($pedido_id) ?></p>
    </div>
</header>
</div>

<div class="page-pad">
<div class="det-panel">

    <!-- ══ CABEÇALHO DO PEDIDO ══ -->
    <div class="det-order-head">
        <div>
            <!-- NOTA BACKEND: 'Pedido #' . $pedido['id'] -->
            <h2 class="det-order-title">Pedido #<?= htmlspecialchars($pedido_id) ?></h2>
            <!-- NOTA BACKEND: date('d/m/Y \a\s H:i', strtotime($pedido['criado_em'])) -->
            <p class="det-order-date">Em 29/04/2026 às 19:30</p>
        </div>
        <!-- NOTA BACKEND: exibir o botao Repetir somente se $pedido['status'] == 'entregue' -->
        <?php if ($is_finalizado): ?>
        <button class="btn-repetir" onclick="repetirPedido(<?= intval($pedido_id) ?>)">
            <i class="fa-solid fa-rotate-right"></i> Repetir pedido
        </button>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- ══ STATUS / TIMELINE VERTICAL ══ -->
    <div class="det-section-label">Status</div>
    <!-- NOTA BACKEND: classe do badge = $pedido['status'] com tracos (ex: 'em-producao') -->
    <span class="order-status em-producao" style="margin-bottom:20px;display:inline-block;">Em Produção</span>

    <!--
        NOTA BACKEND: para cada etapa da timeline, adicionar a classe 'done' no li
        se o timestamp correspondente nao for NULL no banco:
          - Etapa 1 (realizado): sempre done
          - Etapa 2 (em producao): done se $pedido['producao_em'] != null
          - Etapa 3 (saiu entrega): done se $pedido['saiu_em'] != null
          - Etapa 4 (finalizado):   done se $pedido['entregue_em'] != null
        Horario da etapa: date('H:i', strtotime($pedido['producao_em'])) etc.
    -->
    <ul class="det-timeline">
        <li class="det-tl-step done">
            <span class="det-tl-dot"><i class="fa-solid fa-check"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido realizado</span>
                <span class="det-tl-time">19:30</span>
            </div>
        </li>
        <li class="det-tl-step done">
            <span class="det-tl-dot"><i class="fa-solid fa-fire-burner"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido em produção</span>
                <span class="det-tl-time">19:32</span>
            </div>
        </li>
        <li class="det-tl-step">
            <span class="det-tl-dot"><i class="fa-solid fa-motorcycle"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Saiu para entrega</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
        <li class="det-tl-step">
            <span class="det-tl-dot"><i class="fa-solid fa-flag-checkered"></i></span>
            <div class="det-tl-content">
                <span class="det-tl-label">Pedido finalizado</span>
                <span class="det-tl-time">—</span>
            </div>
        </li>
    </ul>

    <div class="divider"></div>

    <!-- ══ FORMA DE ENTREGA ══ -->
    <!-- NOTA BACKEND: $pedido['forma_entrega'] = 'delivery' | 'retirada' | 'local' -->
    <!-- Icone e texto mudam conforme o valor do campo no banco -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de entrega</div>
        <div class="det-info-row">
            <i class="fa-solid fa-motorcycle"></i>
            <div>
                <p class="det-info-title">Entrega via delivery</p>
                <!-- NOTA BACKEND: exibir endereco se forma_entrega == 'delivery' -->
                <!-- $pedido['logradouro'] . ', ' . $pedido['numero'] . ' - ' . $pedido['bairro'] -->
                <p class="det-info-sub">Rua das Flores, 123 · Jardim Primavera, Grajaú</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ FORMA DE PAGAMENTO ══ -->
    <!-- NOTA BACKEND: $pedido['forma_pagamento'] = 'pix' | 'credito' | 'dinheiro' -->
    <!-- Icone muda: pix = fa-brands fa-pix (cor #32BCAD), credito = fa-regular fa-credit-card (azul), dinheiro = fa-solid fa-money-bill-wave (verde) -->
    <div class="det-info-block">
        <div class="det-section-label">Forma de pagamento</div>
        <div class="det-info-row">
            <i class="fa-regular fa-credit-card" style="color:#1976D2;"></i>
            <div>
                <p class="det-info-title">Cartão de Crédito</p>
                <p class="det-info-sub">Pagamento na entrega</p>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- ══ ITENS DO PEDIDO ══ -->
    <div class="det-section-label">Itens do pedido</div>

    <!--
        NOTA BACKEND: foreach ($itens as $item) — gerar um .det-item para cada item
        Campos: $item['quantidade'], $item['nome'], $item['imagem'],
                $item['preco_unitario'], $item['adicionais'], $item['obs']
    -->
    <div class="det-items-list">

        <div class="det-item">
            <!-- NOTA BACKEND: src = $item['imagem'] -->
            <img class="det-item-img"
                 src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160"
                 alt="Bolo de Chocolate">
            <div class="det-item-info">
                <!-- NOTA BACKEND: $item['quantidade'] . 'x ' . $item['nome'] -->
                <p class="det-item-name">1x Bolo de Chocolate no Pote</p>
                <!-- NOTA BACKEND: exibir apenas se $item['adicionais'] nao for vazio -->
                <p class="det-item-addons">+ Cobertura extra de brigadeiro</p>
                <!-- NOTA BACKEND: number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') -->
                <p class="det-item-price">R$ 15,00</p>
            </div>
        </div>

        <div class="det-item">
            <img class="det-item-img"
                 src="https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=160"
                 alt="Brigadeiro Gourmet">
            <div class="det-item-info">
                <p class="det-item-name">1x Brigadeiro Gourmet Kit</p>
                <!-- NOTA BACKEND: NAO exibir o p.det-item-addons se $item['adicionais'] for vazio -->
                <p class="det-item-price">R$ 13,00</p>
            </div>
        </div>

        <!-- NOTA BACKEND: exibir .det-obs-box apenas se $item['obs'] nao for vazio -->
        <div class="det-obs-box">
            <i class="fa-solid fa-note-sticky"></i>
            <!-- NOTA BACKEND: htmlspecialchars($item['obs']) -->
            <span>Sem cobertura extra no brigadeiro</span>
        </div>

    </div>
    <!-- NOTA BACKEND: fim do foreach ($itens as $item) -->

    <div class="divider"></div>

    <!-- ══ RESUMO FINANCEIRO ══ -->
    <!-- NOTA BACKEND: todos os valores vem da tabela pedidos -->
    <div class="det-summary">
        <div class="det-summary-row">
            <span>Subtotal</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['subtotal'], 2, ',', '.') -->
            <span>R$ 28,00</span>
        </div>
        <div class="det-summary-row">
            <span>Taxa de entrega</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['taxa_entrega'], 2, ',', '.') -->
            <!-- Se forma_entrega != 'delivery', exibir 'Gratis' e taxa = 0 -->
            <span>R$ 5,00</span>
        </div>
        <!-- NOTA BACKEND: exibir esta linha somente se $pedido['desconto'] > 0 -->
        <div class="det-summary-row det-summary-desconto">
            <span>Desconto</span>
            <!-- NOTA BACKEND: '- R$ ' . number_format($pedido['desconto'], 2, ',', '.') -->
            <span>— R$ 2,00</span>
        </div>
        <div class="det-summary-row det-summary-total">
            <span>Total</span>
            <!-- NOTA BACKEND: 'R$ ' . number_format($pedido['total'], 2, ',', '.') -->
            <span>R$ 31,00</span>
        </div>
    </div>

    <!-- ══ AÇÕES ══ -->
    <div style="margin-top:24px;display:flex;flex-direction:column;gap:10px;">
        <?php if (!$is_finalizado): ?>
        <!-- NOTA BACKEND: exibir somente enquanto o pedido estiver ativo (nao finalizado/cancelado) -->
        <button class="btn btn-outline" id="btn-contato">
            <i class="fa-solid fa-headset"></i> Falar com o Estabelecimento
        </button>
        <?php if ($status_atual === 'aguardando'): ?>
        <!-- NOTA BACKEND: O cliente SÓ PODE CANCELAR se o pedido ainda não foi aceito pela loja -->
        <button class="btn btn-outline" style="border-color: #E53935; color: #E53935;" onclick="abrirModalCancelarCliente()">
            <i class="fa-solid fa-ban"></i> Cancelar Pedido
        </button>
        <?php endif; ?>
        <?php else: ?>
        <button class="btn btn-primary" onclick="repetirPedido(<?= intval($pedido_id) ?>)">
            <i class="fa-solid fa-rotate-right"></i> Repetir este pedido
        </button>
        <?php endif; ?>
        <button class="btn btn-ghost" onclick="window.location.href='pedidos.php'">
            Voltar aos meus pedidos
        </button>
    </div>

</div><!-- /det-panel -->
</div><!-- /page-pad -->

<!-- MODAL CANCELAR PEDIDO (CLIENTE) -->
<div id="modal-cancelar-cliente" class="modal-overlay" style="z-index: 2010; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; padding: 20px;">
    <div class="modal-content" style="background: white; width: 400px; max-width: 100%; border-radius: 8px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 class="modal-title" style="color: #E53935; margin: 0; font-size: 1.2rem;">Cancelar Pedido</h2>
            <button onclick="fecharModalCancelarCliente()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="detalhes-body" style="margin-bottom: 20px;">
            <p style="margin-bottom: 10px; color: #555;">Por qual motivo você deseja cancelar seu pedido?</p>
            <select id="motivo-cancelamento-cliente" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; font-size: 1rem; margin-bottom: 15px;">
                <option value="" disabled selected>Selecione um motivo...</option>
                <option value="Demorou muito para ser aceito">Demorou muito para ser aceito</option>
                <option value="Fiz o pedido errado">Fiz o pedido errado</option>
                <option value="Desisti da compra">Desisti da compra</option>
                <option value="Endereço incorreto">Endereço incorreto</option>
                <option value="Outro motivo">Outro motivo</option>
            </select>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
            <button type="button" class="btn" style="background: #E53935; color: white; padding: 10px 15px; font-weight: bold; border-radius: 6px; cursor: pointer; border: none;" onclick="confirmarCancelamentoCliente()">Confirmar Cancelamento</button>
            <button type="button" class="btn" style="padding: 10px 15px; background: #eee; border:none; border-radius: 6px; cursor: pointer; font-weight: bold; color: #555;" onclick="fecharModalCancelarCliente()">Voltar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCESSO (CLIENTE) -->
<div id="modal-sucesso-cliente" class="modal-overlay" style="z-index: 9999; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; padding: 20px;">
    <div class="modal-content" style="background: white; width: 350px; max-width: 100%; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <i class="fa-solid fa-circle-check" style="font-size: 3.5rem; color: #43A047; margin-bottom: 15px;"></i>
        <h2 style="color: var(--marrom); margin-bottom: 10px; font-size: 1.4rem;">Pedido Cancelado</h2>
        <p id="modal-sucesso-cliente-msg" style="color: #666; margin-bottom: 20px; line-height: 1.4;"></p>
        <button onclick="window.location.reload()" style="background: var(--marrom); color: white; border: none; padding: 10px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;">Voltar aos Meus Pedidos</button>
    </div>
</div>

<?php include '../includes/user_footer.php'; ?>
<script>
/*
 * ═══════════════════════════════════════════════════════════════════════════
 * JS + PHP — detalhes_pedido.php (INTEGRACAO_JS_PHP.txt na raiz)
 * ═══════════════════════════════════════════════════════════════════════════
 * Esta página deve ser gerada pelo PHP com $pedido_id vindo de $_GET['id'].
 * O JS abaixo só abre modais e simula cancelar/repetir pedido.
 *
 * Ligue ao backend:
 *   • Cancelar: POST user/api/cancelar_pedido.php com pedido_id + motivo só se
 *     status ainda for PENDENTE (regra de negócio no PHP).
 *   • repetirPedido: GET api/pedido_itens.php monta o carrinho — veja comentário
 *     longo no código; tabelas reais: item_pedido, produto.
 *   • WhatsApp: leia número de config_loja no PHP e passe para o JS:
 *     const WHATSAPP = "<?= preg_replace('/\D/','', $config['whatsapp']) ?>";
 * ═══════════════════════════════════════════════════════════════════════════
 */
function abrirModalCancelarCliente() {
    document.getElementById('modal-cancelar-cliente').style.display = 'flex';
}

function fecharModalCancelarCliente() {
    document.getElementById('modal-cancelar-cliente').style.display = 'none';
}

function confirmarCancelamentoCliente() {
    const motivo = document.getElementById('motivo-cancelamento-cliente').value;
    if (!motivo) {
        alert('Por favor, selecione o motivo do cancelamento.');
        return;
    }
    
    // NOTA BACKEND: POST para api/cancelar_pedido.php
    // Body: { pedido_id: <?= intval($pedido_id) ?>, motivo: motivo }
    
    fecharModalCancelarCliente();
    
    // Troca o alert pelo modal bonito, recarrega ao fechar (via html onclick)
    document.getElementById('modal-sucesso-cliente-msg').innerText = 'Seu pedido foi cancelado. O motivo informado foi registrado.';
    document.getElementById('modal-sucesso-cliente').style.display = 'flex';
}

/*
 * NOTA BACKEND — funcao repetirPedido()
 *
 * Criar o endpoint: /user/api/pedido_itens.php
 * Recebe: GET ?id={pedidoId}
 * Retorna JSON: [{ "id":1, "nome":"Bolo...", "img":"url", "preco":13.00, "qty":1 }]
 *
 * Query SQL para o endpoint:
 *   SELECT pi.quantidade AS qty, pi.preco_unitario AS preco,
 *          prod.id, prod.nome, prod.imagem AS img
 *   FROM pedido_itens pi
 *   INNER JOIN produtos prod ON prod.id = pi.produto_id
 *   WHERE pi.pedido_id = intval($_GET['id'])
 *   AND EXISTS (
 *     SELECT 1 FROM pedidos p
 *     WHERE p.id = pi.pedido_id AND p.usuario_id = $_SESSION['usuario_id']
 *   )
 */
function repetirPedido(pedidoId) {
    /* NOTA BACKEND: quando o endpoint estiver pronto, descomente abaixo e remova o alert():

    fetch('api/pedido_itens.php?id=' + pedidoId)
        .then(function(r) { return r.json(); })
        .then(function(itens) {
            var cart = itens.map(function(i) {
                return {
                    key: i.id + '||',
                    id: i.id,
                    name: i.nome,
                    img: i.img,
                    basePrice: i.preco,
                    addons: [],
                    unitPrice: i.preco,
                    qty: i.qty,
                    obs: ''
                };
            });
            localStorage.setItem('chokko_cart', JSON.stringify(cart));
            window.location.href = 'carrinho.php';
        })
        .catch(function() {
            alert('Erro ao buscar itens. Tente novamente.');
        });
    */

    alert('Repetir pedido estara disponivel quando o backend estiver conectado.');
}

/*
 * NOTA BACKEND — botao Falar com Estabelecimento
 * Numero do WhatsApp deve vir de uma tabela de configuracoes da loja no banco.
 * Substituir 5511999999999 pelo numero real.
 */
var btnContato = document.getElementById('btn-contato');
if (btnContato) {
    btnContato.addEventListener('click', function() {
        /* NOTA BACKEND: descomente a linha abaixo e insira o numero real:
        window.open('https://wa.me/5511999999999?text=Ola! Tenho duvida sobre o Pedido #<?= intval($pedido_id) ?>', '_blank');
        */
        alert('WhatsApp: numero a ser configurado pelo backend.');
    });
}
</script>
