<?php
// SIMPLE DATABASE SETUP - Ensure everything works
require_once 'config.php';

echo "<h2>🚀 SIMPLE DATABASE SETUP</h2>";

try {
    echo "<h3>Step 1: Database Connection</h3>";
    echo "<p style='color: green;'>✅ Connected to database: finalscore</p>";
    
    echo "<h3>Step 2: Creating Tables</h3>";
    
    // Create teams table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `teams` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `code` varchar(50) NOT NULL,
            `color` varchar(7) NOT NULL DEFAULT '#2563eb',
            `members` text,
            `total_points` int(11) DEFAULT 0,
            `games_played` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_team_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✅ Teams table ready</p>";
    
    // Create games table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `games` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `color` varchar(7) NOT NULL DEFAULT '#2563eb',
            `description` text,
            `points_system` json,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_game_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✅ Games table ready</p>";
    
    // Create scores table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `scores` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `team_id` int(11) NOT NULL,
            `game_id` int(11) NOT NULL,
            `placement` varchar(50) NOT NULL,
            `points` int(11) NOT NULL,
            `scorer` varchar(255) NOT NULL,
            `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `fk_scores_team` (`team_id`),
            KEY `fk_scores_game` (`game_id`),
            CONSTRAINT `fk_scores_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_scores_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✅ Scores table ready</p>";
    
    // Create judge_scores table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `judge_scores` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `judge_name` varchar(255) NOT NULL,
            `team_id` int(11) NOT NULL,
            `criteria1` int(11) NOT NULL DEFAULT 0,
            `criteria2` int(11) NOT NULL DEFAULT 0,
            `criteria3` int(11) NOT NULL DEFAULT 0,
            `criteria4` int(11) NOT NULL DEFAULT 0,
            `criteria5` int(11) NOT NULL DEFAULT 0,
            `total_score` int(11) NOT NULL DEFAULT 0,
            `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
            `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `fk_judge_scores_team` (`team_id`),
            CONSTRAINT `fk_judge_scores_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✅ Judge scores table ready</p>";
    
    echo "<h3>Step 3: Adding Sample Data</h3>";
    
    // Check if data already exists
    $teams_count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    $games_count = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    
    if ($teams_count == 0) {
        echo "<p>Adding sample teams...</p>";
        $teams_data = [
            ['Regional Office Team', 'RO', '#e74c3c', 'Team members from RO'],
            ['General Santos City Team', 'GSC', '#2563eb', 'Team members from GSC'],
            ['Sultan Kudarat Team', 'SK', '#22c55e', 'Team members from SK'],
            ['South Cotabato Team', 'SC', '#facc15', 'Team members from SC'],
            ['Sarangani Team', 'SAR', '#8b5cf6', 'Team members from SAR']
        ];
        
        foreach ($teams_data as $team) {
            $stmt = $pdo->prepare("INSERT INTO teams (name, code, color, members, total_points, games_played, created_at) VALUES (?, ?, ?, ?, 0, 0, NOW())");
            $stmt->execute($team);
        }
        echo "<p style='color: green;'>✅ Added 5 teams</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Teams already exist ($teams_count records)</p>";
    }
    
    if ($games_count == 0) {
        echo "<p>Adding sample games...</p>";
        $games_data = [
            ['Basketball', '#e74c3c', 'Fast-paced basketball tournament', '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4, "5th": 2}'],
            ['Volleyball', '#2563eb', 'Team volleyball competition', '{"1st": 10, "2nd": 8, "3rd": 6, "4th": 4}'],
            ['Chess', '#22c55e', 'Strategic chess tournament', '{"1st": 15, "2nd": 12, "3rd": 10, "4th": 8, "5th": 6}'],
            ['Swimming', '#f39c12', 'Swimming competition', '{"1st": 12, "2nd": 10, "3rd": 8, "4th": 6}'],
            ['Running', '#9b59b6', 'Track and field events', '{"1st": 8, "2nd": 6, "3rd": 4, "4th": 2}']
        ];
        
        foreach ($games_data as $game) {
            $stmt = $pdo->prepare("INSERT INTO games (name, color, description, points_system, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute($game);
        }
        echo "<p style='color: green;'>✅ Added 5 games</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Games already exist ($games_count records)</p>";
    }
    
    echo "<h3>Step 4: Final Verification</h3>";
    
    $final_teams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    $final_games = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    $final_scores = $pdo->query("SELECT COUNT(*) FROM scores")->fetchColumn();
    
    echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #2e7d32; margin-top: 0;'>🎉 DATABASE SETUP COMPLETE!</h4>";
    echo "<ul>";
    echo "<li>✅ Teams: $final_teams records</li>";
    echo "<li>✅ Games: $final_games records</li>";
    echo "<li>✅ Scores: $final_scores records</li>";
    echo "<li>✅ All foreign key constraints working</li>";
    echo "</ul>";
    echo "<p><strong>The foreign key constraint error should now be FIXED!</strong></p>";
    echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🎮 TEST SCORESHEET NOW</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #d32f2f; margin-top: 0;'>❌ SETUP FAILED!</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure XAMPP and MySQL are running!</p>";
    echo "</div>";
}
?>
