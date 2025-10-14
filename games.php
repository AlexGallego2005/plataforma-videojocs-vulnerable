<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';
require_once __DIR__ . '/secret/games_model.php';

requireLogin();
$usuari = getUser($pdo);
$jocs = getAllJocs($pdo);
?>
<!doctype html>
<html lang="ca">

<head>
  <meta charset="utf-8">
  <title>Llista de jocs</title>
  <link rel="stylesheet" href="./assets/global.style.css">
  <link rel="stylesheet" href="./assets/games.style.css">
</head>

<body>
  <header>
    <div class="identity">
      <img width="30px" src="./assets/helmet.png" alt="">
      <strong style="font-size: 26px;">Spartanos</strong>
    </div>
    <div class="user">
      <span>Bienvenido, <strong><?= htmlspecialchars($usuari['nom_usuari']) ?></strong></span>
      <a href="/logout.php">Salir</a>
    </div>
  </header>
  <ul style="list-style-type: none;" class="game-container">
    <?php foreach ($jocs as $j): ?>
      <li>
        <!--<strong><?= htmlspecialchars($j['nom_joc']) ?></strong>
        <?php if (!$j['actiu']) echo "(inactiu)"; ?><br>
        <small><?= htmlspecialchars($j['descripcio']) ?></small><br>
        <span>Puntuació màxima: <?= $j['puntuacio_maxima'] ?> | Nivells: <?= $j['nivells_totals'] ?></span>-->
        <a href="/juegos/<?= $j['id'] ?? '' ?>"><img class="friv_game" src="<?= $j['imatge_joc'] ?? '' ?>"></a>
      </li>

    <?php endforeach; ?>
  </ul>


  <marquee style="position: fixed; bottom: 0; color: white;">La página se llama "Spartanos" porque las personas espartanas vivían con lo justo y necesario. Por eso, hemos adoptado esa filosofía a nuestro proyecto, ya que queremos hacer lo justo y necesario para execeler. Un saludo!</marquee>
</body>

</html>