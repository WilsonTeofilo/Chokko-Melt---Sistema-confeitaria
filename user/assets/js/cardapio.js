// ============================================================
// cardapio.js — Lógica do cardápio e do modal de produto
// ============================================================
// NOTA BACKEND: quando o PHP estiver pronto, os <article class="product-card">
// serão gerados via foreach no banco de dados com data-* reais.
// O localStorage do carrinho é temporário e vai ser substituído
// por $_SESSION no PHP.
// ============================================================

// Variáveis globais que guardam o estado do modal aberto
var produtoAtual = null; // qual produto está sendo visto
var quantidadeAtual = 1; // quantidade selecionada

// Pega os elementos do modal da tela
var modal           = document.getElementById('productModal');
var modalImg        = document.getElementById('modalImg');
var modalTitulo     = document.getElementById('modal-title');
var modalDescricao  = document.getElementById('modalDesc');
var modalPrecoEl    = document.getElementById('modalPriceDisplay');
var modalQtdEl      = document.getElementById('modalQty');
var modalBtnPrecoEl = document.getElementById('modalBtnPrice');
var modalBotaoAdd   = document.getElementById('modalAddBtn');
var modalObs        = document.getElementById('modalObs');
var secaoAddons     = document.getElementById('modalAddonsSection');
var listaAddons     = document.getElementById('modalAddonsList');
var botaoMenos      = document.getElementById('modalQtyMinus');
var botaoMais       = document.getElementById('modalQtyPlus');
var toast           = document.getElementById('toastCart');
var toastMensagem   = document.getElementById('toastMsg');


// ── Funções de apoio ─────────────────────────────────────────

// Transforma um número em formato de dinheiro: 13 → "R$ 13,00"
function formatarDinheiro(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',');
}

// Lê o carrinho salvo no navegador (localStorage)
// NOTA BACKEND: quando o PHP assumir o carrinho, apague esta função
function pegarCarrinho() {
    var dados = localStorage.getItem('chokko_cart');
    if (!dados) {
        return [];
    }
    try {
        return JSON.parse(dados);
    } catch (e) {
        return [];
    }
}

// Salva o carrinho atualizado no navegador
// NOTA BACKEND: quando o PHP assumir o carrinho, apague esta função
function salvarCarrinho(carrinho) {
    localStorage.setItem('chokko_cart', JSON.stringify(carrinho));
    atualizarBadge();
}

// Atualiza o número no ícone da sacola na navegação inferior
function atualizarBadge() {
    var carrinho = pegarCarrinho();
    var totalItens = 0;

    // Conta quantos itens tem no carrinho somando as quantidades
    for (var i = 0; i < carrinho.length; i++) {
        var item = carrinho[i];
        totalItens = totalItens + item.qty;
    }

    var badge = document.getElementById('cart-badge');
    if (badge) {
        if (totalItens > 0) {
            badge.textContent = totalItens;
            badge.classList.add('has-items');
        } else {
            badge.textContent = '';
            badge.classList.remove('has-items');
        }
    }
}

// Mostra a mensagem de confirmação (Toast) quando algo é adicionado
function mostrarToast(mensagem) {
    toastMensagem.textContent = mensagem;
    toast.classList.add('show');
    // Esconde automaticamente depois de 2,2 segundos
    setTimeout(function() {
        toast.classList.remove('show');
    }, 2200);
}

// Recalcula o preço total mostrado no botão "Adicionar" do modal
// Leva em conta a quantidade e os adicionais marcados
function atualizarPrecoNoBotao() {
    if (!produtoAtual) {
        return;
    }

    var precoBase = produtoAtual.price;
    var precoExtra = 0;

    // Percorre todos os checkboxes de adicionais marcados
    var checkboxesMarcados = document.querySelectorAll('.addon-check:checked');
    for (var i = 0; i < checkboxesMarcados.length; i++) {
        var cb = checkboxesMarcados[i];
        precoExtra = precoExtra + parseFloat(cb.dataset.price || 0);
    }

    var total = (precoBase + precoExtra) * quantidadeAtual;
    modalBtnPrecoEl.textContent = formatarDinheiro(total);
}


// ── Abrir o modal do produto ──────────────────────────────────
function abrirModalProduto(card) {
    // Verifica se a loja está aberta (variável definida pelo main.js)
    if (window.isLojaAberta === false) {
        alert('A loja está fechada no momento. Confira nosso horário de funcionamento!');
        return;
    }

    // Lê os dados do produto a partir dos atributos data-* do card HTML
    produtoAtual = {
        id    : card.dataset.id,
        name  : card.dataset.name,
        desc  : card.dataset.desc,
        price : parseFloat(card.dataset.price),
        img   : card.dataset.img,
        addons: [] // adicionais do produto
    };

    // Tenta ler os adicionais (formato JSON)
    try {
        produtoAtual.addons = JSON.parse(card.dataset.addons || '[]');
    } catch (e) {
        produtoAtual.addons = [];
    }

    quantidadeAtual = 1;

    // Preenche o modal com as informações do produto
    modalImg.src                = produtoAtual.img;
    modalImg.alt                = produtoAtual.name;
    modalTitulo.textContent     = produtoAtual.name;
    modalDescricao.textContent  = produtoAtual.desc;
    modalPrecoEl.textContent    = formatarDinheiro(produtoAtual.price);
    modalQtdEl.textContent      = '1';
    modalObs.value              = '';

    // Limpa e monta a lista de adicionais (se existirem)
    listaAddons.innerHTML = '';
    if (produtoAtual.addons.length > 0) {
        secaoAddons.style.display = 'block';

        for (var i = 0; i < produtoAtual.addons.length; i++) {
            var addon = produtoAtual.addons[i];
            var labelPreco = '';

            if (addon.price > 0) {
                labelPreco = '+' + formatarDinheiro(addon.price);
            } else {
                labelPreco = 'Grátis';
            }

            listaAddons.innerHTML += '<label class="addon-item">' +
                '<div class="addon-left">' +
                    '<span class="addon-name">' + addon.name + '</span>' +
                    '<span class="addon-price">' + labelPreco + '</span>' +
                '</div>' +
                '<input type="checkbox" class="addon-check"' +
                    ' data-price="' + addon.price + '"' +
                    ' data-id="' + addon.id + '"' +
                    ' data-name="' + addon.name + '">' +
            '</label>';
        }

        // Atualiza o preço quando marcar/desmarcar um adicional
        listaAddons.onchange = atualizarPrecoNoBotao;

    } else {
        secaoAddons.style.display = 'none';
        listaAddons.onchange = null;
    }

    atualizarPrecoNoBotao();

    // Botão de diminuir começa desabilitado (quantidade mínima é 1)
    botaoMenos.disabled = true;

    // Abre o modal
    modal.classList.add('open');
    document.body.style.overflow = 'hidden'; // Trava o scroll da página
}

function fecharModal() {
    modal.classList.remove('open');
    document.body.style.overflow = ''; // Libera o scroll
    produtoAtual = null;
}


// ── Botões de quantidade no modal ─────────────────────────────
botaoMenos.addEventListener('click', function() {
    if (quantidadeAtual > 1) {
        quantidadeAtual = quantidadeAtual - 1;
        modalQtdEl.textContent = quantidadeAtual;

        // Desabilita o botão de menos se chegou em 1
        if (quantidadeAtual === 1) {
            botaoMenos.disabled = true;
        }

        atualizarPrecoNoBotao();
    }
});

botaoMais.addEventListener('click', function() {
    if (quantidadeAtual < 10) {
        quantidadeAtual = quantidadeAtual + 1;
        modalQtdEl.textContent = quantidadeAtual;
        botaoMenos.disabled = false;
        atualizarPrecoNoBotao();
    }
});


// ── Botão Adicionar ao carrinho ───────────────────────────────
// NOTA BACKEND: quando o PHP assumir o carrinho via sessão, este
// botão vai fazer um POST para carrinho.php ao invés de salvar em localStorage.
modalBotaoAdd.addEventListener('click', function() {
    if (!produtoAtual) {
        return;
    }

    // Coleta os adicionais marcados pelo usuário
    var adicionaisSelecionados = [];
    var precoExtra = 0;
    var checkboxesMarcados = document.querySelectorAll('.addon-check:checked');

    for (var i = 0; i < checkboxesMarcados.length; i++) {
        var cb = checkboxesMarcados[i];
        var precoAddon = parseFloat(cb.dataset.price || 0);

        adicionaisSelecionados.push({
            id    : cb.dataset.id,
            name  : cb.dataset.name,
            price : precoAddon
        });

        precoExtra = precoExtra + precoAddon;
    }

    var observacao = modalObs.value.trim();
    var precoUnitario = produtoAtual.price + precoExtra;

    // Cria uma chave única para identificar este item no carrinho
    // (mesmo produto com adicionais diferentes vira item separado)
    var idsAdicionais = '';
    for (var j = 0; j < adicionaisSelecionados.length; j++) {
        idsAdicionais = idsAdicionais + adicionaisSelecionados[j].id + ',';
    }
    var chaveItem = produtoAtual.id + '|' + idsAdicionais + '|' + observacao;

    var carrinho = pegarCarrinho();

    // Verifica se já existe um item igual no carrinho
    var itemExistente = null;
    for (var k = 0; k < carrinho.length; k++) {
        if (carrinho[k].key === chaveItem) {
            itemExistente = carrinho[k];
            break;
        }
    }

    if (itemExistente) {
        // Se já existe, apenas aumenta a quantidade (máximo 10)
        itemExistente.qty = Math.min(itemExistente.qty + quantidadeAtual, 10);
    } else {
        // Se não existe, adiciona como novo item
        carrinho.push({
            key       : chaveItem,
            id        : produtoAtual.id,
            name      : produtoAtual.name,
            img       : produtoAtual.img,
            basePrice : produtoAtual.price,
            addons    : adicionaisSelecionados,
            unitPrice : precoUnitario,
            qty       : quantidadeAtual,
            obs       : observacao
        });
    }

    salvarCarrinho(carrinho);
    mostrarToast(produtoAtual.name + ' adicionado à sacola!');
    fecharModal();
});


// ── Fechar modal clicando fora ────────────────────────────────
modal.addEventListener('click', function(e) {
    // Só fecha se clicou no fundo escuro, não dentro do modal
    if (e.target === modal) {
        fecharModal();
    }
});


// ── Clique nos cards do cardápio ──────────────────────────────
var cards = document.querySelectorAll('.product-card');
cards.forEach(function(card) {
    card.addEventListener('click', function() {
        abrirModalProduto(card);
    });
});


// ── Filtro de categorias ──────────────────────────────────────
var botoesCat = document.querySelectorAll('.cat-btn');
botoesCat.forEach(function(btn) {
    btn.addEventListener('click', function() {

        // Remove o destaque de todos os botões de categoria
        botoesCat.forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');

        var categoriaSelecionada = btn.dataset.cat;

        // Mostra ou esconde os cards de acordo com a categoria
        var todosCards = document.querySelectorAll('.product-card');
        todosCards.forEach(function(card) {
            if (categoriaSelecionada === 'todos' || card.dataset.cat === categoriaSelecionada) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});


// ── Inicializa o badge ao carregar a página ───────────────────
atualizarBadge();
