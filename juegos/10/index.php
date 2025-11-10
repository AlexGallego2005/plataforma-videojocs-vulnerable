<?php
session_start();

// Incluimos el modelo si existe
if (file_exists(__DIR__ . '/../../api/games_model.php')) {
    require_once __DIR__ . '/../../api/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 10; // ID para el juego de Memory

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
<title>Neon Memory</title>
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

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.game-area {
  background: #050817;
  border-radius: 12px;
  padding: 30px;
  min-height: 500px;
  box-shadow: 0 0 40px rgba(0, 255, 136, 0.4) inset,
              0 0 60px rgba(0, 0, 0, 0.8);
  border: 3px solid rgba(0, 255, 136, 0.4);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.stats {
  display: flex;
  gap: 40px;
  margin-bottom: 30px;
  justify-content: center;
}

.stat-item {
  background: rgba(0, 0, 0, 0.5);
  padding: 15px 30px;
  border-radius: 10px;
  border: 2px solid rgba(0, 255, 136, 0.3);
  text-align: center;
}

.stat-label {
  font-size: 11px;
  color: rgba(0, 255, 136, 0.6);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 5px;
}

.stat-value {
  font-size: 24px;
  font-weight: 900;
  color: #00ff88;
  text-shadow: 0 0 10px rgba(0, 255, 136, 0.8);
}

.grid {
  display: grid;
  gap: 15px;
  margin: 0 auto;
  perspective: 1000px;
}

.card {
  aspect-ratio: 1;
  background: rgba(0, 255, 136, 0.1);
  border: 3px solid rgba(0, 255, 136, 0.3);
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  transform-style: preserve-3d;
  transition: transform 0.6s, box-shadow 0.3s;
}

.card:hover:not(.flipped):not(.matched) {
  box-shadow: 0 0 25px rgba(0, 255, 136, 0.6);
  transform: scale(1.05);
}

.card.flipped {
  transform: rotateY(180deg);
}

.card.matched {
  border-color: #ff00ff;
  animation: matchPulse 0.6s ease-out;
}

@keyframes matchPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); box-shadow: 0 0 40px rgba(255, 0, 255, 1); }
  100% { transform: scale(1); }
}

.card-face {
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  backface-visibility: hidden;
  border-radius: 10px;
  font-size: 48px;
}

.card-back {
  background: linear-gradient(135deg, rgba(0, 255, 136, 0.2), rgba(0, 212, 255, 0.2));
}

.card-back::before {
  content: '?';
  font-size: 56px;
  font-weight: 900;
  color: rgba(0, 255, 136, 0.5);
  text-shadow: 0 0 20px rgba(0, 255, 136, 0.8);
}

.card-front {
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.3), rgba(255, 0, 255, 0.3));
  transform: rotateY(180deg);
  font-size: 52px;
}

#message {
  margin-top: 20px;
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
    <div class="title">🧠 Neon Memory</div>
    <div class="controls">
      <select id="levelSelect">
        <?php foreach ($nivells as $lvl): ?>
          <option value="<?= htmlspecialchars($lvl['nivell']) ?>">Level <?= $lvl['nivell'] ?> — <?= htmlspecialchars($lvl['nom_nivell']) ?></option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn">Start Game</button>
    </div>
  </div>
  
  <div class="game-area">
    <div class="stats">
      <div class="stat-item">
        <div class="stat-label">Level</div>
        <div class="stat-value" id="levelDisplay">-</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Moves</div>
        <div class="stat-value" id="movesDisplay">0</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Matches</div>
        <div class="stat-value" id="matchesDisplay">0</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Time</div>
        <div class="stat-value" id="timeDisplay">0:00</div>
      </div>
    </div>
    
    <div id="grid" class="grid"></div>
  </div>
  
  <div id="message"></div>
  
  <div class="instructions">
    <strong>🎮 HOW TO PLAY:</strong> Haz clic en las cartas para voltearlas y encontrar los pares. Intenta completar el tablero con el menor número de movimientos posible.
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

const usuari_id = <?= json_encode($usuari_id) ?>;
const joc_id = <?= json_encode($joc_id) ?>;

const startBtn = document.getElementById("startBtn");
const msgEl = document.getElementById("message");
const levelSelect = document.getElementById("levelSelect");
const gridEl = document.getElementById("grid");
const levelDisplay = document.getElementById("levelDisplay");
const movesDisplay = document.getElementById("movesDisplay");
const matchesDisplay = document.getElementById("matchesDisplay");
const timeDisplay = document.getElementById("timeDisplay");

// Emojis para las cartas
const EMOJI_SETS = [
  ['🌟', '🔥', '⚡', '💎', '🌈', '🎵', '🎮', '🚀'],
  ['🍎', '🍌', '🍇', '🍊', '🍓', '🍒', '🍑', '🥝'],
  ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼'],
  ['⭐', '💫', '✨', '🌙', '☀️', '🌍', '🪐', '🌌'],
  ['🎨', '🎭', '🎪', '🎬', '🎤', '🎸', '🎹', '🎺']
];

let state = {
  running: false,
  level: 1,
  moves: 0,
  matches: 0,
  targetMatches: 0,
  startTime: 0,
  timerInterval: null,
  cards: [],
  flippedCards: [],
  canFlip: true
};

// Cargar configuraciones dinámicas de niveles
const LEVELS = {};
<?php foreach ($nivells as $lvl): ?>
LEVELS[<?= (int)$lvl['nivell'] ?>] = <?= $lvl['configuracio_json'] ?>;
<?php endforeach; ?>

function shuffle(array) {
  const arr = [...array];
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

function resetGame() {
  state.moves = 0;
  state.matches = 0;
  state.flippedCards = [];
  state.canFlip = true;
  state.startTime = Date.now();
  msgEl.textContent = '';
  
  const config = LEVELS[state.level];
  if (!config) {
    msgEl.textContent = "Error: Level config not found";
    return;
  }
  
  const pairs = config.pairs || 8;
  state.targetMatches = pairs;
  
  const gridSize = config.gridSize || 4;
  gridEl.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
  
  // Ajustar tamaño de cartas según el grid
  const cardSize = Math.min(100, 600 / gridSize);
  gridEl.style.width = `${cardSize * gridSize + 15 * (gridSize - 1)}px`;
  
  // Seleccionar set de emojis aleatorio
  const emojiSet = EMOJI_SETS[Math.floor(Math.random() * EMOJI_SETS.length)];
  const selectedEmojis = emojiSet.slice(0, pairs);
  
  // Crear pares y mezclar
  const cardValues = [...selectedEmojis, ...selectedEmojis];
  const shuffledValues = shuffle(cardValues);
  
  state.cards = shuffledValues.map((emoji, index) => ({
    id: index,
    emoji: emoji,
    flipped: false,
    matched: false
  }));
  
  renderGrid();
  updateDisplay();
  startTimer();
}

function renderGrid() {
  gridEl.innerHTML = '';
  
  state.cards.forEach(card => {
    const cardEl = document.createElement('div');
    cardEl.className = 'card';
    cardEl.dataset.id = card.id;
    
    const cardBack = document.createElement('div');
    cardBack.className = 'card-face card-back';
    
    const cardFront = document.createElement('div');
    cardFront.className = 'card-face card-front';
    cardFront.textContent = card.emoji;
    
    cardEl.appendChild(cardBack);
    cardEl.appendChild(cardFront);
    
    cardEl.addEventListener('click', () => handleCardClick(card.id));
    
    gridEl.appendChild(cardEl);
  });
}

function handleCardClick(cardId) {
  if (!state.running || !state.canFlip) return;
  
  const card = state.cards[cardId];
  if (card.flipped || card.matched) return;
  
  // Voltear carta
  card.flipped = true;
  state.flippedCards.push(card);
  updateCardVisual(cardId);
  
  if (state.flippedCards.length === 2) {
    state.canFlip = false;
    state.moves++;
    updateDisplay();
    
    setTimeout(() => checkMatch(), 800);
  }
}

function checkMatch() {
  const [card1, card2] = state.flippedCards;
  
  if (card1.emoji === card2.emoji) {
    // Match!
    card1.matched = true;
    card2.matched = true;
    state.matches++;
    
    updateCardVisual(card1.id);
    updateCardVisual(card2.id);
    updateDisplay();
    
    // Comprobar victoria
    if (state.matches === state.targetMatches) {
      setTimeout(() => endGame(true), 500);
    }
  } else {
    // No match
    card1.flipped = false;
    card2.flipped = false;
    
    updateCardVisual(card1.id);
    updateCardVisual(card2.id);
  }
  
  state.flippedCards = [];
  state.canFlip = true;
}

function updateCardVisual(cardId) {
  const card = state.cards[cardId];
  const cardEl = document.querySelector(`[data-id="${cardId}"]`);
  
  if (card.matched) {
    cardEl.classList.add('matched');
  }
  
  if (card.flipped) {
    cardEl.classList.add('flipped');
  } else {
    cardEl.classList.remove('flipped');
  }
}

function updateDisplay() {
  levelDisplay.textContent = state.level;
  movesDisplay.textContent = state.moves;
  matchesDisplay.textContent = `${state.matches}/${state.targetMatches}`;
}

function startTimer() {
  if (state.timerInterval) clearInterval(state.timerInterval);
  
  state.timerInterval = setInterval(() => {
    if (!state.running) return;
    
    const elapsed = Math.floor((Date.now() - state.startTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
  }, 1000);
}

function endGame(won) {
  state.running = false;
  if (state.timerInterval) clearInterval(state.timerInterval);
  
  const timeElapsed = Math.floor((Date.now() - state.startTime) / 1000);
  const score = won ? Math.max(1000 - state.moves * 10 - timeElapsed, 100) : 0;
  
  msgEl.textContent = won 
    ? `🏆 Level Complete! Score: ${score} (${state.moves} moves, ${timeElapsed}s)`
    : "💀 Game Over";
  
  if (usuari_id && joc_id) {
    fetch("/api/api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "save_game",
        usuari_id: usuari_id,
        joc_id: joc_id,
        nivell: state.level,
        puntuacio: score,
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
}

startBtn.addEventListener("click", startGame);

// Mostrar pantalla inicial
levelDisplay.textContent = '-';
movesDisplay.textContent = '0';
matchesDisplay.textContent = '0/0';
timeDisplay.textContent = '0:00';
</script>
</body>
</html>