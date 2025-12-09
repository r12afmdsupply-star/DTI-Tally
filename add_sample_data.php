<?php
// Add sample data to database
require_once 'config.php';

echo "<h2>Adding Sample Data</h2>";

try {
    // Add sample games
    $games = [
        ['name' => 'Basketball', 'description' => 'Basketball tournament', 'points_system' => '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4, "5th": 2}'],
        ['name' => 'Volleyball', 'description' => 'Volleyball tournament', 'points_system' => '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4}'],
        ['name' => 'Chess', 'description' => 'Chess competition', 'points_system' => '{"1st": 15, "2nd": 12, "3rd": 10, "4th": 8, "5th": 6}'],
        ['name' => 'Quiz Bee', 'description' => 'Academic quiz competition', 'points_system' => '{"1st": 20, "2nd": 15, "3rd": 10, "4th": 5}']
    ];

    echo "<h3>Adding Games...</h3>";
    foreach ($games as $game) {
        $stmt = $pdo->prepare("INSERT INTO games (name, description, points_system, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$game['name'], $game['description'], $game['points_system']]);
        if ($result) {
            echo "<p style='color: green;'>✓ Added game: " . htmlspecialchars($game['name']) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to add game: " . htmlspecialchars($game['name']) . "</p>";
        }
    }

    // Add sample teams
    $teams = [
        ['name' => 'Regional Office', 'code' => 'RO', 'color' => '#2563eb'],
        ['name' => 'General Santos City', 'code' => 'GSC', 'color' => '#e74c3c'],
        ['name' => 'Sultan Kudarat', 'code' => 'SK', 'color' => '#22c55e'],
        ['name' => 'South Cotabato', 'code' => 'SC', 'color' => '#facc15'],
        ['name' => 'Sarangani', 'code' => 'SAR', 'color' => '#8b5cf6']
    ];

    echo "<h3>Adding Teams...</h3>";
    foreach ($teams as $team) {
        $stmt = $pdo->prepare("INSERT INTO teams (name, code, color, total_points, games_played, created_at) VALUES (?, ?, ?, 0, 0, NOW())");
        $result = $stmt->execute([$team['name'], $team['code'], $team['color']]);
        if ($result) {
            echo "<p style='color: green;'>✓ Added team: " . htmlspecialchars($team['name']) . " (" . htmlspecialchars($team['code']) . ")</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to add team: " . htmlspecialchars($team['name']) . "</p>";
        }
    }

    echo "<h3>Sample data added successfully!</h3>";
    echo "<p><a href='check_database.php'>Check Database Contents</a></p>";
    echo "<p><a href='scoresheet.html'>Go to ScoreSheet</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
