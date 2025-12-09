<?php
// Debug script to check what's happening with scores
require_once 'config.php';

echo "<h2>DEBUG: Score System Analysis</h2>";

try {
    // Check if database exists and has data
    echo "<h3>1. Database Connection Test</h3>";
    echo "<p style='color: green;'>✓ Database connected successfully!</p>";
    
    // Check games
    echo "<h3>2. Games in Database</h3>";
    $games_stmt = $pdo->query("SELECT * FROM games ORDER BY id");
    $games = $games_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($games)) {
        echo "<p style='color: red; font-weight: bold;'>❌ NO GAMES FOUND IN DATABASE!</p>";
        echo "<p>This is why you're getting the foreign key constraint error.</p>";
        echo "<p><a href='add_sample_data.php' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>CLICK HERE TO ADD SAMPLE DATA</a></p>";
    } else {
        echo "<p style='color: green;'>✓ Found " . count($games) . " games:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Points System</th></tr>";
        foreach ($games as $game) {
            echo "<tr>";
            echo "<td><strong>" . $game['id'] . "</strong></td>";
            echo "<td>" . htmlspecialchars($game['name']) . "</td>";
            echo "<td>" . htmlspecialchars($game['description'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($game['points_system'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check teams
    echo "<h3>3. Teams in Database</h3>";
    $teams_stmt = $pdo->query("SELECT * FROM teams ORDER BY id");
    $teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($teams)) {
        echo "<p style='color: red; font-weight: bold;'>❌ NO TEAMS FOUND IN DATABASE!</p>";
        echo "<p><a href='add_sample_data.php' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>CLICK HERE TO ADD SAMPLE DATA</a></p>";
    } else {
        echo "<p style='color: green;'>✓ Found " . count($teams) . " teams:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Code</th><th>Color</th></tr>";
        foreach ($teams as $team) {
            echo "<tr>";
            echo "<td><strong>" . $team['id'] . "</strong></td>";
            echo "<td>" . htmlspecialchars($team['name']) . "</td>";
            echo "<td>" . htmlspecialchars($team['code']) . "</td>";
            echo "<td style='background-color: " . $team['color'] . "; color: white;'>" . htmlspecialchars($team['color']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test API endpoints
    echo "<h3>4. API Endpoint Tests</h3>";
    echo "<p><a href='database_handler.php?action=games' target='_blank'>Test Games API</a></p>";
    echo "<p><a href='database_handler.php?action=teams' target='_blank'>Test Teams API</a></p>";
    echo "<p><a href='database_handler.php?action=scores' target='_blank'>Test Scores API</a></p>";
    
    // Check existing scores
    echo "<h3>5. Existing Scores</h3>";
    $scores_stmt = $pdo->query("SELECT * FROM scores ORDER BY id DESC LIMIT 5");
    $scores = $scores_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($scores)) {
        echo "<p style='color: orange;'>No scores found in database.</p>";
    } else {
        echo "<p>Recent scores:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Team ID</th><th>Game ID</th><th>Placement</th><th>Points</th></tr>";
        foreach ($scores as $score) {
            echo "<tr>";
            echo "<td>" . $score['id'] . "</td>";
            echo "<td>" . $score['team_id'] . "</td>";
            echo "<td>" . $score['game_id'] . "</td>";
            echo "<td>" . htmlspecialchars($score['placement']) . "</td>";
            echo "<td>" . $score['points'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>6. SOLUTION</h3>";
    if (empty($games) || empty($teams)) {
        echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #d32f2f; margin-top: 0;'>🚨 PROBLEM IDENTIFIED!</h4>";
        echo "<p><strong>The database is empty!</strong> This is why you're getting the foreign key constraint error.</p>";
        echo "<p><strong>SOLUTION:</strong> Click the button below to add sample data:</p>";
        echo "<p><a href='add_sample_data.php' style='background: #4caf50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🔧 ADD SAMPLE DATA NOW</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #2e7d32; margin-top: 0;'>✅ DATABASE LOOKS GOOD!</h4>";
        echo "<p>Your database has games and teams. The issue might be in the JavaScript.</p>";
        echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🎮 GO TO SCORESHEET</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #d32f2f; margin-top: 0;'>❌ DATABASE ERROR!</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Possible solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running</li>";
    echo "<li>Make sure MySQL service is started</li>";
    echo "<li>Import the finalscore.sql file into your database</li>";
    echo "<li>Check your database credentials in config.php</li>";
    echo "</ul>";
    echo "</div>";
}
?>
