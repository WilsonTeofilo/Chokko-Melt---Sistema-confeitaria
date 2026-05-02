/**
 * ═══════════════════════════════════════════════════════════════════════════
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt (raiz do projeto)
 *
 * Carregado em: user/index.php (após user_footer → main.js)
 *
 * O que ESTE arquivo faz:
 *   Abre modal do produto, lê data-id, data-name, data-price, data-addons nos
 *   <article class="product-card">. Hoje os produtos são HTML estático/mock.
 *
 * O que VOCÊ (PHP) deve fazer depois:
 *   • Gerar cada card com um foreach PHP vindo do SELECT em produto + adicionais:
 *     data-addons='<?= json_encode($adicionaisDoProduto, JSON_HEX_TAG | JSON_HEX_APOS) ?>'
 *   • Ou entregar JSON de produtos e o JS montar o grid (mais trabalhoso).
 *   • Ao adicionar ao carrinho, o JS só grava em localStorage. Para persistir no
 *     servidor use sessão + tabela item_carrinho ou mantenha só localStorage até
 *     o cliente logar e clicar em finalizar (aí um PHP grava pedido + itens).
 * ═══════════════════════════════════════════════════════════════════════════
 */

/* ========================================
   CARDÁPIO — Modal de detalhes + Carrinho local
   ======================================== */

// ── Estado do modal ──────────────────────
let currentProduct = null;   // produto sendo visualizado
let currentQty     = 1;      // quantidade selecionada

// ── Referências DOM ──────────────────────
const modal        = document.getElementById('productModal');
const modalImg     = document.getElementById('modalImg');
const modalTitle   = document.getElementById('modal-title');
const modalDesc    = document.getElementById('modalDesc');
const modalPriceEl = document.getElementById('modalPriceDisplay');
const modalQtyEl   = document.getElementById('modalQty');
const modalBtnPriceEl = document.getElementById('modalBtnPrice');
const modalAddBtn  = document.getElementById('modalAddBtn');
const modalObs     = document.getElementById('modalObs');
const addonSection = document.getElementById('modalAddonsSection');
const addonsList   = document.getElementById('modalAddonsList');
const qtyMinus     = document.getElementById('modalQtyMinus');
const qtyPlus      = document.getElementById('modalQtyPlus');
const toast        = document.getElementById('toastCart');
const toastMsg     = document.getElementById('toastMsg');

// ── Utilitários ──────────────────────────
function formatBRL(value) {
    return 'R$ ' + value.toFixed(2).replace('.', ',');
}

function getCart() {
    try { return JSON.parse(localStorage.getItem('chokko_cart') || '[]'); }
    catch { return []; }
}

function saveCart(cart) {
    localStorage.setItem('chokko_cart', JSON.stringify(cart));
    updateCartBadge();
}

function updateCartBadge() {
    const cart  = getCart();
    const total = cart.reduce((s, i) => s + i.qty, 0);
    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.textContent = total > 0 ? total : '';
        badge.classList.toggle('has-items', total > 0);
    }
}

function showToast(msg) {
    toastMsg.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2200);
}

// ── Atualiza preço do botão no modal ─────
function updateModalBtnPrice() {
    if (!currentProduct) return;
    let base  = parseFloat(currentProduct.price);
    let extra = 0;
    document.querySelectorAll('.addon-check:checked').forEach(cb => {
        extra += parseFloat(cb.dataset.price || 0);
    });
    const total = (base + extra) * currentQty;
    modalBtnPriceEl.textContent = formatBRL(total);
}

// ── Abrir modal ──────────────────────────
function openProductModal(article) {
    if (window.isLojaAberta === false) {
        alert('A loja está fechada no momento. Confira nosso horário de funcionamento!');
        return;
    }

    currentProduct = {
        id    : article.dataset.id,
        name  : article.dataset.name,
        desc  : article.dataset.desc,
        price : parseFloat(article.dataset.price),
        img   : article.dataset.img,
        addons: JSON.parse(article.dataset.addons || '[]'),
    };
    currentQty = 1;

    // Preenche o modal
    modalImg.src        = currentProduct.img;
    modalImg.alt        = currentProduct.name;
    modalTitle.textContent   = currentProduct.name;
    modalDesc.textContent    = currentProduct.desc;
    modalPriceEl.textContent = formatBRL(currentProduct.price);
    modalQtyEl.textContent   = '1';
    modalObs.value           = '';

    // Acompanhamentos
    addonsList.innerHTML = '';
    if (currentProduct.addons.length > 0) {
        addonSection.style.display = 'block';
        currentProduct.addons.forEach(addon => {
            const priceLabel = addon.price > 0
                ? `+${formatBRL(addon.price)}`
                : 'Grátis';
            addonsList.innerHTML += `
                <label class="addon-item">
                    <div class="addon-left">
                        <span class="addon-name">${addon.name}</span>
                        <span class="addon-price">${priceLabel}</span>
                    </div>
                    <input type="checkbox" class="addon-check"
                           data-price="${addon.price}"
                           data-id="${addon.id}"
                           data-name="${addon.name}">
                </label>`;
        });
        // Recalcula ao marcar/desmarcar (evita acumular listeners a cada abertura)
        addonsList.onchange = updateModalBtnPrice;
    } else {
        addonSection.style.display = 'none';
        addonsList.onchange = null;
    }

    updateModalBtnPrice();
    qtyMinus.disabled = true; // qty começa em 1

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    currentProduct = null;
}

// ── Controle de quantidade no modal ──────
qtyMinus.addEventListener('click', () => {
    if (currentQty > 1) {
        currentQty--;
        modalQtyEl.textContent = currentQty;
        qtyMinus.disabled = (currentQty === 1);
        updateModalBtnPrice();
    }
});

qtyPlus.addEventListener('click', () => {
    if (currentQty < 10) {
        currentQty++;
        modalQtyEl.textContent = currentQty;
        qtyMinus.disabled = false;
        updateModalBtnPrice();
    }
});

// ── Adicionar ao carrinho ─────────────────
modalAddBtn.addEventListener('click', () => {
    if (!currentProduct) return;

    const selectedAddons = [];
    let extraPrice = 0;
    document.querySelectorAll('.addon-check:checked').forEach(cb => {
        selectedAddons.push({ id: cb.dataset.id, name: cb.dataset.name, price: parseFloat(cb.dataset.price || 0) });
        extraPrice += parseFloat(cb.dataset.price || 0);
    });

    const obs = modalObs.value.trim();
    const unitPrice = currentProduct.price + extraPrice;

    const cart = getCart();

    // Chave única por produto + addons selecionados + obs
    const addonKey = selectedAddons.map(a => a.id).sort().join(',');
    const key = `${currentProduct.id}|${addonKey}|${obs}`;

    const catalogSnap = currentProduct.addons.map(a => ({
        id   : String(a.id),
        name : a.name,
        price: Number(a.price) || 0,
    }));

    const existing = cart.find(i => i.key === key);
    if (existing) {
        existing.qty = Math.min(existing.qty + currentQty, 10);
        if (!existing.addonCatalog || existing.addonCatalog.length === 0) {
            existing.addonCatalog = catalogSnap;
        }
    } else {
        cart.push({
            key,
            id        : currentProduct.id,
            name      : currentProduct.name,
            img       : currentProduct.img,
            basePrice : currentProduct.price,
            addons    : selectedAddons,
            /** Catálogo completo de extras deste produto (para editar na sacola depois). */
            addonCatalog: catalogSnap,
            unitPrice,
            qty       : currentQty,
            obs,
        });
    }

    saveCart(cart);
    showToast(`${currentProduct.name} adicionado à sacola!`);
    closeModal();
});

// ── Fechar modal ao clicar fora ───────────
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

// ── Evento nos cards e botões de adicionar ─
document.querySelectorAll('.product-card').forEach(card => {
    // Clique no card inteiro → abre modal
    card.addEventListener('click', (e) => {
        // Se clicou no botão +, não duplica (o botão também dispara o card)
        openProductModal(card);
    });
});

// ── Filtro de categorias ──────────────────
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cat = btn.dataset.cat;
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (cat === 'todos' || card.dataset.cat === cat) ? '' : 'none';
        });
    });
});

// ── Inicializa badge do carrinho ──────────
updateCartBadge();
