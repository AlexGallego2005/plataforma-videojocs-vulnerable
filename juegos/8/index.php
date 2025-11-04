<?php
session_start();

// Incluimos el modelo si existe
if (file_exists(__DIR__ . '/../../secret/games_model.php')) {
    require_once __DIR__ . '/../../secret/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 8; // Nuevo ID para este juego

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
<title>Pixel Runner</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/jpg" href="assets/helmet.png"/>
<style>
body {
  margin: 0; 
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
  display:flex; 
  justify-content:center; 
  align-items:center;
  min-height:100vh; 
  color:white; 
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.container { 
  width:960px; 
  max-width:98vw; 
  padding:20px; 
  background: rgba(0,0,0,0.3);
  border-radius: 16px;
  backdrop-filter: blur(10px);
}
.header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:16px;
  padding-bottom:12px;
  border-bottom: 2px solid rgba(255,255,255,0.2);
}
.title {
  font-size: 24px;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}
.controls {
  display: flex;
  gap: 10px;
  align-items: center;
}
select,button{
  background: rgba(255,255,255,0.1);
  color:white;
  border:2px solid rgba(255,255,255,0.3);
  padding:8px 16px;
  border-radius:8px;
  cursor:pointer;
  font-weight: 600;
  transition: all 0.3s;
}
button:hover {
  background: rgba(255,255,255,0.2);
  transform: translateY(-2px);
}
canvas{
  background: linear-gradient(180deg, #87ceeb 0%, #e0f6ff 100%);
  border-radius:12px;
  display:block;
  width:100%;
  height:480px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
  border: 3px solid rgba(255,255,255,0.3);
}
#message{
  margin-top:12px;
  font-size:16px;
  color:#fef08a;
  font-weight: 600;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
  min-height: 24px;
}
.info {
  margin-top:12px;
  font-size:13px;
  color:rgba(255,255,255,0.7);
}
.instructions {
  background: rgba(0,0,0,0.2);
  padding: 12px;
  border-radius: 8px;
  margin-top: 12px;
  font-size: 14px;
  line-height: 1.6;
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="title">🏃 Pixel Runner</div>
    <div class="controls">
      <select id="levelSelect">
        <?php foreach ($nivells as $lvl): ?>
          <option value="<?= htmlspecialchars($lvl['nivell']) ?>">Nivel <?= $lvl['nivell'] ?> — <?= htmlspecialchars($lvl['nom_nivell']) ?></option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn">Iniciar Juego</button>
    </div>
  </div>
  <canvas id="game" width="900" height="480"></canvas>
  <div id="message"></div>
  <div class="instructions">
    <strong>Controles:</strong> Usa las flechas ← → para moverte y ESPACIO para saltar. Recoge todas las monedas y evita los obstáculos. ¡Llega a la meta!
  </div>
  <div class="info">
    <?php if ($usuari_id): ?>
      🎮 Sesión activa — usuario #<?= htmlspecialchars($usuari_id) ?>, juego #<?= $joc_id ?>
    <?php else: ?>
      ⚠️ No logueado — las partidas no se guardarán.
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

// Estado del juego
let state = {
  running: false,
  level: 1,
  score: 0,
  coins: [],
  obstacles: [],
  platforms: [],
  startTime: 0,
  player: {
    x: 50,
    y: 300,
    w: 32,
    h: 32,
    vx: 0,
    vy: 0,
    speed: 5,
    jumpPower: 12,
    grounded: false
  },
  goal: { x: 0, y: 0, w: 40, h: 60 },
  gravity: 0.5,
  coinsCollected: 0,
  totalCoins: 0
};

let rafId = null;
let keys = { left: false, right: false, space: false, spacePressed: false };

// Cargar configuraciones dinámicas de niveles
const LEVELS = {};
<?php foreach ($nivells as $lvl): ?>
LEVELS[<?= (int)$lvl['nivell'] ?>] = <?= $lvl['configuracio_json'] ?>;
<?php endforeach; ?>

function generateLevel() {
  const config = LEVELS[state.level];
  if (!config) return;

  state.platforms = [];
  state.coins = [];
  state.obstacles = [];
  
  // Plataforma base
  state.platforms.push({ x: 0, y: 420, w: 900, h: 60 });
  
  // Generar plataformas según configuración
  const platformCount = config.platformCount || 8;
  for (let i = 0; i < platformCount; i++) {
    const x = 150 + i * (700 / platformCount);
    const y = 350 - Math.random() * 200;
    const w = 80 + Math.random() * 60;
    state.platforms.push({ x, y, w, h: 20 });
  }
  
  // Generar monedas
  const coinCount = config.coinCount || 10;
  for (let i = 0; i < coinCount; i++) {
    const x = 100 + Math.random() * 750;
    const y = 100 + Math.random() * 250;
    state.coins.push({ x, y, r: 12, collected: false });
  }
  
  // Generar obstáculos
  const obstacleCount = config.obstacleCount || 5;
  for (let i = 0; i < obstacleCount; i++) {
    const x = 200 + Math.random() * 600;
    state.obstacles.push({ x, y: 390, w: 30, h: 30 });
  }
  
  // Meta al final
  state.goal = { x: 840, y: 360, w: 40, h: 60 };
  
  state.totalCoins = state.coins.length;
  state.coinsCollected = 0;
}

function resetGame() {
  state.score = 0;
  state.startTime = Date.now();
  state.player.x = 50;
  state.player.y = 300;
  state.player.vx = 0;
  state.player.vy = 0;
  state.player.grounded = false;
  msgEl.textContent = '';
  
  const config = LEVELS[state.level];
  if (!config) return;
  
  state.player.speed = config.playerSpeed || 5;
  state.player.jumpPower = config.jumpPower || 12;
  state.gravity = config.gravity || 0.5;
  
  generateLevel();
}

function drawPlayer() {
  // Cuerpo
  ctx.fillStyle = "#ef4444";
  ctx.fillRect(state.player.x, state.player.y, state.player.w, state.player.h);
  // Ojos
  ctx.fillStyle = "#ffffff";
  ctx.fillRect(state.player.x + 8, state.player.y + 8, 6, 6);
  ctx.fillRect(state.player.x + 18, state.player.y + 8, 6, 6);
  ctx.fillStyle = "#000000";
  ctx.fillRect(state.player.x + 10, state.player.y + 10, 3, 3);
  ctx.fillRect(state.player.x + 20, state.player.y + 10, 3, 3);
}

function drawPlatforms() {
  state.platforms.forEach(p => {
    ctx.fillStyle = "#10b981";
    ctx.fillRect(p.x, p.y, p.w, p.h);
    // Textura simple
    ctx.strokeStyle = "#059669";
    ctx.lineWidth = 2;
    for (let i = 0; i < p.w; i += 20) {
      ctx.beginPath();
      ctx.moveTo(p.x + i, p.y);
      ctx.lineTo(p.x + i, p.y + p.h);
      ctx.stroke();
    }
  });
}

function drawCoins() {
  state.coins.forEach(c => {
    if (!c.collected) {
      ctx.fillStyle = "#fbbf24";
      ctx.beginPath();
      ctx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = "#f59e0b";
      ctx.lineWidth = 3;
      ctx.stroke();
      // Brillo
      ctx.fillStyle = "#fef3c7";
      ctx.beginPath();
      ctx.arc(c.x - 3, c.y - 3, 4, 0, Math.PI * 2);
      ctx.fill();
    }
  });
}

function drawObstacles() {
  state.obstacles.forEach(o => {
    ctx.fillStyle = "#6b21a8";
    ctx.fillRect(o.x, o.y, o.w, o.h);
    // Detalles de peligro
    ctx.strokeStyle = "#a855f7";
    ctx.lineWidth = 2;
    ctx.strokeRect(o.x + 3, o.y + 3, o.w - 6, o.h - 6);
  });
}

function drawGoal() {
  // Bandera
  ctx.fillStyle = "#22c55e";
  ctx.fillRect(state.goal.x, state.goal.y, 5, state.goal.h);
  ctx.fillStyle = "#4ade80";
  ctx.beginPath();
  ctx.moveTo(state.goal.x + 5, state.goal.y);
  ctx.lineTo(state.goal.x + state.goal.w, state.goal.y + 15);
  ctx.lineTo(state.goal.x + 5, state.goal.y + 30);
  ctx.fill();
}

function drawSky() {
  // Cielo con gradiente
  const gradient = ctx.createLinearGradient(0, 0, 0, 480);
  gradient.addColorStop(0, "#87ceeb");
  gradient.addColorStop(1, "#e0f6ff");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, 900, 480);
  
  // Nubes
  ctx.fillStyle = "rgba(255, 255, 255, 0.6)";
  drawCloud(100, 60, 60);
  drawCloud(400, 100, 80);
  drawCloud(700, 50, 70);
}

function drawCloud(x, y, size) {
  ctx.beginPath();
  ctx.arc(x, y, size * 0.5, 0, Math.PI * 2);
  ctx.arc(x + size * 0.4, y - size * 0.2, size * 0.4, 0, Math.PI * 2);
  ctx.arc(x + size * 0.8, y, size * 0.5, 0, Math.PI * 2);
  ctx.fill();
}

function drawHUD() {
  ctx.fillStyle = "rgba(0, 0, 0, 0.5)";
  ctx.fillRect(10, 10, 250, 60);
  ctx.fillStyle = "#ffffff";
  ctx.font = "bold 16px 'Segoe UI'";
  ctx.fillText(`Nivel: ${state.level}`, 20, 30);
  ctx.fillText(`Monedas: ${state.coinsCollected}/${state.totalCoins}`, 20, 50);
  ctx.fillText(`Puntos: ${state.score}`, 20, 70);
}

function draw() {
  drawSky();
  drawPlatforms();
  drawCoins();
  drawObstacles();
  drawGoal();
  drawPlayer();
  drawHUD();
}

function checkCollision(rect1, rect2) {
  return rect1.x < rect2.x + rect2.w &&
         rect1.x + rect1.w > rect2.x &&
         rect1.y < rect2.y + rect2.h &&
         rect1.y + rect1.h > rect2.y;
}

function update() {
  if (!state.running) return;
  
  const p = state.player;
  
  // Movimiento horizontal
  if (keys.left) p.vx = -p.speed;
  else if (keys.right) p.vx = p.speed;
  else p.vx *= 0.8; // Fricción
  
  // Salto
  if (keys.space && !keys.spacePressed && p.grounded) {
    p.vy = -p.jumpPower;
    p.grounded = false;
    keys.spacePressed = true;
  }
  
  // Gravedad
  p.vy += state.gravity;
  
  // Aplicar velocidades
  p.x += p.vx;
  p.y += p.vy;
  
  // Límites laterales
  if (p.x < 0) p.x = 0;
  if (p.x + p.w > 900) p.x = 900 - p.w;
  
  // Colisión con plataformas
  p.grounded = false;
  state.platforms.forEach(platform => {
    if (checkCollision(p, platform)) {
      // Aterrizaje desde arriba
      if (p.vy > 0 && p.y + p.h - p.vy < platform.y + 10) {
        p.y = platform.y - p.h;
        p.vy = 0;
        p.grounded = true;
      }
    }
  });
  
  // Recoger monedas
  state.coins.forEach(coin => {
    if (!coin.collected) {
      const dx = p.x + p.w / 2 - coin.x;
      const dy = p.y + p.h / 2 - coin.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < coin.r + p.w / 2) {
        coin.collected = true;
        state.coinsCollected++;
        state.score += 50;
      }
    }
  });
  
  // Colisión con obstáculos
  for (let obs of state.obstacles) {
    if (checkCollision(p, obs)) {
      endGame(false, "¡Chocaste con un obstáculo!");
      return;
    }
  }
  
  // Caída al vacío
  if (p.y > 480) {
    endGame(false, "¡Caíste al vacío!");
    return;
  }
  
  // Llegada a la meta
  if (checkCollision(p, state.goal)) {
    if (state.coinsCollected === state.totalCoins) {
      endGame(true, "¡Nivel completado! ¡Todas las monedas recogidas!");
    } else {
      msgEl.textContent = `¡Recoge todas las monedas! (${state.coinsCollected}/${state.totalCoins})`;
    }
  }
  
  draw();
  rafId = requestAnimationFrame(update);
}

function endGame(won, message) {
  state.running = false;
  cancelAnimationFrame(rafId);
  msgEl.textContent = message || (won ? "¡Victoria!" : "Game Over");
  
  const timeElapsed = Math.round((Date.now() - state.startTime) / 1000);
  
  if (usuari_id && joc_id) {
    fetch("../../api.php", {
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
      if (data.success) msgEl.textContent += " — Partida guardada.";
    }).catch(() => {
      msgEl.textContent += " — Error al guardar.";
    });
  }
}

function startGame() {
  state.level = parseInt(levelSelect.value, 10);
  const config = LEVELS[state.level];
  if (!config) {
    msgEl.textContent = "Error: configuración de nivel no encontrada.";
    return;
  }
  resetGame();
  state.running = true;
  cancelAnimationFrame(rafId);
  rafId = requestAnimationFrame(update);
}

// Controles
window.addEventListener("keydown", e => {
  if (e.key === "ArrowLeft") keys.left = true;
  if (e.key === "ArrowRight") keys.right = true;
  if (e.key === " ") {
    e.preventDefault();
    keys.space = true;
  }
});

window.addEventListener("keyup", e => {
  if (e.key === "ArrowLeft") keys.left = false;
  if (e.key === "ArrowRight") keys.right = false;
  if (e.key === " ") {
    keys.space = false;
    keys.spacePressed = false;
  }
});

startBtn.addEventListener("click", startGame);
draw();
</script>
</body>
</html>