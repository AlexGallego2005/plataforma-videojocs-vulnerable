let animes = [];
let currentAnime = null;
let currentHintIndex = 0;
let attempts = 0;
let score = 0;
const maxAttempts = 3;

// Cargar animes al iniciar
async function loadAnimes() {
    try {
        const response = await fetch('data/animes.json');
        if (!response.ok) throw new Error('No se pudo cargar el archivo');
        const data = await response.json();
        animes = data.animes;
        nextAnime();
    } catch (error) {
        document.getElementById('hintBox').innerHTML = 
            '<div class="error">Error al cargar los animes: ' + error.message + '</div>';
    }
}

function nextAnime() {
    if (animes.length === 0) return;
    
    currentAnime = animes[Math.floor(Math.random() * animes.length)];
    currentHintIndex = 0;
    attempts = 0;
    
    const input = document.getElementById('guessInput');
    input.disabled = false;
    input.value = '';
    input.focus();
    
    document.getElementById('message').textContent = '';
    document.getElementById('message').className = 'message';
    
    showHint();
    updateAttempts();
}

function showHint() {
    if (!currentAnime || currentHintIndex >= currentAnime.hints.length) {
        document.getElementById('hintBox').textContent = 
            'Se acabaron las pistas. El anime es: ' + currentAnime.name;
        return;
    }
    document.getElementById('hintBox').textContent = 
        '💡 Pista ' + (currentHintIndex + 1) + ': ' + currentAnime.hints[currentHintIndex];
}

function updateAttempts() {
    const remaining = maxAttempts - attempts;
    document.getElementById('attempts').textContent = 
        'Intentos restantes: ' + remaining + '/' + maxAttempts;
}

function makeGuess() {
    const input = document.getElementById('guessInput');
    const guess = input.value.trim().toLowerCase();
    
    if (!guess) {
        showMessage('Por favor, escribe algo', 'info');
        return;
    }
    
    attempts++;
    updateAttempts();
    
    const correctName = currentAnime.name.toLowerCase();
    
    if (guess === correctName) {
        showMessage('¡Correcto! Es ' + currentAnime.name, 'correct');
        score++;
        document.getElementById('score').textContent = score;
        input.disabled = true;
        return;
    }
    
    if (guess.includes(correctName.split(' ')[0]) || 
        correctName.includes(guess)) {
        showMessage('¡Casi! Pero no es exacto', 'incorrect');
    } else {
        showMessage('Incorrecto. Intentalo de nuevo', 'incorrect');
    }
    
    if (attempts >= maxAttempts) {
        showMessage('Game Over. Era: ' + currentAnime.name, 'incorrect');
        input.disabled = true;
        return;
    }
    
    if (attempts === maxAttempts - 1) {
        showMessage('Última oportunidad. Usa la siguiente pista', 'info');
        currentHintIndex++;
        showHint();
    }
    
    input.value = '';
    input.focus();
}

function skipAnime() {
    if (currentAnime) {
        showMessage('El anime era: ' + currentAnime.name, 'info');
        document.getElementById('guessInput').disabled = true;
    }
}

function showMessage(text, className) {
    const messageEl = document.getElementById('message');
    messageEl.textContent = text;
    messageEl.className = 'message ' + className;
}

// Permitir adivinar con Enter
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('guessInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') makeGuess();
    });
    
    loadAnimes();
});