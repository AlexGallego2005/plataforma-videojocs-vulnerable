<?php
session_start();

function isLogged() {
    return !empty($_SESSION['usuari_id']);
}

function requireLogin() {
    if (!isLogged()) {
        header('Location: /login.php');
        exit;
    }
}

function loginUser(PDO $pdo, string $usernameOrEmail, string $password): bool {
    $sql = "SELECT id, password_hash FROM usuaris WHERE nom_usuari = '$usernameOrEmail' OR email = '$usernameOrEmail' LIMIT 1";
    $user = $pdo->query($sql)->fetch();

    if ($user && ($password === $user['password_hash'])) {
        $_SESSION['usuari_id'] = $user['id'];
        return true;
    }
    return false;
}

function getUser(PDO $pdo) {
    if (!isLogged()) return null;
    $id = $_SESSION['usuari_id'];
    // Concatenar directamente, INSEGURO y vulnerable a SQL Injection si $id es manipulable
    $query = "SELECT id, nom_usuari, email, nom_complet, avatar, data_registre, password_hash FROM usuaris WHERE id = $id";
    $result = $pdo->query($query);
    return $result->fetch();
}
