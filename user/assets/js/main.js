/**
 * ═══════════════════════════════════════════════════════════════════════════
 * INTEGRAÇÃO JavaScript ↔ PHP (para quem está começando)
 * ═══════════════════════════════════════════════════════════════════════════
 * Leia também na RAIZ do projeto: INTEGRACAO_JS_PHP.txt
 *
 * O que ESTE arquivo faz:
 *   Roda em TODAS as páginas que incluem user_footer.php (que carrega main.js).
 *   • Atualiza o número da sacola lendo localStorage 'chokko_cart' (carrinho fake
 *     no navegador até você gravar pedido no MySQL via PHP).
 *   • Horário aberto/fechado: hoje lê 'chokko_hora_abre' / 'chokko_hora_fecha'
 *     do localStorage (admin/config.js grava isso no mock). No sistema real, o
 *     PHP deve expor GET user/api/config_loja.php (lê tabela config_loja) e o
 *     JS deve dar fetch() e preencher o mesmo comportamento.
 *   • Em finalizarPedido.php: bloco de troco (dinheiro) + máscara/validação CPF.
 *     O PHP ao receber o POST deve validar CPF e troco de novo (nunca confie só no JS).
 *
 * Resumo: JS = experiência no navegador. PHP = verdade no servidor (sessão, banco).
 * ═══════════════════════════════════════════════════════════════════════════
 */

/* ========================================
   MAIN.JS — Lógica global (todas as páginas)
   Badge do carrinho na bottom-nav
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {
    // ── Badge do carrinho ──────────────────
    // Lê o localStorage e atualiza o badge da sacola em qualquer página
    try {
        const cart  = JSON.parse(localStorage.getItem('chokko_cart') || '[]');
        const total = cart.reduce((s, i) => s + (parseInt(i.qty) || 0), 0);
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.textContent = total > 0 ? total : '';
            badge.classList.toggle('has-items', total > 0);
        }
    } catch (e) { /* silencioso */ }

    // ── Validação de Horário de Funcionamento ──
    window.isLojaAberta = true; // global
    function checarHorario() {
        const horaAbre = localStorage.getItem('chokko_hora_abre') || '15:00';
        const horaFecha = localStorage.getItem('chokko_hora_fecha') || '22:00';
        
        // Atualiza textos na UI se existirem
        const hoursText = document.getElementById('store-hours-text');
        if (hoursText) {
            hoursText.innerText = `${horaAbre} às ${horaFecha}`;
        }

        const agora = new Date();
        const horaAtual = agora.getHours();
        const minAtual = agora.getMinutes();
        const atualDecimal = horaAtual + (minAtual / 60);

        const [hAbre, mAbre] = horaAbre.split(':').map(Number);
        const abreDecimal = hAbre + (mAbre / 60);

        const [hFecha, mFecha] = horaFecha.split(':').map(Number);
        let fechaDecimal = hFecha + (mFecha / 60);
        
        // Trata fechamento de madrugada
        if (fechaDecimal < abreDecimal) {
            fechaDecimal += 24;
        }
        
        let atualCalculado = atualDecimal;
        if (atualCalculado < abreDecimal && fechaDecimal > 24) {
            atualCalculado += 24;
        }

        window.isLojaAberta = (atualCalculado >= abreDecimal && atualCalculado <= fechaDecimal);
        
        const badge = document.getElementById('store-status-badge');
        if (badge) {
            if (window.isLojaAberta) {
                badge.innerText = 'Loja Aberta';
                badge.style.background = '#43A047';
                badge.style.color = 'white';
            } else {
                badge.innerText = 'Fechado';
                badge.style.background = '#E53935';
                badge.style.color = 'white';
            }
        }
    }
    checarHorario();
    setInterval(checarHorario, 60000); // Checa a cada minuto

    // ── Finalizar pedido: CPF + Troco (front-only) ─────────────
    function onlyDigits(s) { return String(s || '').replace(/\D/g, ''); }

    function validarCPF(cpf) {
        const v = onlyDigits(cpf);
        if (v.length === 0) return true; // opcional
        if (v.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(v)) return false;

        const calcDV = (base, fator) => {
            let soma = 0;
            for (let i = 0; i < base.length; i++) soma += parseInt(base[i], 10) * (fator - i);
            const resto = (soma * 10) % 11;
            return (resto === 10) ? 0 : resto;
        };

        const base9 = v.slice(0, 9);
        const dv1 = calcDV(base9, 10);
        const base10 = v.slice(0, 10);
        const dv2 = calcDV(base10, 11);
        return v === (base9 + String(dv1) + String(dv2));
    }

    function formatCPF(v) {
        const d = onlyDigits(v).slice(0, 11);
        if (d.length <= 3) return d;
        if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
        if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
        return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
    }

    function parseBRL(text) {
        if (!text) return 0;
        const s = String(text).replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
        const n = parseFloat(s);
        return Number.isFinite(n) ? n : 0;
    }

    function ensureTrocoUI() {
        const dinheiroRadio = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]');
        if (!dinheiroRadio) return;

        const containerId = 'dinheiro-troco-wrap';
        let wrap = document.getElementById(containerId);
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = containerId;
            wrap.style.marginTop = '12px';
            wrap.style.display = 'none';
            wrap.innerHTML = `
                <div style="padding: 12px; border: 1px solid var(--cinza-claro); border-radius: 10px; background: #FAF7F5;">
                    <label style="display:block; font-size:.85rem; font-weight:600; color:var(--cinza-texto); margin-bottom:8px;">
                        Pagará com quanto? (opcional)
                    </label>
                    <input id="valor-pago-dinheiro" inputmode="decimal" class="form-control" placeholder="Ex: 50,00" style="margin-bottom: 10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                        <span style="font-size:.8rem; color:var(--cinza-medio);">Troco estimado</span>
                        <strong id="troco-valor" style="color:var(--marrom);">—</strong>
                    </div>
                    <p id="troco-ajuda" style="margin-top:8px; font-size:.75rem; color:var(--cinza-medio); line-height:1.3;">
                        Se deixar em branco, vamos assumir que não precisa de troco.
                    </p>
                </div>
            `;

            // Anexa no card de pagamento (mesmo container dos radios)
            const cardPagamento = dinheiroRadio.closest('.card');
            if (cardPagamento) cardPagamento.appendChild(wrap);

            const input = wrap.querySelector('#valor-pago-dinheiro');
            const trocoEl = wrap.querySelector('#troco-valor');
            const ajudaEl = wrap.querySelector('#troco-ajuda');

            const recalc = () => {
                const totalEl = document.getElementById('fin-total');
                const total = parseBRL(totalEl ? totalEl.textContent : '');
                const pago = parseBRL(input.value);

                if (!input.value.trim()) {
                    trocoEl.textContent = '—';
                    ajudaEl.textContent = 'Se deixar em branco, vamos assumir que não precisa de troco.';
                    return;
                }

                if (!Number.isFinite(pago) || pago <= 0) {
                    trocoEl.textContent = '—';
                    ajudaEl.textContent = 'Digite um valor válido (ex: 50,00).';
                    return;
                }

                const troco = pago - total;
                if (troco < 0) {
                    trocoEl.textContent = '—';
                    ajudaEl.textContent = 'O valor informado é menor que o total do pedido.';
                    return;
                }

                trocoEl.textContent = 'R$ ' + troco.toFixed(2).replace('.', ',');
                ajudaEl.textContent = 'Troco calculado automaticamente com base no total atual.';
            };

            input.addEventListener('input', recalc);
            // Recalcula também se o total for atualizado por algum script
            setTimeout(recalc, 0);
            setTimeout(recalc, 300);
        }

        const onChange = () => {
            const checked = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]')?.checked;
            wrap.style.display = checked ? 'block' : 'none';
        };

        document.querySelectorAll('input[type="radio"][name="pagamento"]').forEach(r => {
            r.addEventListener('change', onChange);
        });
        onChange();

        // Bloqueia o clique do "Fazer Pedido" se dinheiro insuficiente
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            if (!btn.classList.contains('btn-success')) return;
            if (!btn.textContent || !btn.textContent.toLowerCase().includes('fazer pedido')) return;

            const isDinheiro = document.querySelector('input[type="radio"][name="pagamento"][value="dinheiro"]')?.checked;
            const trocoInput = document.getElementById('valor-pago-dinheiro');
            
            if (isDinheiro && trocoInput && trocoInput.value.trim() !== '') {
                const totalEl = document.getElementById('fin-total');
                const total = parseBRL(totalEl ? totalEl.textContent : '');
                const pago = parseBRL(trocoInput.value);

                if (!Number.isFinite(pago) || pago <= 0 || pago < total) {
                    trocoInput.style.borderColor = '#E53935';
                    trocoInput.style.boxShadow = '0 0 0 3px rgba(229,57,53,0.12)';
                    e.preventDefault();
                    e.stopPropagation(); // Impede o clique de chegar no inline onclick
                    e.stopImmediatePropagation();
                    alert('O valor em dinheiro informado é menor que o total do pedido. Verifique o valor para o troco.');
                } else {
                    trocoInput.style.borderColor = '';
                    trocoInput.style.boxShadow = '';
                }
            }
        }, true);
    }

    function ensureCPFValidation() {
        // A página atual não tem id/name no input, então detectamos por placeholder
        const cpfInput = document.querySelector('input.form-control[placeholder="000.000.000-00"]');
        if (!cpfInput) return;

        cpfInput.addEventListener('input', () => {
            const prevPos = cpfInput.selectionStart || 0;
            const before = cpfInput.value;
            cpfInput.value = formatCPF(before);
            // tentativa simples de manter o cursor estável
            const delta = cpfInput.value.length - before.length;
            cpfInput.setSelectionRange(Math.max(prevPos + delta, 0), Math.max(prevPos + delta, 0));
        });

        const setInvalid = (isInvalid) => {
            cpfInput.style.borderColor = isInvalid ? '#E53935' : '';
            cpfInput.style.boxShadow = isInvalid ? '0 0 0 3px rgba(229,57,53,0.12)' : '';
        };

        cpfInput.addEventListener('blur', () => {
            const ok = validarCPF(cpfInput.value);
            setInvalid(!ok);
            if (!ok) alert('CPF inválido. Verifique e tente novamente.');
        });

        // Bloqueia o clique do "Fazer Pedido" se CPF inválido
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            if (!btn.classList.contains('btn-success')) return;
            if (!btn.textContent || !btn.textContent.toLowerCase().includes('fazer pedido')) return;

            const ok = validarCPF(cpfInput.value);
            if (!ok) {
                setInvalid(true);
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                alert('CPF inválido. Corrija antes de finalizar o pedido.');
            }
        }, true);
    }

    ensureTrocoUI();
    ensureCPFValidation();

    // ── Navegação do botão Fazer Pedido (só roda se as validações acima deixarem passar) ──
    const btnFazerPedido = document.getElementById('btn-fazer-pedido');
    if (btnFazerPedido) {
        btnFazerPedido.addEventListener('click', () => {
            window.location.href = 'detalhes_pedido.php';
        });
    }
});
