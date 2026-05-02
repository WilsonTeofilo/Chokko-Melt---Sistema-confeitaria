/**
 * ═══════════════════════════════════════════════════════════════════════════
 * usuarios.js — Admin / Usuários (funcionários)
 * INTEGRAÇÃO JavaScript ↔ PHP (iniciante)
 * ═══════════════════════════════════════════════════════════════════════════
 * Guia: INTEGRACAO_JS_PHP.txt
 *
 * Carregado em: admin/usuarios.php
 *
 * Hoje: tabela e permissões são mock no navegador.
 *
 * O que VOCÊ (PHP) deve fazer:
 *   • Listar: SELECT * FROM usuario (nunca exibir senha em claro).
 *   • Criar usuário: POST api/criar_usuario.php com senha já com password_hash().
 *   • Excluir: POST/DELETE com CSRF ou token se necessário.
 *   • Permissões: se usar tabela usuario_permissao, o JS deve espelhar o que
 *     o PHP gravar; ou renderize checkboxes já marcados pelo PHP no HTML.
 *   • Apenas usuários ADMIN ou root=true podem chamar essas APIs (verificar sessão).
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ── UI helpers (modais bonitos, sem window.confirm) ────────────
function _ensureAdminConfirmModal() {
    if (document.getElementById('admin-confirm-modal')) return;

    const overlay = document.createElement('div');
    overlay.id = 'admin-confirm-modal';
    overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = `
        <div style="width:420px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#5c3c27;font-size:1.2rem;"></i>
                </div>
                <div style="flex:1;">
                    <h3 id="admin-confirm-title" style="margin:0;color:#3b2313;font-size:1.05rem;">Confirmar ação</h3>
                    <p id="admin-confirm-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" id="admin-confirm-cancel" class="btn-action-accept" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>
                <button type="button" id="admin-confirm-ok" class="btn-action-accept" style="flex:1;background:#5c3c27;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Confirmar</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function adminConfirm(message, onYes) {
    _ensureAdminConfirmModal();
    const overlay = document.getElementById('admin-confirm-modal');
    overlay.querySelector('#admin-confirm-msg').textContent = message;
    overlay.style.display = 'flex';

    const close = () => { overlay.style.display = 'none'; };
    const okBtn = overlay.querySelector('#admin-confirm-ok');
    const cancelBtn = overlay.querySelector('#admin-confirm-cancel');

    const cleanup = () => {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
        document.removeEventListener('keydown', onKey);
    };

    const onKey = (e) => {
        if (e.key === 'Escape') {
            cleanup();
            close();
        }
    };
    document.addEventListener('keydown', onKey);

    overlay.onclick = (e) => {
        if (e.target === overlay) {
            cleanup();
            close();
        }
    };

    cancelBtn.onclick = () => {
        cleanup();
        close();
    };

    okBtn.onclick = () => {
        cleanup();
        close();
        if (typeof onYes === 'function') onYes();
    };
}

/* ══════════════════════════════════════════
   CONTADOR FRONT-END (Simula AUTO_INCREMENT)
   NOTA BACKEND: na versão real, o ID vem do INSERT retornando LAST_INSERT_ID()
══════════════════════════════════════════ */
let _ultimoIdUsuario = document.querySelectorAll('.admin-table tbody tr').length; // Começa pelo total atual de linhas

/* ══════════════════════════════════════════
   MÁSCARA DE TELEFONE — (00) 00000-0000
══════════════════════════════════════════ */
function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    v = v.substring(0, 11);                 // Limita a 11 dígitos

    if (v.length <= 10) {
        // Fixo: (00) 0000-0000
        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
    } else {
        // Celular: (00) 00000-0000
        v = v.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3');
    }

    input.value = v;
}

/* Retorna somente os dígitos do telefone (para enviar ao banco) */
function getTelefoneRaw(input) {
    return input.value.replace(/\D/g, '');
}

/* ══════════════════════════════════════════
   ROOT → Força tipo ADMIN automaticamente
══════════════════════════════════════════ */
function toggleRoot(checkbox) {
    const selectTipo = document.getElementById('usr-tipo');
    if (checkbox.checked) {
        selectTipo.value    = 'ADMIN';
        selectTipo.disabled = true; // Não deixa trocar enquanto root está marcado
    } else {
        selectTipo.disabled = false;
    }
}

/* ══════════════════════════════════════════
   PERMISSÕES — MODAL
══════════════════════════════════════════ */
let _rowIdAtual = null; // Rastreia qual linha está sendo editada

function atualizarCheckboxesPermissao(tipo) {
    const checkboxes = document.querySelectorAll('#lista-permissoes-checkboxes input[type="checkbox"]');
    checkboxes.forEach(cb => {
        if (tipo === 'ADMIN') {
            cb.checked  = true;
            cb.disabled = false;
        } else {
            cb.checked  = (cb.value === 'pedidos' || cb.value === 'produtos');
            cb.disabled = false;
        }
    });
}

function abrirModalPermissoes(nome, tipo, rowId) {
    _rowIdAtual = rowId; // Guarda qual linha abriu o modal

    document.getElementById('modal-nome-user').innerText = nome;
    document.getElementById('modal-tipo-user').value     = tipo;
    atualizarCheckboxesPermissao(tipo);

    document.getElementById('modal-permissoes').style.display = 'flex';
}

function fecharModalPermissoes() {
    document.getElementById('modal-permissoes').style.display = 'none';
    _rowIdAtual = null;
}

function salvarPermissoes() {
    const novoTipo = document.getElementById('modal-tipo-user').value;

    // Atualiza o badge na tabela da linha correspondente
    if (_rowIdAtual !== null) {
        const badge = document.getElementById(`badge-tipo-${_rowIdAtual}`);
        if (badge) {
            badge.className  = `badge ${novoTipo === 'ADMIN' ? 'badge-admin' : 'badge-func'}`;
            badge.textContent = novoTipo;
        }
    }

    // NOTA BACKEND: POST api/salvar_permissoes.php { id_usuario: _rowIdAtual, tipo_usuario: novoTipo, permissoes: [...] }
    alert(`Permissões de "${document.getElementById('modal-nome-user').innerText}" salvas como ${novoTipo}!`);
    fecharModalPermissoes();
}

/* ══════════════════════════════════════════
   NOVO USUÁRIO — MODAL
══════════════════════════════════════════ */

function abrirModalNovoUsuario() {
    // Reset do formulário
    document.getElementById('usr-nome').value      = '';
    document.getElementById('usr-email').value     = '';
    document.getElementById('usr-telefone').value  = '';
    document.getElementById('usr-senha').value     = '';
    document.getElementById('usr-tipo').value      = 'FUNCIONARIO';
    document.getElementById('usr-tipo').disabled   = false;
    document.getElementById('usr-root').checked    = false;

    document.getElementById('modal-novo-usuario-title').innerText = 'Novo Usuário';
    document.getElementById('modal-novo-usuario').style.display = 'flex';
}

function fecharModalNovoUsuario() {
    document.getElementById('modal-novo-usuario').style.display = 'none';
}

function salvarNovoUsuario(event) {
    if (event) event.preventDefault();

    const nome     = document.getElementById('usr-nome').value.trim();
    const email    = document.getElementById('usr-email').value.trim();
    const telDigits = getTelefoneRaw(document.getElementById('usr-telefone'));
    const senha    = document.getElementById('usr-senha').value;
    const tipo     = document.getElementById('usr-tipo').value;
    const root     = document.getElementById('usr-root').checked;

    // Validações front-end
    if (!nome)     { alert('Preencha o Nome Completo.'); return; }
    if (!email || !email.includes('@') || !email.includes('.')) { alert('Preencha um Email válido.'); return; }
    if (!telDigits || telDigits.length < 10) { alert('Preencha um telefone válido (DDD + número).'); return; }
    if (!senha || senha.length < 8) { alert('A senha deve ter no mínimo 8 caracteres.'); return; }

    // Verificação de duplicidade na tabela (mockup)
    let duplicidadeEncontrada = false;
    document.querySelectorAll('.admin-table tbody tr').forEach(tr => {
        // Assume que: Coluna 3 = Email, Coluna invisível/data-attribute = telefone (aqui vamos checar só email no frontend já que a tabela só mostra o email)
        const emailTabela = tr.querySelector('td:nth-child(3)')?.innerText;
        if (emailTabela === email) {
            duplicidadeEncontrada = true;
        }
    });

    if (duplicidadeEncontrada) {
        alert('Este e-mail já está cadastrado no sistema.');
        return;
    }

    // NOTA BACKEND: POST api/criar_usuario.php
    // Body: { nome, email, telefone: telDigits (só dígitos), senha, tipo_usuario: tipo, root: root }
    // SQL: INSERT INTO usuario (nome, email, senha, telefone, tipo_usuario, root) VALUES (?,?,?,?,?,?)

    // Simula AUTO_INCREMENT
    _ultimoIdUsuario++;
    const novoId = _ultimoIdUsuario;

    const badgeClass = tipo === 'ADMIN' ? 'badge-admin' : 'badge-func';
    const tipoFinal  = root ? 'ADMIN' : tipo;  // Root sempre força ADMIN

    const tbody = document.querySelector('.admin-table tbody');
    if (tbody) {
        const tr = document.createElement('tr');
        tr.dataset.rowId = novoId;
        tr.dataset.telefoneDigits = telDigits;
        tr.innerHTML = `
            <td>#${novoId}</td>
            <td>${nome}</td>
            <td>${email}</td>
            <td><span class="badge ${tipoFinal === 'ADMIN' ? 'badge-admin' : 'badge-func'}" id="badge-tipo-${novoId}">${tipoFinal}</span></td>
            <td>
                <button class="btn-action-accept btn-permission" onclick="abrirModalPermissoes('${nome}', '${tipoFinal}', ${novoId})">
                    <i class="fa-solid fa-pen"></i> Permissões
                </button>
                <button class="btn-action-accept btn-del" onclick="excluirUsuario(this)">
                    <i class="fa-solid fa-trash"></i> Excluir
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    fecharModalNovoUsuario();
}

/* ══════════════════════════════════════════
   EXCLUIR USUÁRIO (Mockup)
   NOTA BACKEND: DELETE api/deletar_usuario.php?id=X
   SQL: DELETE FROM usuario WHERE id_usuario = ?
══════════════════════════════════════════ */
function excluirUsuario(btn) {
    const tr   = btn.closest('tr');
    const nome = tr.querySelector('td:nth-child(2)')?.innerText || 'este usuário';

    adminConfirm(`Deseja realmente excluir "${nome}"?\nEsta ação é irreversível.`, () => {
        tr.style.transition = 'opacity 0.3s';
        tr.style.opacity    = '0';
        setTimeout(() => {
            tr.remove();
            // Recalcula o contador com base nas linhas que sobraram
            _ultimoIdUsuario = document.querySelectorAll('.admin-table tbody tr').length;
        }, 300);
    });
}
