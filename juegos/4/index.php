<?php
session_start();

if (file_exists(__DIR__ . '/../../secret/games_model.php')) {
    require_once __DIR__ . '/../../secret/games_model.php';
}

$usuari_id = $_SESSION['usuari_id'] ?? null;
$joc_id = 4;

// Cargamos niveles desde la base de datos
$nivells = function_exists('getGameLevels') ? getGameLevels($joc_id) : [];
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Pong - Niveles dinámicos</title>
<style>
:root{--bg:#0b1220;--card:#0f1724;--accent:#58a6ff;--muted:#9aa7b2}
*{box-sizing:border-box}
body{margin:0;background:linear-gradient(180deg,var(--bg),#071026);color:#cbd5e1;font-family:Inter,Segoe UI,Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh}
.container{width:980px;max-width:98vw;background:rgba(255,255,255,0.02);padding:18px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.6)}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.title{font-size:18px;font-weight:600}
.controls{display:flex;gap:8px;align-items:center}
select,button,input[type=number]{background:#0b1116;color:#e6eef6;border:1px solid rgba(255,255,255,0.04);padding:6px 8px;border-radius:6px}
canvas{display:block;background:#071226;border-radius:6px;width:100%;height:520px;max-height:70vh}
.info{display:flex;gap:12px;align-items:center;margin-top:10px}
.stat{background:rgba(255,255,255,0.02);padding:8px;border-radius:8px;font-size:13px}
.small{font-size:12px;color:var(--muted)}
.footer{margin-top:10px;font-size:13px;color:var(--muted)}
.btn-primary{background:var(--accent);border:none;color:#021020;padding:8px 10px;border-radius:8px;cursor:pointer}
.btn-ghost{background:transparent;border:1px solid rgba(255,255,255,0.03);color:#cbd5e1;padding:6px 8px;border-radius:8px;cursor:pointer}
#message{margin-left:8px;color:#a7f3d0}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="title">Pong — <?= count($nivells) ?> niveles</div>
    <div class="controls">
      <label class="small">Nivel</label>
      <select id="levelSelect">
        <?php foreach ($nivells as $n): 
          $cfg = json_decode($n['configuracio_json'], true) ?? [];
          $nombre = htmlspecialchars($n['nom'] ?? ("Nivel {$n['nivell']}"));
        ?>
          <option value="<?= (int)$n['nivell'] ?>">
            <?= $nombre ?><?= isset($cfg['dificultad']) ? " — " . ucfirst($cfg['dificultad']) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button id="startBtn" class="btn-primary">Iniciar</button>
      <button id="resetBtn" class="btn-ghost">Reiniciar</button>
    </div>
  </div>

  <canvas id="gameCanvas" width="900" height="520"></canvas>

  <div class="info">
    <div class="stat">Jugador: <span id="playerScore">0</span></div>
    <div class="stat">IA: <span id="aiScore">0</span></div>
    <div class="stat">Tiempo: <span id="timeElapsed">0.0s</span></div>
    <div class="stat small">Controles: W/S o flechas ↑↓</div>
    <div id="message" class="small"></div>
  </div>

  <div class="footer small">
    Al terminar, si estás logueado la partida se intentará guardar usando la API del servidor.
    <?php if ($usuari_id): ?>
      Sesión: usuario #<?= htmlspecialchars($usuari_id) ?><?php if ($joc_id) echo " — juego #{$joc_id}"; ?>
    <?php else: ?>
      No logueado: la partida no se guardará (opcionalmente inicia sesión).
    <?php endif; ?>
  </div>
</div>

<script>
"use strict";

const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const scoreEl = document.getElementById('playerScore');
const aiScoreEl = document.getElementById('aiScore');
const timeEl = document.getElementById('timeElapsed');
const msgEl = document.getElementById('message');
const startBtn = document.getElementById('startBtn');
const resetBtn = document.getElementById('resetBtn');
const levelSelect = document.getElementById('levelSelect');

let W = canvas.width, H = canvas.height;

// Cargamos niveles desde PHP embebido
const LEVELS = <?= json_encode(array_reduce($nivells, function($a, $n) {
  $a[$n['nivell']] = json_decode($n['configuracio_json'], true) ?? [];
  return $a;
}, []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>;

const usuari_id = <?= json_encode($usuari_id) ?>;
const joc_id = <?= json_encode($joc_id) ?>;

let state = { running:false, lastTime:null, elapsed:0, playerScore:0, aiScore:0, level:1 };

const paddle = { x:20, y:H/2-60, w:12, h:100, speed:7 };
const ai = { x:W-32, y:H/2-60, w:12, h:100, speed:3 };
const ball = { x:W/2, y:H/2, r:9, vx:5, vy:3 };

let keys = { up:false, down:false, w:false, s:false };
let rafId = null;

function resetBall(toPlayer=false) {
  ball.x = W/2; ball.y = H/2;
  const base = LEVELS[state.level]?.ballSpeed || 5;
  const ang = (Math.random()*Math.PI/3) - Math.PI/6;
  const dir = (Math.random() > 0.5 ? 1 : -1) * (toPlayer ? -1 : 1);
  ball.vx = base * dir;
  ball.vy = base * Math.sin(ang);
}

function draw() {
  ctx.clearRect(0,0,W,H);
  ctx.fillStyle = "#071226";
  ctx.fillRect(0,0,W,H);
  ctx.fillStyle = "rgba(255,255,255,0.05)";
  for(let i=0;i<H;i+=24) ctx.fillRect(W/2-1,i,2,12);
  ctx.fillStyle = "#e6eef6";
  roundRect(ctx,paddle.x,paddle.y,paddle.w,paddle.h,4,true);
  roundRect(ctx,ai.x,ai.y,ai.w,ai.h,4,true);
  ctx.beginPath(); ctx.arc(ball.x,ball.y,ball.r,0,Math.PI*2); ctx.fill();
  ctx.font = "20px Inter, Arial";
  ctx.fillStyle = "#cfe9ff";
  ctx.fillText(`Nivel ${state.level}`, 14, 28);
  ctx.fillText(`${state.playerScore}`, W/2 - 60, 40);
  ctx.fillText(`${state.aiScore}`, W/2 + 42, 40);
}
function roundRect(ctx,x,y,w,h,r,fill){ctx.beginPath();ctx.moveTo(x+r,y);ctx.arcTo(x+w,y,x+w,y+h,r);ctx.arcTo(x+w,y+h,x,y+h,r);ctx.arcTo(x,y+h,x,y,r);ctx.arcTo(x,y,x+w,y,r);ctx.closePath();if(fill)ctx.fill();}

function updateAI(dt){
  const c=ai.y+ai.h/2,d=ball.y-c,m=ai.speed*dt*60;
  if(Math.abs(d)>4){ai.y+=Math.sign(d)*Math.min(Math.abs(d),m);ai.y=Math.max(8,Math.min(H-ai.h-8,ai.y));}
}

function update(ts){
  if(!state.running)return;
  if(!state.lastTime)state.lastTime=ts;
  const dt=(ts-state.lastTime)/1000; state.lastTime=ts;
  state.elapsed+=dt; timeEl.textContent=state.elapsed.toFixed(1)+'s';
  if(keys.w||keys.up)paddle.y-=paddle.speed*60*dt;
  if(keys.s||keys.down)paddle.y+=paddle.speed*60*dt;
  paddle.y=Math.max(8,Math.min(H-paddle.h-8,paddle.y));
  updateAI(dt);
  ball.x+=ball.vx; ball.y+=ball.vy;
  if(ball.y-ball.r<6||ball.y+ball.r>H-6)ball.vy*=-1;

  if(ball.x-ball.r<paddle.x+paddle.w&&ball.y>paddle.y&&ball.y<paddle.y+paddle.h){
    const rel=(ball.y-(paddle.y+paddle.h/2))/(paddle.h/2);
    ball.vx=Math.abs(ball.vx); ball.vy=rel*Math.abs(ball.vx);
    ball.vx+=0.5;
  }
  if(ball.x+ball.r>ai.x&&ball.y>ai.y&&ball.y<ai.y+ai.h){
    const rel=(ball.y-(ai.y+ai.h/2))/(ai.h/2);
    ball.vx=-Math.abs(ball.vx); ball.vy=rel*Math.abs(ball.vx);
  }

  if(ball.x<0){state.aiScore++;aiScoreEl.textContent=state.aiScore;resetBall(true);}
  if(ball.x>W){state.playerScore++;scoreEl.textContent=state.playerScore;resetBall(false);}

  const winPoints=LEVELS[state.level]?.winPoints||5;
  if(state.playerScore>=winPoints||state.aiScore>=winPoints){
    state.running=false;
    showEnd(state.playerScore>=winPoints,state.playerScore,state.level,Math.round(state.elapsed));
  }
}

function loop(ts){update(ts);draw();rafId=requestAnimationFrame(loop);}

function startMatch(){
  state.level=parseInt(levelSelect.value)||1;
  const cfg=LEVELS[state.level]||{};
  paddle.h=cfg.paddleH||100; ai.h=cfg.paddleH||100; ai.speed=cfg.aiSpeed||3;
  ball.vx=(cfg.ballSpeed||5)*(Math.random()>0.5?1:-1);
  ball.vy=0; state.playerScore=0; state.aiScore=0; state.elapsed=0; state.running=true; state.lastTime=null;
  resetBall(); msgEl.textContent=''; if(!rafId)rafId=requestAnimationFrame(loop);
}

function showEnd(won,puntos,nivel,duracion){
  msgEl.textContent = won ? `Has ganado el nivel ${nivel}` : `Has perdido el nivel ${nivel}`;
  if(usuari_id&&joc_id){
    fetch('../../api.php',{method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'save_game',usuari_id,joc_id,nivell:nivel,puntuacio:puntos,durada:duracion,guanyat:won?1:0})})
    .then(r=>r.json()).then(d=>{
      if(d.success){msgEl.textContent+=' — Partida guardada.';}
      else msgEl.textContent+=' — Error al guardar.';
    }).catch(()=>msgEl.textContent+=' — Error al contactar con la API.');
  } else msgEl.textContent+=' — No guardada (sin sesión).';
}

function resetGame(){state.playerScore=0;state.aiScore=0;state.elapsed=0;state.running=false;scoreEl.textContent='0';aiScoreEl.textContent='0';timeEl.textContent='0.0s';msgEl.textContent='';resetBall();}
window.addEventListener('keydown',e=>{if(e.key==='ArrowUp')keys.up=true;if(e.key==='ArrowDown')keys.down=true;if(e.key==='w')keys.w=true;if(e.key==='s')keys.s=true;if(e.key===' '&&!state.running)startMatch();});
window.addEventListener('keyup',e=>{if(e.key==='ArrowUp')keys.up=false;if(e.key==='ArrowDown')keys.down=false;if(e.key==='w')keys.w=false;if(e.key==='s')keys.s=false;});
startBtn.addEventListener('click',startMatch);
resetBtn.addEventListener('click',()=>resetGame());
resetGame(); draw();
</script>
</body>
</html>
