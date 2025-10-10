<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_usuari = trim($_POST['nom_usuari'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if (!$nom_usuari || !$email || !$pass) $errors[] = 'Omple tots els camps obligatoris.';
    if ($pass !== $pass2) $errors[] = 'Les contrasenyes no coincideixen.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invàlid.';

    if (empty($errors)) {
        // Comprovar duplicats
        $stmt = $pdo->prepare("SELECT id FROM usuaris WHERE nom_usuari = :u OR email = :e LIMIT 1");
        $stmt->execute(['u'=>$nom_usuari,'e'=>$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Nom d’usuari o email ja registrat.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO usuaris (nom_usuari,email,password_hash,nom_complet) VALUES (:u,:e,:p,:n)");
            $ins->execute(['u'=>$nom_usuari,'e'=>$email,'p'=>$hash,'n'=>$nom_complet]);
            $_SESSION['usuari_id'] = $pdo->lastInsertId();
            header('Location: /games.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <title>Registre</title>
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
<h1>Registre d'usuari</h1>
<?php if($errors) foreach($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
<form method="post">
  <input name="nom_usuari" placeholder="Nom d'usuari" required><br>
  <input name="email" type="email" placeholder="Correu electrònic" required><br>
  <input name="nom_complet" placeholder="Nom complet"><br>
  <input name="password" type="password" placeholder="Contrasenya" required><br>
  <input name="password_confirm" type="password" placeholder="Repeteix contrasenya" required><br>
  <button>Registrar</button>
</form>
<p><a href="/login.php">Ja tens compte? Inicia sessió</a></p>
</body>
</html>
