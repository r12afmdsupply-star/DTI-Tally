<?php
// Simple database handler with better error handling
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'finalscore';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'teams' && $method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM teams ORDER BY total_points DESC, name ASC");
        $teams = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $teams]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading teams: ' . $e->getMessage()]);
    }
} elseif ($action === 'games' && $method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM games ORDER BY date_created DESC");
        $games = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $games]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading games: ' . $e->getMessage()]);
    }
} elseif ($action === 'teams' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO teams (name, code, color, members) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            $input['name'] ?? '',
            $input['code'] ?? '',
            $input['color'] ?? '#2563eb',
            $input['members'] ?? ''
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Team created', 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create team']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating team: ' . $e->getMessage()]);
    }
} elseif ($action === 'games' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO games (name, color, description, points_system) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            $input['name'] ?? '',
            $input['color'] ?? '#2563eb',
            $input['description'] ?? '',
            json_encode($input['points_system'] ?? [])
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Game created', 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create game']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating game: ' . $e->getMessage()]);
    }
} elseif ($action === 'scores' && $method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT s.*, t.name as team_name, t.color as team_color, t.code as team_code, g.name as game_name
            FROM scores s
            JOIN teams t ON s.team_id = t.id
            JOIN games g ON s.game_id = g.id
            ORDER BY s.timestamp DESC
        ");
        $scores = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $scores]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading scores: ' . $e->getMessage()]);
    }
} elseif ($action === 'scores' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO scores (team_id, game_id, placement, points, scorer) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $input['team_id'] ?? 0,
            $input['game_id'] ?? 0,
            $input['placement'] ?? '',
            $input['points'] ?? 0,
            $input['scorer'] ?? 'Admin'
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Score created', 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create score']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating score: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action or method']);
}
?>
