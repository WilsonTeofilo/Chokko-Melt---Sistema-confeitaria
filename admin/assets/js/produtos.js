// ============================================================
// produtos.js — Admin / Produtos e adicionais
// ============================================================

function _ensureAdminDialogModal() {
    if (document.getElementById('admin-dialog-modal')) return;

    var overlay = document.createElement('div');
    overlay.id = 'admin-dialog-modal';
    overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = 
        '<div style="width:440px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">' +
            '<div style="display:flex;gap:12px;align-items:flex-start;">' +
                '<div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">' +
                    '<i id="admin-dialog-icon" class="fa-solid fa-pen-to-square" style="color:#5c3c27;font-size:1.2rem;"></i>' +
                '</div>' +
                '<div style="flex:1;">' +
                    '<h3 id="admin-dialog-title" style="margin:0;color:#3b2313;font-size:1.05rem;">Ação</h3>' +
                    '<p id="admin-dialog-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>' +
                '</div>' +
            '</div>' +
            '<div id="admin-dialog-input-wrap" style="display:none;margin-top:14px;">' +
                '<input id="admin-dialog-input" class="form-control" placeholder="" style="width:100%;">' +
                '<p id="admin-dialog-help" style="margin:8px 0 0;font-size:.75rem;color:#777;line-height:1.3;"></p>' +
            '</div>' +
            '<div style="display:flex;gap:10px;margin-top:18px;">' +
                '<button type="button" id="admin-dialog-cancel" class="btn-action-accept" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>' +
                '<button type="button" id="admin-dialog-ok" class="btn-action-accept" style="flex:1;background:#5c3c27;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Confirmar</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);
}

function adminConfirm(message, onYes) {
    _ensureAdminDialogModal();
    var overlay = document.getElementById('admin-dialog-modal');
    overlay.querySelector('#admin-dialog-title').textContent = 'Confirmar ação';
    overlay.querySelector('#admin-dialog-msg').textContent = message;
    overlay.querySelector('#admin-dialog-input-wrap').style.display = 'none';
    overlay.querySelector('#admin-dialog-ok').textContent = 'Confirmar';
    overlay.style.display = 'flex';

    var okBtn = overlay.querySelector('#admin-dialog-ok');
    var cancelBtn = overlay.querySelector('#admin-dialog-cancel');
    function close() { overlay.style.display = 'none'; }

    function cleanup() {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
    }
    
    overlay.onclick = function(e) {
        if (e.target === overlay) { cleanup(); close(); }
    };
    cancelBtn.onclick = function() { cleanup(); close(); };
    okBtn.onclick = function() { cleanup(); close(); if (typeof onYes === 'function') onYes(); };
}

function adminPrompt(opcoes, onOk) {
    _ensureAdminDialogModal();
    var overlay = document.getElementById('admin-dialog-modal');
    overlay.querySelector('#admin-dialog-title').textContent = opcoes.title || 'Digite um valor';
    overlay.querySelector('#admin-dialog-msg').textContent = opcoes.message || '';
    overlay.querySelector('#admin-dialog-ok').textContent = 'Salvar';

    var wrap = overlay.querySelector('#admin-dialog-input-wrap');
    var input = overlay.querySelector('#admin-dialog-input');
    var help = overlay.querySelector('#admin-dialog-help');
    
    wrap.style.display = 'block';
    input.placeholder = opcoes.placeholder || '';
    input.value = '';
    help.textContent = opcoes.helpText || '';

    overlay.style.display = 'flex';
    input.focus();

    var okBtn = overlay.querySelector('#admin-dialog-ok');
    var cancelBtn = overlay.querySelector('#admin-dialog-cancel');
    function close() { overlay.style.display = 'none'; }

    function cleanup() {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
    }
    
    overlay.onclick = function(e) {
        if (e.target === overlay) { cleanup(); close(); }
    };
    cancelBtn.onclick = function() { cleanup(); close(); };
    okBtn.onclick = function() {
        var value = (input.value || '').trim();
        cleanup();
        close();
        if (typeof onOk === 'function') onOk(value);
    };
}

var _imagemProduto = ''; 
var _editingProdutoTr = null; 

function abrirModalProduto(edit, btnEl) {
    document.getElementById('modal-produto-title').innerText = edit ? 'Editar Produto' : 'Novo Produto';
    document.getElementById('prod-edit-id').value = '';
    _editingProdutoTr = null;

    var imgPreview = document.getElementById('img-preview');
    var inputFile = document.getElementById('imagem_produto');

    if (!edit) {
        document.getElementById('prod-nome').value = '';
        document.getElementById('prod-preco').value = '';
        var pc = document.getElementById('prod-custo');
        if (pc) pc.value = '';
        var desc = document.getElementById('prod-descricao');
        if (desc) desc.value = '';
        var cat = document.getElementById('prod-categoria');
        if (cat) cat.selectedIndex = 0;
        document.getElementById('prod-status').value = 'ativo';
        _imagemProduto = '';
        if (imgPreview) {
            imgPreview.style.display = 'none';
            imgPreview.src = '';
        }
        if (inputFile) inputFile.value = '';
        
        var checkboxes = document.querySelectorAll('#adicionais-list input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = false;
        }
    } else if (btnEl) {
        var tr = btnEl.closest('tr');
        if (!tr || !tr.dataset.produtoId) {
            document.getElementById('modal-produto').style.display = 'flex';
            return;
        }
        _editingProdutoTr = tr;
        document.getElementById('prod-edit-id').value = tr.dataset.produtoId;

        var spanNome = tr.querySelector('.prod-info-cell span');
        document.getElementById('prod-nome').value = spanNome ? spanNome.innerText.trim() : '';
        document.getElementById('prod-categoria').value = tr.cells[2] ? tr.cells[2].innerText.trim() : '';

        var precoCell = tr.cells[3] ? tr.cells[3].innerText : '';
        var numStr = precoCell.replace(/[^\d,]/g, '').replace(',', '.');
        var num = parseFloat(numStr);
        document.getElementById('prod-preco').value = !isNaN(num) ? num.toFixed(2).replace('.', ',') : '';

        document.getElementById('prod-status').value = tr.dataset.status === 'congelado' ? 'congelado' : 'ativo';

        var desc = document.getElementById('prod-descricao');
        if (desc) desc.value = tr.dataset.descricao || '';
        
        var pc = document.getElementById('prod-custo');
        if (pc) pc.value = tr.dataset.custo || '';

        var img = tr.querySelector('.prod-info-cell img');
        _imagemProduto = (img && img.src) ? img.src : '';
        if (imgPreview && _imagemProduto) {
            imgPreview.src = _imagemProduto;
            imgPreview.style.display = 'block';
        } else if (imgPreview) {
            imgPreview.style.display = 'none';
            imgPreview.src = '';
        }
        if (inputFile) inputFile.value = '';
    }

    document.getElementById('modal-produto').style.display = 'flex';
}

function fecharModalProduto() {
    if (window.location.search.indexOf('acao=editar') !== -1) {
        window.location.href = 'produtos.php';
        return;
    }
    document.getElementById('modal-produto').style.display = 'none';
    document.getElementById('prod-edit-id').value = '';
    _editingProdutoTr = null;
    var imgPreview = document.getElementById('img-preview');
    if (imgPreview) {
        imgPreview.style.display = 'none';
        imgPreview.src = '';
    }
    var inputFile = document.getElementById('imagem_produto');
    if (inputFile) inputFile.value = '';
    _imagemProduto = '';
    var desc = document.getElementById('prod-descricao');
    if (desc) desc.value = '';
    var pc = document.getElementById('prod-custo');
    if (pc) pc.value = '';
}

function obterProximoIdProduto() {
    var max = 0;
    var trs = document.querySelectorAll('.admin-table tbody tr[data-produto-id]');
    for (var i = 0; i < trs.length; i++) {
        var n = parseInt(trs[i].dataset.produtoId, 10);
        if (!isNaN(n) && n > max) max = n;
    }
    return max + 1;
}

function aplicarDadosNaLinhaProduto(tr, id, nome, categoria, precoFormatado, imgSrc, status) {
    tr.dataset.produtoId = String(id);
    tr.dataset.status = status;

    var srcSeguro = imgSrc || 'https://placehold.co/50x50/f5e6d0/8B4513?text=Foto';

    var freezeBtn = '';
    if (status === 'ativo') {
        freezeBtn = '<button type="button" class="btn-action-accept btn-freeze" title="Congelar item" onclick="congelarProduto(this)"><i class="fa-solid fa-snowflake"></i></button>';
    } else {
        freezeBtn = '<button type="button" class="btn-action-accept btn-unfreeze" title="Ativar item" onclick="descongelarProduto(this)"><i class="fa-solid fa-fire"></i></button>';
    }

    tr.innerHTML = 
        '<td>#' + id + '</td>' +
        '<td class="prod-info-cell">' +
            '<img src="' + srcSeguro + '" style="width:50px;height:50px;object-fit:cover;border-radius:6px;"> ' +
            '<span>' + nome + '</span>' +
        '</td>' +
        '<td>' + categoria + '</td>' +
        '<td class="bold-text">' + precoFormatado + '</td>' +
        '<td><span class="badge ' + (status === 'ativo' ? 'badge-ativo' : 'badge-congelado') + '">' + (status === 'ativo' ? 'Ativo' : 'Congelado') + '</span></td>' +
        '<td>' +
            '<button type="button" class="btn-action-accept btn-edit" title="Editar" onclick="abrirModalProduto(true, this)"><i class="fa-solid fa-pen"></i></button> ' +
            freezeBtn + ' ' +
            '<button type="button" class="btn-action-accept btn-del" title="Excluir" onclick="excluirProduto(this)"><i class="fa-solid fa-trash"></i></button>' +
        '</td>';
}

function previewImagem(event) {
    var file = event.target.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function () {
        _imagemProduto = reader.result; 
        var output = document.getElementById('img-preview');
        output.src   = _imagemProduto;
        output.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function verificarNovaCategoria(select) {
    if (select.value === 'nova_categoria') {
        adminPrompt({
            title: 'Nova categoria',
            message: 'Digite o nome da nova categoria para adicionar no seletor.',
            placeholder: 'Ex: Bolos de Pote',
            helpText: 'Dica: mantenha nomes curtos e consistentes.'
        }, function(nova) {
            if (nova && nova !== '') {
                var newOption = document.createElement('option');
                newOption.value = nova;
                newOption.text = nova;
                select.add(newOption, select.options[select.options.length - 1]);
                select.value = nova;
            } else {
                select.selectedIndex = 0;
            }
        });
    }
}

function salvarProduto(event) {
    if (event) event.preventDefault();

    var nome      = document.getElementById('prod-nome').value.trim();
    var precoStr  = document.getElementById('prod-preco').value.trim().replace(',', '.');
    var precoRaw  = parseFloat(precoStr);
    var categoria = document.getElementById('prod-categoria').value;
    var status    = document.getElementById('prod-status').value;

    if (!nome) {
        alert('Preencha o Nome do Produto.');
        return;
    }
    if (isNaN(precoRaw) || precoRaw <= 0) {
        alert('Informe um Preço de Venda válido (somente números).');
        return;
    }

    var precoFormatado = 'R$ ' + precoRaw.toFixed(2).replace('.', ',');
    var imgSrc = _imagemProduto ? _imagemProduto : 'https://placehold.co/50x50/f5e6d0/8B4513?text=Foto';

    var tbody = document.querySelector('.admin-table tbody');
    if (!tbody) {
        fecharModalProduto();
        return;
    }

    var editId = document.getElementById('prod-edit-id').value.trim();

    if (_editingProdutoTr && editId) {
        aplicarDadosNaLinhaProduto(_editingProdutoTr, editId, nome, categoria, precoFormatado, imgSrc, status);
    } else {
        var novoId = obterProximoIdProduto();
        var tr = document.createElement('tr');
        aplicarDadosNaLinhaProduto(tr, novoId, nome, categoria, precoFormatado, imgSrc, status);
        tbody.appendChild(tr);
    }

    fecharModalProduto();
}

function excluirProduto(btn) {
    var tr   = btn.closest('tr');
    var id   = tr.dataset.produtoId;
    var span = tr.querySelector('.prod-info-cell span');
    var nome = span ? span.innerText.trim() : 'este produto';

    adminConfirm('Tem certeza que deseja excluir "' + nome + '"?\nEsta ação não pode ser desfeita.', function() {
        window.location.href = 'produtos.php?acao=excluir&id_produto=' + id;
    });
}

function congelarProduto(btn) {
    var tr = btn.closest('tr');
    if (!tr) return;
    var id = tr.dataset.produtoId;
    window.location.href = 'src/produtos_controller.php?acao=alterarStatus&status=0&id=' + id;
}

function descongelarProduto(btn) {
    var tr = btn.closest('tr');
    if (!tr) return;
    var id = tr.dataset.produtoId;
    window.location.href = 'src/produtos_controller.php?acao=alterarStatus&status=1&id=' + id;
}

function filtrarProdutos(status, btn) {
    var botoes = document.querySelectorAll('#produtos-tabs .tab-btn');
    for (var i = 0; i < botoes.length; i++) {
        botoes[i].classList.remove('active');
    }
    btn.classList.add('active');

    var trs = document.querySelectorAll('.admin-table tbody tr');
    for (var j = 0; j < trs.length; j++) {
        if (status === 'todos' || trs[j].dataset.status === status) {
            trs[j].style.display = '';
        } else {
            trs[j].style.display = 'none';
        }
    }
}

function abrirModalAdicional() {
    document.getElementById('add-nome').value = '';
    document.getElementById('add-preco').value = '';
    document.getElementById('modal-novo-adicional').style.display = 'flex';
}

function fecharModalAdicional() {
    document.getElementById('modal-novo-adicional').style.display = 'none';
}

function salvarNovoAdicional() {
    var nome = document.getElementById('add-nome').value.trim();
    var precoVal = document.getElementById('add-preco').value.trim().replace(',', '.');
    var precoNum = parseFloat(precoVal);

    if (!nome) {
        alert('O nome do adicional é obrigatório.');
        return;
    }

    var textoPreco = '';
    if (!isNaN(precoNum) && precoNum > 0) {
        textoPreco = ' (+ R$ ' + precoNum.toFixed(2).replace('.', ',') + ')';
    } else {
        precoNum = 0;
    }

    var valorAdicional = 'novo:' + nome + ':' + precoNum;

    var lista = document.getElementById('adicionais-list');
    var label = document.createElement('label');
    label.className = 'adicional-item';
    label.innerHTML = 
        '<input type="checkbox" name="adicionais[]" value="' + valorAdicional + '" checked style="width:18px;height:18px;accent-color:var(--marrom);">' +
        '<span>' + nome + textoPreco + '</span>';

    var btnCriar = lista.querySelector('.btn-text-add');
    lista.insertBefore(label, btnCriar);

    fecharModalAdicional();
}
