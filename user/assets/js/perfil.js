// ============================================================
// perfil.js — Controla o modal de edição de perfil e validação
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    var modalEdit = document.getElementById('modal-edit-profile');
    var btnEditInfo = document.querySelector('.btn-edit-info');
    var btnCloseEdit = document.querySelectorAll('.btn-close-edit');
    var formEdit = document.getElementById('form-edit-profile');
    var newPass = document.getElementById('new_pass');
    var confNewPass = document.getElementById('conf_new_pass');
    var erroSenhas = document.getElementById('erro-senhas-edit');

    // Abre o modal
    if (btnEditInfo && modalEdit) {
        btnEditInfo.addEventListener('click', function() {
            modalEdit.classList.add('show');
            // Limpa mensagens e campos de senha ao abrir
            if (newPass) newPass.value = '';
            if (confNewPass) confNewPass.value = '';
            if (erroSenhas) erroSenhas.innerText = '';
        });
    }

    // Fecha o modal
    if (btnCloseEdit.length > 0 && modalEdit) {
        for (var i = 0; i < btnCloseEdit.length; i++) {
            btnCloseEdit[i].addEventListener('click', function() {
                modalEdit.classList.remove('show');
            });
        }
    }

    // Validação de senhas antes do submit
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            var senhaVal = newPass.value;
            var confVal = confNewPass.value;

            if (senhaVal !== '') {
                if (senhaVal.length < 8) {
                    e.preventDefault();
                    erroSenhas.innerText = 'A nova senha precisa ter no mínimo 8 caracteres.';
                    return false;
                }
                
                if (senhaVal !== confVal) {
                    e.preventDefault();
                    erroSenhas.innerText = 'As senhas não coincidem!';
                    return false;
                }
            }
        });
    }
});
