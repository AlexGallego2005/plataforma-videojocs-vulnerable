/**
 * @param { HTMLDivElement } player 
 * @param { HTMLImageElement | Array<HTMLImageElement> } enemy 
 */
export function isColliding(player, enemy)
{
    const playerPos = player.getBoundingClientRect();
    const enemyPos = enemy.getBoundingClientRect();
    const isColliding = !(
        playerPos.top    > enemyPos.bottom || // player está por debajo
        playerPos.bottom < enemyPos.top    || // player está por encima
        playerPos.left   > enemyPos.right  || // player está a la derecha
        playerPos.right  < enemyPos.left     // player está a la izquierda
    );

    if (isColliding)
    {
        enemy.remove();
        occupied.textContent = parseInt(occupied.textContent) + 1;
        vacants.textContent = parseInt(vacants.textContent) - 1;

        document.body.style.backgroundColor = `rgb(${ Math.floor(Math.random() * 255) }, ${ Math.floor(Math.random() * 255) }, ${ Math.floor(Math.random() * 255) })`;
        setTimeout(() => {
            document.body.style.backgroundColor = 'black';
        }, 150);
    };
};