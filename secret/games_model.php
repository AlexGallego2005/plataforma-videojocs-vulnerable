<?php
function getAllJocs(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, nom_joc, descripcio, puntuacio_maxima, nivells_totals, actiu FROM jocs ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getJoc(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("SELECT * FROM jocs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
