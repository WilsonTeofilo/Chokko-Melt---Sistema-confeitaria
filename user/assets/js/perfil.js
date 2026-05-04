// ==========================================================
// PERFIL.JS - LÓGICA DO MODAL, CEP E FOTO
// ==========================================================

document.addEventListener('DOMContentLoaded', function() {
    var overlay = document.getElementById('modal-overlay');
    var modalBody = document.getElementById('modal-body');
    var modalTitle = document.getElementById('modal-title');
    var btnSalvar = document.getElementById('btn-salvar-modal');

    // --- FUNÇÃO PARA ABRIR O MODAL PRINCIPAL ---
    window.abrirModal = function(titulo, campos, acaoSalvar) {
        if (!overlay || !modalBody) {
            alert('Erro ao abrir modal. Verifique se o HTML do modal existe.');
            return;
        }
        
        modalTitle.innerText = titulo;
        modalBody.innerHTML = ''; 
        
        for (var i = 0; i < campos.length; i++) {
            var campo = campos[i];

            if (campo.type === 'select' && campo.options) {
                var label = document.createElement('label');
                label.className = 'modal-select-label';
                label.textContent = campo.label;
                label.htmlFor = campo.id;

                var sel = document.createElement('select');
                sel.id = campo.id;
                sel.name = campo.name || campo.id;
                sel.className = 'modal-input modal-select';

                for (var j = 0; j < campo.options.length; j++) {
                    var opt = campo.options[j];
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    if (opt.value === (campo.valor || '')) option.selected = true;
                    sel.appendChild(option);
                }

                modalBody.appendChild(label);
                modalBody.appendChild(sel);
                continue; 
            }

            var input = document.createElement('input');
            input.type = campo.type || 'text';
            input.placeholder = campo.label;
            input.value = campo.valor || '';
            input.id = campo.id;
            
            if (campo.name) input.name = campo.name;
            input.className = 'modal-input';

            if (campo.id.indexOf('cep') !== -1) {
                input.maxLength = 8;
                input.addEventListener('blur', function(e) {
                    buscarCEP(e.target.value);
                });
            }
            modalBody.appendChild(input);
        }

        overlay.style.display = 'flex';
        btnSalvar.onclick = function() { 
            acaoSalvar(); 
            fecharModal(); 
        };
    };

    // --- LÓGICA DE BUSCA DE CEP ---
    function buscarCEP(cep) {
        var valorCep = cep.replace(/\D/g, '');
        if (valorCep.length === 8) {
            fetch('https://viacep.com.br/ws/' + valorCep + '/json/')
                .then(function(response) { return response.json(); })
                .then(function(dados) {
                    if (!dados.erro) {
                        var inputRua = document.getElementById('add-rua') || document.getElementById('edit-rua');
                        var inputBairro = document.getElementById('add-bairro') || document.getElementById('edit-bairro');
                        if (inputRua) inputRua.value = dados.logradouro;
                        if (inputBairro) inputBairro.value = dados.bairro;
                    }
                })
                .catch(function(e) { 
                    alert('Não foi possível buscar o CEP agora. Tente novamente.'); 
                });
        }
    }

    // --- EDITAR PERFIL ---
    var btnEditProfile = document.querySelector('.btn-edit-info');
    if (btnEditProfile) {
        btnEditProfile.addEventListener('click', function() {
            var elNome = document.querySelector('.user-details h2');
            var elFoto = document.querySelector('.avatar');
            var rows = document.querySelectorAll('.contact-row');
            
            var emailValor = rows[0] ? rows[0].innerText.replace(/[✉\s]/g, '') : '';
            var telValor = rows[1] ? rows[1].innerText.replace(/[📞\s()-]/g, '') : '';
            
            if (rows[1] && typeof window.ChokkoFormatTelBR === 'function') {
                telValor = window.ChokkoFormatTelBR(window.ChokkoSomenteDigitos(rows[1].innerText));
            }

            var campos = [
                { id: 'p-nome', name: 'nome', label: 'Nome Completo', valor: elNome.innerText },
                { id: 'p-email', name: 'email', label: 'E-mail', valor: emailValor },
                { id: 'p-tel', name: 'telefone', label: 'Telefone', valor: telValor },
                { id: 'p-foto-upload', name: 'foto', label: 'Trocar foto (Escolha um arquivo)', type: 'file' }
            ];

            window.abrirModal("Editar Perfil", campos, function() {
                var nNome = document.getElementById('p-nome').value;
                var nEmail = document.getElementById('p-email').value;
                var nTel = document.getElementById('p-tel').value;
                var inputFoto = document.getElementById('p-foto-upload');

                if (nNome) elNome.innerText = nNome;
                if (rows[0] && nEmail) rows[0].innerHTML = '<span>✉</span> ' + nEmail;
                if (rows[1] && nTel) {
                    var telDig = nTel.replace(/\D/g, '');
                    if (typeof window.ChokkoSomenteDigitos === 'function') {
                        telDig = window.ChokkoSomenteDigitos(nTel);
                    }
                    var telMostra = nTel;
                    if (typeof window.ChokkoFormatTelBR === 'function') {
                        telMostra = window.ChokkoFormatTelBR(telDig);
                    }
                    rows[1].innerHTML = '<span>📞</span> ' + telMostra;
                }

                if (inputFoto.files && inputFoto.files[0]) {
                    var leitor = new FileReader();
                    leitor.onload = function(e) { elFoto.src = e.target.result; };
                    leitor.readAsDataURL(inputFoto.files[0]);
                }
            });
        });
    }

    // --- ADICIONAR ENDEREÇO ---
    var btnAddAddress = document.querySelector('.btn-add-address');
    if (btnAddAddress) {
        btnAddAddress.addEventListener('click', function() {
            var campos = [
                {
                    id: 'add-titulo', name: 'apelido',
                    label: 'Apelido do endereço',
                    type: 'select',
                    valor: 'casa',
                    options: [
                        { value: 'casa',      label: '🏠 Casa' },
                        { value: 'trabalho',  label: '💼 Trabalho' },
                        { value: 'outro',     label: '📍 Outro' }
                    ]
                },
                { id: 'add-cep',    name: 'cep',    label: 'CEP',    valor: '' },
                { id: 'add-rua',    name: 'rua',    label: 'Rua',    valor: '' },
                { id: 'add-numero', name: 'numero', label: 'Número', valor: '' },
                { id: 'add-bairro', name: 'bairro', label: 'Bairro', valor: '' }
            ];

            window.abrirModal("Novo Endereço", campos, function() {
                var sel = document.getElementById('add-titulo');
                var t = sel ? sel.options[sel.selectedIndex].label : '';
                var tVal = sel ? sel.value : '';
                var c = document.getElementById('add-cep').value;
                var r = document.getElementById('add-rua').value;
                var n = document.getElementById('add-numero').value;
                var b = document.getElementById('add-bairro').value;

                if (t && r) {
                    var container = document.querySelector('.addresses-section');
                    var div = document.createElement('div');
                    div.className = 'address-card';
                    div.dataset.apelido = tVal;
                    div.innerHTML = 
                        '<div style="display:flex; justify-content:space-between;">' +
                            '<strong class="titulo-text">' + t + '</strong>' +
                        '</div>' +
                        '<div style="margin-top:10px; font-size:14px; color:#555;">' +
                            '<p><strong>Rua:</strong> <span class="rua-text">' + r + '</span>, <span class="num-text">' + n + '</span></p>' +
                            '<p><strong>Bairro:</strong> <span class="bairro-text">' + b + '</span></p>' +
                            '<p><strong>CEP:</strong> <span class="cep-text">' + c + '</span></p>' +
                        '</div>' +
                        '<div class="address-footer" style="margin-top:10px; display:flex; gap:10px;">' +
                            '<button class="btn-outline" onclick="editarEndereco(this)">Editar</button>' +
                            '<button class="btn-outline-danger" onclick="excluirEndereco(this)">Excluir</button>' +
                        '</div>';
                    container.appendChild(div);
                }
            });
        });
    }

    // --- LOGOUT ---
    var btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function() {
            localStorage.removeItem('chokko_usuario_id');
            window.location.href = 'index.php';
        });
    }

    // Fechar modal no botão X ou cancelar
    var btnCancel = document.querySelector('.btn-cancel');
    if (btnCancel) {
        btnCancel.addEventListener('click', fecharModal);
    }
});

function fecharModal() {
    var overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.style.display = 'none';
}

window.editarEndereco = function(botao) {
    var card = botao.closest('.address-card');
    var apelidoAtual = card.dataset.apelido || 'casa';

    var campos = [
        {
            id: 'edit-titulo', name: 'apelido',
            label: 'Apelido do endereço',
            type: 'select',
            valor: apelidoAtual,
            options: [
                { value: 'casa',     label: '🏠 Casa' },
                { value: 'trabalho', label: '💼 Trabalho' },
                { value: 'outro',    label: '📍 Outro' }
            ]
        },
        { id: 'edit-cep',    name: 'cep',    label: 'CEP',    valor: card.querySelector('.cep-text').innerText },
        { id: 'edit-rua',    name: 'rua',    label: 'Rua',    valor: card.querySelector('.rua-text').innerText },
        { id: 'edit-num',    name: 'numero', label: 'Número', valor: card.querySelector('.num-text').innerText },
        { id: 'edit-bairro', name: 'bairro', label: 'Bairro', valor: card.querySelector('.bairro-text').innerText }
    ];

    window.abrirModal("Editar Endereço", campos, function() {
        var sel = document.getElementById('edit-titulo');
        if (sel) {
            card.dataset.apelido = sel.value;
            card.querySelector('.titulo-text').textContent = sel.options[sel.selectedIndex].label;
        }
        card.querySelector('.cep-text').innerText    = document.getElementById('edit-cep').value;
        card.querySelector('.rua-text').innerText    = document.getElementById('edit-rua').value;
        card.querySelector('.num-text').innerText    = document.getElementById('edit-num').value;
        card.querySelector('.bairro-text').innerText = document.getElementById('edit-bairro').value;
    });
}

// --- MODAL BONITO DE EXCLUSÃO ---
window.excluirEndereco = function(botao) {
    var overlayId = 'user-confirm-modal';
    var overlay = document.getElementById(overlayId);
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = overlayId;
        overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
        overlay.innerHTML = 
            '<div style="width:420px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">' +
                '<div style="display:flex;gap:12px;align-items:flex-start;">' +
                    '<div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">' +
                        '<i class="fa-solid fa-triangle-exclamation" style="color:#3b2313;font-size:1.2rem;"></i>' +
                    '</div>' +
                    '<div style="flex:1;">' +
                        '<h3 style="margin:0;color:#3b2313;font-size:1.05rem;">Excluir endereço</h3>' +
                        '<p id="user-confirm-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>' +
                    '</div>' +
                '</div>' +
                '<div style="display:flex;gap:10px;margin-top:18px;">' +
                    '<button type="button" id="user-confirm-cancel" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>' +
                    '<button type="button" id="user-confirm-ok" style="flex:1;background:#3b2313;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Excluir</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    overlay.querySelector('#user-confirm-msg').textContent = 'Tem certeza que deseja excluir este endereço?';
    overlay.style.display = 'flex';

    var okBtn = overlay.querySelector('#user-confirm-ok');
    var cancelBtn = overlay.querySelector('#user-confirm-cancel');

    function close() { overlay.style.display = 'none'; }
    
    function cleanup() {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
    }

    overlay.onclick = function(e) { if (e.target === overlay) { cleanup(); close(); } };
    cancelBtn.onclick = function() { cleanup(); close(); };
    okBtn.onclick = function() {
        cleanup(); 
        close();
        botao.closest('.address-card').remove();
    };
}
