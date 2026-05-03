<?php include '../includes/admin_header.php'; ?>
<link rel="stylesheet" href="assets/css/config.css">

<section class="welcome-area">
    <h1>Configurações da Loja</h1>
    <p>Ajuste os parâmetros de funcionamento do sistema.</p>
</section>

<section class="table-wrapper config-wrapper">
    <form onsubmit="salvarConfig(event)">
        <div class="config-section">
            <h3 class="config-title">Informações Básicas</h3>
            
            <div class="form-group">
                <label class="form-label">Nome da Loja</label>
                <input type="text" id="config-nome-loja" value="Chokko Melt" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp para Contato</label>
                <input type="text" id="config-whatsapp" value="(11) 99999-9999" class="form-control" maxlength="16"
                    oninput="typeof ChokkoMascaraTelefoneInput==='function'&&ChokkoMascaraTelefoneInput(this)">
            </div>
        </div>

        <div class="config-section">
            <h3 class="config-title">Operação e Delivery</h3>
            
            <div class="form-group flex-gap-15">
                <div class="flex-1">
                    <label class="form-label">Horário de Abertura</label>
                    <input type="time" id="config-hora-abre" value="15:00" class="form-control">
                </div>
                <div class="flex-1">
                    <label class="form-label">Horário de Fechamento</label>
                    <input type="time" id="config-hora-fecha" value="05:00" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Taxa de Entrega Padrão (R$)</label>
                <input type="text" id="config-taxa-entrega" value="5,00" class="form-control config-short-input">
            </div>
        </div>

        <div class="config-footer">
            <button type="submit" class="btn-action-accept config-save-btn">
                <i class="fa-solid fa-save"></i> Salvar Configurações
            </button>
        </div>
    </form>
</section>

<script src="../user/assets/js/chokko_digits.js"></script>
<!-- config.js: valida formulário no cliente; PHP deve processar POST e gravar em config_loja. INTEGRACAO_JS_PHP.txt -->
<script src="assets/js/config.js"></script>

<?php include '../includes/admin_footer.php'; ?>
