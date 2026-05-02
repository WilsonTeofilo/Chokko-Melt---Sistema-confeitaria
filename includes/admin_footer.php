        </main>
    </div>

    <?php
    /*
     * INTEGRAÇÃO JS ↔ PHP (admin): este footer fecha o layout do painel.
     * Scripts específicos (pedidos.js, produtos.js…) vêm na própria página.
     * Sessão: proteja cada admin/*.php com require 'auth_admin.php' que checa
     * $_SESSION['usuario_id'] ou equivalente. Veja INTEGRACAO_JS_PHP.txt na raiz.
     */
    ?>
    <!-- MODAL GLOBAL PARA SUBSTITUIR ALERTS -->
    <div id="modal-global-alert" style="z-index: 99999; display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; width: 380px; max-width: 100%; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <i id="modal-global-icon" class="fa-solid fa-bell" style="font-size: 3.5rem; color: #5c3c27; margin-bottom: 15px;"></i>
            <h2 id="modal-global-title" style="color: #5c3c27; margin-bottom: 10px; font-size: 1.4rem;">ChokkoMelt</h2>
            <p id="modal-global-msg" style="color: #555; margin-bottom: 25px; line-height: 1.4; font-size: 0.95rem;"></p>
            <button onclick="document.getElementById('modal-global-alert').style.display='none'" style="background: #5c3c27; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;">OK, Entendi</button>
        </div>
    </div>
    <script>
        /* Mesmo padrão do user_footer: UX só no browser; validação crítica no PHP. */
        window.alert = function(msg) {
            document.getElementById('modal-global-msg').innerText = msg;
            document.getElementById('modal-global-alert').style.display = 'flex';
        };
    </script>
</body>
</html>
