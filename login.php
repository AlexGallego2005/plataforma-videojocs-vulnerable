<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['user'] ?? '');
    $p = $_POST['password'] ?? '';
    if (loginUser($pdo, $u, $p)) {
        header('Location: /games.php');
        exit;
    } else {
        $error = 'Credencials incorrectes.';
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <title>Inici de sessió</title>
    
    <link rel="stylesheet" href="./assets/global.style.css">
    <link rel="stylesheet" href="./assets/index.style.css">
</head>
<body>
    <div class="home">
        <div class="identity">
            <img width="80px" src="./assets/helmet.png" alt="">
            <h1>Spartanos</h1>
        </div>
        <br>
        <h2>Inicia sessió</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
        <input name="user" placeholder="Usuari o email" required><br>
        <input name="password" type="password" placeholder="Contrasenya" required><br>
        <button>Entrar</button>
        </form>
        <p><a href="/register.php">Crear compte nou</a></p>
    </div>
</body>
</html>
