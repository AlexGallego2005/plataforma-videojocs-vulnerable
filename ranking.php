<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';
require_once __DIR__ . '/secret/games_model.php';

requireLogin();
$usuari = getUser($pdo);
$jocs = getAllJocs($pdo);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="./assets/global.style.css">
    <link rel="stylesheet" href="./assets/games.style.css">
    <link rel="stylesheet" href="./assets/index.style.css">
</head>
<body>
     <div class="home">
        <div class="identity">
            <img width="80px" src="./assets/helmet.png" alt="">
            <h1>Spartanos</h1>
        </div>
        <br>
        <h2>Los 5 mejores jugadores</h2>
        <table>
            <tr>
                <th>Usuarios</th>
                <th>Juego</th>
                <th>Puntuacion</th>
                <th>Fecha</th>
            </tr>
                <?php foreach ($usuari as $u): ?>
                    <?= htmlspecialchars($u['nom_usuari'] ?? '') ?><br>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($jocs as $j): ?>
                    <?= htmlspecialchars($j['nom_joc'] ?? '') ?><br>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($puntuacion as $p): ?>
                    <?= htmlspecialchars($p['puntuacio'] ?? '') ?><br>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($date as $d): ?>
                    <?= htmlspecialchars($d['fecha'] ?? '') ?><br>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
</body>
</html>