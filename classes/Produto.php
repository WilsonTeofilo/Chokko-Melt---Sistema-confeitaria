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

//cadastro :ainda falta colocar uma brecada caso o nome do produto ja exista 
    public function Cadastrar($nome,$descricao,$dispo,$img,$preco,$custo,$categoria) {

        $cadastro = $this->pdo->prepare("INSERT INTO produto (nome,descricao,disponibilidade,imagem,preco,custo_compra,id_categoria) VALUES (:n,:descri,:dispo,:imag,:preco:custo,:idCAT)");
        $cadastro->execute(
            [':n'=>$nome,
            ':descri'=>$descricao,
            ':dispo' =>$dispo,
            ':imag' => $img,
            ':preco'=>$preco,
            ':custo'=>$custo,
            ':idCAT'=>$categoria
        ]);

        
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
        if (count($resultado)>0){
        return $resultado;}
        else{ throw new Exception("NENHUM PRODUTO COM ESSE ID....");}}

          catch(PDOException $e){echo "ERRO DE CONEXÃO BD:". $e->getMessage();}
    catch(Exception $e){echo "ERR: ". $e->getMessage(); };
  
        }
    

 
public function atualizar($id, $nome, $descricao, $dispo, $img, $preco, $custo, $categoria) {
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
}

 
        public function excluir($id) {
        $deletar = $this->pdo->prepare("DELETE FROM produtos where id_produto = :id");
        $deletar->bindValue(':id',$id,PDO::PARAM_INT);
        $deletar->execute();
        }

}


?>
