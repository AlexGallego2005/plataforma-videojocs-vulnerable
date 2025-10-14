/* juego Pong con niveles
   Usa Canvas. Intenta cargar niveles desde /api.php; hay fallback.
   Controles: W/S o flechas arriba/abajo.
*/

(() => {
  const API = '/api.php';
  const JOC_ID = 4;

  // Niveles por defecto (según README)
  const defaultLevels = [
    { id: 1, name: "Principiant", ballSpeed: 4, aiSpeed: 3, paddleHeight: 120, aiPaddleHeight: 120, winScore: 200 },
    { id: 2, name: "Aprenent", ballSpeed: 5, aiSpeed: 4, paddleHeight: 110, aiPaddleHeight: 110, winScore: 300 },
    { id: 3, name: "Intermedi", ballSpeed: 6, aiSpeed: 5, paddleHeight: 100, aiPaddleHeight: 100, winScore: 400 },
    { id: 4, name: "Avançat", ballSpeed: 7, aiSpeed: 6, paddleHeight: 90, aiPaddleHeight: 90, winScore: 500 },
    { id: 5, name: "Expert", ballSpeed: 8, aiSpeed: 7, paddleHeight: 80, aiPaddleHeight: 80, winScore: 600 }
  ];

  // DOM
  const canvas = document.getElementById('pongCanvas');
  const startBtn = document.getElementById('startBtn');
  const pauseBtn = document.getElementById('pauseBtn');
  const nextBtn = document.getElementById('nextBtn');
  const levelSelect = document.getElementById('levelSelect');
  const scoreEl = document.getElementById('score');
  const aiScoreEl = document.getElementById('aiScore');
  const levelNameEl = document.getElementById('levelName');
  const timeEl = document.getElementById('time');

  if (!canvas) {
    console.error('No se encontró canvas #pongCanvas');
    return;
  }

  const ctx = canvas.getContext('2d');
  // Estado
  let levels = defaultLevels.slice();
  let currentLevelIndex = 0;
  let running = false;
  let lastTime = 0;
  let elapsedStart = 0;

  // Game objects
  const game = {
    player: { x: 20, y: 0, width: 10, height: 100, score: 0 },
    ai:     { x: 0,  y: 0, width: 10, height: 100, score: 0 },
    ball:   { x: 0, y: 0, vx: 0, vy: 0, radius: 8 },
    hits: 0
  };
  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);


  // Input
  const input = { up: false, down: false };
  window.addEventListener('keydown', e => {
    if (e.key === 'ArrowUp' || e.key === 'w' || e.key === 'W') input.up = true;
    if (e.key === 'ArrowDown' || e.key === 's' || e.key === 'S') input.down = true;
  });
  window.addEventListener('keyup', e => {
    if (e.key === 'ArrowUp' || e.key === 'w' || e.key === 'W') input.up = false;
    if (e.key === 'ArrowDown' || e.key === 's' || e.key === 'S') input.down = false;
  });

  // Fetch niveles
  async function loadLevels() {
    try {
      const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_levels', joc_id: JOC_ID })
      });
      if (!res.ok) throw new Error('no ok');
      const data = await res.json();
      if (Array.isArray(data) && data.length) {
        // Esperamos que cada entry tenga la configuración JSON en un campo, o esté directa
        levels = data.map(l => {
          if (typeof l.config === 'string') {
            try { return Object.assign({ id: l.id, name: l.name || `Nivell ${l.id}` }, JSON.parse(l.config)); }
            catch { return Object.assign({ id: l.id, name: l.name || `Nivell ${l.id}` }, defaultLevels[l.id-1] || {}); }
          } else {
            return {
              id: l.id ?? null,
              name: l.name ?? l.nom ?? `Nivell ${l.id ?? '?'}`,
              ballSpeed: l.ballSpeed ?? l.ball_speed ?? 5,
              aiSpeed: l.aiSpeed ?? l.ai_speed ?? 4,
              paddleHeight: l.paddleHeight ?? l.paddle_height ?? 100,
              aiPaddleHeight: l.aiPaddleHeight ?? l.ai_paddle_height ?? 100,
              winScore: l.winScore ?? l.win_score ?? 300
            };
          }
        });
      }
    } catch (err) {
      console.warn('No se pudieron cargar niveles desde API, usando defaults.');
      levels = defaultLevels.slice();
    }
    populateLevelSelect();
    applyLevel(0);
  }

  function populateLevelSelect() {
    levelSelect.innerHTML = '';
    levels.forEach((lvl, i) => {
      const opt = document.createElement('option');
      opt.value = i;
      opt.textContent = `${lvl.id ?? (i+1)} - ${lvl.name}`;
      levelSelect.appendChild(opt);
    });
  }

  function applyLevel(index) {
    currentLevelIndex = Math.max(0, Math.min(index, levels.length - 1));
    const lvl = levels[currentLevelIndex];
    game.player.height = lvl.paddleHeight;
    game.ai.height = lvl.aiPaddleHeight;
    game.aiSpeed = lvl.aiSpeed;
    game.ballSpeedBase = lvl.ballSpeed;
    game.winScore = lvl.winScore;
    resetRound();
    updateHUD();
  }

  function resizeCanvas() {
    canvas.width = Math.min(900, Math.floor(window.innerWidth * 0.9));
    canvas.height = Math.min(600, Math.floor(window.innerHeight * 0.7));
    // keep paddles inside
    game.player.y = (canvas.height - (game.player.height || 100)) / 2;
    game.ai.y = (canvas.height - (game.ai.height || 100)) / 2;
  }

  function resetRound() {
    game.player.score = 0;
    game.ai.score = 0;
    game.hits = 0;
    // place paddles middle
    game.player.y = (canvas.height - game.player.height) / 2;
    game.ai.y = (canvas.height - game.ai.height) / 2;
    // ball center
    game.ball.x = canvas.width / 2;
    game.ball.y = canvas.height / 2;
    const angle = (Math.random() * Math.PI / 3) - Math.PI / 6; // -30..30 deg
    const dir = Math.random() < 0.5 ? -1 : 1;
    const speed = game.ballSpeedBase || 5;
    game.ball.vx = Math.cos(angle) * speed * dir;
    game.ball.vy = Math.sin(angle) * speed;
    lastTime = performance.now();
    elapsedStart = lastTime;
    updateHUD();
  }

  function updateHUD() {
    scoreEl.textContent = game.player.score;
    aiScoreEl.textContent = game.ai.score;
    levelNameEl.textContent = `${(levels[currentLevelIndex] && levels[currentLevelIndex].name) || 'Nivell' } (Obj: ${game.winScore})`;
  }

  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

  function step(now) {
    if (!running) return;
    const dt = (now - lastTime) / 1000;
    lastTime = now;

    // Player movement
    const playerSpeed = 300; // px/s
    if (input.up) game.player.y -= playerSpeed * dt;
    if (input.down) game.player.y += playerSpeed * dt;
    game.player.y = clamp(game.player.y, 0, canvas.height - game.player.height);

    // AI movement - simple follow with speed cap
    const aiTarget = game.ball.y - game.ai.height / 2;
    const aiSpeedPx = (levels[currentLevelIndex].aiSpeed || game.aiSpeed || 4) * 60; // scale
    if (Math.abs(aiTarget - game.ai.y) > 2) {
      game.ai.y += clamp(aiTarget - game.ai.y, -aiSpeedPx * dt, aiSpeedPx * dt);
      game.ai.y = clamp(game.ai.y, 0, canvas.height - game.ai.height);
    }

    // Ball movement
    game.ball.x += game.ball.vx * dt * 60 / 60;
    game.ball.y += game.ball.vy * dt * 60 / 60;

    // Top/bottom collision
    if (game.ball.y - game.ball.radius < 0) {
      game.ball.y = game.ball.radius;
      game.ball.vy *= -1;
    } else if (game.ball.y + game.ball.radius > canvas.height) {
      game.ball.y = canvas.height - game.ball.radius;
      game.ball.vy *= -1;
    }

    // Player paddle collision
    if (game.ball.x - game.ball.radius < game.player.x + game.player.width) {
      if (game.ball.y > game.player.y && game.ball.y < game.player.y + game.player.height) {
        // hit
        game.ball.x = game.player.x + game.player.width + game.ball.radius;
        reflectBallFromPaddle(game.player);
        registerPlayerHit();
      } else {
        // missed by player -> AI scores -> defeat
        game.ai.score += 1;
        onPoint(false);
        return;
      }
    }

    // AI paddle collision
    if (game.ball.x + game.ball.radius > canvas.width - (game.ai.width + (canvas.width - game.ai.x - game.ai.width))) {
      // ai.x is on the right side; compute ai paddle left edge
      const aiLeft = canvas.width - game.ai.x - game.ai.width;
      if (game.ball.x + game.ball.radius > aiLeft) {
        // check overlap simpler: use ai paddle rect
      }
    }
    // simpler AI paddle detection:
    const aiPaddleLeft = canvas.width - game.ai.x - game.ai.width;
    if (game.ball.x + game.ball.radius > aiPaddleLeft) {
      if (game.ball.y > game.ai.y && game.ball.y < game.ai.y + game.ai.height) {
        game.ball.x = aiPaddleLeft - game.ball.radius;
        reflectBallFromPaddle(game.ai, true);
        // when AI hits, no score for player, but could speed up slightly
      } else {
        // AI missed -> player scores
        game.player.score += 1;
        registerPlayerScore();
        onPoint(true);
        return;
      }
    }

    // Draw
    draw();

    // update timer
    const elapsed = Math.floor((now - elapsedStart) / 1000);
    timeEl.textContent = `${pad(Math.floor(elapsed / 60))}:${pad(elapsed % 60)}`;

    // win condition
    if (game.player.score >= game.winScore) {
      onLevelWin();
      return;
    }

    // continue
    requestAnimationFrame(step);
  }

  function reflectBallFromPaddle(paddle, isAi = false) {
    // calculate impact relative to paddle center to set vy
    const paddleCenter = paddle.y + paddle.height / 2;
    const diff = (game.ball.y - paddleCenter) / (paddle.height / 2);
    const maxAngle = Math.PI / 3; // 60 deg
    const angle = diff * maxAngle;
    const speed = Math.hypot(game.ball.vx, game.ball.vy);
    const baseSpeed = game.ballSpeedBase || 5;
    // increment speed slightly per hit
    const newSpeed = Math.min(baseSpeed + 0.05 * game.hits + 0.5, baseSpeed * 1.6 + game.hits * 0.2);
    const dir = isAi ? -1 : 1;
    game.ball.vx = Math.cos(angle) * newSpeed * (isAi ? -1 : 1);
    game.ball.vy = Math.sin(angle) * newSpeed;
    game.hits++;
  }

  function registerPlayerHit() {
    game.player.score += 10;
    updateHUD();
  }

  function registerPlayerScore() {
    // +50 when player scores by AI miss
    game.player.score += 50;
    updateHUD();
  }

  function onPoint(playerScored) {
    // Save partial match? We only save full matches on win/lose per README.
    // Reset ball and continue after short pause
    draw(); // final frame
    setTimeout(() => {
      // reset ball center, keep scores
      game.ball.x = canvas.width / 2;
      game.ball.y = canvas.height / 2;
      const angle = (Math.random() * Math.PI / 3) - Math.PI / 6;
      const dir = playerScored ? -1 : 1;
      const speed = game.ballSpeedBase || 5;
      game.ball.vx = Math.cos(angle) * speed * dir;
      game.ball.vy = Math.sin(angle) * speed;
      lastTime = performance.now();
      requestAnimationFrame(step);
    }, 700);
  }

  function onLevelWin() {
    running = false;
    draw();
    // Guardar partida
    const duration = Math.floor((performance.now() - elapsedStart) / 1000);
    saveMatch(true, duration);
    showMessage(`Has ganado el nivel ${levels[currentLevelIndex].name}.`);
    nextBtn.disabled = currentLevelIndex >= levels.length - 1;
  }

  function onLevelLose() {
    running = false;
    draw();
    const duration = Math.floor((performance.now() - elapsedStart) / 1000);
    saveMatch(false, duration);
    showMessage(`Has perdido el nivel ${levels[currentLevelIndex].name}.`);
  }

  function saveMatch(guanyat, durada) {
    // Intenta enviar la petición; backend debe usar sesión para autorizar
    const payload = {
      action: 'save_game',
      joc_id: JOC_ID,
      nivell: (levels[currentLevelIndex] && levels[currentLevelIndex].id) || (currentLevelIndex + 1),
      puntuacio: game.player.score,
      durada: durada,
      guanyat: !!guanyat
    };
    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(r => {
      // no hacemos nada especial; backend responde según sesión
    }).catch(e => console.warn('No se pudo guardar partida', e));
  }

  function showMessage(txt) {
    const msg = document.getElementById('message');
    if (!msg) return;
    msg.textContent = txt;
    msg.classList.add('visible');
    setTimeout(() => msg.classList.remove('visible'), 3000);
  }

  function drawNet() {
    ctx.fillStyle = '#00d4ff';
    const step = 20;
    for (let y = 0; y < canvas.height; y += step * 2) {
      ctx.fillRect(canvas.width / 2 - 1, y, 2, step);
    }
  }

  function draw() {
    // background
    ctx.fillStyle = '#071019';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // net
    drawNet();

    // paddles
    // player left
    ctx.fillStyle = '#00ff41';
    ctx.fillRect(game.player.x, game.player.y, game.player.width, game.player.height);

    // ai right (position ai.x measured from right)
    const aiDrawX = canvas.width - game.ai.x - game.ai.width;
    ctx.fillStyle = '#ff4444';
    ctx.fillRect(aiDrawX, game.ai.y, game.ai.width, game.ai.height);

    // ball
    ctx.beginPath();
    ctx.arc(game.ball.x, game.ball.y, game.ball.radius, 0, Math.PI * 2);
    ctx.fillStyle = '#ffffff';
    ctx.fill();

    // HUD
    ctx.fillStyle = '#00d4ff';
    ctx.font = '16px system-ui, Arial';
    ctx.fillText(`Tú: ${game.player.score}`, 20, 20);
    ctx.fillText(`IA: ${game.ai.score}`, canvas.width - 80, 20);
  }

  function pad(n) { return String(n).padStart(2, '0'); }

  // Controls
  startBtn.addEventListener('click', () => {
    if (!running) {
      running = true;
      lastTime = performance.now();
      elapsedStart = performance.now();
      requestAnimationFrame(step);
    }
  });
  pauseBtn.addEventListener('click', () => {
    running = !running;
    if (running) {
      lastTime = performance.now();
      requestAnimationFrame(step);
    }
  });
  nextBtn.addEventListener('click', () => {
    if (currentLevelIndex < levels.length - 1) {
      applyLevel(currentLevelIndex + 1);
      nextBtn.disabled = true;
    }
  });
  levelSelect.addEventListener('change', () => {
    applyLevel(parseInt(levelSelect.value, 10));
  });

  // Start
  loadLevels();

  // Expose simple API for index.php (opcional)
  window.PongGame = {
    applyLevelIndex: applyLevel,
    start: () => startBtn.click(),
    pause: () => pauseBtn.click(),
    getState: () => ({ score: game.player.score, level: currentLevelIndex })
  };

})();
