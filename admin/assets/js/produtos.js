/**
 * ═══════════════════════════════════════════════════════════════════════════
 * produtos.js — Admin / Produtos e adicionais
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt
 *
 * Carregado em: admin/produtos.php
 *
 * Hoje: insere linhas na tabela HTML e modais sem falar com MySQL.
 *
 * O que VOCÊ (PHP) deve fazer:
 *   • Listar produtos: SELECT com JOIN categoria; gerar linhas da <tbody>.
 *   • salvarProduto(): POST multipart para admin/api/salvar_produto.php
 *     (INSERT/UPDATE produto + upload de imagem para pasta uploads/).
 *   • Adicionais: tabelas adicional e produto_adicional — API para vincular.
 *   • Categoria “dinâmica”: ao criar nome novo, PHP INSERT em categoria e
 *     devolve id para o <select>, ou recarrega a página.
 *   • Sempre validar preço, permissão de admin e tipo MIME da imagem no PHP.
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ── UI helpers (modais bonitos: confirm/prompt) ────────────────
function _ensureAdminDialogModal() {
    if (document.getElementById('admin-dialog-modal')) return;

    const overlay = document.createElement('div');
    overlay.id = 'admin-dialog-modal';
    overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = `
        <div style="width:440px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">
                    <i id="admin-dialog-icon" class="fa-solid fa-pen-to-square" style="color:#5c3c27;font-size:1.2rem;"></i>
                </div>
                <div style="flex:1;">
                    <h3 id="admin-dialog-title" style="margin:0;color:#3b2313;font-size:1.05rem;">Ação</h3>
                    <p id="admin-dialog-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>
                </div>
            </div>

            <div id="admin-dialog-input-wrap" style="display:none;margin-top:14px;">
                <input id="admin-dialog-input" class="form-control" placeholder="" style="width:100%;">
                <p id="admin-dialog-help" style="margin:8px 0 0;font-size:.75rem;color:#777;line-height:1.3;"></p>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" id="admin-dialog-cancel" class="btn-action-accept" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>
                <button type="button" id="admin-dialog-ok" class="btn-action-accept" style="flex:1;background:#5c3c27;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Confirmar</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function adminConfirm(message, onYes) {
    _ensureAdminDialogModal();
    const overlay = document.getElementById('admin-dialog-modal');
    overlay.querySelector('#admin-dialog-title').textContent = 'Confirmar ação';
    overlay.querySelector('#admin-dialog-msg').textContent = message;
    overlay.querySelector('#admin-dialog-input-wrap').style.display = 'none';
    overlay.querySelector('#admin-dialog-ok').textContent = 'Confirmar';
    overlay.style.display = 'flex';

    const okBtn = overlay.querySelector('#admin-dialog-ok');
    const cancelBtn = overlay.querySelector('#admin-dialog-cancel');
    const close = () => { overlay.style.display = 'none'; };

    const cleanup = () => {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
        document.removeEventListener('keydown', onKey);
    };
    const onKey = (e) => {
        if (e.key === 'Escape') {
            cleanup(); close();
        }
    };
    document.addEventListener('keydown', onKey);
    overlay.onclick = (e) => {
        if (e.target === overlay) { cleanup(); close(); }
    };
    cancelBtn.onclick = () => { cleanup(); close(); };
    okBtn.onclick = () => { cleanup(); close(); if (typeof onYes === 'function') onYes(); };
}

function adminPrompt({ title, message, placeholder, helpText }, onOk) {
    _ensureAdminDialogModal();
    const overlay = document.getElementById('admin-dialog-modal');
    overlay.querySelector('#admin-dialog-title').textContent = title || 'Digite um valor';
    overlay.querySelector('#admin-dialog-msg').textContent = message || '';
    overlay.querySelector('#admin-dialog-ok').textContent = 'Salvar';

    const wrap = overlay.querySelector('#admin-dialog-input-wrap');
    const input = overlay.querySelector('#admin-dialog-input');
    const help = overlay.querySelector('#admin-dialog-help');
    wrap.style.display = 'block';
    input.placeholder = placeholder || '';
    input.value = '';
    help.textContent = helpText || '';

    overlay.style.display = 'flex';
    input.focus();

    const okBtn = overlay.querySelector('#admin-dialog-ok');
    const cancelBtn = overlay.querySelector('#admin-dialog-cancel');
    const close = () => { overlay.style.display = 'none'; };

    const cleanup = () => {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
        document.removeEventListener('keydown', onKey);
    };
    const onKey = (e) => {
        if (e.key === 'Escape') { cleanup(); close(); }
        if (e.key === 'Enter') { okBtn.click(); }
    };
    document.addEventListener('keydown', onKey);
    overlay.onclick = (e) => {
        if (e.target === overlay) { cleanup(); close(); }
    };
    cancelBtn.onclick = () => { cleanup(); close(); };
    okBtn.onclick = () => {
        const value = (input.value || '').trim();
        cleanup();
        close();
        if (typeof onOk === 'function') onOk(value);
    };
}

/* ══════════════════════════════════════════
   MODAL — ABRIR / FECHAR
══════════════════════════════════════════ */

function abrirModalProduto(edit = false, btnEl = null) {
    document.getElementById('modal-produto-title').innerText = edit ? 'Editar Produto' : 'Novo Produto';
    document.getElementById('prod-edit-id').value = '';
    _editingProdutoTr = null;

    const imgPreview = document.getElementById('img-preview');
    const inputFile = document.getElementById('imagem_produto');

    if (!edit) {
        document.getElementById('prod-nome').value = '';
        document.getElementById('prod-preco').value = '';
        const pc = document.getElementById('prod-custo');
        if (pc) pc.value = '';
        const cat = document.getElementById('prod-categoria');
        if (cat) cat.selectedIndex = 0;
        document.getElementById('prod-status').value = 'ativo';
        _imagemProduto = '';
        if (imgPreview) {
            imgPreview.style.display = 'none';
            imgPreview.src = '';
        }
        if (inputFile) inputFile.value = '';
    } else if (btnEl) {
        const tr = btnEl.closest('tr');
        if (!tr || !tr.dataset.produtoId) {
            document.getElementById('modal-produto').style.display = 'flex';
            return;
        }
        _editingProdutoTr = tr;
        document.getElementById('prod-edit-id').value = tr.dataset.produtoId;

        document.getElementById('prod-nome').value = tr.querySelector('.prod-info-cell span')?.innerText.trim() || '';
        document.getElementById('prod-categoria').value = tr.cells[2]?.innerText.trim() || '';

        const precoCell = tr.cells[3]?.innerText || '';
        const num = parseFloat(precoCell.replace(/[^\d,]/g, '').replace(',', '.'));
        document.getElementById('prod-preco').value = !isNaN(num) ? num.toFixed(2).replace('.', ',') : '';

        document.getElementById('prod-status').value = tr.dataset.status === 'congelado' ? 'congelado' : 'ativo';

        const img = tr.querySelector('.prod-info-cell img');
        _imagemProduto = img && img.src ? img.src : '';
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
    document.getElementById('modal-produto').style.display = 'none';
    document.getElementById('prod-edit-id').value = '';
    _editingProdutoTr = null;
    const imgPreview = document.getElementById('img-preview');
    if (imgPreview) {
        imgPreview.style.display = 'none';
        imgPreview.src = '';
    }
    const inputFile = document.getElementById('imagem_produto');
    if (inputFile) inputFile.value = '';
    _imagemProduto = '';
}

/* ══════════════════════════════════════════
   PREVIEW DE IMAGEM (Upload Front-End)
   A URL base64 fica guardada aqui pra ser usada no salvarProduto()
══════════════════════════════════════════ */

let _imagemProduto = ''; // Armazena o data URL da imagem selecionada
let _editingProdutoTr = null; // Linha em edição (null = novo produto)

function _escProduto(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
}

function obterProximoIdProduto() {
    let max = 0;
    document.querySelectorAll('.admin-table tbody tr[data-produto-id]').forEach(tr => {
        const n = parseInt(tr.dataset.produtoId, 10);
        if (!isNaN(n) && n > max) max = n;
    });
    return max + 1;
}

/** Atualiza os <td> da linha (ID + 5 colunas após inclusão da coluna ID). */
function aplicarDadosNaLinhaProduto(tr, id, nome, categoria, precoFormatado, imgSrc, status) {
    tr.dataset.produtoId = String(id);
    tr.dataset.status = status;

    const srcSeguro = imgSrc || 'https://placehold.co/50x50/f5e6d0/8B4513?text=Foto';

    const freezeBtn =
        status === 'ativo'
            ? `<button type="button" class="btn-action-accept btn-freeze" title="Congelar item" onclick="congelarProduto(this)"><i class="fa-solid fa-snowflake"></i></button>`
            : `<button type="button" class="btn-action-accept btn-unfreeze" title="Ativar item" onclick="descongelarProduto(this)"><i class="fa-solid fa-fire"></i></button>`;

    tr.innerHTML = `
        <td>#${id}</td>
        <td class="prod-info-cell">
            <img src="${srcSeguro.replace(/"/g, '&quot;')}" alt="${_escProduto(nome)}" style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
            <span>${_escProduto(nome)}</span>
        </td>
        <td>${_escProduto(categoria)}</td>
        <td class="bold-text">${_escProduto(precoFormatado)}</td>
        <td><span class="badge ${status === 'ativo' ? 'badge-ativo' : 'badge-congelado'}">${status === 'ativo' ? 'Ativo' : 'Congelado'}</span></td>
        <td>
            <button type="button" class="btn-action-accept btn-edit" title="Editar" onclick="abrirModalProduto(true, this)"><i class="fa-solid fa-pen"></i></button>
            ${freezeBtn}
            <button type="button" class="btn-action-accept btn-del" title="Excluir" onclick="excluirProduto(this)"><i class="fa-solid fa-trash"></i></button>
        </td>
    `;
}

function previewImagem(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function () {
        _imagemProduto = reader.result; // Guarda para usar no salvar
        const output = document.getElementById('img-preview');
        output.src   = _imagemProduto;
        output.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

/* ══════════════════════════════════════════
   SALVAR PRODUTO (Mockup)
   NOTA BACKEND: substituir pelo fetch POST para api/salvar_produto.php
   com FormData contendo: nome, preco, custo_compra, id_categoria, disponibilidade, imagem(file)
══════════════════════════════════════════ */

function verificarNovaCategoria(select) {
    if (select.value === 'nova_categoria') {
        adminPrompt(
            {
                title: 'Nova categoria',
                message: 'Digite o nome da nova categoria para adicionar no seletor.',
                placeholder: 'Ex: Bolos de Pote',
                helpText: 'Dica: mantenha nomes curtos e consistentes.',
            },
            (nova) => {
                if (nova && nova.trim() !== '') {
                    const nomeCat = nova.trim();
                    const newOption = document.createElement('option');
                    newOption.value = nomeCat;
                    newOption.text = nomeCat;
                    // Insere antes do último (que é o + Criar nova categoria)
                    select.add(newOption, select.options[select.options.length - 1]);
                    select.value = nomeCat;
                } else {
                    // Reverte pro primeiro se cancelar/blank
                    select.selectedIndex = 0;
                }
            }
        );
    }
}

function salvarProduto(event) {
    if (event) event.preventDefault();

    const nome      = document.getElementById('prod-nome').value.trim();
    // Pega o valor com vírgula, troca por ponto para o JS entender matematicamente
    const precoStr  = document.getElementById('prod-preco').value.trim().replace(',', '.');
    const precoRaw  = parseFloat(precoStr);
    const categoria = document.getElementById('prod-categoria').value;
    const status    = document.getElementById('prod-status').value;

    // Validações
    if (!nome) {
        alert('Preencha o Nome do Produto.');
        document.getElementById('prod-nome').focus();
        return;
    }
    if (isNaN(precoRaw) || precoRaw <= 0) {
        alert('Informe um Preço de Venda válido (somente números).');
        document.getElementById('prod-preco').focus();
        return;
    }

    // Formata preço para exibição — R$ X,XX
    const precoFormatado = 'R$ ' + precoRaw.toFixed(2).replace('.', ',');
    const imgSrc = _imagemProduto
        ? _imagemProduto
        : 'https://placehold.co/50x50/f5e6d0/8B4513?text=Foto';

    const tbody = document.querySelector('.admin-table tbody');
    if (!tbody) {
        fecharModalProduto();
        return;
    }

    const editId = document.getElementById('prod-edit-id').value.trim();

    if (_editingProdutoTr && editId) {
        aplicarDadosNaLinhaProduto(_editingProdutoTr, editId, nome, categoria, precoFormatado, imgSrc, status);
    } else {
        const novoId = obterProximoIdProduto();
        const tr = document.createElement('tr');
        aplicarDadosNaLinhaProduto(tr, novoId, nome, categoria, precoFormatado, imgSrc, status);
        tbody.appendChild(tr);
    }

    fecharModalProduto();
}

/* ══════════════════════════════════════════
   EXCLUIR PRODUTO (Mockup)
   NOTA BACKEND: fetch DELETE para api/deletar_produto.php?id=X
   SQL: DELETE FROM produto WHERE id_produto = ?
══════════════════════════════════════════ */

function excluirProduto(btn) {
    const tr   = btn.closest('tr');
    const nome = tr.querySelector('.prod-info-cell span')?.innerText.trim() || 'este produto';

    adminConfirm(`Tem certeza que deseja excluir "${nome}"?\nEsta ação não pode ser desfeita.`, () => {
        tr.style.transition = 'opacity 0.3s';
        tr.style.opacity = '0';
        setTimeout(() => tr.remove(), 300);
    });
}

/* ══════════════════════════════════════════
   CONGELAR / DESCONGELAR (Mockup)
   NOTA BACKEND: fetch PATCH para api/produto_disponibilidade.php
   SQL: UPDATE produto SET disponibilidade = ? WHERE id_produto = ?
══════════════════════════════════════════ */

function congelarProduto(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    const badge = tr.querySelector('td:nth-child(5) .badge') || tr.querySelector('.badge');
    tr.dataset.status = 'congelado';
    if (badge) {
        badge.className = 'badge badge-congelado';
        badge.textContent = 'Congelado';
    }
    btn.outerHTML = `<button type="button" class="btn-action-accept btn-unfreeze" title="Ativar item" onclick="descongelarProduto(this)"><i class="fa-solid fa-fire"></i></button>`;
}

function descongelarProduto(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    const badge = tr.querySelector('td:nth-child(5) .badge') || tr.querySelector('.badge');
    tr.dataset.status = 'ativo';
    if (badge) {
        badge.className = 'badge badge-ativo';
        badge.textContent = 'Ativo';
    }
    btn.outerHTML = `<button type="button" class="btn-action-accept btn-freeze" title="Congelar item" onclick="congelarProduto(this)"><i class="fa-solid fa-snowflake"></i></button>`;
}

/* ══════════════════════════════════════════
   FILTRAR POR STATUS (Tabs)
══════════════════════════════════════════ */

function filtrarProdutos(status, btn) {
    // Atualiza tab ativa
    document.querySelectorAll('#produtos-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filtra as linhas
    document.querySelectorAll('.admin-table tbody tr').forEach(tr => {
        if (status === 'todos' || tr.dataset.status === status) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}

/* ══════════════════════════════════════════
   CRIAR ADICIONAL DINÂMICO
══════════════════════════════════════════ */

function abrirModalAdicional() {
    document.getElementById('add-nome').value = '';
    document.getElementById('add-preco').value = '';
    document.getElementById('modal-novo-adicional').style.display = 'flex';
}

function fecharModalAdicional() {
    document.getElementById('modal-novo-adicional').style.display = 'none';
}

function salvarNovoAdicional() {
    const nome = document.getElementById('add-nome').value.trim();
    const preco = parseFloat(document.getElementById('add-preco').value);

    if (!nome) {
        alert('O nome do adicional é obrigatório.');
        return;
    }

    // Formata o preço se ele existir e for maior que 0
    let textoPreco = '';
    if (!isNaN(preco) && preco > 0) {
        textoPreco = ` (+ R$ ${preco.toFixed(2).replace('.', ',')})`;
    }

    const lista = document.getElementById('adicionais-list');
    const label = document.createElement('label');
    label.className = 'adicional-item';
    label.innerHTML = `
        <input type="checkbox" style="width:18px;height:18px;accent-color:var(--marrom);">
        <span>${nome}${textoPreco}</span>
    `;

    // Insere antes do botão de criar
    const btnCriar = lista.querySelector('.btn-text-add');
    lista.insertBefore(label, btnCriar);

    fecharModalAdicional();
}
