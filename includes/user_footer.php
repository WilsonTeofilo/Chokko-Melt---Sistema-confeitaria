<?php
/**
 * user_footer.php — fecha o layout das páginas do CLIENTE.
 *
 * INTEGRAÇÃO JS ↔ PHP (iniciante):
 *   • Sempre carrega assets/js/main.js (badge, horário, CPF/troco em finalizar).
 *   • O alert() do navegador é trocado por um modal (bloco <script> abaixo).
 *   • Quem “manda” em login/sessão é o PHP (session_start + $_SESSION).
 *     O JS só lê cookies/localStorage até você criar api/check_session.php.
 *   • Guia geral na raiz: INTEGRACAO_JS_PHP.txt
 */
$current_page = basename($_SERVER['PHP_SELF']);

// Importa classes necessárias de forma segura usando caminhos absolutos
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Carrinho.php';

// Instancia a classe Carrinho e conta os itens
$carrinhoObj = new Carrinho(DATABASE, HOST, USER, PASS);
$totalItensCarrinho = $carrinhoObj->contarItens();
?>
    <footer class="bottom-nav">
        <a href="index.php" class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i><span>Início</span>
        </a>
        <a href="pedidos.php" class="nav-item <?= $current_page == 'pedidos.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-clipboard-list"></i><span>Pedidos</span>
        </a>
        <a href="carrinho.php" class="nav-item <?= $current_page == 'carrinho.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-bag-shopping"></i>
            <?php if ($totalItensCarrinho > 0): ?>
                <span class="badge-count has-items" id="cart-badge"><?= $totalItensCarrinho ?></span>
            <?php else: ?>
                <span class="badge-count" id="cart-badge"></span>
            <?php endif; ?>
            <span>Sacola</span>
        </a>
        <a href="perfil.php" class="nav-item <?= $current_page == 'perfil.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i><span>Perfil</span>
        </a>
    </footer>
    <script src="assets/js/chokko_digits.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- MODAL GLOBAL PARA SUBSTITUIR ALERTS -->
    <div id="modal-global-alert" style="z-index: 99999; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; width: 380px; max-width: 100%; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <i id="modal-global-icon" class="fa-solid fa-bell" style="font-size: 3.5rem; color: #3b2313; margin-bottom: 15px;"></i>
            <h2 id="modal-global-title" style="color: #3b2313; margin-bottom: 10px; font-size: 1.4rem;">ChokkoMelt</h2>
            <p id="modal-global-msg" style="color: #555; margin-bottom: 25px; line-height: 1.4; font-size: 0.95rem;"></p>
            <button onclick="document.getElementById('modal-global-alert').style.display='none'" style="background: #3b2313; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;">OK, Entendi</button>
        </div>
    </div>
    <script>
        /* Troca alert() feio por modal — não substitui mensagens vindas do PHP;
           para erros do servidor use $_SESSION['flash'] e imprima em PHP ou
           devolva JSON e trate com fetch(). */
        window.alert = function(msg) {
            document.getElementById('modal-global-msg').innerText = msg;
            document.getElementById('modal-global-alert').style.display = 'flex';
        };
    </script>
</body>
</html>
