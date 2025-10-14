import { Enemy } from './classes.js';
import { isColliding } from './library.js';

const player = document.getElementById('player');
const enemies = document.getElementById('enemies');
const occupied = document.getElementById('occupied');
const vacants = document.getElementById('vacants');

function initPlayer()
{
    player.style.marginTop = (window.innerHeight / 2) - (player.getBoundingClientRect().height / 2) + 'px';
};

async function loadEnemy()
{
    const enemyImage =await (await fetch('https://randomuser.me/api/')).json();
    const enemy = new Enemy().setImage(enemyImage.results[0].picture.thumbnail);

    const enemyFrame = document.createElement('img');
    enemyFrame.setAttribute('src', enemy.imageUrl);
    enemyFrame.style.top = `${ enemy.posY }px`;

    enemyFrame.addEventListener(true, (w) => {
        
    })

    enemies.insertAdjacentElement('beforeend', enemyFrame);
};

function frame()
{
    enemies.querySelectorAll('#enemies img').forEach(enemy => isColliding(player, enemy));

    requestAnimationFrame(frame);
};

function startGame()
{
    initPlayer();
    setInterval(() => loadEnemy(), 250);

    requestAnimationFrame(frame);
};

window.onload = startGame();

window.addEventListener('keypress', (k) => {
    switch (k.code) {
        case 'KeyW': {
            player.style.marginTop = parseInt(player.style.marginTop) > 0 ? `${ parseInt(player.style.marginTop ? player.style.marginTop : 0) - 25 }px` : '0px';
            break;
        };

        case 'KeyS': {
            player.style.marginTop = `${ parseInt(player.style.marginTop ? player.style.marginTop : 0) + 25 }px`;
            break;
        };
    }
});