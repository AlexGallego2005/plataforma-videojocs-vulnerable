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
<head><meta charset="utf-8"><title>Llista de jocs</title></head>
<body>
<header>
  <p>Benvingut, <strong><?=htmlspecialchars($usuari['nom_usuari'])?></strong> | <a href="/logout.php">Sortir</a></p>
</header>
<h1>Jocs disponibles</h1>
<ul>
<?php foreach($jocs as $j): ?>
  <li>
    <strong><?=htmlspecialchars($j['nom_joc'])?></strong>  
    <?php if(!$j['actiu']) echo "(inactiu)"; ?><br>
    <small><?=htmlspecialchars($j['descripcio'])?></small><br>
    <span>Puntuació màxima: <?=$j['puntuacio_maxima']?> | Nivells: <?=$j['nivells_totals']?></span>
  </li>
<?php endforeach; ?>
</ul>
</body>
</html>
