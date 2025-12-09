<?php
// Simple Database-Driven Scoring System
require_once __DIR__ . '/../config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_score':
                $game_id = $_POST['game_id'];
                $team_id = $_POST['team_id'];
                $placement = $_POST['placement'];
                $points = $_POST['points'];
                $scorer_name = $_POST['scorer_name'];
                
                $stmt = $pdo->prepare("INSERT INTO scores (game_id, team_id, placement, points, scorer_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$game_id, $team_id, $placement, $points, $scorer_name]);
                
                // Update team stats
                $stmt = $pdo->prepare("UPDATE teams SET total_points = total_points + ?, games_played = games_played + 1 WHERE id = ?");
                $stmt->execute([$points, $team_id]);
                
                header('Location: simple_scoring.php?success=1');
                exit;
                break;
        }
    }
}

// Get all games
$games_stmt = $pdo->query("SELECT * FROM games ORDER BY name");
$games = $games_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teams
$teams_stmt = $pdo->query("SELECT * FROM teams ORDER BY name");
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current scores/rankings
$scores_stmt = $pdo->query("
    SELECT t.*, 
           COALESCE(SUM(s.points), 0) as total_points,
           COUNT(s.id) as games_played
    FROM teams t 
    LEFT JOIN scores s ON t.id = s.team_id 
    GROUP BY t.id 
    ORDER BY total_points DESC, games_played ASC
");
$rankings = $scores_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM - Simple Scoring</title>
    <link rel="stylesheet" href="scoresheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .scoring-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .game-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .game-card:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }
        .game-card.selected {
            border-color: #2563eb;
            background: #f0f7ff;
        }
        .scoring-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
        }
        .scoring-form.active {
            display: block;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .rankings-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .rankings-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .rankings-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        .rankings-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .rankings-table tr:hover {
            background: #f8f9fa;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body>
    <div class="scoring-container">
        <!-- Header -->
        <header style="text-align: center; margin-bottom: 30px;">
            <h1><i class="fas fa-trophy"></i> DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM - Simple Scoring</h1>
            <p>Database-Driven Scoring System</p>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Score added successfully!
            </div>
        <?php endif; ?>

        <!-- Game Selection -->
        <div class="game-selection">
            <h2><i class="fas fa-gamepad"></i> Select Game</h2>
            <div class="game-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card" onclick="selectGame(<?php echo $game['id']; ?>, '<?php echo htmlspecialchars($game['name']); ?>', '<?php echo htmlspecialchars($game['points_system']); ?>')">
                        <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                        <p><?php echo htmlspecialchars($game['description'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Scoring Form -->
        <div class="scoring-form" id="scoringForm">
            <h2><i class="fas fa-plus-circle"></i> Add Score</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_score">
                <input type="hidden" name="game_id" id="gameId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="selectedGame">Game:</label>
                        <input type="text" id="selectedGame" readonly>
                    </div>
                    <div class="form-group">
                        <label for="teamSelect">Team:</label>
                        <select name="team_id" id="teamSelect" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?> (<?php echo htmlspecialchars($team['code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="placement">Placement:</label>
                        <select name="placement" id="placement" required onchange="updatePoints()">
                            <option value="">Select Placement</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="points">Points:</label>
                        <input type="number" name="points" id="points" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="scorerName">Scorer:</label>
                    <input type="text" name="scorer_name" id="scorerName" placeholder="Who recorded this score?" required>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Score
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelScoring()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Rankings -->
        <div class="rankings">
            <h2><i class="fas fa-trophy"></i> Current Rankings</h2>
            <div class="rankings-table">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th>Code</th>
                            <th>Total Points</th>
                            <th>Games Played</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($rankings as $team): 
                        ?>
                            <tr>
                                <td><strong><?php echo $rank; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $team['color']; ?>;"></div>
                                        <?php echo htmlspecialchars($team['name']); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($team['code']); ?></strong></td>
                                <td><strong><?php echo $team['total_points']; ?></strong></td>
                                <td><?php echo $team['games_played']; ?></td>
                            </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentGame = null;
        let pointsSystem = {};

        function selectGame(gameId, gameName, pointsSystemJson) {
            // Remove previous selection
            document.querySelectorAll('.game-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            event.target.closest('.game-card').classList.add('selected');
            
            // Set current game
            currentGame = { id: gameId, name: gameName };
            pointsSystem = JSON.parse(pointsSystemJson);
            
            // Update form
            document.getElementById('gameId').value = gameId;
            document.getElementById('selectedGame').value = gameName;
            
            // Populate placement options
            const placementSelect = document.getElementById('placement');
            placementSelect.innerHTML = '<option value="">Select Placement</option>';
            
            Object.keys(pointsSystem).forEach(placement => {
                const option = document.createElement('option');
                option.value = placement;
                option.textContent = placement;
                placementSelect.appendChild(option);
            });
            
            // Show form
            document.getElementById('scoringForm').classList.add('active');
        }

        function updatePoints() {
            const placement = document.getElementById('placement').value;
            const pointsInput = document.getElementById('points');
            
            if (placement && pointsSystem[placement] !== undefined) {
                pointsInput.value = pointsSystem[placement];
            } else {
                pointsInput.value = '';
            }
        }

        function cancelScoring() {
            document.getElementById('scoringForm').classList.remove('active');
            document.querySelectorAll('.game-card').forEach(card => {
                card.classList.remove('selected');
            });
            currentGame = null;
            pointsSystem = {};
        }
    </script>
</body>
</html>
