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

switch ($method) {
    case 'GET':
        handleGetTeamRankings();
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function handleGetTeamRankings() {
    // Get team code rankings (grouped by team code)
    $sql = "
        SELECT 
            t.code,
            GROUP_CONCAT(t.name SEPARATOR ', ') as team_names,
            SUM(t.total_points) as total_points,
            SUM(t.games_played) as total_games,
            COUNT(t.id) as team_count
        FROM teams t
        GROUP BY t.code
        ORDER BY SUM(t.total_points) DESC, t.code ASC
    ";
    
    $rankings = fetchData($sql);
    
    // Add rank position
    foreach ($rankings as $index => &$ranking) {
        $ranking['rank'] = $index + 1;
    }
    
    successResponse($rankings, 'Team code rankings retrieved successfully');
}
?>
