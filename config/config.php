<?php 
define('HOST', 'localhost');
define('USER','root');
define('PASS','');
define('DATABASE', 'chokko_melt');

$conn = new mysqli(HOST, USER, PASS, DATABASE);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>