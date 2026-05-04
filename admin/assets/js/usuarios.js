function _ensureAdminConfirmModal() {
    if (document.getElementById('admin-confirm-modal')) return;
    var overlay = document.createElement('div');
    overlay.id = 'admin-confirm-modal';
    overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;';
    overlay.innerHTML =
        '<div style="background:#fff;border-radius:16px;padding:30px;width:420px;max-width:90%;box-shadow:0 15px 35px rgba(0,0,0,.25);animation:modalPop .3s ease;">' +
            '<p id="admin-confirm-msg" style="font-size:1rem;color:#3b2313;line-height:1.5;margin:0 0 20px;white-space:pre-line;"></p>' +
            '<div style="display:flex;gap:10px;">' +
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

function restaurarCheckboxes(permissoesSalvas) {
    var lista = permissoesSalvas ? permissoesSalvas.split(',') : [];
    var checkboxes = document.querySelectorAll('#lista-permissoes-checkboxes input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].disabled = false;
        checkboxes[i].checked  = lista.indexOf(checkboxes[i].value) !== -1;
    }
}

function abrirModalPermissoes(nome, tipo, rowId, permissoesSalvas) {
    _rowIdAtual = rowId;
    document.getElementById('modal-nome-user').innerText = nome;
    document.getElementById('modal-tipo-user').value     = tipo;

    // Se tem permissoes salvas no banco, usa elas. Se nao, usa o padrao do tipo.
    if (permissoesSalvas && permissoesSalvas.length > 0) {
        restaurarCheckboxes(permissoesSalvas);
    } else {
        atualizarCheckboxesPermissao(tipo);
    }

    document.getElementById('modal-permissoes').style.display = 'flex';
}

function fecharModalPermissoes() {
    document.getElementById('modal-permissoes').style.display = 'none';
    _rowIdAtual = null;
}

function salvarPermissoes() {
    if (_rowIdAtual === null) return;

    var novoTipo = document.getElementById('modal-tipo-user').value;

    // Coleta os checkboxes marcados
    var checkboxes = document.querySelectorAll('#lista-permissoes-checkboxes input[type="checkbox"]');
    var perms = [];
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            perms.push(checkboxes[i].value);
        }
    }

    // Cria um form invisivel e faz POST pro PHP (seu padrao de codigo)
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'src/auth_admin.php';

    var campos = {
        'acao': 'AtualizarPermissoes',
        'id_usuario': _rowIdAtual,
        'tipo_usuario': novoTipo,
        'permissoes': perms.join(',')
    };

    for (var key in campos) {
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = key;
        input.value = campos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

function abrirModalNovoUsuario() {
    var form = document.querySelector('#modal-novo-usuario form');
    if (form) form.reset();
    var selectTipo = document.getElementById('usr-tipo');
    if (selectTipo) selectTipo.disabled = false;
    document.getElementById('modal-novo-usuario-title').innerText = 'Novo Usuario';
    document.getElementById('modal-novo-usuario').style.display = 'flex';
}

function fecharModalNovoUsuario() {
    document.getElementById('modal-novo-usuario').style.display = 'none';
}

function excluirUsuario(btn, idUsuario) {
    var tr     = btn.closest('tr');
    var nomeTd = tr.querySelector('td:nth-child(2)');
    var nome   = nomeTd ? nomeTd.innerText : 'este usuario';

    adminConfirm('Deseja realmente excluir "' + nome + '"?\nEsta acao e irreversivel.', function() {
        window.location.href = 'src/auth_admin.php?acao=ExcluirUsuario&id=' + idUsuario;
    });
}

// ============================================================
// VALIDACAO DE FORCA E CONFIRMACAO DE SENHA (ADMIN)
// ============================================================
document.addEventListener("DOMContentLoaded", function() {
    var inputSenha     = document.getElementById("usr_senha");
    var inputConfirmar = document.getElementById("usr_senha_confirmar");
    var formAdmin      = document.querySelector("#modal-novo-usuario form");
    var erroSenhas     = document.getElementById("erro-senhas");
    var strengthWrap   = document.getElementById("senha-strength-wrap");
    var strengthBar    = document.getElementById("senha-strength-bar");
    var chkLen         = document.getElementById("chk-len");
    var chkUpper       = document.getElementById("chk-upper");
    var chkSpecial     = document.getElementById("chk-special");

    function marcarRequisito(el, passed) {
        if (!el) return;
        var icon = el.querySelector("i");
        if (passed) {
            el.classList.add("ok");
            if (icon) icon.className = "fa-solid fa-circle-check";
        } else {
            el.classList.remove("ok");
            if (icon) icon.className = "fa-solid fa-circle-xmark";
        }
    }

    function avaliarSenha(senha) {
        var len     = senha.length >= 8;
        var upper   = /[A-Z]/.test(senha);
        var special = /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>\/?`~]/.test(senha);

        marcarRequisito(chkLen, len);
        marcarRequisito(chkUpper, upper);
        marcarRequisito(chkSpecial, special);

        var pts = 0;
        if (len) pts++;
        if (upper) pts++;
        if (special) pts++;
        if (senha.length >= 12) pts++;

        if (strengthBar) strengthBar.className = "strength-" + pts;
        return (len && upper && special);
    }

    function validarSenhasIguais() {
        if (!inputSenha || !inputConfirmar || !erroSenhas) return true;
        if (inputSenha.value === inputConfirmar.value) {
            erroSenhas.textContent = "";
            return true;
        } else {
            erroSenhas.textContent = "As senhas nao coincidem.";
            return false;
        }
    }

    if (inputSenha && strengthWrap) {
        inputSenha.addEventListener("input", function() {
            var val = inputSenha.value;
            if (val.length === 0) strengthWrap.classList.add("hidden");
            else strengthWrap.classList.remove("hidden");
            avaliarSenha(val);
            if (inputConfirmar && inputConfirmar.value.length > 0) validarSenhasIguais();
        });
    }

    if (inputConfirmar) {
        inputConfirmar.addEventListener("input", validarSenhasIguais);
    }

    if (formAdmin) {
        formAdmin.addEventListener("submit", function(e) {
            var isForte = avaliarSenha(inputSenha ? inputSenha.value : "");
            if (!isForte) {
                e.preventDefault();
                if (strengthWrap) strengthWrap.classList.remove("hidden");
                if (inputSenha) inputSenha.focus();
                return;
            }
            if (!validarSenhasIguais()) {
                e.preventDefault();
                if (inputConfirmar) inputConfirmar.focus();
                return;
            }
        });
    }
});