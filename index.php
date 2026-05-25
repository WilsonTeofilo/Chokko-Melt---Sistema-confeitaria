<?php
/**

 * Este arquivo fica na raiz do servidor apenas para capturar 
 * os acessos diretos (ex: localhost/ChokkoSemIA/) e 
 * redirecionar o cliente automaticamente para a loja (/user/).
 */

header("Location: user/index.php");
exit;
?>
