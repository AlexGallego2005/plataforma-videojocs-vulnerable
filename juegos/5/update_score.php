<?php
session_start();
require_once '../../secret/games_model.php';

if (!isset($_SESSION['usuari_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit();
}

$usuari_id = $_SESSION['usuari_id'];
$joc_id = 5;

if (!isset($_POST['points'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta puntuación']);
    exit();
}

$points = (int) $_POST['points'];
$progres = getUserProgress($usuari_id, $joc_id);

if ($points > $progres['puntuacio_maxima']) {
    updateUserMaxScore($usuari_id, $joc_id, $points);
    echo json_encode(['updated' => true, 'new_record' => $points]);
} else {
    echo json_encode(['updated' => false]);
}
?>
