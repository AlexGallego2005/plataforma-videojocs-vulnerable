<?php
session_start();

// Cargar modelo de niveles
if (file_exists(__DIR__ . '/../../api/games_model.php')) {
    require_once __DIR__ . '/../../api/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 7; // ID único para este juego

$nivells = [];
if (function_exists('getGameLevels')) {
    try {
        $nivells = getGameLevels($joc_id);
    } catch (Exception $e) {
        $nivells = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Fruit Catcher 2.0</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/jpg" href="assets/helmet.png"/>
<style>
body {
  margin: 0;
  background: #fef9c3;
  font-family: sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.container {
  width: 960px;
  max-width: 98vw;
  padding: 18px;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
select, button {
  background: #facc15;
  color: #111;
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
}
#gameArea {
  position: relative;
  width: 900px;
  height: 520px;
  background: #fef08a;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 10px #aaa inset;
}
#basket {
  position: absolute;
  bottom: 0;
  width: 100px;
  height: 20px;
  background: #92400e;
  left: 400px;
}
.fruit {
  position: absolute;
  width: 20px;
  height: 20px;
  background: #dc2626;
  border-radius: 50%;
}
#message {
  margin-top: 6px;
  font-size: 14px;
  color: #166534;
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div><strong>Fruit Catcher 2.0</strong> — Atrapa todas las frutas</div>
    <div>
      Nivel:
      <select id="levelSelect">
        <?php foreach ($nivells as $lvl): ?>
          <option value="<?= htmlspecialchars($lvl['nivell']) ?>">Nivel <?= $lvl['nivell'] ?> — <?= htmlspecialchars($lvl['nom_nivell']) ?></option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn">Jugar</button>
    </div>
  </div>
  <div id="gameArea">
    <div id="basket"></div>
  </div>
  <div id="message"></div>
  <div style="margin-top:10px;font-size:13px;color:#444">
    <?php if ($usuari_id): ?>
      Sesión activa — usuario #<?= htmlspecialchars($usuari_id) ?>, juego #<?= $joc_id ?>
    <?php else: ?>
      No logueado — las partidas no se guardarán.
    <?php endif; ?>
  </div>
</div>

<script>
const gameArea = document.getElementById("gameArea");
const basket = document.getElementById("basket");
const startBtn = document.getElementById("startBtn");
const levelSelect = document.getElementById("levelSelect");
const msgEl = document.getElementById("message");

const usuari_id = <?= json_encode($usuari_id) ?>;
const joc_id = <?= json_encode($joc_id) ?>;

const LEVELS = {};
<?php foreach ($nivells as $lvl): ?>
LEVELS[<?= (int)$lvl['nivell'] ?>] = <?= $lvl['configuracio_json'] ?>;
<?php endforeach; ?>

let state = {
  running: false,
  level: 1,
  score: 0,
  time: 0,
  fruitCount: 0,
  fruitSpeed: 2,
  basketSpeed: 10,
  left: 400,
  fruits: [],
  intervalId: null
};

function startGame() {
  state.level = parseInt(levelSelect.value, 10);
  const conf = LEVELS[state.level];
  console.log(conf)
  state.fruitCount = conf.fruitCount;
  state.fruitSpeed = conf.fruitSpeed;
  state.basketSpeed = conf.basketSpeed;
  state.score = 0;
  state.time = 0;
  state.fruits = [];
  msgEl.textContent = "";
  gameArea.querySelectorAll(".fruit").forEach(f => f.remove());
  state.running = true;
  spawnFruits();
  state.intervalId = setInterval(updateGame, 1000 / 60);
}

function spawnFruits() {
  for (let i = 0; i < state.fruitCount; i++) {
    const fruit = document.createElement("div");
    fruit.className = "fruit";
    fruit.style.left = Math.random() * 880 + "px";
    fruit.style.top = -Math.random() * 300 + "px";
    gameArea.appendChild(fruit);
    state.fruits.push(fruit);
  }
}

function updateGame() {
  state.time += 1/60;
  state.fruits.forEach((fruit, index) => {
    let top = parseFloat(fruit.style.top);
    top += state.fruitSpeed;
    fruit.style.top = top + "px";

    const fruitRect = fruit.getBoundingClientRect();
    const basketRect = basket.getBoundingClientRect();

    if (
      fruitRect.bottom >= basketRect.top &&
      fruitRect.left >= basketRect.left &&
      fruitRect.right <= basketRect.right
    ) {
      fruit.remove();
      state.fruits.splice(index, 1);
      state.score += 10;
    } else if (top > 520) {
      endGame(false);
    }
  });

  if (state.fruits.length === 0) {
    endGame(true);
  }
}

function endGame(won) {
  clearInterval(state.intervalId);
  state.running = false;
  msgEl.textContent = won ? "¡Has atrapado todas las frutas!" : "¡Se te escapó una fruta!";
  if (usuari_id && joc_id) {
    fetch("/api/api.php", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify({
        action: "save_game",
        usuari_id: usuari_id,
        joc_id: joc_id,
        nivell: state.level,
        puntuacio: state.score,
        durada: Math.round(state.time),
        guanyat: won ? 1 : 0
      })
    }).then(r => r.json()).then(data => {
      if(data.success) msgEl.textContent += " — Partida guardada.";
    }).catch(() => {
      msgEl.textContent += " — Error al guardar.";
    });
  }
}

window.addEventListener("keypress", e => {
  if (!state.running) return;
  let left = state.left;
  if (e.code === "KeyA") left -= state.basketSpeed;
  if (e.code === "KeyD") left += state.basketSpeed;
  left = Math.max(0, Math.min(800, left));
  state.left = left;
  basket.style.left = left + "px";
});

startBtn.addEventListener("click", startGame);
</script>
</body>
</html>
