<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Game - Inseguro Edition 😈</title>
    <link rel="icon" type="image/png" href="/assets/images/helmet.png"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Comic Sans MS', cursive;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: white;
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: rainbow 3s infinite;
        }

        @keyframes rainbow {
            0% { color: #ff0080; }
            25% { color: #00ff80; }
            50% { color: #0080ff; }
            75% { color: #ff8000; }
            100% { color: #ff0080; }
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-size: 1.2em;
            font-weight: bold;
        }

        .btn-reiniciar {
            background: rgba(255,255,255,0.3);
            border: 2px solid white;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-reiniciar:hover {
            background: rgba(255,255,255,0.5);
            transform: scale(1.1) rotate(5deg);
        }

        .game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .card {
            aspect-ratio: 1;
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 3px solid rgba(255,255,255,0.5);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            transition: all 0.3s;
            user-select: none;
        }

        .card:hover:not(.flipped):not(.matched) {
            background: rgba(255,255,255,0.4);
            transform: scale(1.05);
        }

        .card.flipped {
            background: white;
            transform: rotateY(180deg);
        }

        .card.matched {
            background: #4ade80;
            border-color: #22c55e;
            animation: pulse 0.5s;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .card-back {
            color: rgba(255,255,255,0.5);
            font-size: 4em;
        }

        .victory {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: white;
            padding: 40px 60px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .victory.show {
            transform: translate(-50%, -50%) scale(1);
            animation: bounce 0.6s;
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }

        .victory h2 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .victory p {
            color: #666;
            font-size: 1.3em;
        }

        .instructions {
            text-align: center;
            color: rgba(255,255,255,0.8);
            margin-top: 20px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Memory Inseguro 😈</h1>
        
        <div class="stats">
            <div class="stat">Movimientos: <span id="moves">0</span></div>
            <button class="btn-reiniciar" onclick="reiniciarJuego()">🔄 Reiniciar</button>
        </div>

        <div class="game-board" id="gameBoard"></div>

        <div class="instructions">
            ⚠️ Encuentra todas las parejas... si te atreves ⚠️
        </div>
    </div>

    <div class="victory" id="victory">
        <h2>¡GANASTE! 🎉</h2>
        <p>Lo completaste en <span id="finalMoves">0</span> movimientos</p>
    </div>

    <script>
        const emojis = ['💀', '👻', '🎃', '🦇', '🕷️', '🧛', '🧟', '🔮'];
        let cards = [];
        let flippedCards = [];
        let matchedPairs = 0;
        let moves = 0;
        let canFlip = true;

        function initGame() {
            const gameBoard = document.getElementById('gameBoard');
            gameBoard.innerHTML = '';
            
            cards = [...emojis, ...emojis]
                .sort(() => Math.random() - 0.5)
                .map((emoji, index) => ({ emoji, id: index }));
            
            cards.forEach((card, index) => {
                const cardElement = document.createElement('div');
                cardElement.className = 'card';
                cardElement.dataset.index = index;
                cardElement.innerHTML = '<div class="card-back">?</div>';
                cardElement.addEventListener('click', () => flipCard(index));
                gameBoard.appendChild(cardElement);
            });

            flippedCards = [];
            matchedPairs = 0;
            moves = 0;
            canFlip = true;
            document.getElementById('moves').textContent = moves;
            document.getElementById('victory').classList.remove('show');
        }

        function flipCard(index) {
            if (!canFlip || flippedCards.length >= 2) return;
            
            const cardElement = document.querySelectorAll('.card')[index];
            if (cardElement.classList.contains('flipped') || 
                cardElement.classList.contains('matched')) return;

            cardElement.classList.add('flipped');
            cardElement.innerHTML = cards[index].emoji;
            flippedCards.push(index);

            if (flippedCards.length === 2) {
                moves++;
                document.getElementById('moves').textContent = moves;
                checkMatch();
            }
        }

        function checkMatch() {
            canFlip = false;
            const [first, second] = flippedCards;
            const cardElements = document.querySelectorAll('.card');

            setTimeout(() => {
                if (cards[first].emoji === cards[second].emoji) {
                    cardElements[first].classList.add('matched');
                    cardElements[second].classList.add('matched');
                    matchedPairs++;

                    if (matchedPairs === emojis.length) {
                        setTimeout(showVictory, 500);
                    }
                } else {
                    cardElements[first].classList.remove('flipped');
                    cardElements[second].classList.remove('flipped');
                    cardElements[first].innerHTML = '<div class="card-back">?</div>';
                    cardElements[second].innerHTML = '<div class="card-back">?</div>';
                }

                flippedCards = [];
                canFlip = true;
            }, 800);
        }

        function showVictory() {
            document.getElementById('finalMoves').textContent = moves;
            document.getElementById('victory').classList.add('show');
        }

        function reiniciarJuego() {
            initGame();
        }

        initGame();
    </script>
</body>
</html>