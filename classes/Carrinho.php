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
            $this->idCliente = $_SESSION['idlogado'];
            $this->inicializarCarrinhoBanco();
            $this->carregarItensBanco();
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
            $chave = $row['id_produto'] . '|' . $row['observacao'];
            $this->itens[] = [
                'id_item_carrinho' => $row['id_item_carrinho'],
                'chave'          => $chave,
                'id_produto'     => $row['id_produto'],
                'nome'           => $row['nome'],
                'imagem'         => $row['imagem'],
                'preco_unitario' => (float)$row['preco_unitario'],
                'quantidade'     => (int)$row['quantidade'],
                'observacao'     => $row['observacao']
            ];
        }
    }

    public function adicionar($idProduto, $quantidade, $observacao = '') {
        $quantidade = (int)$quantidade;
        if ($quantidade <= 0) return;

        // Limite de 10 por produto/observacao
        $chave = $idProduto . '|' . $observacao;
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

                $_SESSION['carrinho'][] = [
                    'chave'          => $chave,
                    'id_produto'     => $idProduto,
                    'nome'           => $nome,
                    'imagem'         => $imagem,
                    'preco_unitario' => $precoUnitario,
                    'quantidade'     => min(10, $quantidade),
                    'observacao'     => $observacao
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
            $subtotal += $item['preco_unitario'] * $item['quantidade'];
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
                // Verifica se já existe o mesmo item no banco
                $chave = $itemSessao['id_produto'] . '|' . $itemSessao['observacao'];
                $stmtCheck = $this->pdo->prepare("
                    SELECT id_item_carrinho, quantidade
                    FROM item_carrinho
                    WHERE id_carrinho = :id_carrinho AND id_produto = :id_prod AND observacao = :obs
                    LIMIT 1
                ");
                $stmtCheck->execute([
                    'id_carrinho' => $this->idCarrinho,
                    'id_prod' => $itemSessao['id_produto'],
                    'obs' => $itemSessao['observacao']
                ]);
                $itemBanco = $stmtCheck->fetch();

                if ($itemBanco) {
                    // Se já existe, soma as quantidades e limita a 10
                    $novaQtd = min(10, $itemBanco['quantidade'] + $itemSessao['quantidade']);
                    $stmtUpdate = $this->pdo->prepare("UPDATE item_carrinho SET quantidade = :qtd WHERE id_item_carrinho = :id");
                    $stmtUpdate->execute([
                        'qtd' => $novaQtd,
                        'id' => $itemBanco['id_item_carrinho']
                    ]);
                } else {
                    // Se não existe, insere
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
                }
            }
            // Limpa o carrinho da sessão
            $_SESSION['carrinho'] = [];
        }
        $this->carregarItensBanco();
    }
}
