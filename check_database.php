<?php
// Check database contents
require_once 'config.php';

echo "<h2>Database Contents Check</h2>";

// Check games
echo "<h3>Games in Database:</h3>";
$games_stmt = $pdo->query("SELECT * FROM games ORDER BY id");
$games = $games_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($games)) {
    echo "<p style='color: red;'>No games found in database!</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Points System</th><th>Created At</th></tr>";
    foreach ($games as $game) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($game['id']) . "</td>";
        echo "<td>" . htmlspecialchars($game['name']) . "</td>";
        echo "<td>" . htmlspecialchars($game['description'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($game['points_system'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($game['created_at'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check teams
echo "<h3>Teams in Database:</h3>";
$teams_stmt = $pdo->query("SELECT * FROM teams ORDER BY id");
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($teams)) {
    echo "<p style='color: red;'>No teams found in database!</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Code</th><th>Color</th><th>Total Points</th><th>Games Played</th></tr>";
    foreach ($teams as $team) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($team['id']) . "</td>";
        echo "<td>" . htmlspecialchars($team['name']) . "</td>";
        echo "<td>" . htmlspecialchars($team['code'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($team['color'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($team['total_points'] ?? '0') . "</td>";
        echo "<td>" . htmlspecialchars($team['games_played'] ?? '0') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check scores
echo "<h3>Scores in Database:</h3>";
$scores_stmt = $pdo->query("SELECT * FROM scores ORDER BY id DESC LIMIT 10");
$scores = $scores_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($scores)) {
    echo "<p style='color: orange;'>No scores found in database.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Team ID</th><th>Game ID</th><th>Placement</th><th>Points</th><th>Scorer</th><th>Timestamp</th></tr>";
    foreach ($scores as $score) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($score['id']) . "</td>";
        echo "<td>" . htmlspecialchars($score['team_id']) . "</td>";
        echo "<td>" . htmlspecialchars($score['game_id']) . "</td>";
        echo "<td>" . htmlspecialchars($score['placement']) . "</td>";
        echo "<td>" . htmlspecialchars($score['points']) . "</td>";
        echo "<td>" . htmlspecialchars($score['scorer']) . "</td>";
        echo "<td>" . htmlspecialchars($score['timestamp']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Database Schema Check:</h3>";
echo "<p><strong>Games table structure:</strong></p>";
$games_schema = $pdo->query("DESCRIBE games")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($games_schema, true) . "</pre>";

echo "<p><strong>Teams table structure:</strong></p>";
$teams_schema = $pdo->query("DESCRIBE teams")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($teams_schema, true) . "</pre>";

echo "<p><strong>Scores table structure:</strong></p>";
$scores_schema = $pdo->query("DESCRIBE scores")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($scores_schema, true) . "</pre>";
?>