<?php
session_start();
require_once '../../api/games_model.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuari_id'])) {
    header('Location: /login.php');
    exit();
}

$usuari_id = $_SESSION['usuari_id'];
$joc_id = 5; // ID del juego Pong

// Obtener información del juego y niveles
$game = getGameById($joc_id);
$nivells = getGameLevels($joc_id);
$progres = getUserProgress($usuari_id, $joc_id);
$points = 0;

// Si no hay progreso, crearlo
if (!$progres) {
    createUserProgress($usuari_id, $joc_id);
    $progres = getUserProgress($usuari_id, $joc_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego Simple</title>
    <link rel="icon" type="image/jpg" href="assets/helmet.png"/>
    <link rel="stylesheet" href="./assets/style.css">

    <style>
* {
    box-sizing: border-box;
}
html, body {
    min-height: 100vh;
    margin: 0;
    padding: 0;
}
.enemy {
    border: 1px solid black;
}
    </style>
<body style="display: grid; grid-template-rows: min-content auto;">
    <div class="game-nav">
        <div class="stats">
            <span>
                Nivel:
                <span id="level">
                    1
                </span>
            </span>
            <span>
                Puntos:
                <span id="points">
                    <?php echo $points; ?>
                </span>
            </span>
            <span>
                Récord:
                <span id="best">
                    <?php echo $progres['puntuacio_maxima']; ?>
                </span>
            </span>
        </div>
    </div>
    <div id="lost" class="lose" style="display: none; text-align: center; position: fixed; align-items: center; justify-content: center; width: 100vw; z-index: 999; height: 100vh; background-color: white;">
        <h1>has perdido</h1>
        <h3>[F5] para volver a jugar</h3>
        <hr>
    </div>
    <div id="game-area" style="position: relative; min-height: 100%;"></div>
    <script>
        const game_configs = <?php echo json_encode($nivells) ?>;
        var points = <?php echo $points ?>;
        var maxPoints = <?php echo $points ?>;
        var this_level;
        var lost =  false;
        var this_level_json;
        const losted = document.getElementById('lost');
        const gameArea = document.getElementById('game-area');
        const pointer = document.getElementById('points');
        const level = document.getElementById('level');
        const bestEl = document.getElementById('best');

        function setup()
        {
            this_level = game_configs.filter(l => l.puntuacio_minima <= points).at(-1);
            this_level_json = JSON.parse(this_level.configuracio_json);
            level.textContent = this_level.nivell;
            document.styleSheets[0].insertRule(`.enemy { background-color: #${ this_level_json.color } }`, 0)

            requestAnimationFrame(loop);
        };
        
        function loop()
        {
            if (lost)
            {
                document.body.style.userSelect = "none";
                losted.style.display = 'block';
                losted.insertAdjacentHTML(`beforeend`, `<p>máximo puntos sesion: ${ maxPoints }</p><p>record: ${ bestEl.textContent }</p>`)
                return;
            };

            if (this_level.nivell !== game_configs.filter(l => l.puntuacio_minima <= points).at(-1).nivell)
            {
                setup();
                return;
            }

            if (points < 0)
            {
                lost = true;
                return;
            };

            var paused = 1_000*(this_level.nivell/0.5);
            const enemy = document.createElement('div');
            const size = Math.floor(Math.random() * this_level_json.sizeVariationPx) + this_level_json.sizePx - (this_level_json.sizeVariationPx / 2);
            enemy.style.width = `${ size }px`;
            enemy.style.height = `${ size }px`;
            enemy.addEventListener('click', () => {
                points+=10*this_level.nivell;
                pointer.textContent = points;
                if (points > maxPoints) maxPoints = points;

                const currentBest = parseInt(bestEl.textContent, 10);

                // Si supera el récord, actualizar en servidor
                if (points > currentBest) {
                    bestEl.textContent = points; // actualizar en pantalla
                    fetch('update_score.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'points=' + encodeURIComponent(points)
                    });
                }

                enemy.parentElement.removeChild(enemy);
            });
            enemy.setAttribute('ttl', Date.now());
            enemy.setAttribute('class', 'enemy');
            enemy.style.position = "fixed";
            enemy.style.top = `${ Math.floor(Math.random() * (gameArea.getBoundingClientRect().height - this_level_json.sizePx) )}px`;
            enemy.style.left = `${ Math.floor(Math.random() * (gameArea.getBoundingClientRect().width - this_level_json.sizePx) )}px`;
            setTimeout(() => {
                if (lost) return;
                if (enemy)
                {
                    enemy.parentElement.removeChild(enemy);
                    points-=10;
                    pointer.textContent = points;
                    if (points<0) lost = true;
                };
            }, this_level_json.ttl * 1000);
            gameArea.insertAdjacentElement('beforeend', enemy);
            setTimeout(() => {
                requestAnimationFrame(loop);
            }, 900 + (100 * this_level.nivell));
        };
        setup();
    </script>
</body>
</html>