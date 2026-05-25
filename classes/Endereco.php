<?php

class Endereco {
    private $pdo;

    public function __construct($dbname, $host, $user, $senha){
        try { 
            $this->pdo = new PDO("mysql:dbname=".$dbname.";host=".$host.";charset=utf8mb4", $user, $senha);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Erro de conexão com o banco de dados: " . $e->getMessage());
        } catch(Exception $e) {
            throw new Exception("Erro geral: " . $e->getMessage());
        }
    }

    // Retorna a quantidade de endereços de um cliente
    public function contarEnderecos($id_cliente) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM endereco WHERE id_cliente = :id_cliente");
        $stmt->execute(['id_cliente' => $id_cliente]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    // Retorna todos os endereços de um cliente
    public function listarPorCliente($id_cliente) {
        $stmt = $this->pdo->prepare("SELECT * FROM endereco WHERE id_cliente = :id_cliente ORDER BY id_endereco ASC");
        $stmt->execute(['id_cliente' => $id_cliente]);
        return $stmt->fetchAll();
    }

    // Retorna um único endereço pelo ID (garantindo que pertence ao cliente logado)
    public function buscarPorId($id_endereco, $id_cliente) {
        $stmt = $this->pdo->prepare("SELECT * FROM endereco WHERE id_endereco = :id_endereco AND id_cliente = :id_cliente LIMIT 1");
        $stmt->execute([
            'id_endereco' => $id_endereco,
            'id_cliente' => $id_cliente
        ]);
        return $stmt->fetch();
    }

    // Cadastra um novo endereço se o cliente tiver menos de 3
    public function cadastrar($id_cliente, $dados) {
        if ($this->contarEnderecos($id_cliente) >= 3) {
            throw new Exception("Você atingiu o limite de 3 endereços. Exclua um endereço antigo antes de adicionar um novo.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO endereco (rua, numero, complemento, bairro, cep, ponto_referencia, id_cliente) 
            VALUES (:rua, :numero, :complemento, :bairro, :cep, :ponto_referencia, :id_cliente)
        ");

        $stmt->execute([
            'rua' => $dados['rua'],
            'numero' => $dados['numero'],
            'complemento' => $dados['complemento'] ?? null,
            'bairro' => $dados['bairro'],
            'cep' => $dados['cep'],
            'ponto_referencia' => $dados['ponto_referencia'] ?? null,
            'id_cliente' => $id_cliente
        ]);

        return $this->pdo->lastInsertId();
    }

    // Edita um endereço existente (validando propriedade)
    public function editar($id_endereco, $id_cliente, $dados) {
        $stmt = $this->pdo->prepare("
            UPDATE endereco 
            SET rua = :rua, 
                numero = :numero, 
                complemento = :complemento, 
                bairro = :bairro, 
                cep = :cep, 
                ponto_referencia = :ponto_referencia
            WHERE id_endereco = :id_endereco AND id_cliente = :id_cliente
        ");

        return $stmt->execute([
            'rua' => $dados['rua'],
            'numero' => $dados['numero'],
            'complemento' => $dados['complemento'] ?? null,
            'bairro' => $dados['bairro'],
            'cep' => $dados['cep'],
            'ponto_referencia' => $dados['ponto_referencia'] ?? null,
            'id_endereco' => $id_endereco,
            'id_cliente' => $id_cliente
        ]);
    }

    // Exclui um endereço existente (validando propriedade)
    public function excluir($id_endereco, $id_cliente) {
        $stmt = $this->pdo->prepare("DELETE FROM endereco WHERE id_endereco = :id_endereco AND id_cliente = :id_cliente");
        return $stmt->execute([
            'id_endereco' => $id_endereco,
            'id_cliente' => $id_cliente
        ]);
    }
}
