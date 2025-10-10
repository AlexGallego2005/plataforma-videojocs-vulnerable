<?php
//processa_sessions.php
session_start();

// Login de proves: només un usuari i password fixos
$usuariCorrecte = "pep";
$passwordCorrecte = "1234";

if(isset($_POST['login'])) {
    $usuari = $_POST['usuari'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($usuari === $usuariCorrecte && $password === $passwordCorrecte) {
        $_SESSION['usuari'] = $usuari;
        unset($_SESSION['errors']);
    } else {
        $_SESSION['errors'] = "<h1>Usuari o contrasenya incorrectes!</h1>";
        unset($_SESSION['usuari']);
    }
    header("Location: index_sessions.php");
    exit;
}
?>