// ============================================================
// usuarios.js — Admin / Usuários (funcionários)
// ============================================================

function _ensureAdminConfirmModal() {
    if (document.getElementById('admin-confirm-modal')) return;

    var overlay = document.createElement('div');
    overlay.id = 'admin-confirm-modal';
    overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = 
        '<div style="width:420px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">' +
            '<div style="display:flex;gap:12px;align-items:flex-start;">' +
                '<div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">' +
                    '<i class="fa-solid fa-triangle-exclamation" style="color:#5c3c27;font-size:1.2rem;"></i>' +
                '</div>' +
                '<div style="flex:1;">' +
                    '<h3 id="admin-confirm-title" style="margin:0;color:#3b2313;font-size:1.05rem;">Confirmar ação</h3>' +
                    '<p id="admin-confirm-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>' +
                '</div>' +
            '</div>' +
            '<div style="display:flex;gap:10px;margin-top:18px;">' +
                '<button type="button" id="admin-confirm-cancel" class="btn-action-accept" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>' +
                '<button type="button" id="admin-confirm-ok" class="btn-action-accept" style="flex:1;background:#5c3c27;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Confirmar</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);
}

function adminConfirm(message, onYes) {
    _ensureAdminConfirmModal();
    var overlay = document.getElementById('admin-confirm-modal');
    overlay.querySelector('#admin-confirm-msg').textContent = message;
    overlay.style.display = 'flex';

    function close() { overlay.style.display = 'none'; }
    var okBtn = overlay.querySelector('#admin-confirm-ok');
    var cancelBtn = overlay.querySelector('#admin-confirm-cancel');

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
        cleanup();
        close();
        if (typeof onYes === 'function') onYes();
    };
}

var _ultimoIdUsuario = 0;
window.onload = function() {
    _ultimoIdUsuario = document.querySelectorAll('.admin-table tbody tr').length;
};

function mascaraTelefone(input) {
    var v = input.value.replace(/\D/g, ''); 
    v = v.substring(0, 11);                 

    if (v.length <= 10) {
        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
    } else {
        v = v.replace(/^(\d{2})(\d{5})(\d{0,4})$/, '($1) $2-$3');
    }
    input.value = v;
}

function getTelefoneRaw(input) {
    return input.value.replace(/\D/g, '');
}

function toggleRoot(checkbox) {
    var selectTipo = document.getElementById('usr-tipo');
    if (checkbox.checked) {
        selectTipo.value    = 'ADMIN';
        selectTipo.disabled = true; 
    } else {
        selectTipo.disabled = false;
    }
}

var _rowIdAtual = null;

function atualizarCheckboxesPermissao(tipo) {
    var checkboxes = document.querySelectorAll('#lista-permissoes-checkboxes input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        var cb = checkboxes[i];
        if (tipo === 'ADMIN') {
            cb.checked  = true;
            cb.disabled = false;
        } else {
            cb.checked  = (cb.value === 'pedidos' || cb.value === 'produtos');
            cb.disabled = false;
        }
    }
}

function abrirModalPermissoes(nome, tipo, rowId) {
    _rowIdAtual = rowId; 

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
    var novoTipo = document.getElementById('modal-tipo-user').value;

    if (_rowIdAtual !== null) {
        var badge = document.getElementById('badge-tipo-' + _rowIdAtual);
        if (badge) {
            badge.className  = 'badge ' + (novoTipo === 'ADMIN' ? 'badge-admin' : 'badge-func');
            badge.textContent = novoTipo;
        }
    }

    alert('Permissões salvas!');
    fecharModalPermissoes();
}

function abrirModalNovoUsuario() {
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

    var nome     = document.getElementById('usr-nome').value.trim();
    var email    = document.getElementById('usr-email').value.trim();
    var telDigits = getTelefoneRaw(document.getElementById('usr-telefone'));
    var senha    = document.getElementById('usr-senha').value;
    var tipo     = document.getElementById('usr-tipo').value;
    var root     = document.getElementById('usr-root').checked;

    if (!nome)     { alert('Preencha o Nome Completo.'); return; }
    if (!email || email.indexOf('@') === -1 || email.indexOf('.') === -1) { alert('Preencha um Email válido.'); return; }
    if (!telDigits || telDigits.length < 10) { alert('Preencha um telefone válido (DDD + número).'); return; }
    if (!senha || senha.length < 8) { alert('A senha deve ter no mínimo 8 caracteres.'); return; }

    var duplicidadeEncontrada = false;
    var linhas = document.querySelectorAll('.admin-table tbody tr');
    for (var i = 0; i < linhas.length; i++) {
        var emailTabela = linhas[i].querySelector('td:nth-child(3)');
        if (emailTabela && emailTabela.innerText === email) {
            duplicidadeEncontrada = true;
        }
    }

    if (duplicidadeEncontrada) {
        alert('Este e-mail já está cadastrado no sistema.');
        return;
    }

    _ultimoIdUsuario = _ultimoIdUsuario + 1;
    var novoId = _ultimoIdUsuario;

    var tipoFinal  = root ? 'ADMIN' : tipo;  

    var tbody = document.querySelector('.admin-table tbody');
    if (tbody) {
        var tr = document.createElement('tr');
        tr.dataset.rowId = novoId;
        tr.dataset.telefoneDigits = telDigits;
        tr.innerHTML = 
            '<td>#' + novoId + '</td>' +
            '<td>' + nome + '</td>' +
            '<td>' + email + '</td>' +
            '<td><span class="badge ' + (tipoFinal === 'ADMIN' ? 'badge-admin' : 'badge-func') + '" id="badge-tipo-' + novoId + '">' + tipoFinal + '</span></td>' +
            '<td>' +
                '<button class="btn-action-accept btn-permission" onclick="abrirModalPermissoes(\'' + nome + '\', \'' + tipoFinal + '\', ' + novoId + ')">' +
                    '<i class="fa-solid fa-pen"></i> Permissões' +
                '</button> ' +
                '<button class="btn-action-accept btn-del" onclick="excluirUsuario(this)">' +
                    '<i class="fa-solid fa-trash"></i> Excluir' +
                '</button>' +
            '</td>';
        tbody.appendChild(tr);
    }

    fecharModalNovoUsuario();
}

function excluirUsuario(btn) {
    var tr   = btn.closest('tr');
    var nomeTd = tr.querySelector('td:nth-child(2)');
    var nome = nomeTd ? nomeTd.innerText : 'este usuário';

    adminConfirm('Deseja realmente excluir "' + nome + '"?\nEsta ação é irreversível.', function() {
        tr.style.transition = 'opacity 0.3s';
        tr.style.opacity    = '0';
        setTimeout(function() {
            tr.remove();
            _ultimoIdUsuario = document.querySelectorAll('.admin-table tbody tr').length;
        }, 300);
    });
}
