<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/games_model.php';

requireLogin();
$usuari = getUser($pdo);
$jocs = getAllJocs($pdo);
$ranking = getRanking($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" type="image/jpg" href="assets/helmet.png"/>
    <link rel="stylesheet" href="/assets/css/global.style.css">
    <link rel="stylesheet" href="/assets/css/games.style.css">
    <link rel="stylesheet" href="/assets/css/index.style.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    <div class="home">
        <div class="identity">
            <img width="80px" src="/assets/images/helmet.png" alt="">
            <h1>Spartanos</h1>
        </div>
        <br>
        <h2>Los 5 mejores jugadores</h2>
        <table style="border:solid 1px black;">
            <tr>
                <th>Juego</th>
                <th>Usuario</th>
                <th>Puntuación</th>
            </tr>
            <tr>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['nom_joc'])?> <br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['nom_usuari'])?><br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['puntuacio_maxima'])?> <br>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>