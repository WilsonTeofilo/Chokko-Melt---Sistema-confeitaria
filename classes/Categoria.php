<?php

class Categoria {
    private $pdo;
    public function __construct($dbname, $host, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:dbname=".$dbname.";host=".$host, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro com banco de dados na Classe Categoria: " . $e->getMessage());
        } catch (Exception $e) {
            die("Erro genérico na Classe Categoria: " . $e->getMessage());
        }
    }


    public function cadastrar($nome) {
        $stmt = $this->pdo->prepare("INSERT INTO categoria (nome) VALUES (:nome)");
        $stmt->execute(['nome' => trim($nome)]);
        return $this->pdo->lastInsertId();
    }


    public function listarTodas() {
        $stmt = $this->pdo->query("SELECT * FROM categoria ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE id_categoria = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function atualizar($id, $nome) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET nome = :nome WHERE id_categoria = :id");
        return $stmt->execute([
            'nome' => trim($nome),
            'id' => $id
        ]);
    }


    public function excluir($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
