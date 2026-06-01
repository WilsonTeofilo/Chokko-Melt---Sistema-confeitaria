<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']); 

// Carrega conexão com o banco e busca horário de funcionamento
require_once __DIR__ . '/../config/config.php';
$horario = [
    'horario' => '15:00 às 22:00',
    'hora_abre' => '15:00',
    'hora_fecha' => '22:00'
];
try {
    $stmt = $conn->prepare("SELECT * FROM config_loja WHERE id_config = 1");
    $stmt->execute();
    $config_loja = $stmt->fetch();
    if ($config_loja) {
        $hora_abre = date('H:i', strtotime($config_loja['hora_abre']));
        $hora_fecha = date('H:i', strtotime($config_loja['hora_fecha']));
        $horario = [
            'horario' => $hora_abre . ' às ' . $hora_fecha,
            'hora_abre' => $hora_abre,
            'hora_fecha' => $hora_fecha
        ];
    }
} catch (Throwable $e) {
    // Silencioso, mantém padrão
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chokko Melt</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lily+Script+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= ($current_page === 'perfil.php') ? 'perfil-bg' : '' ?>" data-hora-abre="<?= htmlspecialchars($horario['hora_abre']) ?>" data-hora-fecha="<?= htmlspecialchars($horario['hora_fecha']) ?>">

<!-- HEADER MUNDIAL (SÓ APARECE SE ESTIVER LOGADO) -->
<?php if(isset($_SESSION['userlogado'])): ?>
    <div style="background-color: #3b2313; color: white; padding: 8px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <span>Olá, <strong><?= htmlspecialchars($_SESSION['userlogado']); ?></strong> 👋</span>
        <a href="src/auth/logout.php" style="color: #ffbaba; text-decoration: none; font-weight: bold; border: 1px solid #ffbaba; padding: 4px 10px; border-radius: 4px; transition: 0.3s;">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
    </div>
<?php endif; ?>
