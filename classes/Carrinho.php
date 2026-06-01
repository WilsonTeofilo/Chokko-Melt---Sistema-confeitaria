<?php
class Carrinho {
    private $pdo;
    private $idCliente;
    private $idCarrinho = null;
    private $itens = []; // Array de itens. Cada item tem: chave, id_produto, nome, imagem, preco_unitario, quantidade, observacao

    public function __construct($dbname, $host, $user, $pass) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->pdo = new PDO("mysql:dbname=".$dbname.";host=".$host.";charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro com banco de dados na Classe Carrinho: " . $e->getMessage());
        }

        // Se o cliente está logado
        if (isset($_SESSION['idlogado'])) {
            $idSessao = $_SESSION['idlogado'];

            // Verifica se o cliente da sessão ainda existe no banco
            $stmtVerifica = $this->pdo->prepare("SELECT id_cliente FROM cliente WHERE id_cliente = :id LIMIT 1");
            $stmtVerifica->execute(['id' => $idSessao]);
            $clienteExiste = $stmtVerifica->fetch();

            if ($clienteExiste) {
                $this->idCliente = $idSessao;
                $this->inicializarCarrinhoBanco();
                $this->carregarItensBanco();
            } else {
                // Sessão com ID inválido (ex: banco foi re-indexado) — limpa e trata como visitante
                session_destroy();
                session_start();
                $this->idCliente = null;
                $_SESSION['carrinho'] = [];
                $this->itens = [];
            }
        } else {
            // Visitante: usa a sessão
            $this->idCliente = null;
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }
            $this->itens = $_SESSION['carrinho'];
        }
    }

    private function inicializarCarrinhoBanco() {
        // Busca se existe carrinho para o cliente
        $stmt = $this->pdo->prepare("SELECT id_carrinho FROM carrinho WHERE id_cliente = :id_cliente LIMIT 1");
        $stmt->execute(['id_cliente' => $this->idCliente]);
        $carrinho = $stmt->fetch();

        if ($carrinho) {
            $this->idCarrinho = $carrinho['id_carrinho'];
        } else {
            // Se não existe, cria um
            $stmtInsert = $this->pdo->prepare("INSERT INTO carrinho (id_cliente) VALUES (:id_cliente)");
            $stmtInsert->execute(['id_cliente' => $this->idCliente]);
            $this->idCarrinho = $this->pdo->lastInsertId();
        }
    }

    private function carregarItensBanco() {
        $stmt = $this->pdo->prepare("
            SELECT ic.id_item_carrinho, ic.quantidade, ic.preco_unitario, ic.observacao, p.id_produto, p.nome, p.imagem
            FROM item_carrinho ic
            INNER JOIN produto p ON ic.id_produto = p.id_produto
            WHERE ic.id_carrinho = :id_carrinho
        ");
        $stmt->execute(['id_carrinho' => $this->idCarrinho]);
        $rows = $stmt->fetchAll();

        $this->itens = [];
        foreach ($rows as $row) {
            // Busca adicionais deste item do carrinho
            $stmtAd = $this->pdo->prepare("
                SELECT id_adicional, nome_snapshot AS nome, preco_unitario_snapshot AS preco, custo_unitario_snapshot AS custo
                FROM item_carrinho_adicional
                WHERE id_item_carrinho = :id_item_carrinho
            ");
            $stmtAd->execute(['id_item_carrinho' => $row['id_item_carrinho']]);
            $adicionais = $stmtAd->fetchAll();

            $adicionaisIds = [];
            foreach ($adicionais as $ad) {
                $adicionaisIds[] = (int)$ad['id_adicional'];
            }
            sort($adicionaisIds);
            
            $chave = $row['id_produto'] . '|' . $row['observacao'] . '|' . implode(',', $adicionaisIds);

            $this->itens[] = [
                'id_item_carrinho' => $row['id_item_carrinho'],
                'chave'          => $chave,
                'id_produto'     => $row['id_produto'],
                'nome'           => $row['nome'],
                'imagem'         => $row['imagem'],
                'preco_unitario' => (float)$row['preco_unitario'],
                'quantidade'     => (int)$row['quantidade'],
                'observacao'     => $row['observacao'],
                'adicionais'     => $adicionais
            ];
        }
    }

    public function adicionar($idProduto, $quantidade, $observacao = '', $adicionais = []) {
        $quantidade = (int)$quantidade;
        if ($quantidade <= 0) return;

        // Limita o array de adicionais
        $adicionaisIds = array_map('intval', $adicionais);
        sort($adicionaisIds);

        // Limite de 10 por produto/observacao/adicionais
        $chave = $idProduto . '|' . $observacao . '|' . implode(',', $adicionaisIds);
        $itemExistenteIndex = -1;
        foreach ($this->itens as $idx => $item) {
            if ($item['chave'] === $chave) {
                $itemExistenteIndex = $idx;
                break;
            }
        }

        if ($this->idCliente !== null) {
            // Logado: mexe no banco
            if ($itemExistenteIndex !== -1) {
                // Já existe: atualiza quantidade no banco (limitado a 10)
                $novaQtd = min(10, $this->itens[$itemExistenteIndex]['quantidade'] + $quantidade);
                $stmt = $this->pdo->prepare("UPDATE item_carrinho SET quantidade = :qtd WHERE id_item_carrinho = :id");
                $stmt->execute([
                    'qtd' => $novaQtd,
                    'id' => $this->itens[$itemExistenteIndex]['id_item_carrinho']
                ]);
            } else {
                // Novo item no banco
                // Busca preço unitário do produto para snapshot
                $stmtProd = $this->pdo->prepare("SELECT preco FROM produto WHERE id_produto = :id LIMIT 1");
                $stmtProd->execute(['id' => $idProduto]);
                $prod = $stmtProd->fetch();
                $precoUnitario = $prod ? (float)$prod['preco'] : 0.0;

                $stmt = $this->pdo->prepare("
                    INSERT INTO item_carrinho (preco_unitario, quantidade, observacao, id_produto, id_carrinho)
                    VALUES (:preco, :qtd, :obs, :id_prod, :id_carrinho)
                ");
                $stmt->execute([
                    'preco' => $precoUnitario,
                    'qtd' => min(10, $quantidade),
                    'obs' => $observacao,
                    'id_prod' => $idProduto,
                    'id_carrinho' => $this->idCarrinho
                ]);
                $idItemCarrinho = $this->pdo->lastInsertId();

                // Agora insere os adicionais na tabela `item_carrinho_adicional`
                if (!empty($adicionaisIds)) {
                    foreach ($adicionaisIds as $idAdicional) {
                        // Busca dados do adicional para snapshot
                        $stmtAd = $this->pdo->prepare("SELECT nome, preco, custo FROM adicional WHERE id_adicional = :id LIMIT 1");
                        $stmtAd->execute(['id' => $idAdicional]);
                        $ad = $stmtAd->fetch();
                        if ($ad) {
                            $stmtInsAd = $this->pdo->prepare("
                                INSERT INTO item_carrinho_adicional (id_item_carrinho, id_adicional, nome_snapshot, preco_unitario_snapshot, custo_unitario_snapshot, quantidade)
                                VALUES (:id_item, :id_ad, :nome, :preco, :custo, 1)
                            ");
                            $stmtInsAd->execute([
                                'id_item' => $idItemCarrinho,
                                'id_ad' => $idAdicional,
                                'nome' => $ad['nome'],
                                'preco' => $ad['preco'],
                                'custo' => $ad['custo']
                            ]);
                        }
                    }
                }
            }
            $this->carregarItensBanco();
        } else {
            // Visitante: mexe na sessão
            if ($itemExistenteIndex !== -1) {
                $_SESSION['carrinho'][$itemExistenteIndex]['quantidade'] = min(10, $_SESSION['carrinho'][$itemExistenteIndex]['quantidade'] + $quantidade);
            } else {
                // Pega dados do produto do banco para a sessão
                $stmtProd = $this->pdo->prepare("SELECT nome, preco, imagem FROM produto WHERE id_produto = :id LIMIT 1");
                $stmtProd->execute(['id' => $idProduto]);
                $prod = $stmtProd->fetch();

                $nome = $prod ? $prod['nome'] : '';
                $precoUnitario = $prod ? (float)$prod['preco'] : 0.0;
                $imagem = $prod ? $prod['imagem'] : '';

                // Carrega adicionais para a sessão
                $addonsSession = [];
                if (!empty($adicionaisIds)) {
                    foreach ($adicionaisIds as $idAdicional) {
                        $stmtAd = $this->pdo->prepare("SELECT nome, preco, custo FROM adicional WHERE id_adicional = :id LIMIT 1");
                        $stmtAd->execute(['id' => $idAdicional]);
                        $ad = $stmtAd->fetch();
                        if ($ad) {
                            $addonsSession[] = [
                                'id_adicional' => (int)$idAdicional,
                                'nome' => $ad['nome'],
                                'preco' => (float)$ad['preco'],
                                'custo' => $ad['custo'] !== null ? (float)$ad['custo'] : null
                            ];
                        }
                    }
                }

                $_SESSION['carrinho'][] = [
                    'chave'          => $chave,
                    'id_produto'     => $idProduto,
                    'nome'           => $nome,
                    'imagem'         => $imagem,
                    'preco_unitario' => $precoUnitario,
                    'quantidade'     => min(10, $quantidade),
                    'observacao'     => $observacao,
                    'adicionais'     => $addonsSession
                ];
            }
            $this->itens = $_SESSION['carrinho'];
        }
    }

    public function alterarQuantidade($chave, $delta) {
        $delta = (int)$delta;
        $itemExistenteIndex = -1;
        foreach ($this->itens as $idx => $item) {
            if ($item['chave'] === $chave) {
                $itemExistenteIndex = $idx;
                break;
            }
        }

        if ($itemExistenteIndex === -1) return;

        $novaQtd = $this->itens[$itemExistenteIndex]['quantidade'] + $delta;

        if ($this->idCliente !== null) {
            if ($novaQtd <= 0) {
                // Remove
                $stmt = $this->pdo->prepare("DELETE FROM item_carrinho WHERE id_item_carrinho = :id");
                $stmt->execute(['id' => $this->itens[$itemExistenteIndex]['id_item_carrinho']]);
            } else {
                // Atualiza
                $stmt = $this->pdo->prepare("UPDATE item_carrinho SET quantidade = :qtd WHERE id_item_carrinho = :id");
                $stmt->execute([
                    'qtd' => min(10, $novaQtd),
                    'id' => $this->itens[$itemExistenteIndex]['id_item_carrinho']
                ]);
            }
            $this->carregarItensBanco();
        } else {
            if ($novaQtd <= 0) {
                unset($_SESSION['carrinho'][$itemExistenteIndex]);
                $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
            } else {
                $_SESSION['carrinho'][$itemExistenteIndex]['quantidade'] = min(10, $novaQtd);
            }
            $this->itens = $_SESSION['carrinho'];
        }
    }

    public function remover($chave) {
        $itemExistenteIndex = -1;
        foreach ($this->itens as $idx => $item) {
            if ($item['chave'] === $chave) {
                $itemExistenteIndex = $idx;
                break;
            }
        }

        if ($itemExistenteIndex === -1) return;

        if ($this->idCliente !== null) {
            $stmt = $this->pdo->prepare("DELETE FROM item_carrinho WHERE id_item_carrinho = :id");
            $stmt->execute(['id' => $this->itens[$itemExistenteIndex]['id_item_carrinho']]);
            $this->carregarItensBanco();
        } else {
            unset($_SESSION['carrinho'][$itemExistenteIndex]);
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
            $this->itens = $_SESSION['carrinho'];
        }
    }

    public function listarItens() {
        return $this->itens;
    }

    public function contarItens() {
        $total = 0;
        foreach ($this->itens as $item) {
            $total += $item['quantidade'];
        }
        return $total;
    }

    public function calcularSubtotal() {
        $subtotal = 0.0;
        foreach ($this->itens as $item) {
            $precoItem = $item['preco_unitario'];
            if (!empty($item['adicionais'])) {
                foreach ($item['adicionais'] as $ad) {
                    $precoItem += (float)$ad['preco'];
                }
            }
            $subtotal += $precoItem * $item['quantidade'];
        }
        return $subtotal;
    }

    public function esvaziar() {
        if ($this->idCliente !== null) {
            $stmt = $this->pdo->prepare("DELETE FROM item_carrinho WHERE id_carrinho = :id_carrinho");
            $stmt->execute(['id_carrinho' => $this->idCarrinho]);
            $this->itens = [];
        } else {
            $_SESSION['carrinho'] = [];
            $this->itens = [];
        }
    }

    public function sincronizarSessaoParaBanco($idCliente) {
        $this->idCliente = $idCliente;
        $this->inicializarCarrinhoBanco();

        if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
            foreach ($_SESSION['carrinho'] as $itemSessao) {
                // Como itens com adicionais diferentes são itens separados,
                // vamos carregar o que está no banco e procurar pelo item com a mesma chave.
                $this->carregarItensBanco();
                $itemBancoEncontrado = null;
                foreach ($this->itens as $itemB) {
                    if ($itemB['chave'] === $itemSessao['chave']) {
                        $itemBancoEncontrado = $itemB;
                        break;
                    }
                }

                if ($itemBancoEncontrado) {
                    // Se já existe, soma as quantidades e limita a 10
                    $novaQtd = min(10, $itemBancoEncontrado['quantidade'] + $itemSessao['quantidade']);
                    $stmtUpdate = $this->pdo->prepare("UPDATE item_carrinho SET quantidade = :qtd WHERE id_item_carrinho = :id");
                    $stmtUpdate->execute([
                        'qtd' => $novaQtd,
                        'id' => $itemBancoEncontrado['id_item_carrinho']
                    ]);
                } else {
                    // Se não existe, insere o item
                    $stmtInsert = $this->pdo->prepare("
                        INSERT INTO item_carrinho (preco_unitario, quantidade, observacao, id_produto, id_carrinho)
                        VALUES (:preco, :qtd, :obs, :id_prod, :id_carrinho)
                    ");
                    $stmtInsert->execute([
                        'preco' => $itemSessao['preco_unitario'],
                        'qtd' => $itemSessao['quantidade'],
                        'obs' => $itemSessao['observacao'],
                        'id_prod' => $itemSessao['id_produto'],
                        'id_carrinho' => $this->idCarrinho
                    ]);
                    $idItemCarrinho = $this->pdo->lastInsertId();

                    // E insere os adicionais associados
                    if (!empty($itemSessao['adicionais'])) {
                        foreach ($itemSessao['adicionais'] as $ad) {
                            $stmtInsAd = $this->pdo->prepare("
                                INSERT INTO item_carrinho_adicional (id_item_carrinho, id_adicional, nome_snapshot, preco_unitario_snapshot, custo_unitario_snapshot, quantidade)
                                VALUES (:id_item, :id_ad, :nome, :preco, :custo, 1)
                            ");
                            $stmtInsAd->execute([
                                'id_item' => $idItemCarrinho,
                                'id_ad' => $ad['id_adicional'],
                                'nome' => $ad['nome'],
                                'preco' => $ad['preco'],
                                'custo' => $ad['custo']
                            ]);
                        }
                    }
                }
            }
            // Limpa o carrinho da sessão
            $_SESSION['carrinho'] = [];
        }
        $this->carregarItensBanco();
    }
}
