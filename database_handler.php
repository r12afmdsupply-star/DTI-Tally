<?php
require_once 'config.php';

// Set headers first to prevent HTML output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'teams':
        handleTeams($method, $input);
        break;
    case 'games':
        handleGames($method, $input);
        break;
    case 'scores':
        handleScores($method, $input);
        break;
    case 'judge_scores':
        handleJudgeScores($method, $input);
        break;
    case 'judge_auth':
        handleJudgeAuth($method, $input);
        break;
    case 'judge_logout':
        handleJudgeLogout($method, $input);
        break;
case 'judge_event_status':
    handleJudgeEventStatus($method, $input);
    break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleTeams($method, $input) {
    global $pdo;
    
    try {
        switch ($method) {
            case 'GET':
                error_log("Loading teams from database");
                $stmt = $pdo->query("SELECT * FROM teams ORDER BY total_points DESC, name ASC");
                $teams = $stmt->fetchAll();
                error_log("Found " . count($teams) . " teams");
                echo json_encode(['success' => true, 'data' => $teams]);
                break;
            
        case 'POST':
            error_log("Creating team: " . json_encode($input));
            if (!isset($input['name']) || !isset($input['code'])) {
                echo json_encode(['success' => false, 'message' => 'Name and code required']);
                return;
            }
            
            // Check if event_type column exists, add it if not
            $eventTypeColumn = $pdo->query("SHOW COLUMNS FROM teams LIKE 'event_type'")->fetch();
            if (!$eventTypeColumn) {
                try {
                    $pdo->exec("ALTER TABLE teams ADD COLUMN event_type VARCHAR(100) DEFAULT NULL AFTER code");
                    error_log("Added event_type column to teams table");
                } catch (PDOException $e) {
                    error_log("Could not add event_type column: " . $e->getMessage());
                }
            }
            
            // Insert team with or without event_type
            if ($eventTypeColumn || isset($input['event_type'])) {
                $stmt = $pdo->prepare("INSERT INTO teams (name, code, event_type, color, members) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['name'],
                    $input['code'],
                    $input['event_type'] ?? null,
                    $input['color'] ?? '#2563eb',
                    $input['members'] ?? ''
                ]);
            } else {
            $stmt = $pdo->prepare("INSERT INTO teams (name, code, color, members) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([
                $input['name'],
                $input['code'],
                $input['color'] ?? '#2563eb',
                $input['members'] ?? ''
            ]);
            }
            
            if ($result) {
                error_log("Team created successfully with ID: " . $pdo->lastInsertId());
                echo json_encode(['success' => true, 'message' => 'Team created', 'id' => $pdo->lastInsertId()]);
            } else {
                error_log("Failed to create team");
                echo json_encode(['success' => false, 'message' => 'Failed to create team']);
            }
            break;
            
        case 'DELETE':
            if (!isset($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID required']);
                return;
            }
            
            $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
            $result = $stmt->execute([$input['id']]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Team deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete team']);
            }
            break;
        }
    } catch (Exception $e) {
        error_log("Error in handleTeams: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGames($method, $input) {
    global $pdo;
    
    try {
        switch ($method) {
        case 'GET':
            error_log("Loading games from database");
            $stmt = $pdo->query("SELECT * FROM games ORDER BY date_created DESC");
            $games = $stmt->fetchAll();
            
            // Parse JSON fields
            foreach ($games as &$game) {
                // Parse points_system if it's a JSON string
                if (isset($game['points_system']) && is_string($game['points_system'])) {
                    $game['points_system'] = json_decode($game['points_system'], true);
                }
                // Parse authorized_judges if it's a JSON string
                if (isset($game['authorized_judges']) && is_string($game['authorized_judges'])) {
                    $game['authorized_judges'] = json_decode($game['authorized_judges'], true);
                }
            }
            unset($game);
            
            error_log("Found " . count($games) . " games");
            echo json_encode(['success' => true, 'data' => $games]);
            break;
            
        case 'POST':
            error_log("Creating game: " . json_encode($input));
            if (!isset($input['name'])) {
                echo json_encode(['success' => false, 'message' => 'Name required']);
                return;
            }
            
            // Get category, default to 'scorer' if not set
            $category = $input['category'] ?? 'scorer';
            $judgeEventType = $input['judge_event_type'] ?? null;
            $authorizedJudges = $input['authorized_judges'] ?? null;
            $scoringFormula = $input['scoring_formula'] ?? 'legacy';
            
            // Check if columns exist
            $categoryColumn = $pdo->query("SHOW COLUMNS FROM games LIKE 'category'")->fetch();
            $judgeEventTypeColumn = $pdo->query("SHOW COLUMNS FROM games LIKE 'judge_event_type'")->fetch();
            $authorizedJudgesColumn = $pdo->query("SHOW COLUMNS FROM games LIKE 'authorized_judges'")->fetch();
            $scoringFormulaColumn = $pdo->query("SHOW COLUMNS FROM games LIKE 'scoring_formula'")->fetch();

            // Try to add scoring_formula column if it doesn't exist
            if (!$scoringFormulaColumn) {
                try {
                    $pdo->exec("ALTER TABLE games ADD COLUMN scoring_formula varchar(50) NOT NULL DEFAULT 'legacy' AFTER judge_event_type");
                    $scoringFormulaColumn = true;
                } catch (PDOException $e) {
                    error_log("Could not add scoring_formula column: " . $e->getMessage());
                }
            }
            
            // Try to add authorized_judges column if it doesn't exist
            if (!$authorizedJudgesColumn && $category === 'judge') {
                try {
                    $pdo->exec("ALTER TABLE games ADD COLUMN authorized_judges JSON DEFAULT NULL AFTER judge_event_type");
                    $authorizedJudgesColumn = true;
                } catch (PDOException $e) {
                    error_log("Could not add authorized_judges column: " . $e->getMessage());
                }
            }
            
            if ($categoryColumn && $judgeEventTypeColumn && $authorizedJudgesColumn && $scoringFormulaColumn) {
                // All columns exist - use full query
                $stmt = $pdo->prepare("INSERT INTO games (name, color, description, category, judge_event_type, authorized_judges, scoring_formula, points_system) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['name'],
                    $input['color'] ?? '#2563eb',
                    $input['description'] ?? '',
                    $category,
                    $category === 'judge' ? $judgeEventType : null,
                    $category === 'judge' && $authorizedJudges ? json_encode($authorizedJudges) : null,
                    $scoringFormula,
                    json_encode($input['points_system'] ?? [])
                ]);
            } else if ($categoryColumn && $judgeEventTypeColumn) {
                // Both category and judge_event_type columns exist
                $stmt = $pdo->prepare("INSERT INTO games (name, color, description, category, judge_event_type, points_system) VALUES (?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['name'],
                    $input['color'] ?? '#2563eb',
                    $input['description'] ?? '',
                    $category,
                    $category === 'judge' ? $judgeEventType : null,
                    json_encode($input['points_system'] ?? [])
                ]);
            } else if ($categoryColumn) {
                // Only category column exists
                $stmt = $pdo->prepare("INSERT INTO games (name, color, description, category, points_system) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['name'],
                    $input['color'] ?? '#2563eb',
                    $input['description'] ?? '',
                    $category,
                    json_encode($input['points_system'] ?? [])
                ]);
            } else {
                // Fallback for older database schema
                $stmt = $pdo->prepare("INSERT INTO games (name, color, description, points_system) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['name'],
                    $input['color'] ?? '#2563eb',
                    $input['description'] ?? '',
                    json_encode($input['points_system'] ?? [])
                ]);
            }
            
            if ($result) {
                error_log("Game created successfully with ID: " . $pdo->lastInsertId());
                echo json_encode(['success' => true, 'message' => 'Game created', 'id' => $pdo->lastInsertId()]);
            } else {
                error_log("Failed to create game");
                echo json_encode(['success' => false, 'message' => 'Failed to create game']);
            }
            break;

        case 'PUT':
            // Update game metadata such as scoring_formula
            error_log("Updating game (PUT): " . json_encode($input));

            if (!isset($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID required']);
                return;
            }

            $gameId = (int)$input['id'];

            // Currently we only support updating scoring_formula via this endpoint
            $updates = [];
            $params = [];

            if (isset($input['scoring_formula'])) {
                // Ensure column exists
                $scoringFormulaColumn = $pdo->query("SHOW COLUMNS FROM games LIKE 'scoring_formula'")->fetch();
                if (!$scoringFormulaColumn) {
                    try {
                        $pdo->exec("ALTER TABLE games ADD COLUMN scoring_formula varchar(50) NOT NULL DEFAULT 'legacy' AFTER judge_event_type");
                        $scoringFormulaColumn = true;
                    } catch (PDOException $e) {
                        error_log("Could not add scoring_formula column on PUT: " . $e->getMessage());
                    }
                }

                if ($scoringFormulaColumn) {
                    $updates[] = "scoring_formula = ?";
                    $params[] = $input['scoring_formula'];
                }
            }

            if (empty($updates)) {
                echo json_encode(['success' => false, 'message' => 'No updatable fields provided']);
                return;
            }

            $params[] = $gameId;

            $sql = "UPDATE games SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Game updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update game']);
            }
            break;
            
        case 'DELETE':
            if (!isset($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID required']);
                return;
            }
            
            $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
            $result = $stmt->execute([$input['id']]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Game deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete game']);
            }
            break;
        }
    } catch (Exception $e) {
        error_log("Error in handleGames: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleScores($method, $input) {
    global $pdo;
    
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT s.*, t.name as team_name, t.color as team_color, t.code as team_code, g.name as game_name
                FROM scores s
                JOIN teams t ON s.team_id = t.id
                JOIN games g ON s.game_id = g.id
                ORDER BY s.timestamp DESC
            ");
            $scores = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $scores]);
            break;
            
        case 'POST':
            // DEBUG: Log what we received
            error_log("SCORE INSERT DEBUG - Received data: " . json_encode($input));
            
            if (!isset($input['team_id']) || !isset($input['game_id']) || !isset($input['placement']) || !isset($input['points'])) {
                echo json_encode(['success' => false, 'message' => 'All fields required']);
                return;
            }
            
            // DEBUG: Log the IDs we're checking
            error_log("SCORE INSERT DEBUG - Checking game_id: " . $input['game_id']);
            error_log("SCORE INSERT DEBUG - Checking team_id: " . $input['team_id']);
            
            // Validate that game_id exists
            $gameCheck = $pdo->prepare("SELECT id FROM games WHERE id = ?");
            $gameCheck->execute([$input['game_id']]);
            $gameExists = $gameCheck->fetch();
            error_log("SCORE INSERT DEBUG - Game exists: " . ($gameExists ? 'YES' : 'NO'));
            
            if (!$gameExists) {
                // DEBUG: Show what games actually exist
                $allGames = $pdo->query("SELECT id, name FROM games")->fetchAll();
                error_log("SCORE INSERT DEBUG - Available games: " . json_encode($allGames));
                echo json_encode(['success' => false, 'message' => 'Game ID ' . $input['game_id'] . ' does not exist in database. Available games: ' . json_encode($allGames)]);
                return;
            }
            
            // Validate that team_id exists
            $teamCheck = $pdo->prepare("SELECT id FROM teams WHERE id = ?");
            $teamCheck->execute([$input['team_id']]);
            $teamExists = $teamCheck->fetch();
            error_log("SCORE INSERT DEBUG - Team exists: " . ($teamExists ? 'YES' : 'NO'));
            
            if (!$teamExists) {
                // DEBUG: Show what teams actually exist
                $allTeams = $pdo->query("SELECT id, name FROM teams")->fetchAll();
                error_log("SCORE INSERT DEBUG - Available teams: " . json_encode($allTeams));
                echo json_encode(['success' => false, 'message' => 'Team ID ' . $input['team_id'] . ' does not exist in database. Available teams: ' . json_encode($allTeams)]);
                return;
            }

            // Enforce single attempt per team per event
            $duplicateCheck = $pdo->prepare("SELECT id FROM scores WHERE team_id = ? AND game_id = ? LIMIT 1");
            $duplicateCheck->execute([$input['team_id'], $input['game_id']]);
            if ($duplicateCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'This team already has a score for the selected event. Only one submission per team is allowed.']);
                return;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO scores (team_id, game_id, placement, points, scorer) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([
                    $input['team_id'],
                    $input['game_id'],
                    $input['placement'],
                    $input['points'],
                    $input['scorer'] ?? 'Admin'
                ]);
                
                if ($result) {
                    // Update team stats
                    updateTeamStats($input['team_id']);
                    echo json_encode(['success' => true, 'message' => 'Score created', 'id' => $pdo->lastInsertId()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create score']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
    }
}

function handleJudgeScores($method, $input) {
    global $pdo;
    
    switch ($method) {
        case 'GET':
            // Check if game_id column exists
            $gameIdColumn = $pdo->query("SHOW COLUMNS FROM judge_scores LIKE 'game_id'")->fetch();
            
            if ($gameIdColumn) {
                $stmt = $pdo->query("
                    SELECT js.*, t.name as team_name, t.color as team_color, t.code as team_code, g.name as game_name
                    FROM judge_scores js
                    JOIN teams t ON js.team_id = t.id
                    LEFT JOIN games g ON js.game_id = g.id
                    ORDER BY js.timestamp DESC
                ");
            } else {
                $stmt = $pdo->query("
                    SELECT js.*, t.name as team_name, t.color as team_color, t.code as team_code
                    FROM judge_scores js
                    JOIN teams t ON js.team_id = t.id
                    ORDER BY js.timestamp DESC
                ");
            }
            $scores = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $scores]);
            break;
            
        case 'POST':
            if (!isset($input['judge_name']) || !isset($input['team_id'])) {
                echo json_encode(['success' => false, 'message' => 'Judge name and team ID required']);
                return;
            }
            
            // Check if game_id column exists, add it if not
            $gameIdColumn = $pdo->query("SHOW COLUMNS FROM judge_scores LIKE 'game_id'")->fetch();
            if (!$gameIdColumn && isset($input['game_id'])) {
                try {
                    $pdo->exec("ALTER TABLE judge_scores ADD COLUMN game_id INT(11) DEFAULT NULL AFTER team_id");
                    $gameIdColumn = true;
                } catch (PDOException $e) {
                    error_log("Could not add game_id column: " . $e->getMessage());
                }
            }
            
            // Check if participant_type column exists, add it if not
            $participantTypeColumn = $pdo->query("SHOW COLUMNS FROM judge_scores LIKE 'participant_type'")->fetch();
            if (!$participantTypeColumn) {
                try {
                    $pdo->exec("ALTER TABLE judge_scores ADD COLUMN participant_type VARCHAR(20) DEFAULT NULL AFTER team_id");
                    $participantTypeColumn = true;
                } catch (PDOException $e) {
                    error_log("Could not add participant_type column: " . $e->getMessage());
                }
            }
            
            // Check if details_json column exists, add it if not (for Beauty Pageant structured data)
            $detailsJsonColumn = $pdo->query("SHOW COLUMNS FROM judge_scores LIKE 'details_json'")->fetch();
            if (!$detailsJsonColumn) {
                try {
                    $pdo->exec("ALTER TABLE judge_scores ADD COLUMN details_json LONGTEXT NULL AFTER percentage");
                    $detailsJsonColumn = true;
                } catch (PDOException $e) {
                    error_log("Could not add details_json column: " . $e->getMessage());
                }
            }
            
            // Check if judge has already scored this team for this game (for UPDATE support)
            $participantType = $input['participant_type'] ?? null;
            $existingScore = null;
            
            if ($gameIdColumn && isset($input['game_id'])) {
                if ($participantTypeColumn && $participantType) {
                    // Check with participant_type
                    $checkStmt = $pdo->prepare("SELECT id FROM judge_scores WHERE judge_name = ? AND team_id = ? AND game_id = ? AND participant_type = ? LIMIT 1");
                    $checkStmt->execute([$input['judge_name'], $input['team_id'], $input['game_id'], $participantType]);
                } else {
                    // Check without participant_type (regular scoring)
                    $checkStmt = $pdo->prepare("SELECT id FROM judge_scores WHERE judge_name = ? AND team_id = ? AND game_id = ? AND (participant_type IS NULL OR participant_type = '') LIMIT 1");
                    $checkStmt->execute([$input['judge_name'], $input['team_id'], $input['game_id']]);
                }
            } else {
                if ($participantTypeColumn && $participantType) {
                    $checkStmt = $pdo->prepare("SELECT id FROM judge_scores WHERE judge_name = ? AND team_id = ? AND (game_id IS NULL OR game_id = 0) AND participant_type = ? LIMIT 1");
                    $checkStmt->execute([$input['judge_name'], $input['team_id'], $participantType]);
                } else {
                    $checkStmt = $pdo->prepare("SELECT id FROM judge_scores WHERE judge_name = ? AND team_id = ? AND (game_id IS NULL OR game_id = 0) AND (participant_type IS NULL OR participant_type = '') LIMIT 1");
                    $checkStmt->execute([$input['judge_name'], $input['team_id']]);
                }
            }
            $existingScore = $checkStmt->fetch();
            
            // Normalize details_json (store as JSON string if provided)
            $detailsJson = null;
            if (isset($input['details_json'])) {
                if (is_array($input['details_json']) || is_object($input['details_json'])) {
                    $detailsJson = json_encode($input['details_json']);
                } else {
                    $detailsJson = $input['details_json'];
                }
            }
            
            // If score exists and no ID provided, allow update (for backward compatibility)
            // If ID is provided in input, use it for update
            if ($existingScore && !isset($input['id'])) {
                $input['id'] = $existingScore['id'];
            }
            
            $totalScore = ($input['criteria1'] ?? 0) + ($input['criteria2'] ?? 0) + ($input['criteria3'] ?? 0) + ($input['criteria4'] ?? 0) + ($input['criteria5'] ?? 0);
            $percentage = ($totalScore / 100) * 100;
            
            // If ID is provided, update existing score
            if (isset($input['id']) && $input['id']) {
                // UPDATE existing score
                if ($gameIdColumn && isset($input['game_id'])) {
                    if ($participantTypeColumn) {
                        $stmt = $pdo->prepare("UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ?, details_json = ? WHERE id = ?");
                        $result = $stmt->execute([
                            $input['criteria1'] ?? 0,
                            $input['criteria2'] ?? 0,
                            $input['criteria3'] ?? 0,
                            $input['criteria4'] ?? 0,
                            $input['criteria5'] ?? 0,
                            $totalScore,
                            $percentage,
                            $detailsJson,
                            $input['id']
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ?, details_json = ? WHERE id = ?");
                        $result = $stmt->execute([
                            $input['criteria1'] ?? 0,
                            $input['criteria2'] ?? 0,
                            $input['criteria3'] ?? 0,
                            $input['criteria4'] ?? 0,
                            $input['criteria5'] ?? 0,
                            $totalScore,
                            $percentage,
                            $detailsJson,
                            $input['id']
                        ]);
                    }
                } else {
                    if ($participantTypeColumn) {
                        $stmt = $pdo->prepare("UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ?, details_json = ? WHERE id = ?");
                        $result = $stmt->execute([
                            $input['criteria1'] ?? 0,
                            $input['criteria2'] ?? 0,
                            $input['criteria3'] ?? 0,
                            $input['criteria4'] ?? 0,
                            $input['criteria5'] ?? 0,
                            $totalScore,
                            $percentage,
                            $detailsJson,
                            $input['id']
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ?, details_json = ? WHERE id = ?");
                        $result = $stmt->execute([
                            $input['criteria1'] ?? 0,
                            $input['criteria2'] ?? 0,
                            $input['criteria3'] ?? 0,
                            $input['criteria4'] ?? 0,
                            $input['criteria5'] ?? 0,
                            $totalScore,
                            $percentage,
                            $detailsJson,
                            $input['id']
                        ]);
                    }
                }
                
                if ($result) {
                    // Update team stats
                    updateTeamStats($input['team_id']);
                    echo json_encode(['success' => true, 'message' => 'Judge score updated', 'id' => $input['id']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update judge score']);
                }
            } else {
                // INSERT new score
            if ($gameIdColumn && isset($input['game_id'])) {
                if ($participantTypeColumn) {
                    $stmt = $pdo->prepare("INSERT INTO judge_scores (judge_name, game_id, team_id, participant_type, criteria1, criteria2, criteria3, criteria4, criteria5, total_score, percentage, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $input['judge_name'],
                        $input['game_id'] ?? null,
                        $input['team_id'],
                        $participantType,
                        $input['criteria1'] ?? 0,
                        $input['criteria2'] ?? 0,
                        $input['criteria3'] ?? 0,
                        $input['criteria4'] ?? 0,
                        $input['criteria5'] ?? 0,
                        $totalScore,
                        $percentage,
                        $detailsJson
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO judge_scores (judge_name, game_id, team_id, criteria1, criteria2, criteria3, criteria4, criteria5, total_score, percentage, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $input['judge_name'],
                        $input['game_id'] ?? null,
                        $input['team_id'],
                        $input['criteria1'] ?? 0,
                        $input['criteria2'] ?? 0,
                        $input['criteria3'] ?? 0,
                        $input['criteria4'] ?? 0,
                        $input['criteria5'] ?? 0,
                        $totalScore,
                        $percentage,
                        $detailsJson
                    ]);
                }
            } else {
                if ($participantTypeColumn) {
                    $stmt = $pdo->prepare("INSERT INTO judge_scores (judge_name, team_id, participant_type, criteria1, criteria2, criteria3, criteria4, criteria5, total_score, percentage, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $input['judge_name'],
                        $input['team_id'],
                        $participantType,
                        $input['criteria1'] ?? 0,
                        $input['criteria2'] ?? 0,
                        $input['criteria3'] ?? 0,
                        $input['criteria4'] ?? 0,
                        $input['criteria5'] ?? 0,
                        $totalScore,
                        $percentage,
                        $detailsJson
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO judge_scores (judge_name, team_id, criteria1, criteria2, criteria3, criteria4, criteria5, total_score, percentage, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $input['judge_name'],
                        $input['team_id'],
                        $input['criteria1'] ?? 0,
                        $input['criteria2'] ?? 0,
                        $input['criteria3'] ?? 0,
                        $input['criteria4'] ?? 0,
                        $input['criteria5'] ?? 0,
                        $totalScore,
                        $percentage,
                        $detailsJson
                    ]);
                }
            }
            
            if ($result) {
                // Update team stats
                updateTeamStats($input['team_id']);
                echo json_encode(['success' => true, 'message' => 'Judge score created', 'id' => $pdo->lastInsertId()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create judge score']);
                }
            }
            break;
            
        case 'PUT':
            // Handle UPDATE via PUT method
            if (!isset($input['judge_name']) || !isset($input['team_id'])) {
                echo json_encode(['success' => false, 'message' => 'Judge name and team ID required']);
                return;
            }
            
            if (!isset($input['id']) || !$input['id']) {
                echo json_encode(['success' => false, 'message' => 'Score ID required for update']);
                return;
            }
            
            $totalScore = ($input['criteria1'] ?? 0) + ($input['criteria2'] ?? 0) + ($input['criteria3'] ?? 0) + ($input['criteria4'] ?? 0) + ($input['criteria5'] ?? 0);
            $percentage = ($totalScore / 100) * 100;
            
            // Update the score
            $stmt = $pdo->prepare("UPDATE judge_scores SET criteria1 = ?, criteria2 = ?, criteria3 = ?, criteria4 = ?, criteria5 = ?, total_score = ?, percentage = ? WHERE id = ?");
            $result = $stmt->execute([
                $input['criteria1'] ?? 0,
                $input['criteria2'] ?? 0,
                $input['criteria3'] ?? 0,
                $input['criteria4'] ?? 0,
                $input['criteria5'] ?? 0,
                $totalScore,
                $percentage,
                $input['id']
            ]);
            
            if ($result) {
                // Update team stats
                updateTeamStats($input['team_id']);
                echo json_encode(['success' => true, 'message' => 'Judge score updated', 'id' => $input['id']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update judge score']);
            }
            break;
    }
}

// Helper function to calculate category-based rank score
function calculateCategoryBasedRankScore($teamId, $gameId, $allGameScores, $categories) {
    // Count total criteria across all categories
    $totalCriteria = 0;
    foreach ($categories as $category) {
        $totalCriteria += count($category['criteria'] ?? []);
    }
    
    if ($totalCriteria === 0) return 0;
    
    // Group scores by team and judge
    $scoresByTeam = [];
    $judges = [];
    
    foreach ($allGameScores as $score) {
        $scoreTeamId = $score['team_id'];
        $judgeName = $score['judge_name'];
        
        if (!isset($scoresByTeam[$scoreTeamId])) {
            $scoresByTeam[$scoreTeamId] = [];
        }
        if (!isset($scoresByTeam[$scoreTeamId][$judgeName])) {
            $scoresByTeam[$scoreTeamId][$judgeName] = [];
        }
        
        if (!in_array($judgeName, $judges)) {
            $judges[] = $judgeName;
        }
        
        for ($i = 1; $i <= $totalCriteria; $i++) {
            $scoresByTeam[$scoreTeamId][$judgeName][$i] = intval($score["criteria{$i}"] ?? 0);
        }
    }
    
    $teamIds = array_keys($scoresByTeam);
    $teamCount = count($teamIds);
    
    if ($teamCount === 0 || count($judges) === 0) return 0;
    
    // Calculate ranks for all teams and all criteria
    $teamAvgRanks = [];
    
    foreach ($teamIds as $tId) {
        $teamAvgRanks[$tId] = [];
        
        for ($criteriaNum = 1; $criteriaNum <= $totalCriteria; $criteriaNum++) {
            $ranks = [];
            
            foreach ($judges as $judgeName) {
                $thisTeamScore = $scoresByTeam[$tId][$judgeName][$criteriaNum] ?? 0;
                
                $rank = 1;
                foreach ($teamIds as $otherTeamId) {
                    if ($otherTeamId != $tId) {
                        $otherTeamScore = $scoresByTeam[$otherTeamId][$judgeName][$criteriaNum] ?? 0;
                        if ($otherTeamScore > $thisTeamScore) {
                            $rank++;
                        } else if ($otherTeamScore == $thisTeamScore && $otherTeamId < $tId) {
                            $rank++;
                        }
                    }
                }
                
                $ranks[] = $rank;
            }
            
            $avgRank = count($ranks) > 0 ? array_sum($ranks) / count($ranks) : $teamCount;
            $teamAvgRanks[$tId][$criteriaNum] = $avgRank;
        }
    }
    
    // Calculate final score using category structure
    $finalScore = 0;
    $globalCriteriaIndex = 1;
    
    foreach ($categories as $category) {
        $categoryScore = 0;
        $categoryMaxScore = 0;
        $categoryPercentage = $category['categoryPercentage'] ?? 0;
        
        foreach ($category['criteria'] ?? [] as $criteria) {
            $avgRank = $teamAvgRanks[$teamId][$globalCriteriaIndex] ?? $teamCount;
            $maxRank = $teamCount;
            
            // Convert rank to score: rank 1 (best) = 100, rank N (worst) = lower
            $rankScore = (($maxRank - $avgRank + 1) / $maxRank) * 100;
            
            // Criteria contributes: rankScore × (criteriaPercentage / 100)
            $criteriaPercentage = $criteria['percentage'] ?? 0;
            $criteriaContribution = $rankScore * ($criteriaPercentage / 100);
            $categoryScore += $criteriaContribution;
            $categoryMaxScore += 100 * ($criteriaPercentage / 100);
            
            $globalCriteriaIndex++;
        }
        
        // Category score = (categoryScore / categoryMaxScore) × categoryPercentage
        if ($categoryMaxScore > 0) {
            $categoryContribution = ($categoryScore / $categoryMaxScore) * $categoryPercentage;
            $finalScore += $categoryContribution;
        }
    }
    
    return round($finalScore, 2);
}

function updateTeamStats($teamId) {
    global $pdo;
    
    // Calculate total points and games played
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(s.points), 0) as total_points,
            COUNT(s.id) as games_played
        FROM scores s 
        WHERE s.team_id = ?
    ");
    $stmt->execute([$teamId]);
    $stats = $stmt->fetch();
    
    // Get all judge scores for this team, grouped by game
    $stmt = $pdo->prepare("
        SELECT 
            js.game_id,
            js.judge_name,
            js.criteria1,
            js.criteria2,
            js.criteria3,
            js.criteria4,
            js.criteria5,
            js.total_score
        FROM judge_scores js 
        WHERE js.team_id = ? AND js.game_id IS NOT NULL
        ORDER BY js.game_id
    ");
    $stmt->execute([$teamId]);
    $allJudgeScores = $stmt->fetchAll();
    
    // Group scores by game_id
    $scoresByGame = [];
    foreach ($allJudgeScores as $score) {
        $gameId = $score['game_id'];
        if (!isset($scoresByGame[$gameId])) {
            $scoresByGame[$gameId] = [];
        }
        $scoresByGame[$gameId][] = $score;
    }
    
    // Calculate final score for each game based on formula
    $judgePoints = 0;
    $judgeGames = 0;
    
    foreach ($scoresByGame as $gameId => $gameScores) {
        if (empty($gameScores)) continue;
        
        // Get game info including scoring_formula and criteria system
        $gameStmt = $pdo->prepare("SELECT scoring_formula, points_system FROM games WHERE id = ?");
        $gameStmt->execute([$gameId]);
        $game = $gameStmt->fetch();
        
        $scoringFormula = $game['scoring_formula'] ?? 'legacy';
        $pointsSystem = json_decode($game['points_system'] ?? '{}', true);
        $criteriaList = $pointsSystem['criteria'] ?? [];
        
        $finalScore = 0;
        
        if ($scoringFormula === 'beauty_pageant_formula') {
            // BEAUTY PAGEANT RANK-BASED FORMULA (Green Table Formula)
            // For rank-based scoring, we need ALL teams' scores for this game
            $allGameScoresStmt = $pdo->prepare("
                SELECT js.team_id, js.judge_name, js.criteria1, js.criteria2, js.criteria3, js.criteria4, js.criteria5
                FROM judge_scores js 
                WHERE js.game_id = ?
                ORDER BY js.team_id, js.judge_name
            ");
            $allGameScoresStmt->execute([$gameId]);
            $allGameScores = $allGameScoresStmt->fetchAll();
            
            if (count($allGameScores) > 0) {
                // Check if this is a category-based structure
                if (isset($pointsSystem['type']) && $pointsSystem['type'] === 'beauty_pageant' && 
                    isset($pointsSystem['categories']) && count($pointsSystem['categories']) > 0) {
                    // Category-based calculation
                    $finalScore = calculateCategoryBasedRankScore($teamId, $gameId, $allGameScores, $pointsSystem['categories']);
                } else {
                    // Legacy flat criteria structure
                    $criteriaCount = min(count($criteriaList), 5);
                    if ($criteriaCount > 0) {
                    // Get original criteria percentages
                    $originalPercentages = [];
                    for ($i = 0; $i < $criteriaCount; $i++) {
                        $criteria = $criteriaList[$i];
                        $originalPercentages[$i + 1] = $criteria['percentage'] ?? 0;
                    }
                    
                    // Group scores by team and judge
                    $scoresByTeam = [];
                    $judges = [];
                    
                    foreach ($allGameScores as $score) {
                        $teamId = $score['team_id'];
                        $judgeName = $score['judge_name'];
                        
                        if (!isset($scoresByTeam[$teamId])) {
                            $scoresByTeam[$teamId] = [];
                        }
                        if (!isset($scoresByTeam[$teamId][$judgeName])) {
                            $scoresByTeam[$teamId][$judgeName] = [];
                        }
                        
                        if (!in_array($judgeName, $judges)) {
                            $judges[] = $judgeName;
                        }
                        
                        for ($i = 1; $i <= $criteriaCount; $i++) {
                            $scoresByTeam[$teamId][$judgeName][$i] = intval($score["criteria{$i}"] ?? 0);
                        }
                    }
                    
                    $teamIds = array_keys($scoresByTeam);
                    $teamCount = count($teamIds);
                    
                    if ($teamCount > 0 && count($judges) > 0) {
                        // Step 1: For each criteria and each judge, rank all teams (1=best score, 2=second, etc.)
                        // Step 2: Average ranks per criteria across all judges
                        $teamAvgRanks = [];
                        
                        foreach ($teamIds as $teamId) {
                            $teamAvgRanks[$teamId] = [];
                            
                            for ($criteriaNum = 1; $criteriaNum <= $criteriaCount; $criteriaNum++) {
                                $ranks = [];
                                
                                // For each judge, rank this team among all teams for this criteria
                                foreach ($judges as $judgeName) {
                                    $thisTeamScore = $scoresByTeam[$teamId][$judgeName][$criteriaNum] ?? 0;
                                    
                                    // Count how many teams scored better (higher score = better, so lower rank)
                                    $rank = 1;
                                    foreach ($teamIds as $otherTeamId) {
                                        if ($otherTeamId != $teamId) {
                                            $otherTeamScore = $scoresByTeam[$otherTeamId][$judgeName][$criteriaNum] ?? 0;
                                            if ($otherTeamScore > $thisTeamScore) {
                                                $rank++;
                                            } else if ($otherTeamScore == $thisTeamScore && $otherTeamId < $teamId) {
                                                // Tie-breaker: use team ID for consistent ranking
                                                $rank++;
                                            }
                                        }
                                    }
                                    
                                    $ranks[] = $rank;
                                }
                                
                                // Average the ranks across all judges
                                $avgRank = count($ranks) > 0 ? array_sum($ranks) / count($ranks) : $teamCount;
                                $teamAvgRanks[$teamId][$criteriaNum] = $avgRank;
                            }
                        }
                        
                        // Step 3: Convert averaged ranks to weights (inverse - lower rank = higher weight)
                        // Step 4: Calculate final score for this specific team
                        // Note: $teamId here is the team we're calculating stats for (from function parameter)
                        
                        if (isset($teamAvgRanks[$teamId])) {
                            // Calculate inverse weights for this team
                            $inverseWeights = [];
                            $totalInverse = 0;
                            
                            for ($criteriaNum = 1; $criteriaNum <= $criteriaCount; $criteriaNum++) {
                                $avgRank = $teamAvgRanks[$teamId][$criteriaNum];
                                $inverseWeights[$criteriaNum] = $teamCount + 1 - $avgRank;
                                $totalInverse += $inverseWeights[$criteriaNum];
                            }
                            
                            // Calculate final score
                            // Based on green table: Step 4 applies original percentages adjusted by rank performance
                            if ($totalInverse > 0) {
                                // Calculate rank-based weights that redistribute original percentages
                                $rankBasedWeights = [];
                                $totalRankWeight = 0;
                                
                                for ($criteriaNum = 1; $criteriaNum <= $criteriaCount; $criteriaNum++) {
                                    $originalPct = $originalPercentages[$criteriaNum] ?? 0;
                                    $normalizedInverse = $inverseWeights[$criteriaNum] / $totalInverse;
                                    
                                    // Redistribute: better rank gets more of the original percentage
                                    $rankBonus = ($criteriaCount - $teamAvgRanks[$teamId][$criteriaNum] + 1) / $criteriaCount;
                                    $rankBasedWeights[$criteriaNum] = ($originalPct / 100) * (0.5 + 0.5 * $rankBonus);
                                    $totalRankWeight += $rankBasedWeights[$criteriaNum];
                                }
                                
                                // Normalize to ensure weights sum to 1
                                if ($totalRankWeight > 0) {
                                    for ($criteriaNum = 1; $criteriaNum <= $criteriaCount; $criteriaNum++) {
                                        $normalizedWeight = $rankBasedWeights[$criteriaNum] / $totalRankWeight;
                                        
                                        // Calculate score based on rank
                                        $maxRank = $teamCount;
                                        $avgRank = $teamAvgRanks[$teamId][$criteriaNum];
                                        $rankScore = (($maxRank - $avgRank + 1) / $maxRank) * 100;
                                        
                                        // Apply normalized weight
                                        $finalScore += $rankScore * $normalizedWeight;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        } else if ($scoringFormula === 'new_formula') {
            // RANKING FORMULA: Average criteria first, then apply weights
            $judgeCount = count($gameScores);
            if ($judgeCount > 0) {
                // Step 1: Average each criterion across all judges
                $averagedCriteria = [
                    1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0
                ];
                
                foreach ($gameScores as $score) {
                    $averagedCriteria[1] += ($score['criteria1'] ?? 0);
                    $averagedCriteria[2] += ($score['criteria2'] ?? 0);
                    $averagedCriteria[3] += ($score['criteria3'] ?? 0);
                    $averagedCriteria[4] += ($score['criteria4'] ?? 0);
                    $averagedCriteria[5] += ($score['criteria5'] ?? 0);
                }
                
                // Calculate averages
                foreach ($averagedCriteria as $key => $sum) {
                    $averagedCriteria[$key] = $sum / $judgeCount;
                }
                
                // Step 2: Apply weights from criteria system and sum
                $finalScore = 0;
                for ($i = 0; $i < min(count($criteriaList), 5); $i++) {
                    $criteria = $criteriaList[$i];
                    $percentage = $criteria['percentage'] ?? 0;
                    $weight = $percentage / 100; // Convert percentage to decimal (e.g., 40% = 0.40)
                    $criteriaNum = $i + 1;
                    $averagedValue = $averagedCriteria[$criteriaNum];
                    $finalScore += $averagedValue * $weight;
                }
            }
        } else {
            // AVERAGING FORMULA (legacy): Sum total_score, then average
            $totalScoreSum = 0;
            $judgeCount = 0;
            
            foreach ($gameScores as $score) {
                $totalScoreSum += ($score['total_score'] ?? 0);
                $judgeCount++;
            }
            
            if ($judgeCount > 0) {
                $finalScore = $totalScoreSum / $judgeCount;
            }
        }
        
        $judgePoints += round($finalScore, 2);
        $judgeGames += 1;
    }
    
    $totalPoints = ($stats['total_points'] ?? 0) + $judgePoints;
    $totalGames = ($stats['games_played'] ?? 0) + $judgeGames;
    
    // Update team
    $stmt = $pdo->prepare("UPDATE teams SET total_points = ?, games_played = ? WHERE id = ?");
    $stmt->execute([$totalPoints, $totalGames, $teamId]);
}

function handleJudgeAuth($method, $input) {
    global $pdo;
    
    try {
        switch ($method) {
            case 'POST':
                if (!isset($input['judge_name']) || empty(trim($input['judge_name']))) {
                    echo json_encode(['success' => false, 'message' => 'Judge name required']);
                    return;
                }
                
                $judgeName = trim($input['judge_name']);
                
                // Step 1: Check if judge exists in previous scores
                $stmt = $pdo->prepare("SELECT DISTINCT judge_name FROM judge_scores WHERE LOWER(judge_name) = LOWER(?) LIMIT 1");
                $stmt->execute([$judgeName]);
                $judgeExists = $stmt->fetch();
                
                if ($judgeExists) {
                    $_SESSION['judge_name'] = $judgeExists['judge_name'];
                    $_SESSION['judge_validated'] = true;
                    $_SESSION['judge_auth_method'] = 'database';
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Judge verified in database',
                        'judge_name' => $_SESSION['judge_name'],
                        'auth_method' => 'database'
                    ]);
                    return;
                }
                
                // Step 2: Check if judge is in any game's authorized_judges list
                $stmt = $pdo->query("SELECT authorized_judges FROM games WHERE category = 'judge' AND authorized_judges IS NOT NULL");
                $games = $stmt->fetchAll();
                
                $isAuthorized = false;
                $authorizedJudgeName = null;
                
                foreach ($games as $game) {
                    $authorizedJudges = json_decode($game['authorized_judges'], true);
                    if (is_array($authorizedJudges)) {
                        foreach ($authorizedJudges as $authJudge) {
                            if (strtolower(trim($authJudge)) === strtolower($judgeName)) {
                                $isAuthorized = true;
                                $authorizedJudgeName = $authJudge; // Use the exact name from the list
                                break 2;
                            }
                        }
                    }
                }
                
                if ($isAuthorized) {
                    $_SESSION['judge_name'] = $authorizedJudgeName;
                    $_SESSION['judge_validated'] = true;
                    $_SESSION['judge_auth_method'] = 'authorized_list';
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Judge authorized for at least one game',
                        'judge_name' => $_SESSION['judge_name'],
                        'auth_method' => 'authorized_list'
                    ]);
                    return;
                }
                
                // Step 3: Check if any games have authorized judges configured
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM games WHERE category = 'judge' AND authorized_judges IS NOT NULL AND JSON_LENGTH(authorized_judges) > 0");
                $result = $stmt->fetch();
                
                if ($result && $result['count'] == 0) {
                    // No authorized judges configured yet - allow access for backward compatibility
                    $_SESSION['judge_name'] = $judgeName;
                    $_SESSION['judge_validated'] = true;
                    $_SESSION['judge_auth_method'] = 'no_restrictions';
                    echo json_encode([
                        'success' => true, 
                        'message' => 'No authorized judges configured yet. Access granted.',
                        'judge_name' => $_SESSION['judge_name'],
                        'auth_method' => 'no_restrictions'
                    ]);
                    return;
                }
                
                // Judge is not authorized and not in database
                echo json_encode([
                    'success' => false, 
                    'message' => 'Judge name not found in database and not authorized for any games. Access denied.'
                ]);
                break;
                
            case 'GET':
                // Check current session
                if (isset($_SESSION['judge_validated']) && $_SESSION['judge_validated'] === true) {
                    echo json_encode([
                        'success' => true,
                        'judge_name' => $_SESSION['judge_name'] ?? '',
                        'validated' => true,
                        'auth_method' => $_SESSION['judge_auth_method'] ?? 'unknown'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'validated' => false,
                        'message' => 'No active judge session'
                    ]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    } catch (Exception $e) {
        error_log("Error in handleJudgeAuth: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleJudgeLogout($method, $input) {
    try {
        if ($method === 'POST' || $method === 'GET') {
            // Clear session data
            unset($_SESSION['judge_name']);
            unset($_SESSION['judge_validated']);
            unset($_SESSION['judge_auth_method']);
            
            // Optionally destroy the session
            // session_destroy();
            
            echo json_encode([
                'success' => true,
                'message' => 'Judge logged out successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
    } catch (Exception $e) {
        error_log("Error in handleJudgeLogout: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error logging out: ' . $e->getMessage()]);
    }
}

function ensureJudgeEventStatusTable() {
    global $pdo;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS judge_event_status (
            id INT(11) NOT NULL AUTO_INCREMENT,
            judge_name VARCHAR(255) NOT NULL,
            game_id INT(11) NOT NULL,
            status ENUM('pending','completed') NOT NULL DEFAULT 'pending',
            completed_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY judge_event_unique (judge_name(191), game_id),
            KEY idx_game_id (game_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function handleJudgeEventStatus($method, $input) {
    global $pdo;
    ensureJudgeEventStatusTable();

    try {
        switch ($method) {
            case 'GET':
                $judgeName = $_GET['judge_name'] ?? null;
                $gameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : null;

                $query = "SELECT judge_name, game_id, status, completed_at FROM judge_event_status WHERE 1=1";
                $params = [];

                if ($judgeName) {
                    $query .= " AND judge_name = ?";
                    $params[] = $judgeName;
                }

                if ($gameId) {
                    $query .= " AND game_id = ?";
                    $params[] = $gameId;
                }

                $query .= " ORDER BY completed_at DESC";

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $rows]);
                break;

            case 'POST':
                if (!isset($input['judge_name']) || !isset($input['game_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Judge name and game ID required']);
                    return;
                }

                $status = $input['status'] ?? 'completed';
                if (!in_array($status, ['pending', 'completed'], true)) {
                    $status = 'pending';
                }

                $completedAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;

                $stmt = $pdo->prepare("
                    INSERT INTO judge_event_status (judge_name, game_id, status, completed_at)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        status = VALUES(status),
                        completed_at = VALUES(completed_at)
                ");

                $result = $stmt->execute([
                    $input['judge_name'],
                    $input['game_id'],
                    $status,
                    $completedAt
                ]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Status updated']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    } catch (Exception $e) {
        error_log("Error in handleJudgeEventStatus: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
