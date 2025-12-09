<?php
require_once __DIR__ . '/../../config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        handleGetTeams();
        break;
    case 'POST':
        handleCreateTeam($input);
        break;
    case 'PUT':
        handleUpdateTeam($input);
        break;
    case 'DELETE':
        handleDeleteTeam($input);
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function handleGetTeams() {
    $sql = "SELECT * FROM teams ORDER BY total_points DESC, name ASC";
    $teams = fetchData($sql);
    successResponse($teams, 'Teams retrieved successfully');
}

function handleCreateTeam($data) {
    if (!isset($data['name']) || empty($data['name'])) {
        errorResponse('Team name is required');
    }
    
    if (!isset($data['code']) || empty($data['code'])) {
        errorResponse('Team code is required');
    }
    
    // Check if team name already exists
    $existing = fetchRow("SELECT id FROM teams WHERE name = ?", [$data['name']]);
    if ($existing) {
        errorResponse('Team with this name already exists');
    }
    
    // Note: Teams can have the same code (e.g., multiple teams from same region)
    // No need to check for unique team codes
    
    $sql = "INSERT INTO teams (name, code, color, members, total_points, games_played) VALUES (?, ?, ?, ?, ?, ?)";
    $params = [
        $data['name'],
        $data['code'],
        $data['color'] ?? '#2563eb',
        $data['members'] ?? '',
        0,
        0
    ];
    
    $insertId = insertData($sql, $params);
    
    if ($insertId) {
        successResponse(['id' => $insertId], 'Team created successfully');
    } else {
        errorResponse('Failed to create team');
    }
}

function handleUpdateTeam($data) {
    if (!isset($data['id'])) {
        errorResponse('Team ID is required');
    }
    
    $sql = "UPDATE teams SET name = ?, code = ?, color = ?, members = ? WHERE id = ?";
    $params = [
        $data['name'],
        $data['code'],
        $data['color'],
        $data['members'],
        $data['id']
    ];
    
    $affected = updateData($sql, $params);
    
    if ($affected > 0) {
        successResponse(null, 'Team updated successfully');
    } else {
        errorResponse('Team not found or no changes made');
    }
}

function handleDeleteTeam($data) {
    if (!isset($data['id'])) {
        errorResponse('Team ID is required');
    }
    
    $affected = updateData("DELETE FROM teams WHERE id = ?", [$data['id']]);
    
    if ($affected > 0) {
        successResponse(null, 'Team deleted successfully');
    } else {
        errorResponse('Team not found or already deleted');
    }
}
?>
