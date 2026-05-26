<?php

class Auth {
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

    // Validação cruzada de email/telefone (cliente + usuario)
    public function verificarDuplicidade($email, $telefone, $excluirId = null, $tabelaExcluir = null) {
        // 1. Tabela cliente
        $sqlCliente = "SELECT id_cliente FROM cliente WHERE email = :email OR telefone = :telefone";
        if ($tabelaExcluir === 'cliente' && $excluirId !== null) {
            $sqlCliente .= " AND id_cliente != :excluirId";
        }
        $stmtCliente = $this->pdo->prepare($sqlCliente);
        $paramsCliente = ['email' => $email, 'telefone' => $telefone];
        if ($tabelaExcluir === 'cliente' && $excluirId !== null) {
            $paramsCliente['excluirId'] = $excluirId;
        }
        $stmtCliente->execute($paramsCliente);
        if ($stmtCliente->rowCount() > 0) {
            return true;
        }

        // 2. Tabela usuario
        $sqlUsuario = "SELECT id_usuario FROM usuario WHERE email = :email OR telefone = :telefone";
        if ($tabelaExcluir === 'usuario' && $excluirId !== null) {
            $sqlUsuario .= " AND id_usuario != :excluirId";
        }
        $stmtUsuario = $this->pdo->prepare($sqlUsuario);
        $paramsUsuario = ['email' => $email, 'telefone' => $telefone];
        if ($tabelaExcluir === 'usuario' && $excluirId !== null) {
            $paramsUsuario['excluirId'] = $excluirId;
        }
        $stmtUsuario->execute($paramsUsuario);
        if ($stmtUsuario->rowCount() > 0) {
            return true;
        }

        return false;
    }

    // Busca cliente por email
    public function buscarClientePorEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM cliente WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    // Busca admin/funcionario por email
    public function buscarAdminPorEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    // Cadastra novo cliente (com validação cruzada)
    public function cadastrarCliente($nome, $email, $telefone, $senha) {
        if ($this->verificarDuplicidade($email, $telefone)) {
            throw new Exception("Esse e-mail ou número de telefone já pertence a uma conta (pode ser de um cliente ou de outro administrador).");
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO cliente (nome, email, senha, telefone) VALUES (:nome, :email, :senha, :telefone)");
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senhaHash,
            'telefone' => $telefone
        ]);

        return $this->pdo->lastInsertId();
    }

    // Atualiza dados do cliente (com validação cruzada)
    public function atualizarPerfilCliente($id_cliente, $nome, $telefone, $senha = null) {
        $stmtEmail = $this->pdo->prepare("SELECT email FROM cliente WHERE id_cliente = :id LIMIT 1");
        $stmtEmail->execute(['id' => $id_cliente]);
        $cli = $stmtEmail->fetch();
        $email = $cli ? $cli['email'] : '';

        if ($this->verificarDuplicidade($email, $telefone, $id_cliente, 'cliente')) {
            throw new Exception("Esse número de telefone já pertence a outra conta cadastrada.");
        }

        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE cliente SET nome = :nome, telefone = :telefone, senha = :senha WHERE id_cliente = :id");
            return $stmt->execute([
                'nome' => $nome,
                'telefone' => $telefone,
                'senha' => $senhaHash,
                'id' => $id_cliente
            ]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE cliente SET nome = :nome, telefone = :telefone WHERE id_cliente = :id");
            return $stmt->execute([
                'nome' => $nome,
                'telefone' => $telefone,
                'id' => $id_cliente
            ]);
        }
    }

    // Cadastra novo administrador/funcionario (com validação cruzada)
    public function cadastrarAdmin($nome, $email, $telefone, $senha, $tipo) {
        if ($this->verificarDuplicidade($email, $telefone)) {
            throw new Exception("Esse e-mail ou número de telefone já pertence a uma conta (pode ser de um cliente ou de outro administrador).");
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $is_root = ($tipo === 'ADMIN') ? 1 : 0;
        $permissoes_string = ($tipo === 'ADMIN') ? 'pedidos,extrato,produtos,usuarios,config' : 'pedidos,produtos';

        $stmt = $this->pdo->prepare("INSERT INTO usuario (nome, email, telefone, senha, tipo_usuario, root, permissoes) VALUES (:nome, :email, :telefone, :senha, :tipo, :root, :permissoes)");
        
        return $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $senhaHash,
            'tipo' => $tipo,
            'root' => $is_root,
            'permissoes' => $permissoes_string
        ]);
    }

    // Exclui um administrador/funcionario
    public function excluirAdmin($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuario WHERE id_usuario = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Atualiza permissões de administrador/funcionario
    public function atualizarPermissoesAdmin($id, $tipo, $permissoes) {
        $is_root = ($tipo === 'ADMIN') ? 1 : 0;
        $stmt = $this->pdo->prepare("UPDATE usuario SET tipo_usuario = :tipo, root = :root, permissoes = :perms WHERE id_usuario = :id");
        return $stmt->execute([
            'tipo' => $tipo,
            'root' => $is_root,
            'perms' => $permissoes,
            'id' => $id
        ]);
    }
}
