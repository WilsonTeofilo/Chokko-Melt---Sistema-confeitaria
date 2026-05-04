// Espera a tela carregar inteira antes de rodar os scripts
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // 1. Botão voltar ao login

    var btnBackToLogin = document.getElementById('btn-back-login');
    if (btnBackToLogin) {
        btnBackToLogin.addEventListener('click', function() {
            window.location.href = 'login.php';
        });
    }

    // 2. Pegar os elementos da tela que vamos usar

    var btnCriarConta  = document.getElementById('btn-criar-conta');
    var form           = document.querySelector('form'); // Pega o primeiro form da tela
    var inputTelefone  = document.getElementById('telefoneJS');
    var inputSenha     = document.getElementById('senhaJS');
    var inputConfirmar = document.getElementById('confirmar_senhaJS');
    var erroSenhas     = document.getElementById('erro-senhas');
    
    // Elementos do medidor de força
    var strengthWrap   = document.getElementById('senha-strength-wrap');
    var strengthBar    = document.getElementById('senha-strength-bar');
    var chkLen         = document.getElementById('chk-len');
    var chkUpper       = document.getElementById('chk-upper');
    var chkSpecial     = document.getElementById('chk-special');

    // Esconde o medidor de força logo no início
    if (strengthWrap) {
        strengthWrap.classList.add('hidden');
    }

    // ============================================================
    // 3. Função para mudar a cor do check (X vermelho ou V verde)
    // ============================================================
    function marcarRequisito(elementoHTML, passouNoTeste) {
        if (!elementoHTML) {
            return;
        }

        var icone = elementoHTML.querySelector('i');
        
        if (passouNoTeste === true) {
            elementoHTML.classList.add('ok');
            if (icone) {
                icone.className = 'fa-solid fa-circle-check'; // Fica verde
            }
        } else {
            elementoHTML.classList.remove('ok');
            if (icone) {
                icone.className = 'fa-solid fa-circle-xmark'; // Fica vermelho
            }
        }
    }

    // ============================================================
    // 4. Função principal: verifica a força da senha
    // ============================================================
    function avaliarSenha(senhaDigitada) {
        // Teste 1: Tem 8 caracteres ou mais?
        var temTamanho = false;
        if (senhaDigitada.length >= 8) {
            temTamanho = true;
        }

        // Teste 2: Tem letra maiúscula? (Procura de A até Z)
        var temMaiuscula = false;
        if (/[A-Z]/.test(senhaDigitada)) {
            temMaiuscula = true;
        }

        // Teste 3: Tem caractere especial? (Procura símbolos)
        var temEspecial = false;
        if (/[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>/?`~]/.test(senhaDigitada)) {
            temEspecial = true;
        }

        // Atualiza a tela com os resultados dos testes
        marcarRequisito(chkLen, temTamanho);
        marcarRequisito(chkUpper, temMaiuscula);
        marcarRequisito(chkSpecial, temEspecial);

        // Calcula a pontuação para a barra colorida
        var pontos = 0;
        if (temTamanho) pontos = pontos + 1;
        if (temMaiuscula) pontos = pontos + 1;
        if (temEspecial) pontos = pontos + 1;
        
        // Se a senha for bem grande (12 ou mais), ganha um ponto extra de força
        if (senhaDigitada.length >= 12) {
            pontos = pontos + 1;
        }

        // Muda a classe da barra para mudar a cor no CSS (strength-1, strength-2...)
        if (strengthBar) {
            strengthBar.className = 'strength-' + pontos;
        }

        // Retorna verdadeiro se passou nos 3 testes obrigatórios
        if (temTamanho && temMaiuscula && temEspecial) {
            return true;
        } else {
            return false;
        }
    }

    // ============================================================
    // 5. O que acontece quando o usuário DIGITA a senha
    // ============================================================
    if (inputSenha && strengthWrap) {
        inputSenha.addEventListener('input', function() {
            var valorDigitado = inputSenha.value;

            // Se o campo estiver vazio, esconde a caixa do medidor
            if (valorDigitado.length === 0) {
                strengthWrap.classList.add('hidden');
            } else {
                strengthWrap.classList.remove('hidden');
            }

            // Manda avaliar a senha
            avaliarSenha(valorDigitado);

            // Se o cara já digitou algo no confirmar senha, re-avalia lá também
            if (inputConfirmar && inputConfirmar.value.length > 0) {
                validarSenhasIguais();
            }
        });
    }

    // ============================================================
    // 6. Função para ver se as duas senhas são iguais
    // ============================================================
    function validarSenhasIguais() {
        if (!inputSenha || !inputConfirmar || !erroSenhas) {
            return true;
        }

        var senha1 = inputSenha.value;
        var senha2 = inputConfirmar.value;

        if (senha1 === senha2) {
            // Se forem iguais, apaga o erro da tela
            erroSenhas.textContent = '';
            return true;
        } else {
            // Se forem diferentes, escreve a mensagem de erro
            erroSenhas.textContent = '⚠️ As senhas não coincidem.';
            return false;
        }
    }

    // O que acontece quando digita no campo de confirmar senha
    if (inputConfirmar) {
        inputConfirmar.addEventListener('input', function() {
            validarSenhasIguais();
        });
    }

    // ============================================================
    // 7. O que acontece ao clicar em "Criar Conta"
    // ============================================================
    if (btnCriarConta && form) {
        btnCriarConta.addEventListener('click', function(event) {

            // Como agora o botão é um <input type="submit">, 
            // precisamos impedir que ele envie a página sozinho antes da validação
            event.preventDefault();

            // Passo 1: A senha é forte o suficiente?
            var senhaDigitada = '';
            if (inputSenha) {
                senhaDigitada = inputSenha.value;
            }

            var senhaForte = avaliarSenha(senhaDigitada);
            if (senhaForte === false) {
                // Se a senha for fraca, mostra o medidor pro usuário ver o que falta
                if (strengthWrap) {
                    strengthWrap.classList.remove('hidden');
                }
                inputSenha.focus(); // Coloca o cursor piscando na senha
                return; // Para a execução, não envia o form
            }

            // Passo 2: As duas senhas são iguais?
            var saoIguais = validarSenhasIguais();
            if (saoIguais === false) {
                inputConfirmar.focus(); // Coloca cursor no confirmar
                return; // Para a execução
            }

            // Passo 3: Limpar telefone (Tirar parênteses e traços)
            if (inputTelefone) {
                // Checa se o arquivo do telefone foi carregado e a função existe
                if (typeof ChokkoSomenteDigitos === 'function') {
                    // Substitui "(11) 98888-7777" por "11988887777"
                    inputTelefone.value = ChokkoSomenteDigitos(inputTelefone.value);
                }
            }

            // Passo 4: Se chegou até aqui sem dar return, está tudo OK! Pode enviar
            form.submit(); 
        });
    }

});
