<?php include '../includes/admin_header.php'; ?>
            <?php
            /*
             * Dashboard admin: métricas e linhas devem vir do PHP (COUNT / SELECT em pedido).
             *
             * ATENÇÃO (JS): os botões chamam atualizarStatusPedido(), definido em
             * assets/js/pedidos.js junto com modais (#modal-cancelar-pedido, etc.) que
             * existem só em admin/pedidos.php. Esta página NÃO inclui pedidos.js nem esses
             * modais — os cliques podem quebrar ou não atualizar a coluna de ações.
             * Opções para iniciante: (1) trocar os botões por link para pedidos.php;
             * (2) incluir na dashboard o mesmo HTML dos modais + <script src="pedidos.js">
             * e alinhar colunas da tabela ao que verDetalhesPedido() espera em pedidos.php.
             * Guia: INTEGRACAO_JS_PHP.txt
             */
            ?>
            <section class="welcome-area">
                <h1>Painel de Pedidos</h1>
                <p>Monitore e gerencie as solicitações em tempo real.</p>
            </section>

            <section class="metrics-container">
                <div class="m-card orange">
                    <i class="fa-solid fa-clock"></i>
                    <div class="m-info">
                        <strong>12</strong>
                        <span>Pendentes</span>
                    </div>
                </div>

                <div class="m-card green">
                    <i class="fa-solid fa-circle-check"></i>
                    <div class="m-info">
                        <strong>08</strong>
                        <span>Aceitos</span>
                    </div>
                </div>

                <div class="m-card red">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div class="m-info">
                        <strong>02</strong>
                        <span>Cancelados</span>
                    </div>
                </div>

                <div class="m-card blue">
                    <i class="fa-solid fa-flag-checkered"></i>
                    <div class="m-info">
                        <strong>45</strong>
                        <span>Finalizados</span>
                    </div>
                </div>
            </section>

            <section class="table-wrapper">
                <h2>Pedidos em Aberto</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CLIENTE</th>
                            <th>PAGAMENTO</th>
                            <th>STATUS</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-status="PENDENTE">
                        <td>#1024</td>
                        <td>Juliana Souza</td>
                        <td>Pix</td>
                        <td><span class="badge pendente">Pendente</span></td>
                            <td>
                                <button class="btn-action-accept btn-aceitar" onclick="atualizarStatusPedido(this, 'EM_PREPARO')" title="Aceitar pedido">
                                    <i class="fa-solid fa-check"></i> Aceitar
                                </button>
                                <button class="btn-action-accept btn-cancelar" onclick="atualizarStatusPedido(this, 'CANCELADO')" title="Cancelar pedido">
                                    <i class="fa-solid fa-xmark"></i> Cancelar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
<?php include '../includes/admin_footer.php'; ?>