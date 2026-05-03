/**
 * ═══════════════════════════════════════════════════════════════════════════
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt (raiz do projeto)
 *
 * Carregado em: user/carrinho.php
 *
 * O que ESTE arquivo faz:
 *   Lê o carrinho do localStorage ('chokko_cart'), mostra na tela, altera
 *   quantidades. Ao clicar em finalizar, hoje checa 'chokko_usuario_id' no
 *   localStorage (login fake em login.php).
 *
 * O que VOCÊ (PHP) deve fazer depois:
 *   • Substituir a simulação por fetch('user/api/check_session.php') que faz
 *     session_start() e devolve { "logado": true/false }.
 *   • Ao finalizar de verdade: enviar o JSON do carrinho para
 *     user/api/gravar_pedido.php (POST), PHP insere em pedido, item_pedido,
 *     item_pedido_adicional, pagamento, limpa carrinho no banco ou no storage.
 *   • localStorage é só auxiliar; pedido “oficial” nasce no INSERT do MySQL.
 * ═══════════════════════════════════════════════════════════════════════════
 */

/* ========================================
   CARRINHO — Lê localStorage e renderiza
   ======================================== */

// ── Utilitários ──────────────────────────
function formatBRL(v) {
    return 'R$ ' + parseFloat(v).toFixed(2).replace('.', ',');
}

function escHtml(text) {
    if (text == null) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}

function escAttr(text) {
    return String(text == null ? '' : text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function normalizeCartItem(item) {
    const addons = item.addons || [];
    const addSum = addons.reduce((s, a) => s + (parseFloat(a.price) || 0), 0);
    if (item.basePrice == null || Number.isNaN(Number(item.basePrice))) {
        item.basePrice = Math.max(0, (parseFloat(item.unitPrice) || 0) - addSum);
    }
    if (!Array.isArray(item.addonCatalog)) item.addonCatalog = [];
}

function migrateCartOnce() {
    const cart = getCart();
    let dirty = false;
    cart.forEach((item) => {
        const prev = JSON.stringify(item);
        normalizeCartItem(item);
        if (JSON.stringify(item) !== prev) dirty = true;
    });
    if (dirty) saveCart(cart);
}

function getCart() {
    try { return JSON.parse(localStorage.getItem('chokko_cart') || '[]'); }
    catch { return []; }
}

function saveCart(cart) {
    localStorage.setItem('chokko_cart', JSON.stringify(cart));
}

// ── Referências DOM ──────────────────────
const cartEmpty    = document.getElementById('cart-empty');
const cartList     = document.getElementById('cart-items-list');
const cartSummary  = document.getElementById('cart-summary');
const cardEntrega  = document.getElementById('card-entrega');
const cartActions  = document.getElementById('cart-actions');
const sumSubtotal  = document.getElementById('sum-subtotal');
const rowEntrega   = document.getElementById('row-entrega');
const sumEntrega   = document.getElementById('sum-entrega');
const sumTotal     = document.getElementById('sum-total');
const btnTotal     = document.getElementById('btn-total-label');
const btnFinalizar = document.getElementById('btn-finalizar');

// Taxa de entrega por opção (pode variar no futuro via backend)
const TAXAS = { delivery: 5.00, retirada: 0.00, local: 0.00 };
let currentEntrega = 'delivery';

let _cartEditIdx = null;

function openCartItemEditor(idx) {
    const cart = getCart();
    const item = cart[idx];
    if (!item) return;

    normalizeCartItem(item);
    _cartEditIdx = idx;

    const modal = document.getElementById('cart-edit-modal');
    const title = document.getElementById('cart-edit-title');
    const wrap = document.getElementById('cart-edit-addons');
    const obsEl = document.getElementById('cart-edit-obs');
    if (!modal || !title || !wrap || !obsEl) return;

    title.textContent = item.name || 'Item';
    obsEl.value = item.obs || '';

    const catalog = item.addonCatalog || [];
    const selected = new Set((item.addons || []).map(a => String(a.id)));

    wrap.innerHTML = '';
    if (catalog.length === 0) {
        wrap.innerHTML = '<p class="cart-edit-muted">Este produto não tem lista de complementos aqui. Você pode mudar só a observação — ou retire o item e adicione de novo pelo cardápio para escolher extras.</p>';
    } else {
        catalog.forEach((ad) => {
            const id = String(ad.id);
            const checked = selected.has(id);
            const price = parseFloat(ad.price) || 0;
            const priceLabel = price > 0 ? `+ ${formatBRL(price)}` : 'Grátis';
            wrap.innerHTML += `
                <label class="cart-edit-addon">
                    <span class="cart-edit-addon-text">
                        <span class="cart-edit-addon-name">${escHtml(ad.name)}</span>
                        <span class="cart-edit-addon-price">${priceLabel}</span>
                    </span>
                    <input type="checkbox" class="cae-check"
                        data-id="${escAttr(id)}"
                        data-name="${escAttr(ad.name)}"
                        data-price="${price}"
                        ${checked ? 'checked' : ''}>
                </label>`;
        });
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeCartEditModal() {
    const modal = document.getElementById('cart-edit-modal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }
    document.body.style.overflow = '';
    _cartEditIdx = null;
}

function saveCartItemEdit() {
    if (_cartEditIdx === null) return;

    const cart = getCart();
    const old = cart[_cartEditIdx];
    if (!old) {
        closeCartEditModal();
        return;
    }

    normalizeCartItem(old);

    const selected = [];
    document.querySelectorAll('#cart-edit-addons .cae-check:checked').forEach((cb) => {
        selected.push({
            id   : cb.dataset.id,
            name : cb.dataset.name,
            price: parseFloat(cb.dataset.price || '0') || 0,
        });
    });

    const obsEl = document.getElementById('cart-edit-obs');
    const obs = obsEl ? obsEl.value.trim() : '';

    const base = parseFloat(old.basePrice) || 0;
    const extra = selected.reduce((s, a) => s + a.price, 0);
    const unitPrice = base + extra;
    const addonKey = selected.map(a => String(a.id)).sort().join(',');
    const key = `${old.id}|${addonKey}|${obs}`;
    const qty = old.qty;

    const newItem = {
        ...old,
        key,
        addons: selected,
        unitPrice,
        obs,
    };

    const idx = _cartEditIdx;
    cart.splice(idx, 1);

    const dupIdx = cart.findIndex((i) => i.key === key);
    if (dupIdx >= 0) {
        cart[dupIdx].qty = Math.min(cart[dupIdx].qty + qty, 10);
    } else {
        cart.splice(idx, 0, newItem);
    }

    saveCart(cart);
    closeCartEditModal();
    renderCart();
    updateCartBadge();
}

// ── Renderizar carrinho ──────────────────
function renderCart() {
    migrateCartOnce();
    const cart = getCart();

    if (cart.length === 0) {
        cartEmpty.style.display   = 'flex';
        cartList.innerHTML        = '';
        cartSummary.style.display = 'none';
        cardEntrega.style.display = 'none';
        cartActions.style.display = 'none';
        return;
    }

    cartEmpty.style.display   = 'none';
    cartSummary.style.display = 'block';
    cardEntrega.style.display = 'block';
    cartActions.style.display = 'block';

    cartList.innerHTML = cart.map((item, idx) => {
        normalizeCartItem(item);
        const addonText = item.addons && item.addons.length > 0
            ? item.addons.map(a => a.name).join(', ')
            : '';
        const obsHtml = item.obs ? `<p class="item-obs">"${escHtml(item.obs)}"</p>` : '';
        const safeImg = String(item.img || '').replace(/"/g, '&quot;');

        return `
        <div class="cart-item" data-idx="${idx}">
            <div class="cart-item-main" onclick="openCartItemEditor(${idx})" role="button" tabindex="0" aria-label="Editar complementos e observação deste item">
                <img class="cart-item-img" src="${safeImg}" alt="${escAttr(item.name)}">
                <div class="cart-item-info">
                    <h4>${escHtml(item.name)}</h4>
                    ${addonText ? `<p class="item-addons">+ ${escHtml(addonText)}</p>` : ''}
                    ${obsHtml}
                    <p class="item-price">${formatBRL(item.unitPrice * item.qty)}</p>
                    <button type="button" class="btn-edit-inline"><i class="fa-solid fa-pen"></i> Editar Adicionais/Obs</button>
                </div>
            </div>
            <div class="qty-ctrl">
                <button type="button" onclick="changeQty(${idx}, -1)">−</button>
                <span>${item.qty}</span>
                <button type="button" onclick="changeQty(${idx}, 1)">+</button>
            </div>
            <button type="button" class="cart-item-remove" onclick="removeItem(${idx})" title="Remover">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>`;
    }).join('');

    updateTotals();
}

// ── Atualiza totais ───────────────────────
function updateTotals() {
    const cart = getCart();
    const subtotal = cart.reduce((s, i) => s + i.unitPrice * i.qty, 0);
    const taxa     = TAXAS[currentEntrega] || 0;
    const total    = subtotal + taxa;

    sumSubtotal.textContent = formatBRL(subtotal);
    sumEntrega.textContent  = taxa > 0 ? formatBRL(taxa) : 'Grátis';
    sumTotal.textContent    = formatBRL(total);
    btnTotal.textContent    = `· ${formatBRL(total)}`;

    // Esconde linha de entrega se grátis
    rowEntrega.style.display = taxa > 0 ? '' : 'none';
}

// ── Alterar quantidade ────────────────────
function changeQty(idx, delta) {
    const cart = getCart();
    if (!cart[idx]) return;
    cart[idx].qty += delta;
    if (cart[idx].qty < 1) {
        cart.splice(idx, 1);
    } else if (cart[idx].qty > 10) {
        cart[idx].qty = 10;
    }
    saveCart(cart);
    renderCart();
}

// ── Remover item ──────────────────────────
function removeItem(idx) {
    const cart = getCart();
    cart.splice(idx, 1);
    saveCart(cart);
    renderCart();
}

// ── Radios de forma de entrega ────────────
document.querySelectorAll('.delivery-option').forEach(option => {
    option.addEventListener('click', () => {
        // Remove active de todos
        document.querySelectorAll('.delivery-option').forEach(o => o.classList.remove('active'));
        option.classList.add('active');

        // Marca o radio nativo
        const radio = option.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            currentEntrega = radio.value;
        }

        updateTotals();
    });
});

// ── Finalizar pedido (verifica login antes) ──────────────
btnFinalizar.addEventListener('click', () => {
    // Salva a opção de entrega no localStorage
    localStorage.setItem('chokko_entrega', currentEntrega);

    /*
     * VERIFICAÇÃO DE LOGIN
     * ─────────────────────────────────────────────────────
     * O JS faz um fetch() em /user/api/check_session.php
     * Esse endpoint PHP responde:
     *   { "logado": true }  → usuário tem sessão ativa
     *   { "logado": false } → usuário não está logado
     *
     * O carrinho (chokko_cart) e a entrega (chokko_entrega) já estão
     * salvos no localStorage. Eles sobrevivem ao redirecionamento.
     * Após o login, login.php redireciona de volta para carrinho.php,
     * que detecta o parâmetro ?retorno=finalizar e avança automaticamente.
     *
     * NOTA BACKEND (check_session.php):
     *   <?php
     *   session_start();
     *   header('Content-Type: application/json');
     *   echo json_encode(['logado' => isset($_SESSION['usuario_id'])]);
     *
     * NOTA BACKEND (login.php):
     *   Após login bem-sucedido, verificar se existe $_GET['retorno']
     *   Se $_GET['retorno'] === 'finalizar', redirecionar para:
     *   header('Location: carrinho.php?retorno=finalizar');
     * ─────────────────────────────────────────────────────
     */

    // FRONTEND MOCKUP: simula usuário deslogado para demonstração.
    // Quando o backend estiver pronto, substituir TODO o bloco abaixo
    // pelo fetch real ao check_session.php:
    //
    // fetch('api/check_session.php')
    //     .then(r => r.json())
    //     .then(data => {
    //         if (data.logado) {
    //             window.location.href = 'finalizarPedido.php';
    //         } else {
    //             // Cart já está no localStorage, só precisamos ir ao login
    //             window.location.href = 'login.php?retorno=finalizar';
    //         }
    //     })
    //     .catch(() => {
    //         // Se o fetch falhar (ex: offline), tenta avançar mesmo assim
    //         window.location.href = 'finalizarPedido.php';
    //     });

    // ── SIMULAÇÃO FRONTEND (remover quando backend conectar) ──
    // Lê se existe um cookie/flag de sessão mockup
    const usuarioLogado = localStorage.getItem('chokko_usuario_id');
    if (usuarioLogado) {
        window.location.href = 'finalizarPedido.php';
    } else {
        // Guarda a intenção de finalizar para o login saber onde voltar
        localStorage.setItem('chokko_retorno', 'finalizar');
        window.location.href = 'login.php?retorno=finalizar';
    }
});

// ── Auto-avança se voltou do login com intenção de finalizar ──
// NOTA BACKEND: quando o backend estiver ativo, este bloco pode ser removido.
// O login.php já redireciona diretamente para finalizarPedido.php após autenticar.
(function verificarRetornoLogin() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('retorno') === 'finalizar') {
        const retorno = localStorage.getItem('chokko_retorno');
        const usuario = localStorage.getItem('chokko_usuario_id');
        if (retorno === 'finalizar' && usuario) {
            localStorage.removeItem('chokko_retorno');
            // Pequeno delay para o carrinho renderizar antes de avançar
            setTimeout(() => { window.location.href = 'finalizarPedido.php'; }, 400);
        }
    }
})();


// ── Badge do footer ───────────────────────
function updateCartBadge() {
    const cart  = getCart();
    const total = cart.reduce((s, i) => s + i.qty, 0);
    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.textContent = total > 0 ? total : '';
        badge.classList.toggle('has-items', total > 0);
    }
}

// ── Modal editar item (script no fim da página: liga já ou no DOMContentLoaded) ──
(function wireCartEditModal() {
    const bind = () => {
        const modal = document.getElementById('cart-edit-modal');
        const bd = document.getElementById('cart-edit-backdrop');
        const closeBtn = document.getElementById('cart-edit-close');
        const saveBtn = document.getElementById('cart-edit-save');

        if (bd) bd.addEventListener('click', closeCartEditModal);
        if (closeBtn) closeBtn.addEventListener('click', closeCartEditModal);
        if (saveBtn) saveBtn.addEventListener('click', saveCartItemEdit);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
                closeCartEditModal();
            }
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();

// ── Inicialização ─────────────────────────
renderCart();
updateCartBadge();