<?php
// COMPLETE DATABASE IMPORT - Import the entire finalscore.sql
require_once 'config.php';

echo "<h2>🔧 COMPLETE DATABASE IMPORT</h2>";

try {
    echo "<h3>Step 1: Reading SQL File</h3>";
    
    $sql_file = 'finalscore.sql';
    if (!file_exists($sql_file)) {
        echo "<p style='color: red;'>❌ finalscore.sql file not found!</p>";
        exit;
    }
    
    $sql_content = file_get_contents($sql_file);
    echo "<p style='color: green;'>✅ SQL file read successfully (" . strlen($sql_content) . " characters)</p>";
    
    echo "<h3>Step 2: Executing SQL Commands</h3>";
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            
            // Show progress for important statements
            if (strpos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE `?(\w+)`?/', $statement, $matches);
                $table_name = $matches[1] ?? 'unknown';
                echo "<p style='color: green;'>✅ Created table: $table_name</p>";
            } elseif (strpos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO `?(\w+)`?/', $statement, $matches);
                $table_name = $matches[1] ?? 'unknown';
                echo "<p style='color: green;'>✅ Inserted data into: $table_name</p>";
            }
            
        } catch (PDOException $e) {
            $error_count++;
            echo "<p style='color: orange;'>⚠️ Statement " . ($index + 1) . " failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<h3>Step 3: Verification</h3>";
    
    // Check what was created
    $tables = ['teams', 'games', 'scores', 'judge_scores'];
    $created_tables = 0;
    
    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($exists) {
            $created_tables++;
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "<p style='color: green;'>✅ Table '$table' exists with $count records</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Check specific data
    $teams_count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    $games_count = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    
    echo "<h3>Step 4: Final Results</h3>";
    
    if ($created_tables == count($tables) && $teams_count > 0 && $games_count > 0) {
        echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #2e7d32; margin-top: 0;'>🎉 SUCCESS!</h4>";
        echo "<p><strong>Database imported successfully!</strong></p>";
        echo "<ul>";
        echo "<li>✅ $created_tables tables created</li>";
        echo "<li>✅ $teams_count teams imported</li>";
        echo "<li>✅ $games_count games imported</li>";
        echo "<li>✅ $success_count SQL statements executed</li>";
        if ($error_count > 0) {
            echo "<li>⚠️ $error_count statements had warnings (this is normal)</li>";
        }
        echo "</ul>";
        echo "<p><strong>The foreign key constraint error should now be FIXED!</strong></p>";
        echo "<p><a href='scoresheet.html' style='background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;'>🎮 TEST SCORESHEET NOW</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3e0; border: 2px solid #ff9800; padding: 20px; border-radius: 5px;'>";
        echo "<h4 style='color: #f57c00; margin-top: 0;'>⚠️ PARTIAL SUCCESS</h4>";
        echo "<p>Database import completed with some issues.</p>";
        echo "<p>Tables created: $created_tables/" . count($tables) . "</p>";
        echo "<p>Teams: $teams_count, Games: $games_count</p>";
        echo "<p><a href='complete_system_review.php' style='background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 REVIEW SYSTEM</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px;'>";
    echo "<h4 style='color: #d32f2f; margin-top: 0;'>❌ IMPORT FAILED!</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP and MySQL are running</li>";
    echo "<li>You have permission to create databases</li>";
    echo "<li>The finalscore.sql file exists</li>";
    echo "</ul>";
    echo "</div>";
}
?>
