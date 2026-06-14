<?php

class Produto {
    private $pdo;

    /**
     * Construtor da classe Produto
     * Recebe os dados de conexão do config.php
     */
    public function __construct($dbname, $host, $user, $pass) {
        try {
            $this->pdo = new PDO("mysql:dbname=".$dbname.";host=".$host, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro com banco de dados na Classe Produto: " . $e->getMessage());
        } catch (Exception $e) {
            die("Erro genérico na Classe Produto: " . $e->getMessage());
        }
    }

    public function Cadastrar($nome,$descricao,$dispo,$img,$preco,$custo,$categoria, $adicionais = []) {

        $cadastro = $this->pdo->prepare("INSERT INTO produto (nome,descricao,disponibilidade,imagem,preco,custo_compra,id_categoria) VALUES (:n,:descri,:dispo,:imag,:preco,:custo,:idCAT)");
        $cadastro->execute(
            [':n'=>$nome,
            ':descri'=>$descricao,
            ':dispo' =>$dispo,
            ':imag' => $img,
            ':preco'=>$preco,
            ':custo'=>$custo,
            ':idCAT'=>$categoria
        ]);

        $id_produto = $this->pdo->lastInsertId();

        if (!empty($adicionais) && is_array($adicionais)) {
            $this->salvarAdicionaisProduto($id_produto, $adicionais);
        }

        return $id_produto;
    }

 //Lista todos os produtos.
    public function listarTodos() {
        try {
    $listar = $this->pdo->prepare("SELECT produto.*, categoria.nome AS nome_categoria FROM produto INNER JOIN categoria ON produto.id_categoria = categoria.id_categoria");
    $listar->execute();
    $listado = $listar->fetchAll(PDO::FETCH_ASSOC);

    if (count($listado)>0){return $listado;}
    else{ throw new Exception('NÃO EXISTE PRODUTOS NO BANCO DE DADOS');}
    }
    catch(PDOException $e){echo "ERRO DE CONEXÃO BD:". $e->getMessage();}
    catch(Exception $e){echo "ERR: ". $e->getMessage(); }

    }

    public function listarAtivos() {
        try {
    $listar = $this->pdo->prepare("SELECT produto.*, categoria.nome AS nome_categoria FROM produto INNER JOIN categoria ON produto.id_categoria = categoria.id_categoria WHERE produto.disponibilidade = 1");
    $listar->execute();
    $listado = $listar->fetchAll(PDO::FETCH_ASSOC);

    if (count($listado)>0){return $listado;}
    else{ throw new Exception('NENHUM PRODUTO NA VITRINE');}
    }
    catch(PDOException $e){echo "ERRO DE CONEXÃO BD:". $e->getMessage();}
    catch(Exception $e){echo "ERR: ". $e->getMessage(); };
    }


    public function buscarPorId($id) {
        try {
        $buscaunica = $this->pdo->prepare("SELECT * FROM produto WHERE id_produto = :id");
        $buscaunica->bindValue(':id',$id,PDO::PARAM_INT);
        $buscaunica->execute();
        $resultado = $buscaunica->fetch(PDO::FETCH_ASSOC);
        if ($resultado){
        return $resultado;}
        else{ throw new Exception("NENHUM PRODUTO COM ESSE ID....");}}

          catch(PDOException $e){echo "ERRO DE CONEXÃO BD:". $e->getMessage();}
    catch(Exception $e){echo "ERR: ". $e->getMessage(); };
  
        }
    

 
public function atualizar($id, $nome, $descricao, $dispo, $img, $preco, $custo, $categoria, $adicionais = null) {
    $sql = "UPDATE produto SET 
            nome = :n, 
            descricao = :descri, 
            disponibilidade = :dispo, 
            imagem = :img, 
            preco = :preco, 
            custo_compra = :custo, 
            id_categoria = :categoria 
            WHERE id_produto = :id";

    $editar = $this->pdo->prepare($sql);
    

    $editar->execute([
        ':n'        => $nome,
        ':descri'   => $descricao,
        ':dispo'    => $dispo,
        ':img'      => $img,
        ':preco'    => $preco,
        ':custo'    => $custo,
        ':categoria'=> $categoria,
        ':id'       => $id
    ]);

    if ($adicionais !== null && is_array($adicionais)) {
        $this->salvarAdicionaisProduto($id, $adicionais);
    }
}

 
        public function excluir($id) {
        $deletar = $this->pdo->prepare("DELETE FROM produto where id_produto = :id");
        $deletar->bindValue(':id',$id,PDO::PARAM_INT);
        $deletar->execute();
        }

        public function obterAdicionais($id_produto) {
            try {
                $stmt = $this->pdo->prepare("
                    SELECT a.id_adicional, a.nome, a.preco, a.custo 
                    FROM adicional a
                    INNER JOIN produto_adicional pa ON a.id_adicional = pa.id_adicional
                    WHERE pa.id_produto = :id_prod AND a.ativo = 1
                ");
                $stmt->execute(['id_prod' => $id_produto]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return [];
            }
        }

        public function salvarAdicionaisProduto($id_produto, $adicionais) {
            $stmtDel = $this->pdo->prepare("DELETE FROM produto_adicional WHERE id_produto = :id_produto");
            $stmtDel->execute([':id_produto' => $id_produto]);

            if (!empty($adicionais)) {
                $stmtIns = $this->pdo->prepare("INSERT INTO produto_adicional (id_produto, id_adicional) VALUES (:id_produto, :id_adicional)");
                foreach ($adicionais as $id_adicional) {
                    $stmtIns->execute([
                        ':id_produto' => $id_produto,
                        ':id_adicional' => (int)$id_adicional
                    ]);
                }
            }
        }

        public function listarTodosAdicionais() {
            $stmt = $this->pdo->prepare("SELECT * FROM adicional WHERE ativo = 1 ORDER BY nome ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function cadastrarNovoAdicional($nome, $preco, $custo = null) {
            $stmtCheck = $this->pdo->prepare("SELECT id_adicional FROM adicional WHERE LOWER(nome) = LOWER(:nome) LIMIT 1");
            $stmtCheck->execute([':nome' => $nome]);
            $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($existente) {
                $stmtAct = $this->pdo->prepare("UPDATE adicional SET ativo = 1, preco = :preco WHERE id_adicional = :id");
                $stmtAct->execute([
                    ':preco' => $preco,
                    ':id' => $existente['id_adicional']
                ]);
                return $existente['id_adicional'];
            }

            $stmtIns = $this->pdo->prepare("INSERT INTO adicional (nome, preco, custo, ativo) VALUES (:nome, :preco, :custo, 1)");
            $stmtIns->execute([
                ':nome' => $nome,
                ':preco' => $preco,
                ':custo' => $custo
            ]);
            return $this->pdo->lastInsertId();
        }

}


?>
