<?php
session_start();
require_once '../../secret/games_model.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuari_id'])) {
    header('Location: ../../login.php');
    exit();
}

$usuari_id = $_SESSION['usuari_id'];
$joc_id = 4; // ID del juego Pong

// Obtener información del juego y niveles
$game = getGameById($joc_id);
$nivells = getGameLevels($joc_id);
$progres = getUserProgress($usuari_id, $joc_id);

// Si no hay progreso, crearlo
if (!$progres) {
    createUserProgress($usuari_id, $joc_id);
    $progres = getUserProgress($usuari_id, $joc_id);
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pong - <?php echo htmlspecialchars($game['nom_joc']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="game-container">
        <div class="header">
            <h1>PONG</h1>
            <div class="stats">
                <span>Nivell: <span id="currentLevel">1</span></span>
                <span>Puntuació: <span id="score">0</span></span>
                <span>Millor: <span id="bestScore"><?php echo $progres['puntuacio_maxima']; ?></span></span>
            </div>
        </div>

        <div class="menu-screen" id="menuScreen">
            <h2>Selecciona Nivell</h2>
            <div class="level-buttons">
                <?php foreach ($nivells as $nivell): ?>
                    <button class="level-btn" 
                            data-level="<?php echo $nivell['nivell']; ?>"
                            data-config='<?php echo htmlspecialchars($nivell['configuracio_json']); ?>'
                            <?php echo ($nivell['nivell'] > $progres['nivell_actual']) ? 'disabled' : ''; ?>>
                        Nivell <?php echo $nivell['nivell']; ?>
                        <?php if ($nivell['nivell'] > $progres['nivell_actual']): ?>
                            <span class="locked">🔒</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <button id="startBtn" class="start-btn" disabled>Començar Partida</button>
            <button id="pauseBtn" class="pause-btn">Pausar</button>
        </div>

        <canvas id="pongCanvas" width="800" height="600"></canvas>

        <div class="game-over-screen" id="gameOverScreen">
            <h2 id="gameOverTitle">Fi del Joc</h2>
            <p id="gameOverMessage"></p>
            <p>Puntuació: <span id="finalScore">0</span></p>
            <button id="restartBtn" class="restart-btn">Tornar a Jugar</button>
            <button id="menuBtn" class="menu-btn">Menú</button>
        </div>

        <div class="controls-info">
            <p>Controls: ↑ ↓ o W S per moure la pala</p>
        </div>
    </div>

    <script>
        const usuariId = <?php echo $usuari_id; ?>;
        const jocId = <?php echo $joc_id; ?>;
        const maxNivellDesbloqueado = <?php echo $progres['nivell_actual']; ?>;
    </script>
    <script src="game.js"></script>
</body>
</html>