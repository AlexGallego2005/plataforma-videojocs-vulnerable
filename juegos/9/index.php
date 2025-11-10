<?php
session_start();

// Incluimos el modelo si existe
if (file_exists(__DIR__ . '/../../api/games_model.php')) {
    require_once __DIR__ . '/../../api/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 9; // Nuevo ID para este juego

// Intentar obtener niveles del juego desde la base de datos
$nivells = [];
if (function_exists('getGameLevels')) {
    try {
        $nivells = getGameLevels($joc_id);
    } catch (Exception $e) {
        $nivells = [];
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Neon Snake</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/jpg" href="assets/helmet.png"/>
<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');

body {
  margin: 0; 
  background: #0a0e27; 
  display:flex; 
  justify-content:center; 
  align-items:center;
  min-height:100vh; 
  color: #00ff88; 
  font-family: 'Orbitron', monospace;
  overflow: hidden;
}

.bg-animation {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: 
    radial-gradient(circle at 20% 50%, rgba(0, 255, 136, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(138, 43, 226, 0.1) 0%, transparent 50%);
  z-index: 0;
}

.container { 
  width: 920px; 
  max-width: 98vw; 
  padding: 24px; 
  background: rgba(10, 14, 39, 0.8);
  border-radius: 20px;
  backdrop-filter: blur(10px);
  box-shadow: 0 0 60px rgba(0, 255, 136, 0.3),
              inset 0 0 30px rgba(0, 0, 0, 0.5);
  border: 2px solid rgba(0, 255, 136, 0.3);
  position: relative;
  z-index: 1;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid rgba(0, 255, 136, 0.3);
}

.title {
  font-size: 32px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 4px;
  background: linear-gradient(45deg, #00ff88, #00d4ff, #ff00ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 30px rgba(0, 255, 136, 0.5);
  animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

.controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

select, button {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  border: 2px solid #00ff88;
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-family: 'Orbitron', monospace;
  transition: all 0.3s;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 1px;
}

button:hover {
  background: rgba(0, 255, 136, 0.3);
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.6);
  transform: translateY(-2px);
}

canvas {
  background: #050817;
  border-radius: 12px;
  display: block;
  width: 100%;
  height: 560px;
  box-shadow: 0 0 40px rgba(0, 255, 136, 0.4) inset,
              0 0 60px rgba(0, 0, 0, 0.8);
  border: 3px solid rgba(0, 255, 136, 0.4);
}

#message {
  margin-top: 16px;
  font-size: 18px;
  color: #ff00ff;
  font-weight: 700;
  text-align: center;
  min-height: 28px;
  text-shadow: 0 0 10px rgba(255, 0, 255, 0.8);
  text-transform: uppercase;
  letter-spacing: 2px;
}

.info {
  margin-top: 16px;
  font-size: 12px;
  color: rgba(0, 255, 136, 0.6);
  text-align: center;
  letter-spacing: 1px;
}

.instructions {
  background: rgba(0, 255, 136, 0.05);
  padding: 16px;
  border-radius: 10px;
  margin-top: 16px;
  font-size: 13px;
  line-height: 1.8;
  border: 1px solid rgba(0, 255, 136, 0.2);
  text-align: center;
}
</style>
</head>
<body>
<div class="bg-animation"></div>
<div class="container">
  <div class="header">
    <div class="title">🐍 Neon Snake</div>
    <div class="controls">
      <select id="levelSelect">
        <?php foreach ($nivells as $lvl): ?>
          <option value="<?= htmlspecialchars($lvl['nivell']) ?>">Level <?= $lvl['nivell'] ?> — <?= htmlspecialchars($lvl['nom_nivell']) ?></option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn">Start Game</button>
    </div>
  </div>
  <canvas id="game" width="900" height="560"></canvas>
  <div id="message"></div>
  <div class="instructions">
    <strong>🎮 CONTROLS:</strong> Usa las flechas ← ↑ ↓ → para mover la serpiente. Come las frutas brillantes para crecer. ¡No te choques contigo mismo ni con los bordes!
  </div>
  <div class="info">
    <?php if ($usuari_id): ?>
      ⚡ Session Active — User #<?= htmlspecialchars($usuari_id) ?> | Game #<?= $joc_id ?>
    <?php else: ?>
      ⚠️ Not Logged In — Games won't be saved
    <?php endif; ?>
  </div>
</div>

<script>
"use strict";
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");
const startBtn = document.getElementById("startBtn");
const msgEl = document.getElementById("message");
const levelSelect = document.getElementById("levelSelect");

const usuari_id = <?= json_encode($usuari_id) ?>;
const joc_id = <?= json_encode($joc_id) ?>;

const GRID = 20;
const COLS = 45;
const ROWS = 28;

let state = {
  running: false,
  level: 1,
  score: 0,
  startTime: 0,
  snake: [],
  direction: { x: 1, y: 0 },
  nextDirection: { x: 1, y: 0 },
  food: { x: 0, y: 0 },
  obstacles: [],
  speed: 150,
  lastUpdate: 0,
  powerUps: [],
  foodEaten: 0,
  targetScore: 0
};

// Cargar configuraciones dinámicas de niveles
const LEVELS = {};
<?php foreach ($nivells as $lvl): ?>
LEVELS[<?= (int)$lvl['nivell'] ?>] = <?= $lvl['configuracio_json'] ?>;
<?php endforeach; ?>

function randomPos() {
  return {
    x: Math.floor(Math.random() * COLS),
    y: Math.floor(Math.random() * ROWS)
  };
}

function resetGame() {
  state.score = 0;
  state.foodEaten = 0;
  state.startTime = Date.now();
  msgEl.textContent = '';
  
  const config = LEVELS[state.level];
  if (!config) return;
  
  state.speed = config.speed || 150;
  state.targetScore = config.targetScore || 100;
  
  // Inicializar serpiente en el centro
  state.snake = [
    { x: Math.floor(COLS / 2), y: Math.floor(ROWS / 2) },
    { x: Math.floor(COLS / 2) - 1, y: Math.floor(ROWS / 2) },
    { x: Math.floor(COLS / 2) - 2, y: Math.floor(ROWS / 2) }
  ];
  
  state.direction = { x: 1, y: 0 };
  state.nextDirection = { x: 1, y: 0 };
  
  // Generar obstáculos
  state.obstacles = [];
  const obstacleCount = config.obstacleCount || 0;
  for (let i = 0; i < obstacleCount; i++) {
    let pos;
    do {
      pos = randomPos();
    } while (isColliding(pos));
    state.obstacles.push(pos);
  }
  
  spawnFood();
  state.powerUps = [];
}

function isColliding(pos) {
  // Verificar si la posición colisiona con la serpiente u obstáculos
  for (let seg of state.snake) {
    if (seg.x === pos.x && seg.y === pos.y) return true;
  }
  for (let obs of state.obstacles) {
    if (obs.x === pos.x && obs.y === pos.y) return true;
  }
  return false;
}

function spawnFood() {
  do {
    state.food = randomPos();
  } while (isColliding(state.food));
}

function drawGrid() {
  ctx.strokeStyle = "rgba(0, 255, 136, 0.05)";
  ctx.lineWidth = 1;
  for (let x = 0; x <= COLS; x++) {
    ctx.beginPath();
    ctx.moveTo(x * GRID, 0);
    ctx.lineTo(x * GRID, ROWS * GRID);
    ctx.stroke();
  }
  for (let y = 0; y <= ROWS; y++) {
    ctx.beginPath();
    ctx.moveTo(0, y * GRID);
    ctx.lineTo(COLS * GRID, y * GRID);
    ctx.stroke();
  }
}

function drawSnake() {
  state.snake.forEach((seg, i) => {
    const x = seg.x * GRID;
    const y = seg.y * GRID;
    
    if (i === 0) {
      // Cabeza con brillo
      const gradient = ctx.createRadialGradient(x + GRID / 2, y + GRID / 2, 0, x + GRID / 2, y + GRID / 2, GRID);
      gradient.addColorStop(0, "#00ffff");
      gradient.addColorStop(0.5, "#00ff88");
      gradient.addColorStop(1, "#00aa55");
      ctx.fillStyle = gradient;
      ctx.fillRect(x + 1, y + 1, GRID - 2, GRID - 2);
      
      // Ojos
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(x + 5, y + 5, 3, 3);
      ctx.fillRect(x + 12, y + 5, 3, 3);
    } else {
      // Cuerpo con gradiente
      const intensity = 1 - (i / state.snake.length) * 0.5;
      ctx.fillStyle = `rgba(0, 255, 136, ${intensity})`;
      ctx.fillRect(x + 2, y + 2, GRID - 4, GRID - 4);
      
      // Brillo en el cuerpo
      ctx.fillStyle = `rgba(0, 255, 255, ${intensity * 0.3})`;
      ctx.fillRect(x + 4, y + 4, 4, 4);
    }
    
    // Borde brillante
    ctx.strokeStyle = `rgba(0, 255, 255, ${0.8 - i * 0.02})`;
    ctx.lineWidth = 2;
    ctx.strokeRect(x + 1, y + 1, GRID - 2, GRID - 2);
  });
}

function drawFood() {
  const x = state.food.x * GRID;
  const y = state.food.y * GRID;
  const time = Date.now() / 200;
  
  // Pulso animado
  const pulse = Math.sin(time) * 2 + GRID / 2;
  
  const gradient = ctx.createRadialGradient(x + GRID / 2, y + GRID / 2, 0, x + GRID / 2, y + GRID / 2, pulse);
  gradient.addColorStop(0, "#ff00ff");
  gradient.addColorStop(0.5, "#ff0088");
  gradient.addColorStop(1, "rgba(255, 0, 255, 0)");
  ctx.fillStyle = gradient;
  ctx.beginPath();
  ctx.arc(x + GRID / 2, y + GRID / 2, pulse, 0, Math.PI * 2);
  ctx.fill();
  
  // Fruta sólida
  ctx.fillStyle = "#ff00ff";
  ctx.beginPath();
  ctx.arc(x + GRID / 2, y + GRID / 2, GRID / 3, 0, Math.PI * 2);
  ctx.fill();
  
  // Brillo
  ctx.fillStyle = "#ffffff";
  ctx.beginPath();
  ctx.arc(x + GRID / 2 - 2, y + GRID / 2 - 2, 3, 0, Math.PI * 2);
  ctx.fill();
}

function drawObstacles() {
  state.obstacles.forEach(obs => {
    const x = obs.x * GRID;
    const y = obs.y * GRID;
    
    ctx.fillStyle = "#8a2be2";
    ctx.fillRect(x + 1, y + 1, GRID - 2, GRID - 2);
    
    // Patrón de peligro
    ctx.strokeStyle = "#ff00ff";
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineTo(x + GRID, y + GRID);
    ctx.moveTo(x + GRID, y);
    ctx.lineTo(x, y + GRID);
    ctx.stroke();
    
    ctx.strokeStyle = "#aa00ff";
    ctx.strokeRect(x + 1, y + 1, GRID - 2, GRID - 2);
  });
}

function drawHUD() {
  // Fondo del HUD
  ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
  ctx.fillRect(10, 10, 280, 80);
  
  // Borde brillante
  ctx.strokeStyle = "#00ff88";
  ctx.lineWidth = 2;
  ctx.strokeRect(10, 10, 280, 80);
  
  ctx.fillStyle = "#00ff88";
  ctx.font = "bold 16px Orbitron";
  ctx.fillText(`LEVEL: ${state.level}`, 20, 30);
  ctx.fillText(`SCORE: ${state.score}`, 20, 50);
  ctx.fillText(`LENGTH: ${state.snake.length}`, 20, 70);
  
  // Objetivo
  ctx.fillStyle = "#ff00ff";
  ctx.font = "bold 14px Orbitron";
  ctx.fillText(`TARGET: ${state.targetScore}`, 150, 50);
}

function draw() {
  // Fondo oscuro
  ctx.fillStyle = "#050817";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  drawGrid();
  drawObstacles();
  drawFood();
  drawSnake();
  drawHUD();
}

function update(timestamp) {
  if (!state.running) return;
  
  if (timestamp - state.lastUpdate < state.speed) {
    requestAnimationFrame(update);
    return;
  }
  
  state.lastUpdate = timestamp;
  
  // Actualizar dirección
  state.direction = { ...state.nextDirection };
  
  // Nueva posición de la cabeza
  const head = { ...state.snake[0] };
  head.x += state.direction.x;
  head.y += state.direction.y;
  
  // Colisión con bordes
  if (head.x < 0 || head.x >= COLS || head.y < 0 || head.y >= ROWS) {
    endGame(false, "💥 Crashed into wall!");
    return;
  }
  
  // Colisión consigo misma
  for (let seg of state.snake) {
    if (seg.x === head.x && seg.y === head.y) {
      endGame(false, "💥 Crashed into yourself!");
      return;
    }
  }
  
  // Colisión con obstáculos
  for (let obs of state.obstacles) {
    if (obs.x === head.x && obs.y === head.y) {
      endGame(false, "💥 Hit an obstacle!");
      return;
    }
  }
  
  // Agregar nueva cabeza
  state.snake.unshift(head);
  
  // Comer comida
  if (head.x === state.food.x && head.y === state.food.y) {
    state.score += 10;
    state.foodEaten++;
    spawnFood();
    
    // Victoria si alcanza el objetivo
    if (state.score >= state.targetScore) {
      endGame(true, "🏆 Level Complete!");
      return;
    }
  } else {
    // Quitar cola si no comió
    state.snake.pop();
  }
  
  draw();
  requestAnimationFrame(update);
}

function endGame(won, message) {
  state.running = false;
  msgEl.textContent = message || (won ? "🏆 Victory!" : "💀 Game Over");
  
  const timeElapsed = Math.round((Date.now() - state.startTime) / 1000);
  
  if (usuari_id && joc_id) {
    fetch("/api/api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "save_game",
        usuari_id: usuari_id,
        joc_id: joc_id,
        nivell: state.level,
        puntuacio: state.score,
        durada: timeElapsed,
        guanyat: won ? 1 : 0
      })
    }).then(r => r.json()).then(data => {
      if (data.success) msgEl.textContent += " — Game Saved";
    }).catch(() => {
      msgEl.textContent += " — Save Error";
    });
  }
}

function startGame() {
  state.level = parseInt(levelSelect.value, 10);
  const config = LEVELS[state.level];
  if (!config) {
    msgEl.textContent = "Error: Level config not found";
    return;
  }
  resetGame();
  state.running = true;
  state.lastUpdate = performance.now();
  requestAnimationFrame(update);
}

// Controles - CORREGIDO
window.addEventListener("keydown", e => {
  if (!state.running) return;
  
  // Prevenir movimiento opuesto a la dirección actual
  if (e.key === "ArrowLeft" && state.direction.x === 0) {
    state.nextDirection = { x: -1, y: 0 };
    e.preventDefault();
  }
  else if (e.key === "ArrowRight" && state.direction.x === 0) {
    state.nextDirection = { x: 1, y: 0 };
    e.preventDefault();
  }
  else if (e.key === "ArrowUp" && state.direction.y === 0) {
    state.nextDirection = { x: 0, y: -1 };
    e.preventDefault();
  }
  else if (e.key === "ArrowDown" && state.direction.y === 0) {
    state.nextDirection = { x: 0, y: 1 };
    e.preventDefault();
  }
});

startBtn.addEventListener("click", startGame);
draw();
</script>
</body>
</html>