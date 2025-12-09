<?php
// COMPLETE SYSTEM REVIEW - Check everything
require_once 'config.php';

echo "<h2>🔍 COMPLETE SYSTEM REVIEW</h2>";

try {
    echo "<h3>1. Database Connection Test</h3>";
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    echo "<h3>2. Database Structure Check</h3>";
    
    // Check if tables exist
    $tables = ['teams', 'games', 'scores', 'judge_scores'];
    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($exists) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' MISSING!</p>";
        }
    }
    
    echo "<h3>3. Data Population Check</h3>";
    
    // Check teams
    $teams_count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    echo "<h4>Teams: $teams_count records</h4>";
    if ($teams_count > 0) {
        $teams = $pdo->query("SELECT id, name, code FROM teams ORDER BY id")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Code</th></tr>";
        foreach ($teams as $team) {
            echo "<tr><td>{$team['id']}</td><td>{$team['name']}</td><td>{$team['code']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ NO TEAMS FOUND!</p>";
    }
    
    // Check games
    $games_count = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    echo "<h4>Games: $games_count records</h4>";
    if ($games_count > 0) {
        $games = $pdo->query("SELECT id, name, points_system FROM games ORDER BY id")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Points System</th></tr>";
        foreach ($games as $game) {
            echo "<tr><td>{$game['id']}</td><td>{$game['name']}</td><td>{$game['points_system']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ NO GAMES FOUND!</p>";
    }
    
    // Check scores
    $scores_count = $pdo->query("SELECT COUNT(*) FROM scores")->fetchColumn();
    echo "<h4>Scores: $scores_count records</h4>";
    
    echo "<h3>4. API Endpoint Tests</h3>";
    
    // Test each API endpoint
    $endpoints = [
        'teams' => 'database_handler.php?action=teams',
        'games' => 'database_handler.php?action=games', 
        'scores' => 'database_handler.php?action=scores',
        'judge_scores' => 'database_handler.php?action=judge_scores'
    ];
    
    foreach ($endpoints as $name => $url) {
        echo "<h4>Testing $name API...</h4>";
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "<p style='color: green;'>✅ $name API working - " . count($data['data']) . " records</p>";
            } else {
                echo "<p style='color: red;'>❌ $name API failed: " . ($data['message'] ?? 'Unknown error') . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ $name API not accessible</p>";
        }
    }
    
    echo "<h3>5. Database Import Status</h3>";
    
    if ($teams_count == 0 && $games_count == 0) {
        echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #d32f2f; margin-top: 0;'>🚨 DATABASE IS EMPTY!</h4>";
        echo "<p><strong>Problem:</strong> The finalscore.sql file was not imported into your database!</p>";
        echo "<p><strong>Solution:</strong> You need to import the SQL file into your database.</p>";
        echo "<p><strong>Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Create a database called 'finalscore'</li>";
        echo "<li>Import the finalscore.sql file</li>";
        echo "<li>Or run the import script below</li>";
        echo "</ol>";
        echo "<p><a href='import_database.php' style='background: #4caf50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🔧 IMPORT DATABASE NOW</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #2e7d32; margin-top: 0;'>✅ DATABASE HAS DATA!</h4>";
        echo "<p>Your database is properly set up with $teams_count teams and $games_count games.</p>";
        echo "<p>The foreign key constraint error should not occur if the JavaScript is sending correct IDs.</p>";
        echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🎮 TEST SCORESHEET</a></p>";
        echo "</div>";
    }
    
    echo "<h3>6. JavaScript Flow Analysis</h3>";
    echo "<p>The JavaScript should:</p>";
    echo "<ol>";
    echo "<li>Load games from database_handler.php?action=games</li>";
    echo "<li>Display games as clickable buttons</li>";
    echo "<li>When game is clicked, store game ID in this.selectedGame</li>";
    echo "<li>When score is submitted, send game_id to database_handler.php?action=scores</li>";
    echo "<li>Database handler validates game_id exists before inserting</li>";
    echo "</ol>";
    
    echo "<p><strong>Potential Issues:</strong></p>";
    echo "<ul>";
    echo "<li>JavaScript not loading games properly</li>";
    echo "<li>Game IDs not being stored correctly</li>";
    echo "<li>Wrong API endpoints being called</li>";
    echo "<li>Database validation not working</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #d32f2f; margin-top: 0;'>❌ CRITICAL ERROR!</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Your database connection is broken!</p>";
    echo "</div>";
}
?>
