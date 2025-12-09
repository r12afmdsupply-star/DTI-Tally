<?php
// Common header for all pages
$pageTitle = $pageTitle ?? "DTI Sports Fest Tally";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="scoresheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="header-flex">
                <img src="logo.png" alt="DTI Logo" class="header-logo">
                <div>
                    <h1><i class="fas fa-trophy"></i> DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM</h1>
                    <p>ScoreSheet &amp; Leaderboards</p>
                </div>
            </div>
        </header>