<?php
function getAllJocs(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, nom_joc, descripcio, imatge_joc, puntuacio_maxima, nivells_totals, actiu FROM jocs ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getJoc(PDO $pdo, int $id) {
    $query = "SELECT * FROM jocs WHERE id = $id";
    $stmt = $pdo->query($query);
    return $stmt->fetch();
}

require_once __DIR__ . '/../config/db.php'; // Conexión PDO definida en db.php

/**
 * Obtener información de un juego por ID
 */
function getGameById($joc_id) {
    global $pdo;
    $query = "SELECT * FROM jocs WHERE id = $joc_id AND actiu = TRUE";
    $result = $pdo->query($query);
    return $result->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener todos los niveles de un juego
 */
function getGameLevels($joc_id) {
    global $pdo;
    $query = "SELECT * FROM nivells_joc WHERE joc_id = $joc_id ORDER BY nivell ASC";
    $result = $pdo->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener un nivel concreto de un juego
 */
function getGameLevel($joc_id, $nivell) {
    global $pdo;
    $query = "SELECT * FROM nivells_joc WHERE joc_id = $joc_id AND nivell = $nivell LIMIT 1";
    $result = $pdo->query($query);
    return $result->fetch(PDO::FETCH_ASSOC);
}


/**
 * Obtener progreso del usuario en un juego
 */
function getUserProgress($usuari_id, $joc_id) {
    global $pdo;
    $query = "SELECT * FROM progres_usuari WHERE usuari_id = $usuari_id AND joc_id = $joc_id";
    $result = $pdo->query($query);
    return $result->fetch(PDO::FETCH_ASSOC);
}

/**
 * Crear progreso inicial para un usuario
 */
function createUserProgress($usuari_id, $joc_id) {
    global $pdo;
    $query = "INSERT INTO progres_usuari 
        (usuari_id, joc_id, nivell_actual, puntuacio_maxima, partides_jugades, ultima_partida)
        VALUES ($usuari_id, $joc_id, 1, 0, 0, NOW())";
    return $pdo->query($query);
}

/**
 * Guardar una partida
 */
function saveGameMatch($usuari_id, $joc_id, $nivell, $puntuacio, $durada) {
    global $pdo;
    $query = "INSERT INTO partides 
              (usuari_id, joc_id, nivell_jugat, puntuacio_obtinguda, data_partida, durada_segons)
              VALUES ($usuari_id, $joc_id, $nivell, $puntuacio, NOW(), $durada)";
    $pdo->query($query);
    return $pdo->lastInsertId();
}

/**
 * Actualizar progreso del usuario
 */
function updateUserProgress($usuari_id, $joc_id, $nivell_jugat, $puntuacio, $guanyat) {
    global $pdo;

    $progres = getUserProgress($usuari_id, $joc_id);
    if (!$progres) {
        createUserProgress($usuari_id, $joc_id);
        $progres = getUserProgress($usuari_id, $joc_id);
    }

    $nova_puntuacio_maxima = max($progres['puntuacio_maxima'], $puntuacio);
    $nou_nivell_actual = $progres['nivell_actual'];

    if ($guanyat && $nivell_jugat == $progres['nivell_actual']) {
        $query = "SELECT COUNT(*) AS total FROM nivells_joc WHERE joc_id = $joc_id";
        $result = $pdo->query($query);
        $total_nivells = $result->fetchColumn();

        if ($nivell_jugat < $total_nivells) {
            $nou_nivell_actual = $nivell_jugat + 1;
        }
    }

    $partides_jugades = $progres['partides_jugades'] + 1;

    $query = "UPDATE progres_usuari
        SET nivell_actual = $nou_nivell_actual,
            puntuacio_maxima = $nova_puntuacio_maxima,
            partides_jugades = $partides_jugades,
            ultima_partida = NOW()
        WHERE usuari_id = $usuari_id AND joc_id = $joc_id";
    return $pdo->query($query);
}

/**
 * Obtener estadísticas del juego para un usuario
 */
function getGameStats($usuari_id, $joc_id) {
    global $pdo;
    $query = "SELECT 
                COUNT(*) AS total_partides,
                MAX(puntuacio_obtinguda) AS millor_puntuacio,
                AVG(puntuacio_obtinguda) AS puntuacio_mitjana,
                SUM(durada_segons) AS temps_total,
                MAX(nivell_jugat) AS nivell_maxim
              FROM partides
              WHERE usuari_id = $usuari_id AND joc_id = $joc_id";
    $result = $pdo->query($query);
    return $result->fetch(PDO::FETCH_ASSOC);
}


/**
 * Obtener ranking de jugadores para un juego
 */
function getGameRanking($joc_id, $limit = 10) {
    global $pdo;
    $query = "SELECT 
                u.nom_usuari,
                p.puntuacio_maxima,
                p.nivell_actual,
                p.partides_jugades,
                u.avatar
              FROM progres_usuari p
              INNER JOIN usuaris u ON p.usuari_id = u.id
              WHERE p.joc_id = $joc_id
              ORDER BY p.puntuacio_maxima DESC, p.nivell_actual DESC
              LIMIT $limit";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Obtener últimas partidas de un usuario
 */
function getRecentMatches($usuari_id, $joc_id, $limit = 5) {
    global $pdo;
    $query = "SELECT 
                nivell_jugat,
                puntuacio_obtinguda,
                data_partida,
                durada_segons
              FROM partides
              WHERE usuari_id = $usuari_id AND joc_id = $joc_id
              ORDER BY data_partida DESC
              LIMIT $limit";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener Ranking
 */
function getRanking($pdo, $joc_id = null, $limit = 5) {
    $query = "
        SELECT joc_id, nom_joc, progres_usuari.puntuacio_maxima, nom_usuari 
        FROM jocs 
        JOIN progres_usuari ON jocs.id = progres_usuari.joc_id 
        JOIN usuaris ON progres_usuari.usuari_id = usuaris.id 
        WHERE progres_usuari.puntuacio_maxima > 0 
        ORDER BY joc_id, puntuacio_maxima DESC
    ";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Actualizar Max Score
 */

function updateUserMaxScore($usuari_id, $joc_id, $points)
{
    global $pdo;
    $query = "UPDATE progres_usuari 
              SET puntuacio_maxima = $points 
              WHERE usuari_id = $usuari_id AND joc_id = $joc_id";
    $pdo->query($query);
}



?>
