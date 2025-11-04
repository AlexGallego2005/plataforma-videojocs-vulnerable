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
    $nom = $_POST['nom_joc'];
    $descripcio = $_POST['descripcio'] ?? '';
    $puntuacio_maxima = $_POST['puntuacio_maxima'] ?? 0;
    $nivells_totals = $_POST['nivells_totals'] ?? 1;
    $actiu = 1;

    $query = "INSERT INTO jocs (nom_joc, descripcio, puntuacio_maxima, nivells_totals, actiu)
            VALUES ('$nom', '$descripcio', $puntuacio_maxima, $nivells_totals, $actiu)";
    $pdo->query($query);
    echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

if (!isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Acció no especificada']);
    exit;
}

$action = $input['action'];

// Guardar partida
if ($action === 'save_game') {
    if (!isset($_SESSION['usuari_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autoritzat']);
        exit;
    }
    
    $usuari_id = $input['usuari_id'];
    $joc_id = $input['joc_id'];
    $nivell = $input['nivell'];
    $puntuacio = $input['puntuacio'];
    $durada = $input['durada'];
    $guanyat = $input['guanyat'];
    
    // Verificar que el usuario de la sesión coincide
    if ($usuari_id != $_SESSION['usuari_id']) {
        echo json_encode(['success' => false, 'message' => 'Usuari no vàlid']);
        exit;
    }
    
    try {
        // Guardar la partida
        $partida_id = saveGameMatch($usuari_id, $joc_id, $nivell, $puntuacio, $durada);
        
        // Actualizar progreso del usuario
        updateUserProgress($usuari_id, $joc_id, $nivell, $puntuacio, $guanyat);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Partida guardada correctament',
            'partida_id' => $partida_id
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al guardar la partida: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Obtener estadísticas
if ($action === 'get_stats') {
    if (!isset($_SESSION['usuari_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autoritzat']);
        exit;
    }
    
    $usuari_id = $_SESSION['usuari_id'];
    $joc_id = $input['joc_id'];
    
    $stats = getGameStats($usuari_id, $joc_id);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    exit;
}

// Obtener Ranking
if ($action === 'get_ranking') {
    // Verifica sesión
    if (!isset($_SESSION['usuari_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autoritzat']);
        exit;
    }

    // Obtener el ranking general o por juego
    $joc_id = $input['joc_id'] ?? null;

    // Llamamos a una función del modelo que haremos a continuación
    $ranking = getRanking($pdo, $joc_id);

    echo json_encode([
        'success' => true,
        'ranking' => $ranking
    ]);
    exit;
}


http_response_code(404);
echo json_encode(['error'=>'not_found']);


