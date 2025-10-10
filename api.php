<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';
require_once __DIR__ . '/secret/games_model.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'GET' && str_contains($uri, 'jocs')) {
    if (isset($_GET['id'])) {
        $joc = getJoc($pdo, (int)$_GET['id']);
        echo json_encode($joc ?: []);
    } else {
        $jocs = getAllJocs($pdo);
        echo json_encode($jocs);
    }
    exit;
}

if ($method === 'POST' && str_contains($uri, 'login')) {
    $u = $body['user'] ?? '';
    $p = $body['password'] ?? '';
    if (loginUser($pdo, $u, $p)) {
        echo json_encode(['ok'=>true, 'session_id'=>session_id()]);
    } else {
        http_response_code(401);
        echo json_encode(['ok'=>false, 'error'=>'credencials']);
    }
    exit;
}

if ($method === 'POST' && str_contains($uri, 'jocs')) {
    session_start();
    if (empty($_SESSION['usuari_id'])) {
        http_response_code(401);
        echo json_encode(['ok'=>false,'error'=>'no_auth']);
        exit;
    }
    $nom = trim($body['nom_joc'] ?? '');
    if (!$nom) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'nom_joc_requerit']); exit; }
    $stmt = $pdo->prepare("INSERT INTO jocs (nom_joc, descripcio, puntuacio_maxima, nivells_totals, actiu)
                           VALUES (:n, :d, :p, :niv, :a)");
    $stmt->execute([
        'n'=>$nom,
        'd'=>$body['descripcio']??'',
        'p'=>$body['puntuacio_maxima']??0,
        'niv'=>$body['nivells_totals']??1,
        'a'=>true
    ]);
    echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}

http_response_code(404);
echo json_encode(['error'=>'not_found']);
