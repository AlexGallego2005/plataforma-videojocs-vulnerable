<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spartanos</title>
    <link rel="icon" type="image/jpg" href="assets/helmet.png"/>
    <link rel="stylesheet" href="./assets/global.style.css">
    <link rel="stylesheet" href="./assets/index.style.css">
</head>
<body>
    <div class="home">
        <div class="identity">
            <img width="80px" src="./assets/helmet.png" alt="">
            <h1>Spartanos</h1>
        </div>
        <br><br>
        <button><a href="/login.php">Iniciar sesión</a></button>
        <button><a href="/register.php">Registrarse</a></button>
    </div>

    <marquee style="position: fixed; bottom: 0; color: white;">La página se llama "Spartanos" porque las personas espartanas vivían con lo justo y necesario. Por eso, hemos adoptado esa filosofía a nuestro proyecto, ya que queremos hacer lo justo y necesario para execeler. Un saludo!</marquee>
</body>
</html>