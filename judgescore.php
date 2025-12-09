<?php
require_once 'config.php';

// Check if judge is validated via session
if (!isset($_SESSION['judge_validated']) || $_SESSION['judge_validated'] !== true || !isset($_SESSION['judge_name'])) {
    // No valid session - redirect to entry page
    header('Location: judge_entry.php');
    exit;
}

$judgeName = $_SESSION['judge_name'];
$preferredEventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
$pageTitle = "Judge Scoring - DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM";
include 'includes/header.php';
?>
<style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1d4ed8;
            --light-blue: #dbeafe;
            --surface-white: #ffffff;
            --border-light: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --success-green: #22c55e;
            --warning-yellow: #fbbf24;
            --warning-orange: #f97316;
            --error-red: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: var(--text-dark);
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 20px 40px;
        }

        /* Header Styles */
        .judge-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
            position: relative;
            overflow: hidden;
        }

        .judge-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-20px, -20px) rotate(360deg); }
        }

        .judge-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .judge-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .judge-header i {
            margin-right: 15px;
            font-size: 2.2rem;
        }

        /* Back Button */
        .back-btn {
            position: fixed;
            top: 30px;
            left: 30px;
            background: var(--primary-blue);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-btn:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        /* Main Grid Layout - Landscape optimized */
        .main-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card Styles */
        .card {
            background: var(--surface-white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card h3 i {
            font-size: 1.3rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--surface-white);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group small {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 6px;
            display: block;
            line-height: 1.4;
        }


        /* Criteria Scoring Grid */
        .criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .criteria-item {
            background: var(--surface-white);
            padding: 10px 8px;
            border-radius: 6px;
            border: 2px solid var(--border-light);
            text-align: center;
            transition: all 0.3s ease;
        }

        .criteria-item:hover {
            border-color: var(--primary-blue);
            transform: translateY(-2px);
        }

        .criteria-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-size: 0.85rem;
            line-height: 1.2;
        }

        .criteria-item input {
            width: 100%;
            padding: 6px;
            border: 2px solid var(--border-light);
            border-radius: 5px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 3px;
        }

        .criteria-item input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .criteria-label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
        }

        /* Total Score Display */
        .total-score-display {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
        }

        .score-summary {
            margin-bottom: 15px;
        }

        .total-label {
            font-size: 1.2rem;
            font-weight: 600;
            margin-right: 10px;
        }

        .total-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-right: 5px;
        }

        .total-percentage {
            font-size: 1.3rem;
            font-weight: 600;
            opacity: 0.9;
        }

        .score-percentage {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Judge Actions */
        .judge-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 40px 0;
        }

        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: var(--surface-white);
            color: var(--text-dark);
            border: 2px solid var(--border-light);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: var(--primary-blue);
            transform: translateY(-2px);
        }

        /* Judge History */
        .judge-history {
            background: var(--surface-white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
        }

        .judge-history h3 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface-white);
        }

        .excel-table th {
            background: var(--primary-blue);
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .excel-table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9rem;
        }

        .excel-table tr:hover {
            background: var(--light-blue);
        }

        .excel-table tr:last-child td {
            border-bottom: none;
        }

        /* Delete Button */
        .btn-danger {
            background: var(--error-red);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }




        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .judge-header {
                padding: 20px;
                margin-bottom: 20px;
            }

            .judge-header h1 {
                font-size: 2rem;
            }

            .back-btn {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
            }

            .card {
                padding: 20px;
            }

            .criteria-grid {
                grid-template-columns: 1fr;
            }

            .judge-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .main-grid {
                gap: 20px;
            }

        }

        /* Score Color States */
        .score-excellent { color: var(--success-green); }
        .score-good { color: var(--warning-yellow); }
        .score-fair { color: var(--warning-orange); }
        .score-poor { color: var(--error-red); }

        /* Beauty Pageant Category Styles */
        .beauty-categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .beauty-category-section {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            min-height: fit-content;
        }

        .beauty-category-section h4 {
            color: #6b21a8;
            margin-bottom: 10px;
            font-size: 1rem;
            font-weight: 600;
        }

        .beauty-criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 8px;
            margin-bottom: 8px;
            flex: 1;
        }

        .beauty-category-total-box {
            padding: 6px;
            background: #f3e8ff;
            border-radius: 6px;
            border: 2px solid #8b5cf6;
            text-align: center;
            margin-top: auto;
        }

        .beauty-category-total-box strong {
            color: #6b21a8;
            font-size: 0.85rem;
        }

        /* Left column compact styling */
        .left-column {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .left-column .card {
            padding: 20px;
        }

        .left-column .card h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        /* Right column full width */
        .right-column {
            min-width: 0; /* Allow flex shrinking */
        }

        .right-column .card {
            padding: 20px;
        }

        /* Desktop: force 2-column landscape so form is not too tall */
        @media (min-width: 1024px) {
            .beauty-categories-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success Animation */
        @keyframes success {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success-animation {
            animation: success 0.6s ease-in-out;
        }
    </style>
</head>
<body>
        <!-- Back Button -->
        <a href="#" id="changeJudgeBtn" class="back-btn" onclick="handleChangeJudge(event); return false;">
            <i class="fas fa-arrow-left"></i> Change Judge
        </a>

        <!-- Judge Header -->
        <div class="judge-header">
            <h1><i class="fas fa-gavel"></i> Judge Scoring </h1>
            <p>Professional Scoring Interface for DTI Judges</p>
        </div>


        <!-- Main Grid Layout -->
        <div class="main-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Judge Information -->
                <div class="card">
                    <h3><i class="fas fa-user-tie"></i> Judge Information</h3>
                    <div class="form-group">
                        <label>Judge Name</label>
                        <div id="judgeDisplay" style="padding: 12px; background: #f3f4f6; border-radius: 8px; font-weight: 600; color: var(--text-dark);">
                            <i class="fas fa-user-tie"></i> <span id="judgeNameDisplay"><?php echo htmlspecialchars($judgeName); ?></span>
                        </div>
                        <small style="color: var(--text-muted); margin-top: 5px; display: block;">
                            Judge assigned to selected game/event
                        </small>
                    </div>
                </div>

                <!-- Team Selection -->
                <div class="card">
                    <h3><i class="fas fa-users"></i> Select Team</h3>
                    <div class="form-group">
                        <label for="judgeTeamSelect">Choose Team</label>
                        <select id="judgeTeamSelect" required>
                            <option value="">Loading teams from database...</option>
                        </select>
                        <small>Select the team you want to evaluate and score</small>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Criteria Scoring -->
                <div class="card">
                    <h3><i class="fas fa-star"></i> Scoring Panel</h3>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem; line-height: 1.4;" id="criteriaInstructions">
                        Score each criteria based on the game's evaluation criteria.
                        Be fair and consistent in your evaluations.
                    </p>
                    
                    <div class="criteria-grid" id="criteriaGrid">
                        <!-- Criteria will be dynamically populated from game's criteria system -->
                        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-spinner fa-spin"></i> Loading criteria...
                        </div>
                    </div>
                    
                </div>

                <!-- Action Buttons -->
                <div class="judge-actions">
                    <button type="button" class="btn btn-primary" id="submitJudgeScore">
                        <i class="fas fa-paper-plane"></i> Submit Score
                    </button>
                    <button type="button" class="btn btn-secondary" id="clearJudgeForm">
                        <i class="fas fa-eraser"></i> Clear Form
                    </button>
                    <button type="button" class="btn btn-secondary" id="markEventCompleteBtn">
                        <i class="fas fa-flag-checkered"></i> Mark Event Done
                    </button>
                </div>
            </div>
        </div>

        <!-- Judge Scores History (hidden/removed in judge UI by request) -->

    <script>
        // Global variables
        let teams = [];
        let games = [];
        let judgeScores = [];
        let selectedGameId = null;
        let selectedGame = null;
        let completedEvents = {};

        // Judge name from PHP session
        const sessionJudgeName = '<?php echo addslashes($judgeName); ?>';
        const preferredEventId = <?php echo $preferredEventId !== null ? $preferredEventId : 'null'; ?>;
        let currentAssignedJudge = sessionJudgeName; // Will be updated when game is selected

        // Initialize page
        document.addEventListener('DOMContentLoaded', async function() {
            // Verify session is still valid (double-check)
            try {
                const response = await fetch('database_handler.php?action=judge_auth');
                const result = await response.json();
                
                if (!result.success || !result.validated || !result.judge_name) {
                    // Session expired or invalid - redirect to entry page
                    alert('Your session has expired. Please log in again.');
                    window.location.href = 'judge_entry.php';
                    return;
                }
                
                // Update judge display
                document.getElementById('judgeNameDisplay').textContent = result.judge_name;
                currentAssignedJudge = result.judge_name;
            } catch (error) {
                console.error('Error checking session:', error);
                // On error, still allow but log it
                console.warn('Session check failed, but continuing with PHP session data');
            }
            
            await loadCompletedEvents();
            loadGamesFromDatabase();
            loadTeamsFromDatabase();
            setupEventListeners();
            updateJudgeTotalScore();
        });

        // Handle Change Judge button click
        async function handleChangeJudge(event) {
            event.preventDefault();
            
            try {
                // Logout from session
                const response = await fetch('database_handler.php?action=judge_logout', {
                    method: 'POST'
                });
                const result = await response.json();
                
                // Redirect to entry page
                window.location.href = 'judge_entry.php';
            } catch (error) {
                console.error('Error logging out:', error);
                // Still redirect even if logout fails
                window.location.href = 'judge_entry.php';
            }
        }

        // Load games from database via API and auto-assign judge's game
        async function loadGamesFromDatabase() {
            try {
                const response = await fetch('database_handler.php?action=games');
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Filter to only show judge category games
                    games = result.data.filter(game => game.category === 'judge');
                    
                    const sessionJudge = sessionJudgeName.trim();
                    const isJudgeAssigned = (game) => {
                        const authorizedJudges = game.authorized_judges;
                        if (!authorizedJudges || !Array.isArray(authorizedJudges) || authorizedJudges.length === 0) {
                            return true;
                        }
                        return authorizedJudges.some(judge =>
                            judge && judge.trim().toLowerCase() === sessionJudge.toLowerCase()
                        );
                    };

                    // Automatically find the game (priority to preferred event id if provided)
                    let assignedGame = null;

                    if (preferredEventId) {
                        assignedGame = games.find(game => game.id == preferredEventId && isJudgeAssigned(game));
                        if (assignedGame && completedEvents[assignedGame.id] === 'completed') {
                            assignedGame = null;
                            showAlert('The selected event has already been marked as completed. Loading your active events.', 'warning');
                        } else if (!assignedGame) {
                            showAlert('The selected event is not available or you are not authorized for it. Loading your default assignment.', 'error');
                        }
                    }
                    
                    if (!assignedGame) {
                        for (const game of games) {
                            if (isJudgeAssigned(game) && completedEvents[game.id] !== 'completed') {
                                assignedGame = game;
                                break;
                            }
                        }
                    }
                    
                    if (assignedGame) {
                        if (completedEvents[assignedGame.id] === 'completed') {
                            showAlert('This event has been marked as completed. Redirecting to your dashboard.', 'warning');
                            setTimeout(() => window.location.href = 'judge_dashboard.php', 2000);
                            return;
                        }
                        
                        // Auto-assign the game
                        selectedGame = assignedGame;
                        selectedGameId = assignedGame.id;
                        
                        // Set the assigned judge name
                        const authorizedJudges = assignedGame.authorized_judges;
                        if (authorizedJudges && Array.isArray(authorizedJudges)) {
                            const assignedJudge = authorizedJudges.find(judge => 
                                judge && judge.trim().toLowerCase() === sessionJudge.toLowerCase()
                            );
                            if (assignedJudge) {
                                currentAssignedJudge = assignedJudge;
                                document.getElementById('judgeNameDisplay').textContent = assignedJudge;
                            }
                        }
                        
                        // Update criteria labels from game's criteria system
                        updateCriteriaLabels(assignedGame);
                        
                        // Refresh team select to show only teams for this event type
                        populateJudgeTeamSelect();
                        
                        showAlert(`Game assigned: <strong>${assignedGame.name}</strong>`, 'success');
                    } else {
                        // No active game assigned - show error
                        showAlert('You do not have any active events to score. Redirecting to your dashboard.', 'error');
                        setTimeout(() => window.location.href = 'judge_dashboard.php', 2500);
                    }
                } else {
                    throw new Error(result.message || 'Failed to load games');
                }
            } catch (error) {
                console.error('Error loading games:', error);
                showAlert('Error loading games from database. Please try again.', 'error');
            }
        }

        // Update criteria labels from game's criteria system
        function updateCriteriaLabels(game) {
            const criteriaGrid = document.getElementById('criteriaGrid');
            if (!criteriaGrid) return;
            
            // Get criteria from game's points_system
            const pointsSystem = game.points_system || {};
            
            // Check if this is a Beauty Pageant event
            if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories && pointsSystem.categories.length > 0) {
                // Render Beauty Pageant categories (per-category scoring types)
                renderBeautyPageantCategories(pointsSystem.categories);
                return;
            }
            
            const criteriaList = pointsSystem.criteria || [];
            
            // Update instructions dynamically
            const instructionsEl = document.getElementById('criteriaInstructions');
            if (instructionsEl) {
                if (criteriaList.length > 0) {
                    // Total max is the sum of percentages (should be 100)
                    const totalMax = criteriaList.reduce((sum, c) => sum + (c.percentage || 0), 0);
                    instructionsEl.textContent = `Score each criteria based on the game's evaluation criteria. Each criteria's maximum score equals its percentage weight. Total possible score: ${totalMax} points. Be fair and consistent in your evaluations.`;
                } else {
                    instructionsEl.textContent = `Score each criteria from 0-20 points. The total possible score is 100 points. Be fair and consistent in your evaluations.`;
                }
            }
            
            // Default criteria names for fallback
            const defaultCriteriaNames = ['Performance', 'Teamwork', 'Strategy', 'Sportsmanship', 'Overall'];
            
            if (criteriaList.length === 0) {
                // Fallback to default criteria if none defined
                criteriaGrid.innerHTML = `
                    <div class="criteria-item">
                        <label for="criteria1">Performance</label>
                        <input type="number" id="criteria1" min="0" max="20" placeholder="0" required>
                        <span class="criteria-label">(20)</span>
                    </div>
                    <div class="criteria-item">
                        <label for="criteria2">Teamwork</label>
                        <input type="number" id="criteria2" min="0" max="20" placeholder="0" required>
                        <span class="criteria-label">(20)</span>
                    </div>
                    <div class="criteria-item">
                        <label for="criteria3">Strategy</label>
                        <input type="number" id="criteria3" min="0" max="20" placeholder="0" required>
                        <span class="criteria-label">(20)</span>
                    </div>
                    <div class="criteria-item">
                        <label for="criteria4">Sportsmanship</label>
                        <input type="number" id="criteria4" min="0" max="20" placeholder="0" required>
                        <span class="criteria-label">(20)</span>
                    </div>
                    <div class="criteria-item">
                        <label for="criteria5">Overall</label>
                        <input type="number" id="criteria5" min="0" max="20" placeholder="0" required>
                        <span class="criteria-label">(20)</span>
                    </div>
                `;
                
                // Update history table headers with default names (5 criteria)
                updateHistoryTableHeaders(defaultCriteriaNames);
            } else {
                // Generate criteria inputs from game's criteria system
                let criteriaHTML = '';
                const criteriaNames = [];
                
                criteriaList.forEach((criteria, index) => {
                    const criteriaNum = index + 1;
                    const criteriaName = criteria.name || `Criteria ${criteriaNum}`;
                    const percentage = criteria.percentage || 0;
                    // Max score equals the percentage value (e.g., 40% = max 40 points)
                    const maxPoints = percentage;
                    
                    criteriaNames.push(criteriaName);
                    
                    criteriaHTML += `
                        <div class="criteria-item">
                            <label for="criteria${criteriaNum}">${criteriaName}</label>
                            <input type="number" id="criteria${criteriaNum}" min="0" max="${maxPoints}" placeholder="0" required>
                            <span class="criteria-label">(${percentage})</span>
                        </div>
                    `;
                });
                
                // Fill remaining slots up to 5 if needed
                for (let i = criteriaList.length; i < 5; i++) {
                    const criteriaNum = i + 1;
                    criteriaHTML += `
                        <div class="criteria-item" style="display: none;">
                            <label for="criteria${criteriaNum}">Criteria ${criteriaNum}</label>
                            <input type="number" id="criteria${criteriaNum}" min="0" max="20" placeholder="0" value="0">
                        </div>
                    `;
                    // Add empty name for hidden criteria
                    if (criteriaNames.length < 5) {
                        criteriaNames.push(`Criteria ${criteriaNum}`);
                    }
                }
                
                criteriaGrid.innerHTML = criteriaHTML;
                
                // Update history table headers with actual criteria names (only show actual count)
                updateHistoryTableHeaders(criteriaNames.slice(0, criteriaList.length));
            }
            
            // Re-attach event listeners for score calculation
            const criteriaInputs = ['criteria1', 'criteria2', 'criteria3', 'criteria4', 'criteria5'];
            criteriaInputs.forEach(criteriaId => {
                const input = document.getElementById(criteriaId);
                if (input) {
                    input.addEventListener('input', updateJudgeTotalScore);
                }
            });
            
            // Update total score display
            updateJudgeTotalScore();
            
            // Re-render history with updated criteria names
            renderJudgeHistory();
        }
        
        // Render Beauty Pageant categories with their criteria (per-category scoring types)
        function renderBeautyPageantCategories(categories, museEscortMode = false) {
            const criteriaGrid = document.getElementById('criteriaGrid');
            const instructionsEl = document.getElementById('criteriaInstructions');
            
            if (instructionsEl) {
                instructionsEl.textContent = `Score each criteria within each category. Each category may have different scoring types (Single, Muse & Escort, or Pair). Be fair and consistent in your evaluations.`;
            }
            
            let categoriesHTML = '';
            let allCriteriaNames = [];
            
            // Group categories by scoring type
            const singleCategories = [];
            const museEscortCategories = [];
            const pairCategories = [];
            
            categories.forEach((category, index) => {
                const scoringType = category.scoring_type || 'single';
                if (scoringType === 'muse_escort') {
                    museEscortCategories.push({...category, originalIndex: index});
                } else if (scoringType === 'pair') {
                    pairCategories.push({...category, originalIndex: index});
                } else {
                    singleCategories.push({...category, originalIndex: index});
                }
            });
            
            // Render single participant categories
            if (singleCategories.length > 0) {
                categoriesHTML += `<div class="beauty-categories-container">`;
                singleCategories.forEach((category, idx) => {
                    const categoryIndex = category.originalIndex;
                    const categoryName = category.name || `Category ${categoryIndex + 1}`;
                    const criteriaList = category.criteria || [];
                    
                categoriesHTML += `
                        <div class="beauty-category-section">
                            <h4 style="color: #6b21a8; margin-bottom: 12px; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-crown"></i> ${categoryName}
                            </h4>
                            <div class="beauty-criteria-grid">
                    `;
                    
                    criteriaList.forEach((criteria, criteriaIndex) => {
                        const criteriaName = criteria.name || `Criteria ${criteriaIndex + 1}`;
                        const percentage = criteria.percentage || 0;
                        allCriteriaNames.push(`${categoryName} - ${criteriaName}`);
                        
                        categoriesHTML += `
                            <div class="criteria-item" style="background: white;">
                                <label for="beauty-criteria-single-${categoryIndex}-${criteriaIndex}">${criteriaName}</label>
                                <input type="number" 
                                       id="beauty-criteria-single-${categoryIndex}-${criteriaIndex}" 
                                       class="beauty-criteria-input"
                                       data-category-index="${categoryIndex}"
                                       data-criteria-index="${criteriaIndex}"
                                       data-participant="single"
                                       min="0" 
                                       max="${percentage}" 
                                       placeholder="0" 
                                       required>
                                <span class="criteria-label">(${percentage})</span>
                            </div>
                        `;
                    });
                    
                    categoriesHTML += `
                            </div>
                            <div class="beauty-category-total-box">
                                <strong>Category Total: <span class="beauty-category-total" data-category-index="${categoryIndex}" data-participant="single">0</span> / ${category.totalPercentage || 100}</strong>
                            </div>
                        </div>
                    `;
                });
                categoriesHTML += `</div>`;
            }
            
            // Render Muse & Escort categories
            if (museEscortCategories.length > 0) {
                categoriesHTML += `
                    <div style="margin-bottom: 20px; margin-top: 20px; background: #f0f9ff; border: 2px solid #8b5cf6; border-radius: 12px; padding: 15px;">
                        <div style="margin-bottom: 15px;">
                            <h4 style="color: #6b21a8; margin-bottom: 10px; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-venus-mars"></i> Individual Participant Scoring
                            </h4>
                            <p style="color: #6b21a8; font-size: 0.9rem; margin-bottom: 15px; padding: 10px; background: #f3e8ff; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                                <i class="fas fa-info-circle"></i> <strong>Important:</strong> These categories require scoring both the <strong>Female (Muse)</strong> and <strong>Male (Escort)</strong> participants separately. Use the tabs below to switch between participants.
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px; background: white; padding: 8px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <button type="button" class="muse-escort-tab active" data-participant="muse" data-category-group="muse_escort" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, #f3e8ff, #e9d5ff); border: 2px solid #8b5cf6; border-bottom: 4px solid #8b5cf6; color: #6b21a8; font-weight: 700; cursor: pointer; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative;">
                                <i class="fas fa-venus" style="font-size: 1.2rem;"></i> 
                                <span>Score Female (Muse)</span>
                                <span class="muse-completion-indicator" style="position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; background: #ef4444; border: 2px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: white; font-weight: bold;">!</span>
                            </button>
                            <button type="button" class="muse-escort-tab" data-participant="escort" data-category-group="muse_escort" style="flex: 1; padding: 12px 20px; background: #f9fafb; border: 2px solid #d1d5db; border-bottom: 4px solid transparent; color: #6b21a8; font-weight: 600; cursor: pointer; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative;">
                                <i class="fas fa-mars" style="font-size: 1.2rem;"></i> 
                                <span>Score Male (Escort)</span>
                                <span class="escort-completion-indicator" style="position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; background: #ef4444; border: 2px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: white; font-weight: bold;">!</span>
                            </button>
                        </div>
                        <div style="background: #fef3c7; border-left: 4px solid #fbbf24; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                            <p style="margin: 0; color: #92400e; font-size: 0.9rem; font-weight: 600;">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Remember:</strong> You must score <strong>BOTH</strong> the Female (Muse) and Male (Escort) participants. Click the tabs above to switch between them.
                            </p>
                        </div>
                        
                        <div id="museEscortScoringSection" class="participant-scoring-section" style="display: block;">
                            <div class="beauty-categories-container" id="museScoringContainer" style="display: block;">
                                <div style="background: #fef3c7; border: 2px solid #fbbf24; border-radius: 8px; padding: 12px; margin-bottom: 15px; text-align: center;">
                                    <strong style="color: #92400e; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fas fa-venus" style="font-size: 1.1rem;"></i>
                                        Currently Scoring: <span style="color: #8b5cf6;">Female Participant (Muse)</span>
                                    </strong>
                                </div>
                `;
                
                // Render for Muse
                museEscortCategories.forEach((category, idx) => {
                    const categoryIndex = category.originalIndex;
                    const categoryName = category.name || `Category ${categoryIndex + 1}`;
                    const criteriaList = category.criteria || [];
                    
                    categoriesHTML += `
                        <div class="beauty-category-section participant-muse">
                            <h4 style="color: #6b21a8; margin-bottom: 12px; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-crown"></i> ${categoryName}
                                <span style="margin-left: auto; padding: 4px 12px; background: #fef3c7; border: 2px solid #fbbf24; border-radius: 6px; font-size: 0.85rem; color: #92400e; font-weight: 700;">
                                    <i class="fas fa-venus"></i> Female (Muse)
                                </span>
                            </h4>
                            <div class="beauty-criteria-grid">
                    `;
                    
                    criteriaList.forEach((criteria, criteriaIndex) => {
                        const criteriaName = criteria.name || `Criteria ${criteriaIndex + 1}`;
                        const percentage = criteria.percentage || 0;
                        allCriteriaNames.push(`Muse - ${categoryName} - ${criteriaName}`);
                        
                        categoriesHTML += `
                            <div class="criteria-item" style="background: white;">
                                <label for="beauty-criteria-muse-${categoryIndex}-${criteriaIndex}">${criteriaName}</label>
                                <input type="number" 
                                       id="beauty-criteria-muse-${categoryIndex}-${criteriaIndex}" 
                                       class="beauty-criteria-input participant-muse"
                                       data-category-index="${categoryIndex}"
                                       data-criteria-index="${criteriaIndex}"
                                       data-participant="muse"
                                       min="0" 
                                       max="${percentage}" 
                                       placeholder="0" 
                                       required>
                                <span class="criteria-label">(${percentage})</span>
                        </div>
                        `;
                    });
                    
                    categoriesHTML += `
                            </div>
                            <div class="beauty-category-total-box">
                                <strong>Category Total: <span class="beauty-category-total participant-muse" data-category-index="${categoryIndex}" data-participant="muse">0</span> / ${category.totalPercentage || 100}</strong>
                            </div>
                        </div>
                    `;
                });
                
                categoriesHTML += `
                            </div>
                            <div class="beauty-categories-container" id="escortScoringContainer" style="display: none;">
                                <div style="background: #dbeafe; border: 2px solid #3b82f6; border-radius: 8px; padding: 12px; margin-bottom: 15px; text-align: center;">
                                    <strong style="color: #1e40af; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fas fa-mars" style="font-size: 1.1rem;"></i>
                                        Currently Scoring: <span style="color: #8b5cf6;">Male Participant (Escort)</span>
                                    </strong>
                                </div>
                `;
                
                // Render for Escort
                museEscortCategories.forEach((category, idx) => {
                    const categoryIndex = category.originalIndex;
                    const categoryName = category.name || `Category ${categoryIndex + 1}`;
                    const criteriaList = category.criteria || [];
                    
                    categoriesHTML += `
                        <div class="beauty-category-section participant-escort">
                            <h4 style="color: #6b21a8; margin-bottom: 12px; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-crown"></i> ${categoryName}
                                <span style="margin-left: auto; padding: 4px 12px; background: #dbeafe; border: 2px solid #3b82f6; border-radius: 6px; font-size: 0.85rem; color: #1e40af; font-weight: 700;">
                                    <i class="fas fa-mars"></i> Male (Escort)
                                </span>
                            </h4>
                            <div class="beauty-criteria-grid">
                    `;
                    
                    criteriaList.forEach((criteria, criteriaIndex) => {
                        const criteriaName = criteria.name || `Criteria ${criteriaIndex + 1}`;
                        const percentage = criteria.percentage || 0;
                        allCriteriaNames.push(`Escort - ${categoryName} - ${criteriaName}`);
                        
                        categoriesHTML += `
                            <div class="criteria-item" style="background: white;">
                                <label for="beauty-criteria-escort-${categoryIndex}-${criteriaIndex}">${criteriaName}</label>
                                <input type="number" 
                                       id="beauty-criteria-escort-${categoryIndex}-${criteriaIndex}" 
                                       class="beauty-criteria-input participant-escort"
                                       data-category-index="${categoryIndex}"
                                       data-criteria-index="${criteriaIndex}"
                                       data-participant="escort"
                                       min="0" 
                                       max="${percentage}" 
                                       placeholder="0" 
                                       required>
                                <span class="criteria-label">(${percentage})</span>
                            </div>
                        `;
                    });
                    
                    categoriesHTML += `
                            </div>
                            <div class="beauty-category-total-box">
                                <strong>Category Total: <span class="beauty-category-total participant-escort" data-category-index="${categoryIndex}" data-participant="escort">0</span> / ${category.totalPercentage || 100}</strong>
                            </div>
                        </div>
                    `;
                });
                
                categoriesHTML += `
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Render Pair categories
            if (pairCategories.length > 0) {
                categoriesHTML += `
                    <div style="margin-top: 20px;">
                        <h4 style="color: #8b5cf6; margin-bottom: 12px; font-size: 1rem; font-weight: 600;">
                            <i class="fas fa-users"></i> Pair Scoring Categories
                        </h4>
                        <div class="beauty-categories-container">
                `;
                
                pairCategories.forEach((category, idx) => {
                    const categoryIndex = category.originalIndex;
                    const categoryName = category.name || `Category ${categoryIndex + 1}`;
                    const criteriaList = category.criteria || [];
                    
                    categoriesHTML += `
                        <div class="beauty-category-section participant-pair">
                            <h4 style="color: #6b21a8; margin-bottom: 12px; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-crown"></i> ${categoryName}
                            </h4>
                            <div class="beauty-criteria-grid">
                    `;
                    
                    criteriaList.forEach((criteria, criteriaIndex) => {
                        const criteriaName = criteria.name || `Criteria ${criteriaIndex + 1}`;
                        const percentage = criteria.percentage || 0;
                        allCriteriaNames.push(`Pair - ${categoryName} - ${criteriaName}`);
                        
                        categoriesHTML += `
                            <div class="criteria-item" style="background: white;">
                                <label for="beauty-criteria-pair-${categoryIndex}-${criteriaIndex}">${criteriaName}</label>
                                <input type="number" 
                                       id="beauty-criteria-pair-${categoryIndex}-${criteriaIndex}" 
                                       class="beauty-criteria-input participant-pair"
                                       data-category-index="${categoryIndex}"
                                       data-criteria-index="${criteriaIndex}"
                                       data-participant="pair"
                                       min="0" 
                                       max="${percentage}" 
                                       placeholder="0" 
                                       required>
                                <span class="criteria-label">(${percentage})</span>
                            </div>
                        `;
                    });
                    
                categoriesHTML += `
                            </div>
                            <div class="beauty-category-total-box">
                                <strong>Category Total: <span class="beauty-category-total participant-pair" data-category-index="${categoryIndex}" data-participant="pair">0</span> / ${category.totalPercentage || 100}</strong>
                        </div>
                    </div>
                `;
                });
                
                categoriesHTML += `</div></div>`;
            }
            
            criteriaGrid.innerHTML = categoriesHTML;
            
            // Attach event listeners for score calculation
            const beautyCriteriaInputs = criteriaGrid.querySelectorAll('.beauty-criteria-input');
            beautyCriteriaInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const categoryIndex = parseInt(this.getAttribute('data-category-index'));
                    const participant = this.getAttribute('data-participant');
                    updateBeautyCategoryTotal(categoryIndex, participant);
                    updateJudgeTotalScore();
                    updateMuseEscortCompletionIndicators();
                });
            });
            
            // Update completion indicators on load
            updateMuseEscortCompletionIndicators();
            
            // Attach tab switching for Muse & Escort categories
            const tabs = criteriaGrid.querySelectorAll('.muse-escort-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const participant = this.getAttribute('data-participant');
                    
                    // Update tab styles
                    tabs.forEach(t => {
                        t.classList.remove('active');
                        t.style.background = '#f9fafb';
                        t.style.border = '2px solid #d1d5db';
                        t.style.borderBottom = '4px solid transparent';
                        t.style.fontWeight = '600';
                    });
                    this.classList.add('active');
                    this.style.background = 'linear-gradient(135deg, #f3e8ff, #e9d5ff)';
                    this.style.border = '2px solid #8b5cf6';
                    this.style.borderBottom = '4px solid #8b5cf6';
                    this.style.fontWeight = '700';
                    
                    // Show/hide sections
                    const museContainer = document.getElementById('museScoringContainer');
                    const escortContainer = document.getElementById('escortScoringContainer');
                    if (museContainer && escortContainer) {
                        museContainer.style.display = participant === 'muse' ? 'block' : 'none';
                        escortContainer.style.display = participant === 'escort' ? 'block' : 'none';
                        
                        // Update indicator text
                        const museIndicator = museContainer.querySelector('div[style*="background: #fef3c7"]');
                        const escortIndicator = escortContainer.querySelector('div[style*="background: #dbeafe"]');
                        
                        if (museIndicator) {
                            museIndicator.style.display = participant === 'muse' ? 'block' : 'none';
                        }
                        if (escortIndicator) {
                            escortIndicator.style.display = participant === 'escort' ? 'block' : 'none';
                        }
                    }
                    
                    // Update completion indicators
                    updateMuseEscortCompletionIndicators();
                });
                
                // Add hover effects
                tab.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        this.style.background = '#f3e8ff';
                        this.style.borderColor = '#8b5cf6';
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                
                tab.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.background = '#f9fafb';
                        this.style.borderColor = '#d1d5db';
                        this.style.transform = 'translateY(0)';
                    }
                });
            });
            
            // Update history table headers - show all criteria names
            updateHistoryTableHeaders(allCriteriaNames);
            
            // Update total score display
            updateJudgeTotalScore();
        }
        
        // Update total for a specific Beauty Pageant category
        function updateBeautyCategoryTotal(categoryIndex, participant = 'single') {
            const categoryInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${categoryIndex}"][data-participant="${participant}"]`);
            let categoryTotal = 0;
            
            categoryInputs.forEach(input => {
                let val = parseFloat(input.value) || 0;
                const max = parseFloat(input.getAttribute('max'));
                const min = parseFloat(input.getAttribute('min'));
                
                if (!isNaN(max) && val > max) {
                    val = max;
                    input.value = max;
                }
                if (!isNaN(min) && val < min) {
                    val = min;
                    input.value = min;
                }
                
                categoryTotal += val;
            });
            
            const totalSpan = document.querySelector(`.beauty-category-total[data-category-index="${categoryIndex}"][data-participant="${participant}"]`);
            if (totalSpan) {
                totalSpan.textContent = categoryTotal;
                
                // Color code based on whether it equals 100%
                if (categoryTotal === 100) {
                    totalSpan.style.color = '#22c55e';
                } else {
                    totalSpan.style.color = '#ef4444';
                }
            }
        }
        
        // Update completion indicators for Muse & Escort tabs
        function updateMuseEscortCompletionIndicators() {
            const museIndicator = document.querySelector('.muse-completion-indicator');
            const escortIndicator = document.querySelector('.escort-completion-indicator');
            
            if (museIndicator) {
                const museInputs = document.querySelectorAll('.beauty-criteria-input[data-participant="muse"]');
                let hasMuseScores = false;
                museInputs.forEach(input => {
                    if (parseFloat(input.value) > 0) {
                        hasMuseScores = true;
                    }
                });
                
                if (hasMuseScores) {
                    museIndicator.style.background = '#22c55e';
                    museIndicator.innerHTML = '✓';
                } else {
                    museIndicator.style.background = '#ef4444';
                    museIndicator.innerHTML = '!';
                }
            }
            
            if (escortIndicator) {
                const escortInputs = document.querySelectorAll('.beauty-criteria-input[data-participant="escort"]');
                let hasEscortScores = false;
                escortInputs.forEach(input => {
                    if (parseFloat(input.value) > 0) {
                        hasEscortScores = true;
                    }
                });
                
                if (hasEscortScores) {
                    escortIndicator.style.background = '#22c55e';
                    escortIndicator.innerHTML = '✓';
                } else {
                    escortIndicator.style.background = '#ef4444';
                    escortIndicator.innerHTML = '!';
                }
            }
        }
        
        // Update history table headers with criteria names and show/hide columns
        function updateHistoryTableHeaders(criteriaNames) {
            const actualCriteriaCount = criteriaNames.length;
            
            // Update header text and show/hide columns
            for (let i = 1; i <= 5; i++) {
                const headerId = `historyCriteria${i}`;
                const headerEl = document.getElementById(headerId);
                if (headerEl) {
                    if (i <= actualCriteriaCount) {
                        // Show column and set name
                        headerEl.textContent = criteriaNames[i - 1] || `Criteria ${i}`;
                        headerEl.style.display = '';
                    } else {
                        // Hide column if not needed
                        headerEl.style.display = 'none';
                    }
                }
            }
        }

        // Load teams from database via API
        async function loadTeamsFromDatabase() {
            try {
                const teamSelect = document.getElementById('judgeTeamSelect');
                teamSelect.innerHTML = '<option value="">Loading teams from database...</option>';
                
                const response = await fetch('database_handler.php?action=teams');
                const result = await response.json();
                
                if (result.success && result.data) {
                    teams = result.data;
                    populateJudgeTeamSelect();
                    loadJudgeScoresFromDatabase(); // Load scores after teams are loaded
                } else {
                    throw new Error(result.message || 'Failed to load teams');
                }
            } catch (error) {
                console.error('Error loading teams:', error);
                showAlert('Error loading teams from database. Please try again.', 'error');
                document.getElementById('judgeTeamSelect').innerHTML = '<option value="">Error loading teams</option>';
            }
        }

        // Load judge scores from database
        async function loadJudgeScoresFromDatabase() {
            try {
                const response = await fetch('database_handler.php?action=judge_scores');
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Store all scores but filter in renderJudgeHistory
                    judgeScores = result.data;
                    renderJudgeHistory();
                    // Refresh team select to show which teams are already scored
                    if (teams.length > 0) {
                        populateJudgeTeamSelect();
                    }
                } else {
                    throw new Error(result.message || 'Failed to load judge scores');
                }
            } catch (error) {
                console.error('Error loading judge scores:', error);
                showAlert('Error loading judge scores from database.', 'error');
            }
        }



        function populateJudgeTeamSelect() {
            const teamSelect = document.getElementById('judgeTeamSelect');
            teamSelect.innerHTML = '<option value="">Select a team...</option>';
            
            const judgeName = currentAssignedJudge || sessionJudgeName;
            
            // Filter teams based on event type if a game is selected
            let filteredTeams = teams;
            if (selectedGame && selectedGame.judge_event_type) {
                const eventType = selectedGame.judge_event_type;
                filteredTeams = teams.filter(team => {
                    // Show teams that match the event type OR teams with no event type (can participate in all)
                    return !team.event_type || team.event_type === eventType;
                });
            }
            
            if (filteredTeams.length === 0) {
                teamSelect.innerHTML = '<option value="">No teams registered for this event type</option>';
                return;
            }
            
            filteredTeams.forEach(team => {
                const option = document.createElement('option');
                option.value = team.id;
                
                // Check if this team has already been scored by this judge for this game
                const alreadyScored = judgeScores.some(score => 
                    score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                    score.team_id == team.id &&
                    (score.game_id == selectedGameId || (!score.game_id && !selectedGameId))
                );
                
                // Show event type in team name if specified
                const eventTypeLabel = team.event_type ? ` [${team.event_type}]` : '';
                
                if (alreadyScored) {
                    option.textContent = `${team.name} ${team.code ? `(${team.code})` : ''}${eventTypeLabel} - ✓ Already Scored (Editable)`;
                    option.style.color = '#2563eb';
                    option.style.fontStyle = 'italic';
                    // Don't disable - allow editing
                } else {
                    option.textContent = `${team.name} ${team.code ? `(${team.code})` : ''}${eventTypeLabel}`;
                }
                
                teamSelect.appendChild(option);
            });
            
            // Add event listener to load existing scores when team is selected
            if (!teamSelect.hasAttribute('data-listener-added')) {
                teamSelect.setAttribute('data-listener-added', 'true');
                teamSelect.addEventListener('change', loadExistingScoreForTeam);
            }
        }

        function setupEventListeners() {
            // Submit judge score
            document.getElementById('submitJudgeScore').addEventListener('click', submitJudgeScore);

            // Clear judge form
            document.getElementById('clearJudgeForm').addEventListener('click', clearJudgeForm);

            // Mark event completed
            document.getElementById('markEventCompleteBtn').addEventListener('click', markCurrentEventCompleted);

            // Real-time score calculation - will be attached when criteria are loaded
            // Event listeners are attached in updateCriteriaLabels function
            
            // Team selection change listener (will be added in populateJudgeTeamSelect)
        }
        
        // Load existing score for selected team
        function loadExistingScoreForTeam() {
            // Always clear current inputs first so scores from a previously
            // selected team are not shown for the newly selected team.
            clearJudgeForm();

            const teamId = parseInt(document.getElementById('judgeTeamSelect').value);
            if (!teamId) {
                return;
            }
            
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const pointsSystem = selectedGame ? (selectedGame.points_system || {}) : {};
            
            // Find existing score(s) for this team
            if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories) {
                // Load scores based on per-category scoring types
                const categories = pointsSystem.categories || [];
                
                categories.forEach((category, categoryIndex) => {
                    const scoringType = category.scoring_type || 'single';
                    
                    if (scoringType === 'muse_escort') {
                        // Load Muse and Escort scores separately
                        const museScore = judgeScores.find(score => 
                            score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                            score.team_id == teamId &&
                            (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                            score.participant_type === 'muse'
                        );
                        
                        const escortScore = judgeScores.find(score => 
                            score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                            score.team_id == teamId &&
                            (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                            score.participant_type === 'escort'
                        );
                        
                        if (museScore) {
                            loadBeautyPageantScore(museScore, 'muse', [category], categoryIndex);
                        }
                        if (escortScore) {
                            loadBeautyPageantScore(escortScore, 'escort', [category], categoryIndex);
                        }
                    } else if (scoringType === 'pair') {
                        // Load Pair score
                        const pairScore = judgeScores.find(score => 
                            score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                            score.team_id == teamId &&
                            (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                            score.participant_type === 'pair'
                        );
                        
                        if (pairScore) {
                            loadBeautyPageantScore(pairScore, 'pair', [category], categoryIndex);
                        }
                    } else {
                        // Load Single participant score
                        const singleScore = judgeScores.find(score => 
                            score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                            score.team_id == teamId &&
                            (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                            (!score.participant_type || score.participant_type === '' || score.participant_type === null)
                        );
                        
                        if (singleScore) {
                            loadBeautyPageantScore(singleScore, 'single', [category], categoryIndex);
                        }
                    }
                });
            } else {
                // Regular event scoring
                const existingScore = judgeScores.find(score => 
                    score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                    score.team_id == teamId &&
                    (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                    (!score.participant_type || score.participant_type === '' || score.participant_type === null)
                );
                
                if (existingScore) {
                    // Load into regular criteria inputs
                    const criteria1 = document.getElementById('criteria1');
                    const criteria2 = document.getElementById('criteria2');
                    const criteria3 = document.getElementById('criteria3');
                    const criteria4 = document.getElementById('criteria4');
                    const criteria5 = document.getElementById('criteria5');
                    
                    if (criteria1) criteria1.value = existingScore.criteria1 || 0;
                    if (criteria2) criteria2.value = existingScore.criteria2 || 0;
                    if (criteria3) criteria3.value = existingScore.criteria3 || 0;
                    if (criteria4) criteria4.value = existingScore.criteria4 || 0;
                    if (criteria5) criteria5.value = existingScore.criteria5 || 0;
                    
                    updateJudgeTotalScore();
                }
            }
        }
        
        // Load Beauty Pageant score into form
        function loadBeautyPageantScore(score, participant, categories, categoryIndex = null) {
            const participantPrefix = participant === 'single' ? '' : participant + '-';

            // PREFERRED: use structured details_json if available
            if (score.details_json) {
                let details = null;
                try {
                    details = typeof score.details_json === 'string'
                        ? JSON.parse(score.details_json)
                        : score.details_json;
                } catch (e) {
                    console.error('Failed to parse details_json for score', score.id, e);
                }

                if (details && details.type === 'beauty_pageant' && Array.isArray(details.categories)) {
                    details.categories.forEach(catDetail => {
                        const actualCategoryIndex = typeof catDetail.index === 'number'
                            ? catDetail.index
                            : 0;
                        const critList = Array.isArray(catDetail.criteria) ? catDetail.criteria : [];

                        critList.forEach(c => {
                            const criteriaIdx = typeof c.index === 'number' ? c.index : 0;
                            const inputId = `beauty-criteria-${participantPrefix}${actualCategoryIndex}-${criteriaIdx}`;
                            const input = document.getElementById(inputId);
                            if (input) {
                                input.value = c.score != null ? c.score : '';
                            }
                        });

                        // Update category total for this participant/category
                        updateBeautyCategoryTotal(actualCategoryIndex, participant);
                    });

                    updateJudgeTotalScore();
                    return;
                }
            }

            // FALLBACK: legacy mapping using criteria1-5 (for older data)
            let globalCriteriaIndex = 0;
            
            categories.forEach((category, catIdx) => {
                const actualCategoryIndex = categoryIndex !== null ? categoryIndex : catIdx;
                const criteriaList = category.criteria || [];
                
                criteriaList.forEach((criteria, criteriaIdx) => {
                    const inputId = `beauty-criteria-${participantPrefix}${actualCategoryIndex}-${criteriaIdx}`;
                    const input = document.getElementById(inputId);
                    
                    if (input) {
                        const criteriaNum = globalCriteriaIndex + 1;
                        let value = 0;
                        
                        if (criteriaNum === 1) value = score.criteria1 || 0;
                        else if (criteriaNum === 2) value = score.criteria2 || 0;
                        else if (criteriaNum === 3) value = score.criteria3 || 0;
                        else if (criteriaNum === 4) value = score.criteria4 || 0;
                        else if (criteriaNum === 5) value = score.criteria5 || 0;
                        
                        input.value = value;
                        globalCriteriaIndex++;
                    }
                });
                
                updateBeautyCategoryTotal(actualCategoryIndex, participant);
            });
            
            updateJudgeTotalScore();
        }
        
        
        // Validate if judge exists in database (has scored before)
        async function validateJudgeInDatabase(judgeName) {
            try {
                const response = await fetch('database_handler.php?action=judge_scores');
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Check if judge name exists in any previous scores
                    const judgeExists = result.data.some(score => 
                        score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase()
                    );
                    
                    // Judge validation complete
                    if (!judgeExists) {
                        showValidationMessage('⚠️ Judge name not found in database. Please verify the name is correct.', 'warning');
                    } else {
                        removeValidationMessage();
                    }
                }
            } catch (error) {
                console.error('Error validating judge:', error);
            }
        }
        
        // Show validation message
        function showValidationMessage(message, type = 'warning') {
            removeValidationMessage();
            
            const judgeDisplay = document.getElementById('judgeDisplay');
            if (!judgeDisplay) return;
            
            const validationDiv = document.createElement('div');
            validationDiv.id = 'judgeValidationMessage';
            validationDiv.style.cssText = `
                margin-top: 8px;
                padding: 10px;
                border-radius: 6px;
                font-size: 0.9rem;
                background: ${type === 'warning' ? '#fef3c7' : '#fee2e2'};
                color: ${type === 'warning' ? '#92400e' : '#991b1b'};
                border: 1px solid ${type === 'warning' ? '#fbbf24' : '#ef4444'};
            `;
            validationDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            
            judgeNameInput.parentElement.appendChild(validationDiv);
        }
        
        // Remove validation message
        function removeValidationMessage() {
            const existingMsg = document.getElementById('judgeValidationMessage');
            if (existingMsg) {
                existingMsg.remove();
            }
        }
        
        // Check if judge exists in database
        async function checkJudgeExistsInDatabase(judgeName) {
            try {
                const response = await fetch('database_handler.php?action=judge_scores');
                const result = await response.json();
                
                if (result.success && result.data) {
                    return result.data.some(score => 
                        score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase()
                    );
                }
                return false;
            } catch (error) {
                console.error('Error checking judge in database:', error);
                return false;
            }
        }


        function updateJudgeTotalScore() {
            // Clamp generic criteria inputs to their min/max so judges cannot exceed caps
            const criteriaIds = ['criteria1', 'criteria2', 'criteria3', 'criteria4', 'criteria5'];
            criteriaIds.forEach(id => {
                const input = document.getElementById(id);
                if (!input) return;
                
                let val = parseFloat(input.value);
                if (isNaN(val)) return;
                
                const max = parseFloat(input.getAttribute('max'));
                const min = parseFloat(input.getAttribute('min'));
                
                if (!isNaN(max) && val > max) {
                    val = max;
                }
                if (!isNaN(min) && val < min) {
                    val = min;
                }
                
                input.value = val;
            });
        }

        // Submit judge score to database
        async function submitJudgeScore() {
            // Use assigned judge from selected game
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const teamId = parseInt(document.getElementById('judgeTeamSelect').value);
            
            // Validation
            if (!judgeName) {
                showAlert('Judge not assigned. Please select a game you are assigned to.', 'error');
                return;
            }
            
            if (!selectedGameId || !selectedGame) {
                showAlert('Please select a game first before submitting scores', 'error');
                return;
            }
            
            if (!teamId) {
                showAlert('Please select a team to score', 'error');
                document.getElementById('judgeTeamSelect').focus();
                return;
            }
            
            // Check authorization (judge should already be assigned from game selection)
            const authorizedJudges = selectedGame.authorized_judges;
            if (authorizedJudges && Array.isArray(authorizedJudges) && authorizedJudges.length > 0) {
                const isAuthorized = authorizedJudges.some(judge => 
                    judge && judge.trim().toLowerCase() === judgeName.toLowerCase()
                );
                if (!isAuthorized) {
                    const judgeExists = await checkJudgeExistsInDatabase(judgeName);
                    if (!judgeExists) {
                        showAlert(
                            `⚠️ Authorization Failed<br><br>
                            Judge name "${judgeName}" is not authorized for this game and was not found in the database.<br>
                            Authorized judges: ${authorizedJudges.join(', ')}<br><br>
                            Please verify your name or contact the administrator.`,
                            'error'
                        );
                        return;
                    } else {
                        const confirmContinue = confirm(
                            `⚠️ Authorization Warning\n\n` +
                            `Judge name "${judgeName}" is not in the authorized list for this game.\n` +
                            `Authorized judges: ${authorizedJudges.join(', ')}\n\n` +
                            `Do you want to continue anyway?`
                        );
                        if (!confirmContinue) {
                            return;
                        }
                    }
                }
            } else {
                const judgeExists = await checkJudgeExistsInDatabase(judgeName);
                if (!judgeExists) {
                    const confirmContinue = confirm(
                        `⚠️ Judge Not Found\n\n` +
                        `Judge name "${judgeName}" was not found in the database.\n\n` +
                        `This appears to be a new judge. Do you want to continue?`
                    );
                    if (!confirmContinue) {
                        return;
                    }
                }
            }
            
            // Check if this is a Beauty Pageant event
            const pointsSystem = selectedGame ? (selectedGame.points_system || {}) : {};
            
            if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories) {
                // Handle per-category scoring types
                await submitBeautyPageantScores(pointsSystem.categories);
                return;
            } else {
                // Regular event scoring
                let criteria1 = 0, criteria2 = 0, criteria3 = 0, criteria4 = 0, criteria5 = 0;
                // Regular event: use standard criteria inputs
                criteria1 = parseInt(document.getElementById('criteria1')?.value) || 0;
                criteria2 = parseInt(document.getElementById('criteria2')?.value) || 0;
                criteria3 = parseInt(document.getElementById('criteria3')?.value) || 0;
                criteria4 = parseInt(document.getElementById('criteria4')?.value) || 0;
                criteria5 = parseInt(document.getElementById('criteria5')?.value) || 0;
                
                // Submit single score
                await submitSingleScore(criteria1, criteria2, criteria3, criteria4, criteria5);
                return;
            }
        }
        
        // Submit single score (regular event or single Beauty Pageant)
        async function submitSingleScore(criteria1, criteria2, criteria3, criteria4, criteria5) {
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const teamId = parseInt(document.getElementById('judgeTeamSelect').value);
            const team = teams.find(t => t.id === teamId);
            
            if (!team) {
                showAlert('Selected team not found. Please refresh the page.', 'error');
                return;
            }
            
            // Check if judge has already scored this team for this game (for UPDATE instead of INSERT)
            const existingScore = judgeScores.find(score => 
                score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                score.team_id == teamId &&
                (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                (!score.participant_type || score.participant_type === '' || score.participant_type === null)
            );
            
            const totalScore = criteria1 + criteria2 + criteria3 + criteria4 + criteria5;
            
            try {
                const scoreData = {
                    judge_name: judgeName,
                    game_id: selectedGameId,
                    team_id: teamId,
                    criteria1: criteria1,
                    criteria2: criteria2,
                    criteria3: criteria3,
                    criteria4: criteria4,
                    criteria5: criteria5,
                    total_score: totalScore
                };
                
                // If existing score, include the ID for UPDATE
                if (existingScore && existingScore.id) {
                    scoreData.id = existingScore.id;
                }
                
                // Always use POST; backend will UPDATE when id is present
                const response = await fetch('database_handler.php?action=judge_scores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(scoreData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadJudgeScoresFromDatabase();
                    populateJudgeTeamSelect();
                    
                    const action = existingScore ? 'updated' : 'submitted';
                    showAlert(
                        `Score ${action} successfully!<br><br>
                        <strong>Judge:</strong> ${judgeName}<br>
                        <strong>Team:</strong> ${team.name} ${team.code ? `(${team.code})` : ''}<br>
                        <strong>Total Score:</strong> ${totalScore} points<br><br>
                        Thank you for your evaluation!`,
                        'success'
                    );
                } else {
                    throw new Error(result.message || 'Failed to submit score');
                }
            } catch (error) {
                console.error('Error submitting score:', error);
                showAlert('Failed to submit score to database. Please try again.', 'error');
            }
        }
        
        // Submit Beauty Pageant scores with per-category scoring types
        async function submitBeautyPageantScores(categories) {
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const teamId = parseInt(document.getElementById('judgeTeamSelect').value);
            const team = teams.find(t => t.id === teamId);
            
            if (!team) {
                showAlert('Selected team not found. Please refresh the page.', 'error');
                return;
            }
            
            // Group categories by scoring type
            const singleCategories = [];
            const museEscortCategories = [];
            const pairCategories = [];
            
            categories.forEach((category, index) => {
                const scoringType = category.scoring_type || 'single';
                if (scoringType === 'muse_escort') {
                    museEscortCategories.push({...category, originalIndex: index});
                } else if (scoringType === 'pair') {
                    pairCategories.push({...category, originalIndex: index});
                } else {
                    singleCategories.push({...category, originalIndex: index});
                }
            });
            
            const scoreGroups = []; // { participant_type, entries: [{categoryIndex, criteriaIndex, score}] }

            function getOrCreateGroup(participantType) {
                let group = scoreGroups.find(g => g.participant_type === participantType);
                if (!group) {
                    group = { participant_type: participantType, entries: [] };
                    scoreGroups.push(group);
                }
                return group;
            }
            
            // Collect single participant categories
            if (singleCategories.length > 0) {
                singleCategories.forEach(category => {
                    const categoryIndex = category.originalIndex;
                    const categoryInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${categoryIndex}"][data-participant="single"]`);
                    categoryInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        const critIndex = parseInt(input.getAttribute('data-criteria-index') || '0', 10);
                        const group = getOrCreateGroup(null);
                        group.entries.push({
                            categoryIndex,
                            criteriaIndex: critIndex,
                            score: value
                    });
                });
                    });
            }
            
            // Collect and submit Muse & Escort categories
            if (museEscortCategories.length > 0) {
                museEscortCategories.forEach(category => {
                    const categoryIndex = category.originalIndex;
                    const museInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${categoryIndex}"][data-participant="muse"]`);
                    const escortInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${categoryIndex}"][data-participant="escort"]`);
                    
                    museInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        const critIndex = parseInt(input.getAttribute('data-criteria-index') || '0', 10);
                        const group = getOrCreateGroup('muse');
                        group.entries.push({
                            categoryIndex,
                            criteriaIndex: critIndex,
                            score: value
                        });
                    });
                    escortInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        const critIndex = parseInt(input.getAttribute('data-criteria-index') || '0', 10);
                        const group = getOrCreateGroup('escort');
                        group.entries.push({
                            categoryIndex,
                            criteriaIndex: critIndex,
                            score: value
                    });
                });
                });
            }
            
            // Collect and submit Pair categories
            if (pairCategories.length > 0) {
                pairCategories.forEach(category => {
                    const categoryIndex = category.originalIndex;
                    const categoryInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${categoryIndex}"][data-participant="pair"]`);
                    categoryInputs.forEach(input => {
                        const value = parseFloat(input.value) || 0;
                        const critIndex = parseInt(input.getAttribute('data-criteria-index') || '0', 10);
                        const group = getOrCreateGroup('pair');
                        group.entries.push({
                            categoryIndex,
                            criteriaIndex: critIndex,
                            score: value
                    });
                });
                    });
            }
            
            // Submit all scores
            try {
                const submittedScores = [];
                
                for (const scoreGroup of scoreGroups) {
                    if (!scoreGroup.entries || scoreGroup.entries.length === 0) continue;

                    const totalScore = scoreGroup.entries.reduce((sum, e) => sum + (e.score || 0), 0);
                    
                    // Check for existing score
                    const existingScore = judgeScores.find(score => 
                        score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                        score.team_id == teamId &&
                        (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                        (score.participant_type || '') === (scoreGroup.participant_type || '')
                    );

                    // Build structured Beauty Pageant details for this participant type
                    const details = {
                        type: 'beauty_pageant',
                        participant_type: scoreGroup.participant_type || null,
                        categories: []
                    };

                    const categoryMap = {};
                    scoreGroup.entries.forEach(entry => {
                        const catIdx = entry.categoryIndex;
                        if (!categoryMap[catIdx]) {
                            const categoryDef = categories[catIdx] || {};
                            categoryMap[catIdx] = {
                                index: catIdx,
                                name: categoryDef.name || `Category ${catIdx + 1}`,
                                weight: categoryDef.categoryPercentage || 0,
                                criteria: []
                            };
                        }
                        categoryMap[catIdx].criteria.push({
                            index: entry.criteriaIndex,
                            score: entry.score
                        });
                    });
                    details.categories = Object.values(categoryMap);
                    
                    const scoreData = {
                        judge_name: judgeName,
                        game_id: selectedGameId,
                        team_id: teamId,
                        participant_type: scoreGroup.participant_type || null,
                        criteria1: 0,
                        criteria2: 0,
                        criteria3: 0,
                        criteria4: 0,
                        criteria5: 0,
                        total_score: totalScore,
                        details_json: details
                    };
                    
                    if (existingScore && existingScore.id) {
                        scoreData.id = existingScore.id;
                    }
                    
                    // Always use POST; backend will UPDATE when id is present
                    const response = await fetch('database_handler.php?action=judge_scores', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(scoreData)
                    });
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to submit score');
                    }
                    
                    submittedScores.push({
                        participant_type: scoreGroup.participant_type || 'single',
                        total: totalScore
                    });
                }
                
                // Success message
                loadJudgeScoresFromDatabase();
                populateJudgeTeamSelect();
                
                let message = `Scores submitted successfully!<br><br><strong>Judge:</strong> ${judgeName}<br><strong>Team:</strong> ${team.name} ${team.code ? `(${team.code})` : ''}<br><br>`;
                
                submittedScores.forEach(score => {
                    const typeLabel = score.participant_type === 'muse' ? 'Muse (Female)' : 
                                     score.participant_type === 'escort' ? 'Escort (Male)' :
                                     score.participant_type === 'pair' ? 'Pair' : 'Single';
                    message += `<strong>${typeLabel} Score:</strong> ${score.total} points<br>`;
                });
                
                message += `<br>Thank you for your evaluation!`;
                showAlert(message, 'success');
                
            } catch (error) {
                console.error('Error submitting Beauty Pageant scores:', error);
                showAlert('Failed to submit scores to database. Please try again.', 'error');
            }
        }
        
        // Submit Muse & Escort scores (two separate scores) - Legacy function kept for compatibility
        async function submitMuseEscortScores(categories) {
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const teamId = parseInt(document.getElementById('judgeTeamSelect').value);
            const team = teams.find(t => t.id === teamId);
            
            if (!team) {
                showAlert('Selected team not found. Please refresh the page.', 'error');
                return;
            }
            
            // Check if judge has already scored this team for this game (for UPDATE instead of INSERT)
            const existingMuse = judgeScores.find(score => 
                score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                score.team_id == teamId &&
                (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                score.participant_type === 'muse'
            );
            
            const existingEscort = judgeScores.find(score => 
                score.judge_name && score.judge_name.toLowerCase() === judgeName.toLowerCase() &&
                score.team_id == teamId &&
                (score.game_id == selectedGameId || (!score.game_id && !selectedGameId)) &&
                score.participant_type === 'escort'
            );
            
            // Validate and collect Muse scores
            const museScores = collectParticipantScores('muse', categories);
            if (!museScores) return; // Error already shown
            
            // Validate and collect Escort scores
            const escortScores = collectParticipantScores('escort', categories);
            if (!escortScores) return; // Error already shown
            
            try {
                // Submit/Update Muse score
                const museScoreData = {
                    judge_name: judgeName,
                    game_id: selectedGameId,
                    team_id: teamId,
                    participant_type: 'muse',
                    criteria1: museScores[0] || 0,
                    criteria2: museScores[1] || 0,
                    criteria3: museScores[2] || 0,
                    criteria4: museScores[3] || 0,
                    criteria5: museScores[4] || 0,
                    total_score: museScores.reduce((sum, s) => sum + s, 0)
                };
                
                // If existing score, include the ID for UPDATE
                if (existingMuse && existingMuse.id) {
                    museScoreData.id = existingMuse.id;
                }
                
                const museMethod = existingMuse ? 'PUT' : 'POST';
                const museResponse = await fetch('database_handler.php?action=judge_scores', {
                    method: museMethod,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(museScoreData)
                });
                
                const museResult = await museResponse.json();
                
                if (!museResult.success) {
                    throw new Error(museResult.message || 'Failed to submit Muse score');
                }
                
                // Submit/Update Escort score
                const escortScoreData = {
                    judge_name: judgeName,
                    game_id: selectedGameId,
                    team_id: teamId,
                    participant_type: 'escort',
                    criteria1: escortScores[0] || 0,
                    criteria2: escortScores[1] || 0,
                    criteria3: escortScores[2] || 0,
                    criteria4: escortScores[3] || 0,
                    criteria5: escortScores[4] || 0,
                    total_score: escortScores.reduce((sum, s) => sum + s, 0)
                };
                
                // If existing score, include the ID for UPDATE
                if (existingEscort && existingEscort.id) {
                    escortScoreData.id = existingEscort.id;
                }
                
                const escortMethod = existingEscort ? 'PUT' : 'POST';
                const escortResponse = await fetch('database_handler.php?action=judge_scores', {
                    method: escortMethod,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(escortScoreData)
                });
                
                const escortResult = await escortResponse.json();
                
                if (!escortResult.success) {
                    throw new Error(escortResult.message || 'Failed to submit Escort score');
                }
                
                // Both scores submitted/updated successfully
                loadJudgeScoresFromDatabase();
                populateJudgeTeamSelect();
                
                const museTotal = museScores.reduce((sum, s) => sum + s, 0);
                const escortTotal = escortScores.reduce((sum, s) => sum + s, 0);
                const action = (existingMuse || existingEscort) ? 'updated' : 'submitted';
                
                showAlert(
                    `Scores ${action} successfully!<br><br>
                    <strong>Judge:</strong> ${judgeName}<br>
                    <strong>Team:</strong> ${team.name} ${team.code ? `(${team.code})` : ''}<br>
                    <strong>Muse (Female) Score:</strong> ${museTotal} points<br>
                    <strong>Escort (Male) Score:</strong> ${escortTotal} points<br>
                    <strong>Combined Total:</strong> ${museTotal + escortTotal} points<br><br>
                    Thank you for your evaluation!`,
                    'success'
                );
            } catch (error) {
                console.error('Error submitting Muse & Escort scores:', error);
                showAlert('Failed to submit scores to database. Please try again.', 'error');
            }
        }
        
        // Collect scores for a specific participant (Muse or Escort)
        function collectParticipantScores(participant, categories) {
            const allCriteriaScores = [];
            
            // Collect scores from all categories (no validation requirement)
            for (let catIndex = 0; catIndex < categories.length; catIndex++) {
                const categoryInputs = document.querySelectorAll(`.beauty-criteria-input[data-category-index="${catIndex}"][data-participant="${participant}"]`);
                
                categoryInputs.forEach(input => {
                    const score = parseFloat(input.value) || 0;
                    allCriteriaScores.push(score);
                });
            }
            
            // Map to criteria1-5
            const criteria1 = allCriteriaScores[0] || 0;
            const criteria2 = allCriteriaScores[1] || 0;
            const criteria3 = allCriteriaScores[2] || 0;
            const criteria4 = allCriteriaScores[3] || 0;
            let criteria5 = allCriteriaScores[4] || 0;
            
            // If more than 5 criteria, sum the rest into criteria5
            if (allCriteriaScores.length > 5) {
                criteria5 += allCriteriaScores.slice(4).reduce((sum, score) => sum + score, 0);
            }
            
            return [criteria1, criteria2, criteria3, criteria4, criteria5];
        }
        

        // Delete judge score from database
        async function deleteJudgeScore(scoreId) {
            if (!confirm('Are you sure you want to delete this score? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch('database_handler.php?action=judge_scores', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: scoreId })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Score deleted successfully!', 'success');
                    // Reload scores from database
                    loadJudgeScoresFromDatabase();
                } else {
                    throw new Error(result.message || 'Failed to delete score');
                }
            } catch (error) {
                console.error('Error deleting score:', error);
                showAlert('Failed to delete score from database. Please try again.', 'error');
            }
        }

        function clearJudgeForm() {
            // Clear scoring inputs - check if Beauty Pageant or regular
            const beautyCriteriaInputs = document.querySelectorAll('.beauty-criteria-input');
            if (beautyCriteriaInputs.length > 0) {
                // Clear Beauty Pageant criteria (both Muse and Escort if applicable)
                beautyCriteriaInputs.forEach(input => {
                    input.value = '';
                });
                // Reset category totals for all participants
                document.querySelectorAll('.beauty-category-total').forEach(total => {
                    total.textContent = '0';
                    total.style.color = '#ef4444';
                });
            } else {
                // Clear regular criteria
                const criteria1 = document.getElementById('criteria1');
                const criteria2 = document.getElementById('criteria2');
                const criteria3 = document.getElementById('criteria3');
                const criteria4 = document.getElementById('criteria4');
                const criteria5 = document.getElementById('criteria5');
                if (criteria1) criteria1.value = '';
                if (criteria2) criteria2.value = '';
                if (criteria3) criteria3.value = '';
                if (criteria4) criteria4.value = '';
                if (criteria5) criteria5.value = '';
            }
            updateJudgeTotalScore();
        }

        function renderJudgeHistory() {
            const table = document.getElementById('judgeHistoryTable');
            if (!table) {
                // History table has been removed from the UI; nothing to render
                return;
            }
            
            // Filter scores to only show current judge's scores for the selected game
            const judgeName = currentAssignedJudge || sessionJudgeName;
            const filteredScores = judgeScores.filter(score => {
                // Match judge name (case-insensitive)
                const scoreJudgeName = (score.judge_name || '').trim().toLowerCase();
                const currentJudgeName = judgeName.trim().toLowerCase();
                const judgeMatches = scoreJudgeName === currentJudgeName;
                
                // Match game ID if selected
                const gameMatches = !selectedGameId || score.game_id == selectedGameId;
                
                return judgeMatches && gameMatches;
            });
            
            // Get criteria names and count from selected game
            let criteriaNames = ['Performance', 'Teamwork', 'Strategy', 'Sportsmanship', 'Overall'];
            let actualCriteriaCount = 5;
            
            if (selectedGame && selectedGame.points_system) {
                const criteriaList = selectedGame.points_system.criteria || [];
                if (criteriaList.length > 0) {
                    criteriaNames = criteriaList.map((c, i) => c.name || `Criteria ${i + 1}`);
                    actualCriteriaCount = criteriaNames.length;
                }
            }
            
            // Calculate total columns: Judge + Team + Participant Type + Criteria columns + Total + Date
            const totalColumns = 3 + actualCriteriaCount + 2; // 3 fixed + N criteria + 2 fixed
            
            if (filteredScores.length === 0) {
                table.innerHTML = `
                    <tr>
                        <td colspan="${totalColumns}" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                            No scores recorded yet
                        </td>
                    </tr>
                `;
                return;
            }

            // Sort by most recent first
            const sorted = [...filteredScores].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
            
            table.innerHTML = sorted.map(score => {
                const team = teams.find(t => t.id === score.team_id);
                const teamColor = team ? team.color : '#2563eb';
                
                // Determine score color class
                let scoreClass = 'score-poor';
                if (score.total_score >= 90) scoreClass = 'score-excellent';
                else if (score.total_score >= 70) scoreClass = 'score-good';
                else if (score.total_score >= 50) scoreClass = 'score-fair';

                // Get participant type label
                let participantType = 'Single';
                let participantBadge = 'background: #e2e8f0; color: #475569;';
                if (score.participant_type) {
                    if (score.participant_type === 'muse') {
                        participantType = 'Muse (Female)';
                        participantBadge = 'background: #fce7f3; color: #9f1239;';
                    } else if (score.participant_type === 'escort') {
                        participantType = 'Escort (Male)';
                        participantBadge = 'background: #dbeafe; color: #1e40af;';
                    } else if (score.participant_type === 'pair') {
                        participantType = 'Pair';
                        participantBadge = 'background: #fef3c7; color: #92400e;';
                    } else {
                        participantType = score.participant_type.charAt(0).toUpperCase() + score.participant_type.slice(1);
                    }
                }

                // Build criteria columns dynamically based on actual count
                let criteriaColumns = '';
                for (let i = 1; i <= actualCriteriaCount; i++) {
                    const criteriaValue = score[`criteria${i}`] || 0;
                    criteriaColumns += `<td style="text-align: center;"><strong>${criteriaValue}</strong></td>`;
                }

                return `
                    <tr>
                        <td><strong>${score.judge_name}</strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${teamColor}; border: 2px solid ${teamColor};"></div>
                                <div>
                                    <div style="font-weight: 600;">${team ? team.name : 'Unknown Team'}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">${team ? team.code : 'N/A'}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; ${participantBadge}">
                                ${participantType}
                            </span>
                        </td>
                        ${criteriaColumns}
                        <td style="text-align: center;"><strong class="${scoreClass}" style="font-size: 1.1em;">${score.total_score}</strong></td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">${new Date(score.timestamp).toLocaleString()}</td>
                    </tr>
                `;
            }).join('');
        }

        async function loadCompletedEvents() {
            try {
                const response = await fetch('database_handler.php?action=judge_event_status&judge_name=' + encodeURIComponent(sessionJudgeName));
                const result = await response.json();
                if (result.success && result.data) {
                    completedEvents = {};
                    result.data.forEach(status => {
                        completedEvents[parseInt(status.game_id, 10)] = status.status;
                    });
                }
            } catch (error) {
                console.error('Error loading completed events:', error);
            }
        }

        async function markCurrentEventCompleted() {
            if (!selectedGameId) {
                showAlert('Select an event before marking it as completed.', 'error');
                return;
            }

            if (completedEvents[selectedGameId] === 'completed') {
                showAlert('This event is already marked as completed.', 'info');
                return;
            }

            if (!confirm('Mark this event as completed? You will no longer be able to submit additional scores for it.')) {
                return;
            }

            try {
                const response = await fetch('database_handler.php?action=judge_event_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        judge_name: currentAssignedJudge || sessionJudgeName,
                        game_id: selectedGameId,
                        status: 'completed'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    completedEvents[selectedGameId] = 'completed';
                    showAlert('Event marked as completed. Redirecting to your dashboard...', 'success');
                    setTimeout(() => window.location.href = 'judge_dashboard.php', 1500);
                } else {
                    showAlert(result.message || 'Unable to mark event as completed.', 'error');
                }
            } catch (error) {
                console.error('Error marking event completed:', error);
                showAlert('Unable to update event status. Please try again.', 'error');
            }
        }

        function showAlert(message, type = 'info') {
            // Remove existing alerts
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alert = document.createElement('div');
            alert.className = `custom-alert alert-${type}`;
            alert.innerHTML = `
                <div style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#22c55e' : '#2563eb'};
                    color: white;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                    z-index: 10000;
                    max-width: 400px;
                    animation: slideIn 0.3s ease-out;
                ">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}" 
                           style="font-size: 1.2rem; margin-top: 2px;"></i>
                        <div style="flex: 1;">${message}</div>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                style="background: none; border: none; color: white; cursor: pointer; padding: 4px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(alert);

            // Auto-remove after 5 seconds for success, 8 seconds for errors
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, type === 'error' ? 8000 : 5000);
        }

        // Add CSS for slideIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
<?php include 'includes/footer.php'; ?>