<?php 
// O defined() garante que, mesmo que este arquivo seja incluído mais de uma
// vez no mesmo script por engano, as constantes não serão redefinidas.
defined('HOST')     || define('HOST',     'localhost');
defined('USER')     || define('USER',     'root');
defined('PASS')     || define('PASS',     '');
defined('DATABASE') || define('DATABASE', 'chokko_melt');

try {
    $conn = new PDO("mysql:host=" . HOST . ";dbname=" . DATABASE . ";charset=utf8mb4", USER, PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>