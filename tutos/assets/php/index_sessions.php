<?php
//index_sessions.php
session_start();

// Si ja hi ha sessió iniciada, redirigir a la pàgina de perfil
if (isset($_SESSION['errors'])) {
  echo $_SESSION['errors'];
} else if(isset($_SESSION['usuari'])) {
    echo "Iniciada la sessió amb l'usuari" . $_SESSION['usuari'] . ", redirigint a perfil.php...";
}
?>

<h2>Login Usuari</h2>
<form method="POST" action="./processa_sessions.php">
  <label for="usuari">Usuari:</label>
  <input type="text" id="usuari" name="usuari" required>
  <label for="password">Contrasenya:</label>
  <input type="password" id="password" name="password" required>
  <button type="submit" name="login">Entrar</button>
</form>