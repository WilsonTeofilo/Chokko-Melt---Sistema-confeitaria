// ============================================================
// cardapio.js — Vitrine: modal do produto + filtro de categoria
// ============================================================
// O que este arquivo faz:
//   1. Abre o modal quando o cliente clica em um produto
//   2. Controla o +/- de quantidade dentro do modal
//   3. Envia o produto para o carrinho (via form POST para o PHP)
//   4. Filtra os produtos por categoria
// ============================================================


// Variáveis globais: guardam o produto que está sendo visto no modal
var produtoAtual    = null;
var quantidadeAtual = 1;

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


// ── Função: formatar número como dinheiro ─────────────────────
// Ex: 13.5 → "R$ 13,50"
function formatarDinheiro(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',');
}


// ── Função: atualiza o preço no botão "Adicionar" ─────────────
// Recalcula toda vez que muda a quantidade
function atualizarPrecoNoBotao() {
    if (!produtoAtual) return;

    var precoBase = produtoAtual.price;
    var precoAdicionais = 0;

    if (listaAddons) {
        var checkboxesChecked = listaAddons.querySelectorAll('.addon-checkbox:checked');
        checkboxesChecked.forEach(function(cb) {
            precoAdicionais += parseFloat(cb.dataset.price);
        });
    }

    var total = (precoBase + precoAdicionais) * quantidadeAtual;
    modalBtnPrecoEl.textContent = formatarDinheiro(total);
}


// ── Função: abre o modal com os dados do produto clicado ──────
function abrirModalProduto(card) {

    // Verifica se a loja está aberta (definida pelo main.js)
    if (window.isLojaAberta === false) {
        alert('A loja está fechada no momento. Confira nosso horário de funcionamento!');
        return;
    }

    // Lê os dados do produto nos atributos data-* do card HTML
    // Esses atributos são gerados pelo PHP no foreach do banco de dados
    produtoAtual = {
        id    : card.dataset.id,
        name  : card.dataset.name,
        desc  : card.dataset.desc,
        price : parseFloat(card.dataset.price),
        img   : card.dataset.img
    };

    quantidadeAtual = 1;

    // Preenche o modal com as informações do produto
    modalImg.src               = produtoAtual.img;
    modalImg.alt               = produtoAtual.name;
    modalTitulo.textContent    = produtoAtual.name;
    modalDescricao.textContent = produtoAtual.desc;
    modalPrecoEl.textContent   = formatarDinheiro(produtoAtual.price);
    modalQtdEl.textContent     = '1';

    // Limpa o campo de observação
    if (modalObs) {
        modalObs.value = '';
    }

    // Renderiza adicionais se existirem no dataset do card clicado
    var addons = [];
    if (card.dataset.addons) {
        try {
            addons = JSON.parse(card.dataset.addons);
        } catch (e) {
            addons = [];
        }
    }

    if (secaoAddons) {
        if (addons && addons.length > 0) {
            secaoAddons.style.display = 'block';
            listaAddons.innerHTML = '';
            addons.forEach(function(addon) {
                var checkboxId = 'addon-' + addon.id_adicional;
                var itemHtml = `
                    <div class="addon-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; width: 100%;">
                            <input type="checkbox" class="addon-checkbox" data-id="${addon.id_adicional}" data-name="${addon.nome}" data-price="${addon.preco}" id="${checkboxId}">
                            <span style="font-size: 0.95rem; color: #424242;">${addon.nome} (+ ${formatarDinheiro(parseFloat(addon.preco))})</span>
                        </label>
                    </div>
                `;
                listaAddons.innerHTML += itemHtml;
            });

            // Adiciona evento de escuta para recalcular preço
            var checkboxes = listaAddons.querySelectorAll('.addon-checkbox');
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', atualizarPrecoNoBotao);
            });
        } else {
            secaoAddons.style.display = 'none';
            listaAddons.innerHTML = '';
        }
    }

    atualizarPrecoNoBotao();

    // Começa com o botão de diminuir desabilitado (mínimo é 1)
    botaoMenos.disabled = true;

    // Abre o modal e trava o scroll da página
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}


// ── Função: fecha o modal ─────────────────────────────────────
function fecharModal() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    produtoAtual = null;
}


// ── Botão de diminuir quantidade ─────────────────────────────
botaoMenos.addEventListener('click', function() {
    if (quantidadeAtual > 1) {
        quantidadeAtual = quantidadeAtual - 1;
        modalQtdEl.textContent = quantidadeAtual;

        // Desabilita o botão quando chega em 1
        if (quantidadeAtual === 1) {
            botaoMenos.disabled = true;
        }

        atualizarPrecoNoBotao();
    }
});


// ── Botão de aumentar quantidade ─────────────────────────────
botaoMais.addEventListener('click', function() {
    if (quantidadeAtual < 10) {
        quantidadeAtual = quantidadeAtual + 1;
        modalQtdEl.textContent = quantidadeAtual;
        botaoMenos.disabled = false;
        atualizarPrecoNoBotao();
    }
});


// ── Botão "Adicionar" — envia para o PHP via form POST ────────
// Cria um formulário invisível na hora e submete.
// O PHP (src/carrinho_acao.php) salva na sessão e redireciona de volta.
modalBotaoAdd.addEventListener('click', function() {
    if (!produtoAtual) return;

    // Obtém os adicionais selecionados
    var adicionaisSelecionados = [];
    if (listaAddons) {
        var checkboxes = listaAddons.querySelectorAll('.addon-checkbox:checked');
        checkboxes.forEach(function(cb) {
            adicionaisSelecionados.push(cb.dataset.id);
        });
    }

    // Monta o formulário invisível
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'src/carrinho_acao.php';

    // Dados que o PHP precisa para salvar o item na sessão
    var campos = {
        acao           : 'adicionar',
        id_produto     : produtoAtual.id,
        nome           : produtoAtual.name,
        imagem         : produtoAtual.img,
        preco_unitario : produtoAtual.price,
        quantidade     : quantidadeAtual,
        observacao     : (modalObs ? modalObs.value.trim() : ''),
        adicionais     : adicionaisSelecionados.join(',') // passa os IDs separados por vírgula
    };

    // Cria um <input hidden> para cada dado e adiciona no form
    for (var campo in campos) {
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = campo;
        input.value = campos[campo];
        form.appendChild(input);
    }

    // Coloca o form na página e dispara o envio
    document.body.appendChild(form);
    form.submit();
});


// ── Fechar modal clicando no fundo escuro ─────────────────────
modal.addEventListener('click', function(e) {
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

        // Tira o destaque de todos os botões
        botoesCat.forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');

        var categoriaSelecionada = btn.dataset.cat;

        // Mostra ou esconde os cards conforme a categoria
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
