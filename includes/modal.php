<?php
/**
 * exibirModalEVoltar()
 * 
 * Exibe um modal de feedback (sucesso ou erro) e encerra a execução.
 * Usado em: auth.php, Endereco.php e qualquer backend futuro.
 *
 * @param string $titulo    Título do modal
 * @param string $mensagem  Texto explicativo
 * @param string $url       URL do botão de ação
 * @param string $labelBtn  Texto do botão (padrão: "OK, Entendi")
 * @param string $icone     Classe Font Awesome do ícone
 * @param string $corIcone  Cor hexadecimal do ícone
 */
function exibirModalEVoltar($titulo, $mensagem, $url, $labelBtn = 'OK, Entendi', $icone = 'fa-circle-exclamation', $corIcone = '#e74c3c') {
    echo '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <title>Aviso</title>
    </head>
    <body style="margin:0;padding:0;font-family:Inter,sans-serif;background:#fdfaf6;">
        <div style="z-index:99999;display:flex;position:fixed;top:0;left:0;width:100%;height:100%;
                    background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);
                    justify-content:center;align-items:center;padding:20px;">
            <div style="background:white;width:380px;max-width:100%;border-radius:14px;
                        padding:32px 28px;text-align:center;box-shadow:0 12px 35px rgba(0,0,0,0.2);">
                <i class="fa-solid '.$icone.'" style="font-size:3.5rem;color:'.$corIcone.';margin-bottom:15px;"></i>
                <h2 style="color:#3b2313;margin:0 0 10px;font-size:1.4rem;">'.$titulo.'</h2>
                <p style="color:#555;margin-bottom:26px;line-height:1.5;font-size:0.95rem;">'.$mensagem.'</p>
                <button onclick="window.location.href=\''.$url.'\'"
                        style="background:linear-gradient(135deg,#9b3f21,#3b2313);color:white;border:none;
                               padding:13px 25px;border-radius:8px;font-weight:700;cursor:pointer;
                               width:100%;font-size:1rem;transition:0.3s;">
                    '.$labelBtn.'
                </button>
            </div>
        </div>
    </body>
    </html>
    ';
    exit;
}

/**
 * exibirModalOverlay()
 * 
 * Exibe um modal em formato de overlay HTML (sem tags body/html e sem dar exit).
 * Usado para exibir mensagens no meio da renderização normal da página, com opção de fechar.
 */
function exibirModalOverlay($idModal, $titulo, $mensagem, $url, $labelBtn, $icone = 'fa-circle-exclamation', $corIcone = '#3b2313') {
    echo '
    <div id="'.$idModal.'" style="z-index:99999;display:flex;position:fixed;top:0;left:0;width:100%;height:100%;
                background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);
                justify-content:center;align-items:center;padding:20px;">
        <div style="background:white;width:380px;max-width:100%;border-radius:14px;position:relative;
                    padding:32px 28px;text-align:center;box-shadow:0 12px 35px rgba(0,0,0,0.2);">
            <button onclick="document.getElementById(\''.$idModal.'\').style.display=\'none\'" 
                    style="position:absolute;top:12px;right:16px;background:none;border:none;
                           font-size:1.6rem;cursor:pointer;color:#888;padding:0;">&times;</button>
                           
            <i class="fa-solid '.$icone.'" style="font-size:3.5rem;color:'.$corIcone.';margin-bottom:15px;"></i>
            <h2 style="color:#3b2313;margin:0 0 10px;font-size:1.4rem;">'.$titulo.'</h2>
            <p style="color:#555;margin-bottom:26px;line-height:1.5;font-size:0.95rem;">'.$mensagem.'</p>
            
            <a href="'.$url.'"
               style="display:block;background:linear-gradient(135deg,#9b3f21,#3b2313);color:white;text-decoration:none;
                      padding:13px 25px;border-radius:8px;font-weight:700;transition:0.3s;
                      width:100%;box-sizing:border-box;">
                '.$labelBtn.'
            </a>
        </div>
    </div>
    ';
}
?>
