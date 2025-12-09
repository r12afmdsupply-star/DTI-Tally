<?php
// DIRECT FIX for the foreign key constraint issue
require_once 'config.php';

echo "<h2>🔧 DIRECT DATABASE FIX</h2>";

try {
    // First, let's check what's in the database
    echo "<h3>Step 1: Checking Current Database State</h3>";
    
    $games_count = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    $teams_count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    
    echo "<p>Games in database: <strong>$games_count</strong></p>";
    echo "<p>Teams in database: <strong>$teams_count</strong></p>";
    
    if ($games_count == 0 || $teams_count == 0) {
        echo "<h3>Step 2: Adding Sample Data</h3>";
        
        // Add sample games
        $games_data = [
            ['Basketball', 'Basketball tournament', '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4, "5th": 2}'],
            ['Volleyball', 'Volleyball tournament', '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4}'],
            ['Chess', 'Chess competition', '{"1st": 15, "2nd": 12, "3rd": 10, "4th": 8, "5th": 6}'],
            ['Swimming', 'Swimming competition', '{"1st": 12, "2nd": 10, "3rd": 8, "4th": 6}']
        ];
        
        echo "<p>Adding games...</p>";
        foreach ($games_data as $game) {
            $stmt = $pdo->prepare("INSERT INTO games (name, description, points_system, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute($game);
            echo "<p style='color: green;'>✓ Added: " . $game[0] . "</p>";
        }
        
        // Add sample teams
        $teams_data = [
            ['Regional Office', 'RO', '#e74c3c'],
            ['General Santos City', 'GSC', '#2563eb'],
            ['Sultan Kudarat', 'SK', '#22c55e'],
            ['South Cotabato', 'SC', '#facc15'],
            ['Sarangani', 'SAR', '#8b5cf6']
        ];
        
        echo "<p>Adding teams...</p>";
        foreach ($teams_data as $team) {
            $stmt = $pdo->prepare("INSERT INTO teams (name, code, color, total_points, games_played, created_at) VALUES (?, ?, ?, 0, 0, NOW())");
            $stmt->execute($team);
            echo "<p style='color: green;'>✓ Added: " . $team[0] . " (" . $team[1] . ")</p>";
        }
        
        echo "<h3>Step 3: Verification</h3>";
        
        // Verify games
        $games = $pdo->query("SELECT * FROM games ORDER BY id")->fetchAll();
        echo "<p><strong>Games now in database:</strong></p>";
        echo "<ul>";
        foreach ($games as $game) {
            echo "<li>ID: <strong>" . $game['id'] . "</strong> - " . $game['name'] . "</li>";
        }
        echo "</ul>";
        
        // Verify teams
        $teams = $pdo->query("SELECT * FROM teams ORDER BY id")->fetchAll();
        echo "<p><strong>Teams now in database:</strong></p>";
        echo "<ul>";
        foreach ($teams as $team) {
            echo "<li>ID: <strong>" . $team['id'] . "</strong> - " . $team['name'] . " (" . $team['code'] . ")</li>";
        }
        echo "</ul>";
        
        echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #2e7d32; margin-top: 0;'>🎉 SUCCESS!</h4>";
        echo "<p><strong>The foreign key constraint error should now be FIXED!</strong></p>";
        echo "<p>Your database now has games and teams with valid IDs.</p>";
        echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🎮 TEST THE SCORESHEET NOW</a></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fff3e0; border: 2px solid #ff9800; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #f57c00; margin-top: 0;'>⚠️ DATABASE ALREADY HAS DATA</h4>";
        echo "<p>Your database already has games and teams. The issue might be elsewhere.</p>";
        echo "<p><a href='debug_scores.php' style='background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 RUN DETAILED DEBUG</a></p>";
        echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎮 GO TO SCORESHEET</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #d32f2f; margin-top: 0;'>❌ ERROR!</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure XAMPP and MySQL are running!</p>";
    echo "</div>";
}
?>
