const enemies = document.getElementById('enemies');
const player = document.getElementById('player');
const occupied = document.getElementById('occupied');
const vacants = document.getElementById('vacants');
const timeLeft = document.getElementById('timeLeft');
const timeLimit = new Date(new Date(Date.now()).getTime() + 10*60000);

function lostGame()
{
    document.body.innerHTML = '<h1>Has perdido.</h1><br/><p onclick="window.reload()>Volver a jugar</p>"';
};

setInterval(() => {
    const secondsLeft = new Date(null);
    secondsLeft.setSeconds(Math.abs((timeLimit.getTime() - new Date(Date.now()).getTime()) / 1000));
    const secondsParsed = secondsLeft.toISOString().substring(14, 19);
    
    if (secondsParsed === '00:00')
        lostGame();

    timeLeft.textContent = secondsParsed;
}, 980);

setInterval(() => {
    fetch('https://randomuser.me/api/').then( (res) => {
        res.json().then(data => {
            enemies.insertAdjacentHTML('beforeend', `<img style="top: ${ Math.floor((Math.random() * (window.innerHeight - 50)) + 25) }px" src="${ data.results[0].picture.thumbnail }">`);
        });
    });
}, Math.floor((Math.random() * 600) + 900));

setInterval(() => {
    const images = enemies.getElementsByTagName('img');

    for (const image of images)
    {
        const pos = image.getBoundingClientRect()
        if (pos.left < 0) image.remove();
    }
}, 1_000);

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

setInterval(() => {
    const playerPos = player.getBoundingClientRect();
    enemies.querySelectorAll('img').forEach(enemy => {
        const enemyPos = enemy.getBoundingClientRect();
        const isColliding = !(
            playerPos.top    > enemyPos.bottom || // player está por debajo
            playerPos.bottom < enemyPos.top    || // player está por encima
            playerPos.left   > enemyPos.right  || // player está a la derecha
            playerPos.right  < enemyPos.left     // player está a la izquierda
        );

        if (isColliding) {
            enemy.remove();
            occupied.textContent = parseInt(occupied.textContent) + 1;
            vacants.textContent = parseInt(vacants.textContent) - 1;

            document.body.style.backgroundColor = `rgb(${ Math.floor(Math.random() * 255) }, ${ Math.floor(Math.random() * 255) }, ${ Math.floor(Math.random() * 255) })`;
            setTimeout(() => {
                document.body.style.backgroundColor = 'black';
            }, 150);
        };
    })
}, 10);