<header style="position: fixed; background-color: black; width: 100%; padding: 20px; color: white; display: grid; grid-template-columns: max-content auto max-content; column-gap: 30px; top: 0; align-items: center;">
    <div class="identity" style="display: grid; grid-template-columns: min-content auto; justify-content: center; align-items: center; column-gap: 10px;"> 
        <img width="30px" src="/assets/helmet.png" alt="">
        <strong style="font-size: 26px;">Spartanos</strong>
    </div>
    <div style="font-size: 18px; display: flex; justify-content: flex-start; column-gap: 20px;">
        <a href="/games.php">Juegos</a>
        <a href="/ranking.php">Ranking</a>
    </div>
    <div style="display: flex; flex-wrap: nowrap; align-items: center; column-gap: 20px;">
        <div class="user" style="display: grid; grid-template-columns: min-content auto; align-items: center; column-gap: 10px;">
            <img width="30px" style="border-radius: 50%;" src="<?= htmlspecialchars($usuari['avatar']) ?>" alt="">
            <a href="/user.php"><?= htmlspecialchars($usuari['nom_usuari']) ?></a>
        </div>
        
        <a href="/logout.php">Salir</a>
    </div>
</header>