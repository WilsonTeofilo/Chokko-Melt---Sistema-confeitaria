/* ==========================================================
   PERFIL.JS - LÓGICA DO MODAL, CEP E FOTO
   ==========================================================
   INTEGRAÇÃO PHP (iniciante) — veja INTEGRACAO_JS_PHP.txt na raiz do projeto.

   Carregado em: user/perfil.php (antes do footer em algumas versões; o footer
   ainda traz main.js).

   Fluxo hoje: tudo é “mentira bonita” no navegador — salvar perfil/endereço
   só altera o HTML até você criar endpoints PHP.

   O que implementar no PHP:
   1) Ao abrir perfil.php: SELECT cliente + endereços WHERE id = $_SESSION['cliente_id']
      e preencher o HTML (ou json_encode para o JS renderizar).
   2) Salvar edição: POST para user/api/atualizar_perfil.php com nome, email, etc.
   3) Novo endereço: POST user/api/criar_endereco.php (INSERT em endereco).
   4) ViaCEP pode continuar no JS só para UX; o PHP deve validar CEP/bairro se
      a regra de negócio for “só Grajaú”.

   Nota: O campo de CEP usa a API pública ViaCEP (só front). Não substitui
   validação no servidor.
   ========================================================== */

document.addEventListener('DOMContentLoaded', () => {
    // Seletores do Modal
    const overlay = document.getElementById('modal-overlay');
    const modalBody = document.getElementById('modal-body');
    const modalTitle = document.getElementById('modal-title');
    const btnSalvar = document.getElementById('btn-salvar-modal');

    // --- FUNÇÃO PARA ABRIR O MODAL (MOTOR DO SISTEMA) ---
    window.abrirModal = (titulo, campos, acaoSalvar) => {
        if (!overlay || !modalBody) {
            alert('Erro ao abrir modal. Verifique se o HTML do modal existe.');
            return;
        }
        
        modalTitle.innerText = titulo;
        modalBody.innerHTML = ''; 
        
        campos.forEach(campo => {
            // SELECT — quando o campo tiver options definido
            if (campo.type === 'select' && campo.options) {
                const label = document.createElement('label');
                label.className = 'modal-select-label';
                label.textContent = campo.label;
                label.htmlFor = campo.id;

                const sel = document.createElement('select');
                sel.id = campo.id;
                sel.name = campo.name || campo.id;
                sel.className = 'modal-input modal-select';

                campo.options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    if (opt.value === (campo.valor || '')) option.selected = true;
                    sel.appendChild(option);
                });

                modalBody.appendChild(label);
                modalBody.appendChild(sel);
                return; // pula o bloco de input abaixo
            }

            // INPUT padrão
            const input = document.createElement('input');
            input.type = campo.type || 'text';
            input.placeholder = campo.label;
            input.value = campo.valor || '';
            input.id = campo.id;
            // Name é útil para o backend capturar
            if (campo.name) input.name = campo.name;
            input.className = 'modal-input';

            // Lógica de busca de CEP automática
            if (campo.id.includes('cep')) {
                input.maxLength = 8;
                input.addEventListener('blur', () => buscarCEP(input.value));
            }
            modalBody.appendChild(input);
        });

        overlay.style.display = 'flex';
        btnSalvar.onclick = () => { 
            // NOTA BACKEND: Aqui você faria o submit AJAX dos dados para o PHP
            acaoSalvar(); 
            fecharModal(); 
        };
    };

    // --- LÓGICA DE BUSCA DE CEP (API VIACEP) ---
    async function buscarCEP(cep) {
        const valorCep = cep.replace(/\D/g, '');
        if (valorCep.length === 8) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${valorCep}/json/`);
                const dados = await response.json();
                if (!dados.erro) {
                    const inputRua = document.getElementById('add-rua') || document.getElementById('edit-rua');
                    const inputBairro = document.getElementById('add-bairro') || document.getElementById('edit-bairro');
                    if (inputRua) inputRua.value = dados.logradouro;
                    if (inputBairro) inputBairro.value = dados.bairro;
                }
            } catch (e) { alert('Não foi possível buscar o CEP agora. Tente novamente.'); }
        }
    }

    // --- 1. EDITAR PERFIL (NOME, EMAIL, TEL, FOTO PC) ---
    const btnEditProfile = document.querySelector('.btn-edit-info');
    if (btnEditProfile) {
        btnEditProfile.addEventListener('click', () => {
            const elNome = document.querySelector('.user-details h2');
            const elFoto = document.querySelector('.avatar');
            const rows = document.querySelectorAll('.contact-row');

            const campos = [
                { id: 'p-nome', name: 'nome', label: 'Nome Completo', valor: elNome.innerText },
                { id: 'p-email', name: 'email', label: 'E-mail', valor: rows[0].innerText.replace(/[✉\s]/g, '') },
                {
                    id: 'p-tel',
                    name: 'telefone',
                    label: 'Telefone',
                    valor: typeof window.ChokkoFormatTelBR === 'function'
                        ? window.ChokkoFormatTelBR(window.ChokkoSomenteDigitos(rows[1].innerText))
                        : rows[1].innerText.replace(/[📞\s()-]/g, ''),
                },
                { id: 'p-foto-upload', name: 'foto', label: 'Trocar foto (Escolha um arquivo)', type: 'file' }
            ];

            abrirModal("Editar Perfil", campos, () => {
                const nNome = document.getElementById('p-nome').value;
                const nEmail = document.getElementById('p-email').value;
                const nTel = document.getElementById('p-tel').value;
                const inputFoto = document.getElementById('p-foto-upload');

                if (nNome) elNome.innerText = nNome;
                if (rows[0] && nEmail) rows[0].innerHTML = `<span>✉</span> ${nEmail}`;
                if (rows[1] && nTel) {
                    const telDig = typeof window.ChokkoSomenteDigitos === 'function'
                        ? window.ChokkoSomenteDigitos(nTel)
                        : String(nTel).replace(/\D/g, '');
                    const telMostra = typeof window.ChokkoFormatTelBR === 'function'
                        ? window.ChokkoFormatTelBR(telDig)
                        : nTel;
                    rows[1].innerHTML = `<span>📞</span> ${telMostra}`;
                    try {
                        localStorage.setItem('chokko_cliente_telefone_digits', telDig);
                    } catch (e) { /* ignore */ }
                }

                // Processa a foto do PC (Preview)
                if (inputFoto.files && inputFoto.files[0]) {
                    const leitor = new FileReader();
                    leitor.onload = (e) => elFoto.src = e.target.result;
                    leitor.readAsDataURL(inputFoto.files[0]);
                }
            });
        });
    }

    // --- 2. ADICIONAR ENDEREÇO ---
    const btnAddAddress = document.querySelector('.btn-add-address');
    if (btnAddAddress) {
        btnAddAddress.addEventListener('click', () => {
            const campos = [
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

            abrirModal("Novo Endereço", campos, () => {
                const sel = document.getElementById('add-titulo');
                // Pega o label da opção selecionada (ex: '🏠 Casa') para exibir no card
                const t = sel ? sel.options[sel.selectedIndex].label : '';
                // Pega o value limpo para enviar ao backend (ex: 'casa')
                const tVal = sel ? sel.value : '';
                const c = document.getElementById('add-cep').value;
                const r = document.getElementById('add-rua').value;
                const n = document.getElementById('add-numero').value;
                const b = document.getElementById('add-bairro').value;

                if (t && r) {
                    const container = document.querySelector('.addresses-section');
                    const div = document.createElement('div');
                    div.className = 'address-card';
                    // NOTA BACKEND: data-apelido armazena o value para envio ao PHP
                    div.dataset.apelido = tVal;
                    div.innerHTML = `
                        <div style="display:flex; justify-content:space-between;">
                            <strong class="titulo-text">${t}</strong>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#555;">
                            <p><strong>Rua:</strong> <span class="rua-text">${r}</span>, <span class="num-text">${n}</span></p>
                            <p><strong>Bairro:</strong> <span class="bairro-text">${b}</span></p>
                            <p><strong>CEP:</strong> <span class="cep-text">${c}</span></p>
                        </div>
                        <div class="address-footer" style="margin-top:10px; display:flex; gap:10px;">
                            <button class="btn-outline" onclick="editarEndereco(this)">Editar</button>
                            <button class="btn-outline-danger" onclick="excluirEndereco(this)">Excluir</button>
                        </div>`;
                    container.appendChild(div);
                }
        });
    }

    // --- 3. LOGOUT ---
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', () => {
            localStorage.removeItem('chokko_usuario_id');
            window.location.href = 'index.php';
        });
    }

    // Fechar modal no botão X ou cancelar
    const btnCancel = document.querySelector('.btn-cancel');
    if (btnCancel) {
        btnCancel.addEventListener('click', fecharModal);
    }
});

/* ==========================================================
   FUNÇÕES GLOBAIS (FORA DO DOMCONTENTLOADED)
   ========================================================== */

function fecharModal() {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.style.display = 'none';
}

window.editarEndereco = function(botao) {
    const card = botao.closest('.address-card');
    // Lê o apelido atual salvo em data-apelido (ex: 'casa', 'trabalho', 'outro')
    const apelidoAtual = card.dataset.apelido || 'casa';

    const campos = [
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

    abrirModal("Editar Endereço", campos, () => {
        const sel = document.getElementById('edit-titulo');
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

window.excluirEndereco = function(botao) {
    // Usa o modal global (alert override) e evita confirm nativo
    const overlayId = 'user-confirm-modal';
    let overlay = document.getElementById(overlayId);
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = overlayId;
        overlay.style.cssText = 'z-index:99999;display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;';
        overlay.innerHTML = `
            <div style="width:420px;max-width:100%;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.25);padding:22px;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:42px;height:42px;border-radius:10px;background:#FAF7F5;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#3b2313;font-size:1.2rem;"></i>
                    </div>
                    <div style="flex:1;">
                        <h3 style="margin:0;color:#3b2313;font-size:1.05rem;">Excluir endereço</h3>
                        <p id="user-confirm-msg" style="margin:8px 0 0;color:#555;line-height:1.35;font-size:.92rem;white-space:pre-line;"></p>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:18px;">
                    <button type="button" id="user-confirm-cancel" style="flex:1;background:#eee;color:#333;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Cancelar</button>
                    <button type="button" id="user-confirm-ok" style="flex:1;background:#3b2313;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-weight:700;cursor:pointer;">Excluir</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    overlay.querySelector('#user-confirm-msg').textContent = 'Tem certeza que deseja excluir este endereço?';
    overlay.style.display = 'flex';

    const okBtn = overlay.querySelector('#user-confirm-ok');
    const cancelBtn = overlay.querySelector('#user-confirm-cancel');

    const close = () => { overlay.style.display = 'none'; };
    const cleanup = () => {
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        overlay.onclick = null;
        document.removeEventListener('keydown', onKey);
    };
    const onKey = (e) => {
        if (e.key === 'Escape') { cleanup(); close(); }
    };
    document.addEventListener('keydown', onKey);
    overlay.onclick = (e) => { if (e.target === overlay) { cleanup(); close(); } };
    cancelBtn.onclick = () => { cleanup(); close(); };
    okBtn.onclick = () => {
        cleanup(); close();
        // NOTA BACKEND: Disparar requisição DELETE para o PHP aqui
        botao.closest('.address-card').remove();
    };
}
