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
    $sql = "SELECT id, password_hash FROM usuaris WHERE nom_usuari = :u OR email = :u LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['u' => $usernameOrEmail]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['usuari_id'] = $user['id'];
        return true;
    }
    return false;
}

function getUser(PDO $pdo) {
    if (!isLogged()) return null;
    $stmt = $pdo->prepare("SELECT id, nom_usuari, email, nom_complet, data_registre FROM usuaris WHERE id = ?");
    $stmt->execute([$_SESSION['usuari_id']]);
    return $stmt->fetch();
}
