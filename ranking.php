<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';
require_once __DIR__ . '/secret/games_model.php';

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
                <th>Juego</th>
                <th>Usuario</th>
                <th>Puntuación</th>
            </tr>
            <tr>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['nom_joc'])?>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['nom_usuari'])?>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($ranking as $r): ?>
                        <?= htmlspecialchars($r['puntuacio'])?>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php foreach ($ranking as $r): ?>
            <?= htmlspecialchars($r['id'])?>
        <?php endforeach; ?>
    </div>
</body>
</html>