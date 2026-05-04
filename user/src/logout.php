<?php
session_start(); // Inicia/resgata a sessão atual
session_unset(); // Limpa todas as variáveis da sessão
session_destroy(); // Destrói a sessão por completo

// Redireciona de volta para a tela de login
header("Location: ../login.php");
exit;
?>
