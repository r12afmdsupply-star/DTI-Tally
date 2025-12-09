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
        handleGetJudgeScores();
        break;
    case 'POST':
        handleCreateJudgeScore($input);
        break;
    case 'PUT':
        handleUpdateJudgeScore($input);
        break;
    case 'DELETE':
        handleDeleteJudgeScore($input);
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function handleGetJudgeScores() {
    $sql = "SELECT js.*, t.name as team_name, t.color as team_color 
            FROM judge_scores js 
            JOIN teams t ON js.team_id = t.id 
            ORDER BY js.timestamp DESC";
    
    $scores = fetchData($sql);
    successResponse($scores, 'Judge scores retrieved successfully');
}

function handleCreateJudgeScore($data) {
    // Validate required fields
    if (!isset($data['judge_name']) || !isset($data['team_id']) || 
        !isset($data['criteria1']) || !isset($data['criteria2']) || 
        !isset($data['criteria3']) || !isset($data['criteria4']) || 
        !isset($data['criteria5'])) {
        errorResponse('Missing required fields');
    }
    
    // Validate criteria scores (0-20 each)
    $criteria = ['criteria1', 'criteria2', 'criteria3', 'criteria4', 'criteria5'];
    foreach ($criteria as $criterion) {
        $score = intval($data[$criterion]);
        if ($score < 0 || $score > 20) {
            errorResponse("Invalid score for {$criterion}. Must be between 0-20");
        }
    }
    
    // Calculate total score and percentage
    $totalScore = $data['criteria1'] + $data['criteria2'] + $data['criteria3'] + 
                  $data['criteria4'] + $data['criteria5'];
    $percentage = ($totalScore / 100) * 100;
    
    // Check if judge already scored this team
    $existingScore = fetchRow(
        "SELECT id FROM judge_scores WHERE judge_name = ? AND team_id = ?",
        [$data['judge_name'], $data['team_id']]
    );
    
    if ($existingScore) {
        errorResponse('Judge has already scored this team');
    }
    
    // Insert new judge score
    $sql = "INSERT INTO judge_scores (judge_name, team_id, criteria1, criteria2, criteria3, criteria4, criteria5, total_score, percentage) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $data['judge_name'],
        $data['team_id'],
        $data['criteria1'],
        $data['criteria2'],
        $data['criteria3'],
        $data['criteria4'],
        $data['criteria5'],
        $totalScore,
        $percentage
    ];
    
    $insertId = insertData($sql, $params);
    
    if ($insertId) {
        successResponse(['id' => $insertId, 'total_score' => $totalScore, 'percentage' => $percentage], 
                       'Judge score created successfully');
    } else {
        errorResponse('Failed to create judge score');
    }
}

function handleUpdateJudgeScore($data) {
    if (!isset($data['id'])) {
        errorResponse('Judge score ID is required');
    }
    
    // Validate criteria scores
    $criteria = ['criteria1', 'criteria2', 'criteria3', 'criteria4', 'criteria5'];
    foreach ($criteria as $criterion) {
        if (isset($data[$criterion])) {
            $score = intval($data[$criterion]);
            if ($score < 0 || $score > 20) {
                errorResponse("Invalid score for {$criterion}. Must be between 0-20");
            }
        }
    }
    
    // Calculate new total if criteria are provided
    if (isset($data['criteria1']) || isset($data['criteria2']) || isset($data['criteria3']) || 
        isset($data['criteria4']) || isset($data['criteria5'])) {
        
        // Get current scores
        $current = fetchRow("SELECT * FROM judge_scores WHERE id = ?", [$data['id']]);
        if (!$current) {
            errorResponse('Judge score not found');
        }
        
        // Update with new values or keep current ones
        $criteria1 = isset($data['criteria1']) ? $data['criteria1'] : $current['criteria1'];
        $criteria2 = isset($data['criteria2']) ? $data['criteria2'] : $current['criteria2'];
        $criteria3 = isset($data['criteria3']) ? $data['criteria3'] : $current['criteria3'];
        $criteria4 = isset($data['criteria4']) ? $data['criteria4'] : $current['criteria4'];
        $criteria5 = isset($data['criteria5']) ? $data['criteria5'] : $current['criteria5'];
        
        $totalScore = $criteria1 + $criteria2 + $criteria3 + $criteria4 + $criteria5;
        $percentage = ($totalScore / 100) * 100;
        
        $sql = "UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ? WHERE id = ?";
        $params = [$criteria1, $criteria2, $criteria3, $criteria4, $criteria5, $totalScore, $percentage, $data['id']];
    } else {
        $sql = "UPDATE judge_scores SET judge_name = ? WHERE id = ?";
        $params = [$data['judge_name'], $data['id']];
    }
    
    $affected = updateData($sql, $params);
    
    if ($affected > 0) {
        successResponse(null, 'Judge score updated successfully');
    } else {
        errorResponse('Failed to update judge score');
    }
}

function handleDeleteJudgeScore($data) {
    if (!isset($data['id'])) {
        errorResponse('Judge score ID is required');
    }
    
    $affected = updateData("DELETE FROM judge_scores WHERE id = ?", [$data['id']]);
    
    if ($affected > 0) {
        successResponse(null, 'Judge score deleted successfully');
    } else {
        errorResponse('Judge score not found or already deleted');
    }
}
?>
