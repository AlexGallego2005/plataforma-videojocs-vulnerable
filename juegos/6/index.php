<?php
session_start();

// Incluimos el modelo si existe
if (file_exists(__DIR__ . '/../../secret/games_model.php')) {
    require_once __DIR__ . '/../../secret/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 6;

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
<title>Space Blaster</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root {
  --bg: #050b1a;
  --ship: #4ade80;
  --enemy: #f87171;
  --bullet: #60a5fa;
}
body {
  margin: 0; background: var(--bg); display:flex; justify-content:center; align-items:center;
  height:100vh; color:white; font-family: Inter, Arial, sans-serif;
}
.container { width:960px; max-width:98vw; padding:18px; }
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
select,button{background:#0d1428;color:white;border:1px solid rgba(255,255,255,0.1);padding:6px 10px;border-radius:6px;cursor:pointer}
canvas{background:#0a1022;border-radius:8px;display:block;width:100%;height:520px;box-shadow:0 0 20px #000 inset;}
#message{margin-top:6px;font-size:14px;color:#9ee6c9}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div><strong>Space Blaster</strong> — Destruye a todos los enemigos</div>
    <div>
      Nivel:
      <select id="levelSelect">
        <?php foreach ($nivells as $lvl): ?>
          <option value="<?= htmlspecialchars($lvl['nivell']) ?>">Nivel <?= $lvl['nivell'] ?> — <?= htmlspecialchars($lvl['nom_nivell']) ?></option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn">Iniciar</button>
    </div>
  </div>
  <canvas id="game" width="900" height="520"></canvas>
  <div id="message"></div>
  <div style="margin-top:10px;font-size:13px;color:#9ca3af">
    <?php if ($usuari_id): ?>
      Sesión activa — usuario #<?= htmlspecialchars($usuari_id) ?>, juego #<?= $joc_id ?>
    <?php else: ?>
      No logueado — las partidas no se guardarán.
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

let state = { running: false, level: 1, enemies: [], bullets: [], score: 0, startTime: 0, player: {x:450,y:460,w:24,h:24,speed:5} };
let rafId=null;
let keys={left:false,right:false,space:false};
const usuari_id = <?= json_encode($usuari_id) ?>;
const joc_id = <?= json_encode($joc_id) ?>;

// Cargar configuraciones dinámicas de niveles
const LEVELS = {};
<?php foreach ($nivells as $lvl): ?>
LEVELS[<?= (int)$lvl['nivell'] ?>] = <?= $lvl['configuracio_json'] ?>;
<?php endforeach; ?>

function resetGame(){
  state.enemies=[];
  state.bullets=[];
  state.score=0;
  state.startTime=Date.now();
  state.player.x=450;
  state.player.y=460;
  msgEl.textContent='';
  
  const levelConfig = LEVELS[state.level];
  if(!levelConfig) return;
  
  for(let i=0;i<levelConfig.enemyCount;i++){
    state.enemies.push({x:Math.random()*860+20,y:Math.random()*120+20,w:28,h:18,vy:levelConfig.enemySpeed});
  }
}

function draw(){
  ctx.clearRect(0,0,900,520);
  // Fondo
  ctx.fillStyle="#060d1f";ctx.fillRect(0,0,900,520);
  // Jugador
  ctx.fillStyle="#4ade80";
  ctx.fillRect(state.player.x,state.player.y,state.player.w,state.player.h);
  // Enemigos
  ctx.fillStyle="#f87171";
  state.enemies.forEach(e=>ctx.fillRect(e.x,e.y,e.w,e.h));
  // Balas
  ctx.fillStyle="#60a5fa";
  state.bullets.forEach(b=>ctx.fillRect(b.x,b.y,b.w,b.h));
  // Puntuación
  ctx.fillStyle="#dbeafe";
  ctx.font="16px Inter";
  ctx.fillText("Puntuación: "+state.score,20,24);
  ctx.fillText("Nivel "+state.level,780,24);
}

function update(ts){
  if(!state.running)return;
  
  const levelConfig = LEVELS[state.level];
  if(!levelConfig){
    endGame(false);
    return;
  }
  
  // Movimiento jugador
  if(keys.left) state.player.x -= state.player.speed;
  if(keys.right) state.player.x += state.player.speed;
  state.player.x=Math.max(0,Math.min(900-state.player.w,state.player.x));
  
  // Balas
  state.bullets.forEach(b=>b.y-=levelConfig.bulletSpeed);
  state.bullets=state.bullets.filter(b=>b.y>-10);
  
  // Enemigos
  state.enemies.forEach(e=>{e.y+=e.vy;if(e.y>520)e.y=-20});
  
  // Colisiones balas-enemigos
  for(let bi=state.bullets.length-1; bi>=0; bi--){
    const b = state.bullets[bi];
    for(let ei=state.enemies.length-1; ei>=0; ei--){
      const e = state.enemies[ei];
      if(b.x<e.x+e.w && b.x+b.w>e.x && b.y<e.y+e.h && b.y+b.h>e.y){
        state.enemies.splice(ei,1);
        state.bullets.splice(bi,1);
        state.score+=10;
        break;
      }
    }
  }
  
  // Colisión jugador-enemigos
  for(let e of state.enemies){
    if(state.player.x<e.x+e.w && state.player.x+state.player.w>e.x && state.player.y<e.y+e.h && state.player.y+state.player.h>e.y){
      endGame(false);
      return;
    }
  }
  
  // Victoria
  if(state.enemies.length===0){
    endGame(true);
    return;
  }
  
  draw();
  rafId=requestAnimationFrame(update);
}

function endGame(won){
  state.running=false;
  cancelAnimationFrame(rafId);
  msgEl.textContent=won?"¡Has ganado!":"¡Has sido destruido!";
  
  const timeElapsed = Math.round((Date.now() - state.startTime) / 1000);
  
  if(usuari_id && joc_id){
    fetch("../../api.php",{
      method:"POST",headers:{"Content-Type":"application/json"},
      body:JSON.stringify({
        action:"save_game",
        usuari_id:usuari_id,
        joc_id:joc_id,
        nivell:state.level,
        puntuacio:state.score,
        durada:timeElapsed,
        guanyat:won?1:0
      })
    }).then(r=>r.json()).then(data=>{
      if(data.success) msgEl.textContent += " — Partida guardada.";
    }).catch(()=>{msgEl.textContent+=" — Error al guardar.";});
  }
}

function startGame(){
  state.level=parseInt(levelSelect.value,10);
  const conf=LEVELS[state.level];
  if(!conf){
    msgEl.textContent="Error: configuración de nivel no encontrada.";
    return;
  }
  state.player.speed=conf.playerSpeed;
  resetGame();
  state.running=true;
  draw();
  cancelAnimationFrame(rafId);
  rafId=requestAnimationFrame(update);
}

window.addEventListener("keydown",e=>{
  if(e.key==="ArrowLeft")keys.left=true;
  if(e.key==="ArrowRight")keys.right=true;
  if(e.key===" "){
    e.preventDefault();
    if(!keys.space && state.running){
      keys.space=true;
      state.bullets.push({x:state.player.x+state.player.w/2-2,y:state.player.y-10,w:4,h:10});
    }
  }
});

window.addEventListener("keyup",e=>{
  if(e.key==="ArrowLeft")keys.left=false;
  if(e.key==="ArrowRight")keys.right=false;
  if(e.key===" ")keys.space=false;
});

startBtn.addEventListener("click",startGame);
draw();
</script>
</body>
</html>