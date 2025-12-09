<?php
require_once 'config.php';

if (!isset($_SESSION['judge_validated']) || $_SESSION['judge_validated'] !== true || !isset($_SESSION['judge_name'])) {
    header('Location: judge_entry.php');
    exit;
}

$judgeName = $_SESSION['judge_name'];
$pageTitle = "Judge Dashboard - DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM";
include 'includes/header.php';
?>
<style>
    :root {
        --primary-blue: #2563eb;
        --primary-dark: #1d4ed8;
        --surface: #ffffff;
        --muted: #64748b;
        --border: #e2e8f0;
        --success: #22c55e;
        --warning: #fbbf24;
        --danger: #ef4444;
        --info: #0ea5e9;
    }

    body {
        background: #f8fafc;
    }

    .back-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        background: var(--primary-blue);
        color: #fff;
        padding: 10px 16px;
        border-radius: 999px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        z-index: 1000;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 15px 60px;
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
        border-radius: 20px;
        padding: 40px;
        color: #fff;
        margin-bottom: 30px;
        box-shadow: 0 15px 40px rgba(37, 99, 235, 0.25);
    }

    .dashboard-header h1 {
        font-size: 2.2rem;
        margin: 0 0 10px;
    }

    .dashboard-header p {
        margin: 0;
        color: rgba(255,255,255,0.85);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: var(--surface);
        border-radius: 16px;
        padding: 25px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }

    .summary-card h3 {
        font-size: 0.95rem;
        color: var(--muted);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .summary-card .value {
        font-size: 1.9rem;
        font-weight: 700;
        color: #0f172a;
    }

    .section {
        margin-bottom: 35px;
    }

    .section h2 {
        font-size: 1.4rem;
        margin-bottom: 15px;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
    }

    .event-card {
        background: var(--surface);
        border-radius: 18px;
        border: 1px solid var(--border);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 10px 24px rgba(15,23,42,0.05);
    }

    .event-card h3 {
        margin: 0;
        font-size: 1.2rem;
        color: #0f172a;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .badge.event {
        background: #eef2ff;
        color: #4338ca;
    }

    .badge.status {
        background: #ecfdf5;
        color: #047857;
    }

    .badge.status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.status.completed {
        background: #ecfdf5;
        color: #047857;
    }

    .event-actions {
        margin-top: auto;
        display: flex;
        gap: 10px;
    }

    .btn {
        border: none;
        border-radius: 10px;
        padding: 12px 18px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-primary {
        background: var(--primary-blue);
        color: white;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    }

    .table-container {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead {
        background: #f1f5f9;
    }

    table th, table td {
        padding: 14px 16px;
        text-align: left;
    }

    table th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
    }

    table tbody tr {
        border-top: 1px solid var(--border);
    }

    table tbody tr:nth-child(every odd) {
        background: #fcfdff;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--muted);
    }

    .scoring-history-table {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow-x: auto;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        margin-bottom: 20px;
    }

    .scoring-history-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .scoring-history-table thead {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
        color: white;
    }

    .scoring-history-table th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .scoring-history-table th:first-child {
        width: 200px;
    }

    .scoring-history-table th:nth-child(2) {
        width: 120px;
        text-align: center;
    }

    .scoring-history-table tbody tr {
        border-top: 1px solid var(--border);
    }

    .scoring-history-table tbody tr:nth-child(even) {
        background: #fcfdff;
    }

    .scoring-history-table td {
        padding: 14px 16px;
        font-size: 0.95rem;
    }

    .scoring-history-table td:first-child {
        font-weight: 600;
        color: #0f172a;
    }

    .scoring-history-table td:nth-child(2) {
        text-align: center;
        font-weight: 600;
        color: var(--muted);
    }

    .score-box {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        min-width: 50px;
        text-align: center;
        margin: 2px;
    }

    .score-box.muse {
        background: #fce7f3;
        color: #be185d;
        border: 2px solid #f9a8d4;
    }

    .score-box.escort {
        background: #dbeafe;
        color: #1e40af;
        border: 2px solid #93c5fd;
    }

    .score-box.single {
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #cbd5e1;
    }

    .score-box.pair {
        background: #fef3c7;
        color: #92400e;
        border: 2px solid #fcd34d;
    }

    .event-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 2px solid var(--border);
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .event-header i {
        color: var(--primary-blue);
    }

    .excel-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .excel-table thead {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
        color: white;
    }

    .excel-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: white !important;
    }

    .excel-table td {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        font-size: 0.9rem;
    }

    .excel-table tbody tr:hover {
        background: #f8fafc;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
        .print-section table {
            page-break-inside: avoid;
        }
        .print-section .scoring-card {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 30px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .events-grid {
            grid-template-columns: 1fr;
        }

        .scoring-history-table {
            overflow-x: auto;
        }

        .scoring-history-table table {
            min-width: 600px;
        }

        .scoring-history-table th,
        .scoring-history-table td {
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .score-box {
            padding: 6px 10px;
            font-size: 0.85rem;
            min-width: 40px;
        }
    }
</style>

<a href="judge_entry.php" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to Judge Entry
</a>

<div class="dashboard-container">
    <div class="dashboard-header">
        <p>Welcome back,</p>
        <h1><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($judgeName); ?></h1>
        <p>Review your assigned events, monitor progress, and continue scoring.</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>Assigned Events</h3>
            <div class="value" id="assignedEventsCount">0</div>
        </div>
        <div class="summary-card">
            <h3>Events In Progress</h3>
            <div class="value" id="eventsInProgress">0</div>
        </div>
        <div class="summary-card">
            <h3>Scores Submitted</h3>
            <div class="value" id="scoresSubmitted">0</div>
        </div>
    </div>

    <div class="section">
        <h2><i class="fas fa-clipboard-check"></i> Assigned Events</h2>
        <div class="events-grid" id="assignedEventsGrid">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i> Loading your assigned events...
            </div>
        </div>
    </div>

    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;"><i class="fas fa-history"></i> Scoring History</h2>
            <div style="display: flex; gap: 10px; align-items: center;">
                <select id="scoringHistoryEventFilter" style="padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; background: var(--surface); color: #0f172a; min-width: 200px;">
                    <option value="">All Events</option>
                </select>
                <button onclick="printScoringHistory()" class="btn btn-primary" style="padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div id="scoringHistoryContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i> Loading scoring history...
            </div>
        </div>
    </div>

    <div class="section" style="text-align: center;">
        <a href="judgescore.php" class="btn btn-primary" style="min-width: 240px;">
            <i class="fas fa-gavel"></i> Go to Scoring Interface
        </a>
    </div>
</div>

<script>

const sessionJudgeName = '<?php echo addslashes($judgeName); ?>';
let assignedEvents = [];
let allGames = []; // Store all games to access event data for scoring history
let judgeScores = [];
let teams = [];
let eventStatuses = {};

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
});

async function loadDashboardData() {
    try {
        const [gamesResponse, scoresResponse, teamsResponse, statusResponse] = await Promise.all([
            fetch('database_handler.php?action=games'),
            fetch('database_handler.php?action=judge_scores'),
            fetch('database_handler.php?action=teams'),
            fetch('database_handler.php?action=judge_event_status&judge_name=' + encodeURIComponent(sessionJudgeName))
        ]);

        const gamesResult = await gamesResponse.json();
        const scoresResult = await scoresResponse.json();
        const teamsResult = await teamsResponse.json();
        const statusResult = await statusResponse.json();

        if (gamesResult.success && gamesResult.data) {
            allGames = gamesResult.data; // Store all games
            assignedEvents = gamesResult.data.filter(game => isJudgeAssigned(game, sessionJudgeName));
        }

        if (scoresResult.success && scoresResult.data) {
            judgeScores = scoresResult.data.filter(score => 
                score.judge_name && score.judge_name.toLowerCase() === sessionJudgeName.toLowerCase()
            );
        }

        if (teamsResult.success && teamsResult.data) {
            teams = teamsResult.data;
        }

        if (statusResult.success && statusResult.data) {
            eventStatuses = {};
            statusResult.data.forEach(row => {
                eventStatuses[row.game_id] = row.status;
            });
        }

        renderSummary();
        renderAssignedEvents();
        populateEventFilter();
        renderScoreHistory();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        const grid = document.getElementById('assignedEventsGrid');
        if (grid) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i> Unable to load your events. Please try again later.
                </div>
            `;
        }
    }
}

function isJudgeAssigned(game, judgeName) {
    if (!game || game.category !== 'judge') return false;
    const judges = Array.isArray(game.authorized_judges) ? game.authorized_judges : [];
    if (judges.length === 0) return true; // fallback if no restriction
    return judges.some(judge => judge && judge.trim().toLowerCase() === judgeName.trim().toLowerCase());
}

function renderSummary() {
    document.getElementById('assignedEventsCount').textContent = assignedEvents.length;

    const completedCount = assignedEvents.filter(event => eventStatuses[event.id] === 'completed').length;
    const activeCount = Math.max(assignedEvents.length - completedCount, 0);

    document.getElementById('eventsInProgress').textContent = activeCount;
    document.getElementById('scoresSubmitted').textContent = judgeScores.length;
}

function renderAssignedEvents() {
    const grid = document.getElementById('assignedEventsGrid');
    if (!grid) return;

    if (assignedEvents.length === 0) {
        grid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-gavel"></i> No events assigned to you yet. Please contact the administrator.
            </div>
        `;
        return;
    }

    grid.innerHTML = assignedEvents.map(event => {
        const scoredCount = judgeScores.filter(score => score.game_id == event.id).length;
        const isCompleted = eventStatuses[event.id] === 'completed';
        const statusClass = isCompleted ? 'completed' : (scoredCount > 0 ? 'pending' : 'pending');
        const statusLabel = isCompleted ? 'Completed' : (scoredCount > 0 ? 'In Progress' : 'Not Started');
        const description = event.description ? event.description : 'No description provided.';
        const judgeEventType = event.judge_event_type ? event.judge_event_type : 'General Event';
        const scoringFormula = event.scoring_formula ? event.scoring_formula : 'legacy';

        const actions = isCompleted
            ? `
                <div class="event-actions">
                    <button class="btn btn-secondary" style="flex:1;" onclick="alert('This event has already been marked as completed. Contact the administrator if changes are needed.');">
                        <i class="fas fa-lock"></i> Completed
                    </button>
                </div>
            `
            : `
                <div class="event-actions">
                    <a href="judgescore.php?event_id=${event.id}" class="btn btn-primary" style="flex:1;">
                        <i class="fas fa-play"></i> Continue Scoring
                    </a>
                    <button class="btn btn-secondary" style="flex:1;" onclick="markEventCompleted(${event.id})">
                        <i class="fas fa-flag-checkered"></i> Mark Done
                    </button>
                </div>
            `;

        // Formula badge text and styling
        let formulaLabel, formulaBadgeColor, formulaBadgeBg;
        if (scoringFormula === 'beauty_pageant_formula') {
            formulaLabel = 'Beauty Pageant Formula';
            formulaBadgeColor = '#8b5cf6';
            formulaBadgeBg = '#f3e8ff';
        } else if (scoringFormula === 'new_formula') {
            formulaLabel = 'Ranking Formula';
            formulaBadgeColor = '#10b981';
            formulaBadgeBg = '#d1fae5';
        } else {
            formulaLabel = 'Averaging Formula';
            formulaBadgeColor = '#64748b';
            formulaBadgeBg = '#f1f5f9';
        }

        return `
            <div class="event-card">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <h3>${event.name}</h3>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
                        <span class="badge event"><i class="fas fa-tag"></i> ${judgeEventType}</span>
                        <select 
                            onchange="updateScoringFormula(${event.id}, this.value)" 
                            ${isCompleted ? 'disabled' : ''}
                            style="padding:6px 10px; border-radius:999px; border:1px solid var(--border); font-size:0.78rem; font-weight:600; background:${isCompleted ? '#e2e8f0' : '#f9fafb'}; color:${isCompleted ? '#94a3b8' : '#0f172a'}; cursor:${isCompleted ? 'not-allowed' : 'pointer'}; opacity:${isCompleted ? '0.6' : '1'};"
                            title="${isCompleted ? 'Event is completed. Formula cannot be changed.' : 'Select scoring formula for this event'}"
                        >
                            <option value="legacy" ${scoringFormula === 'legacy' ? 'selected' : ''}>Averaging Formula</option>
                            <option value="new_formula" ${scoringFormula === 'new_formula' ? 'selected' : ''}>Ranking Formula</option>
                            <option value="beauty_pageant_formula" ${scoringFormula === 'beauty_pageant_formula' ? 'selected' : ''}>Beauty Pageant Formula (Rank-Based)</option>
                        </select>
                    </div>
                </div>
                <p style="color: var(--muted); margin: 0;">${description}</p>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <span class="badge status ${statusClass}">
                        <i class="fas ${isCompleted ? 'fa-check-circle' : (scoredCount > 0 ? 'fa-hourglass-half' : 'fa-clock')}"></i>
                        ${statusLabel}
                    </span>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1;">
                        <i class="fas fa-list-ol"></i> ${scoredCount} score${scoredCount === 1 ? '' : 's'}
                    </span>
                    <span class="badge" style="background:${formulaBadgeBg}; color:${formulaBadgeColor};">
                        <i class="fas fa-calculator"></i> ${formulaLabel}
                    </span>
                </div>
                ${actions}
            </div>
        `;
    }).join('');
}

function populateEventFilter() {
    const filter = document.getElementById('scoringHistoryEventFilter');
    if (!filter) return;

    // Get all events that have scores
    const eventsWithScores = new Set();
    judgeScores.forEach(score => {
        if (score.game_id) {
            eventsWithScores.add(score.game_id);
        }
    });

    // Build options
    let options = '<option value="">All Events</option>';
    allGames.forEach(event => {
        if (eventsWithScores.has(event.id)) {
            options += `<option value="${event.id}">${event.name}</option>`;
        }
    });

    filter.innerHTML = options;
    
    // Add event listener
    filter.addEventListener('change', () => {
        renderScoreHistory();
    });
}

function renderScoreHistory() {
    const container = document.getElementById('scoringHistoryContainer');
    const filter = document.getElementById('scoringHistoryEventFilter');
    if (!container) return;

    if (judgeScores.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i> You have not submitted any scores yet.
            </div>
        `;
        return;
    }

    // Get selected event filter
    const selectedEventId = filter ? filter.value : '';

    const teamsMap = new Map(teams.map(team => [team.id, team]));
    const eventsMap = new Map(allGames.map(event => [event.id, event]));

    // Group scores by game_id
    const scoresByGame = {};
    judgeScores.forEach(score => {
        const gameId = score.game_id || 'unknown';
        // Apply filter
        if (selectedEventId && gameId != selectedEventId) return;
        
        if (!scoresByGame[gameId]) {
            scoresByGame[gameId] = [];
        }
        scoresByGame[gameId].push(score);
    });

    if (Object.keys(scoresByGame).length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i> No scores found for the selected event.
            </div>
        `;
        return;
    }

    let html = '';

    // Render each event's scoring history
    Object.keys(scoresByGame).forEach(gameId => {
        const gameScores = scoresByGame[gameId];
        const event = eventsMap.get(parseInt(gameId)) || { 
            name: gameScores[0]?.game_name || 'Unknown Event', 
            points_system: null 
        };
        
        // Parse points system
        let pointsSystem = null;
        let allCategories = [];
        
        if (event.points_system) {
            try {
                pointsSystem = typeof event.points_system === 'string' 
                    ? JSON.parse(event.points_system) 
                    : event.points_system;
                
                if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories && Array.isArray(pointsSystem.categories)) {
                    allCategories = pointsSystem.categories;
                }
            } catch (e) {
                console.error('Error parsing points_system:', e);
            }
        }

        // Group scores by team and participant type, then by judge
        const scoresByTeamAndType = {};
        gameScores.forEach(score => {
            const teamId = score.team_id;
            const participantType = score.participant_type || 'single';
            const key = `${teamId}_${participantType}`;
            
            if (!scoresByTeamAndType[key]) {
                const team = teamsMap.get(parseInt(teamId));
                scoresByTeamAndType[key] = {
                    teamId: teamId,
                    team: team,
                    teamName: team ? team.name : 'Unknown Team',
                    participantType: participantType,
                    scores: []
                };
            }
            scoresByTeamAndType[key].scores.push(score);
        });

        const teamKeys = Object.keys(scoresByTeamAndType);
        if (teamKeys.length === 0) return;

        // Helper function to get criteria for a specific participant type
        function getCriteriaForParticipantType(participantType, categories) {
            if (!categories || categories.length === 0) {
                // Fallback for non-beauty pageant events
                return [];
            }

            const relevantCriteria = [];
            let globalCriteriaIndex = 1;

            categories.forEach((category, catIdx) => {
                const scoringType = category.scoring_type || 'single';
                let shouldInclude = false;

                // Determine if this category is relevant to the participant type
                if (participantType === 'muse' || participantType === 'escort') {
                    // Muse/Escort should see: muse_escort categories and single categories
                    shouldInclude = (scoringType === 'muse_escort' || scoringType === 'single');
                } else if (participantType === 'pair') {
                    // Pair should see: pair categories and single categories
                    shouldInclude = (scoringType === 'pair' || scoringType === 'single');
                } else {
                    // Single should see: single categories only
                    shouldInclude = (scoringType === 'single');
                }

                if (shouldInclude && category.criteria && Array.isArray(category.criteria)) {
                    category.criteria.forEach((criteria, critIdx) => {
                        relevantCriteria.push({
                            name: criteria.name || `Criteria ${globalCriteriaIndex}`,
                            maxPoints: criteria.percentage || criteria.maxPoints || 0,
                            categoryName: category.name || `Category ${catIdx + 1}`,
                            globalIndex: globalCriteriaIndex,
                            categoryIndex: catIdx,
                            criteriaIndex: critIdx
                        });
                        globalCriteriaIndex++;
                    });
                } else if (shouldInclude) {
                    // If category should be included but has no criteria, still increment index
                    globalCriteriaIndex++;
                }
            });

            return relevantCriteria;
        }

        // Render card for each team/participant combination
        teamKeys.forEach(key => {
            const item = scoresByTeamAndType[key];
            const team = item.team;
            const participantType = item.participantType || 'single';
            
            // Get only criteria relevant to this participant type
            const criteriaList = getCriteriaForParticipantType(participantType, allCategories);
            
            // If no criteria found and it's not a beauty pageant, use defaults
            if (criteriaList.length === 0 && (!pointsSystem || pointsSystem.type !== 'beauty_pageant')) {
                const defaultCriteriaNames = ['Performance', 'Teamwork', 'Strategy', 'Sportsmanship', 'Overall'];
                const defaultMaxPoints = [20, 20, 20, 20, 20];
                criteriaList.push(...defaultCriteriaNames.map((name, idx) => ({
                    name: name,
                    maxPoints: defaultMaxPoints[idx],
                    globalIndex: idx + 1
                })));
            }

            if (criteriaList.length === 0) {
                // Skip if no relevant criteria for this participant type
                return;
            }
            
            html += `<div style="margin-bottom: 16px; padding: 16px; border-radius: 10px; background: #f9fafb; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(15,23,42,0.04);">`;
            
            // Header
            html += `<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">`;
            html += `<div style="display: flex; align-items: center; gap: 10px;">`;
            html += `<div style="width: 32px; height: 32px; border-radius: 999px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #92400e;">`;
            html += `<i class="fas fa-trophy"></i>`;
            html += `</div>`;
            html += `<div>`;
            html += `<div style="font-weight: 700; color: #111827;">${event.name}</div>`;
            const participantLabel = participantType === 'muse' ? 'Muse' : 
                                   participantType === 'escort' ? 'Escort' : 
                                   participantType === 'pair' ? 'Pair' : 'Single';
            html += `<div style="font-size: 0.8rem; color: #6b7280;">${participantLabel}</div>`;
            html += `</div>`;
            html += `</div>`;
            html += `</div>`;

            // Team info
            html += `<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">`;
            html += `<div style="width: 14px; height: 14px; border-radius: 50%; background-color: ${team ? (team.color || '#2563eb') : '#2563eb'};"></div>`;
            html += `<div>`;
            html += `<div style="font-weight: 600; color: #111827;">${item.teamName}</div>`;
            html += `<div style="font-size: 0.8rem; color: #6b7280;">${participantLabel}</div>`;
            html += `</div>`;
            html += `</div>`;

            // Criteria table
            html += `<div style="overflow-x: auto; margin-top: 4px;">`;
            html += `<table class="excel-table" style="background: white; margin: 0; width: 100%;">`;
            html += `<thead>`;
            html += `<tr>`;
            html += `<th>Criteria</th>`;
            html += `<th>Judge Scores</th>`;
            html += `</tr>`;
            html += `</thead>`;
            html += `<tbody>`;

            // Define participant type styles
            const ptStyles = {
                muse: { label: 'Muse', color: '#ec4899', bgColor: '#fce7f3', borderColor: '#f472b6' },
                escort: { label: 'Escort', color: '#3b82f6', bgColor: '#dbeafe', borderColor: '#60a5fa' },
                pair: { label: 'Pair', color: '#f59e0b', bgColor: '#fef3c7', borderColor: '#fbbf24' },
                single: { label: 'Single', color: '#10b981', bgColor: '#d1fae5', borderColor: '#34d399' }
            };

            // Group criteria by category
            const criteriaByCategory = {};
            criteriaList.forEach(criteria => {
                const categoryName = criteria.categoryName || 'General';
                if (!criteriaByCategory[categoryName]) {
                    criteriaByCategory[categoryName] = [];
                }
                criteriaByCategory[categoryName].push(criteria);
            });

            // Render criteria grouped by category
            Object.keys(criteriaByCategory).forEach(categoryName => {
                const categoryCriteria = criteriaByCategory[categoryName];
                let categoryTotal = 0;

                categoryCriteria.forEach((criteria) => {
                    const criteriaNum = criteria.globalIndex || 1;
                    
                    // Get all judge scores for this criteria
                    const judgeScorePairs = [];
                    item.scores.forEach(score => {
                        let scoreValue = 0;
                        
                        // Check details_json for beauty pageant
                        if (score.details_json && pointsSystem && pointsSystem.type === 'beauty_pageant') {
                            try {
                                const details = typeof score.details_json === 'string' 
                                    ? JSON.parse(score.details_json) 
                                    : score.details_json;
                                
                                if (details.type === 'beauty_pageant' && details.categories) {
                                    details.categories.forEach(catDetail => {
                                        if (catDetail.index === criteria.categoryIndex && catDetail.criteria) {
                                            catDetail.criteria.forEach(c => {
                                                if (c.index === criteria.criteriaIndex) {
                                                    scoreValue = Number(c.score || 0);
                                                }
                                            });
                                        }
                                    });
                                }
                            } catch (e) {
                                console.error('Error parsing details_json:', e);
                            }
                        }
                        
                        // Fallback to criteria1-5
                        if (scoreValue === 0) {
                            scoreValue = Number(score[`criteria${criteriaNum}`] || 0);
                        }
                        
                        const judgeName = score.judge_name || 'Unknown';
                        const pt = score.participant_type || 'single';
                        
                        // Only count scores that match the current participant type
                        if (pt === participantType) {
                            judgeScorePairs.push({ judge: judgeName, participantType: pt, score: scoreValue });
                            categoryTotal += scoreValue;
                        }
                    });

                    const judgeBadges = [];
                    judgeScorePairs.forEach(js => {
                        const style = ptStyles[participantType] || ptStyles.single;
                        judgeBadges.push(`<span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; margin: 2px; border-radius: 6px; background: linear-gradient(135deg, ${style.bgColor} 0%, ${style.bgColor}dd 100%); border: 1px solid ${style.borderColor}; font-size: 0.8rem;"><strong style="color: ${style.color};">${js.judge}</strong><span style="color: #6b7280; font-size: 0.75rem;">(${style.label}):</span><span style="color: #1e293b; font-weight: 600;">${js.score}</span></span>`);
                    });

                    const displayName = criteria.categoryName 
                        ? `${criteria.name.toUpperCase()} (${criteria.categoryName.toUpperCase()})` 
                        : criteria.name.toUpperCase();

                    html += `<tr>`;
                    html += `<td style="font-weight: 600; color: #1e293b;">${displayName}</td>`;
                    html += `<td style="padding: 10px; white-space: normal; word-wrap: break-word; line-height: 1.6;">${judgeBadges.join(' ') || '0'}</td>`;
                    html += `</tr>`;
                });

                // Add category total row
                const style = ptStyles[participantType] || ptStyles.single;
                html += `<tr style="background: #f8fafc; font-weight: 700;">`;
                html += `<td style="color: #475569; padding: 12px 16px;">${categoryName.toUpperCase()} TOTAL</td>`;
                html += `<td style="padding: 12px 16px;">`;
                html += `<span style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 6px; background: linear-gradient(135deg, ${style.bgColor} 0%, ${style.bgColor}dd 100%); border: 2px solid ${style.borderColor}; font-size: 0.9rem; font-weight: 700;"><span style="color: ${style.color};">Total:</span><span style="color: #1e293b;">${categoryTotal}</span></span>`;
                html += `</td>`;
                html += `</tr>`;
            });

            html += `</tbody>`;
            html += `</table>`;
            html += `</div>`;
            html += `</div>`;
        });
    });

    if (html === '') {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i> No scoring history available.
            </div>
        `;
    } else {
        container.innerHTML = html;
    }
}

function formatDate(timestamp) {
    if (!timestamp) return '-';
    const date = new Date(timestamp);
    return date.toLocaleString();
}

async function markEventCompleted(gameId) {
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
                judge_name: sessionJudgeName,
                game_id: gameId,
                status: 'completed'
            })
        });

        const result = await response.json();
        if (result.success) {
            eventStatuses[gameId] = 'completed';
            renderSummary();
            renderAssignedEvents();
            alert('Event marked as completed.');
        } else {
            alert(result.message || 'Failed to mark event as completed.');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Unable to update event status. Please try again.');
    }
}

async function updateScoringFormula(gameId, formulaKey) {
    try {
        const response = await fetch('database_handler.php?action=games', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: gameId,
                scoring_formula: formulaKey
            })
        });

        const result = await response.json();
        if (!result.success) {
            alert(result.message || 'Failed to update scoring formula.');
            return;
        }

        // Also update local copy so UI stays in sync
        const event = assignedEvents.find(e => e.id === gameId);
        if (event) {
            event.scoring_formula = formulaKey;
        }
        
        // Refresh the display to show updated formula badge
        renderAssignedEvents();
    } catch (error) {
        console.error('Error updating scoring formula:', error);
        alert('Unable to update scoring formula. Please try again.');
    }
}

function printScoringHistory() {
    const container = document.getElementById('scoringHistoryContainer');
    if (!container || container.innerHTML.includes('empty-state') || container.innerHTML.includes('No scoring history')) {
        alert('No scoring history to print.');
        return;
    }

    // Create a print-friendly version
    const printWindow = window.open('', '_blank');
    const judgeName = sessionJudgeName;
    const currentDate = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    // Get the current content and clean it up for printing
    let content = container.innerHTML;
    
    // Remove empty-state classes and loading spinners
    content = content.replace(/class="empty-state"[^>]*>/g, '');
    content = content.replace(/<i class="fas fa-spinner[^"]*"><\/i>/g, '');
    
    // Create print HTML
    const printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Scoring History - ${judgeName}</title>
            <style>
                @page {
                    margin: 1.5cm;
                }
                * {
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #1e293b;
                    background: white;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #2563eb;
                }
                .print-header h1 {
                    margin: 0 0 10px 0;
                    color: #1e293b;
                    font-size: 1.8rem;
                    font-weight: 700;
                }
                .print-header .judge-info {
                    color: #64748b;
                    font-size: 1rem;
                    margin: 5px 0;
                }
                .print-header .judge-info strong {
                    color: #1e293b;
                }
                .print-header .date {
                    color: #64748b;
                    font-size: 0.9rem;
                }
                .scoring-card {
                    margin-bottom: 30px;
                    padding: 20px;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    background: #f9fafb;
                    page-break-inside: avoid;
                }
                .card-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #e5e7eb;
                }
                .card-header h3 {
                    margin: 0;
                    font-size: 1.2rem;
                    color: #111827;
                }
                .card-header .participant-label {
                    font-size: 0.85rem;
                    color: #6b7280;
                }
                .team-info {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    margin-bottom: 15px;
                }
                .team-color-dot {
                    width: 14px;
                    height: 14px;
                    border-radius: 50%;
                    display: inline-block;
                }
                .team-name {
                    font-weight: 600;
                    color: #111827;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    background: white;
                    border: 1px solid #e5e7eb;
                }
                table thead {
                    background: #2563eb;
                    color: white;
                }
                table th {
                    padding: 12px 16px;
                    text-align: left;
                    font-weight: 600;
                    font-size: 0.85rem;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: white !important;
                }
                table td {
                    padding: 10px 16px;
                    border-top: 1px solid #e5e7eb;
                    font-size: 0.9rem;
                }
                table tbody tr:nth-child(even) {
                    background: #f8fafc;
                }
                table tbody tr[style*="background: #f8fafc"] {
                    background: #f1f5f9 !important;
                    font-weight: 700;
                }
                table tbody tr[style*="background: #f8fafc"] td {
                    padding: 12px 16px;
                    color: #475569;
                }
                .score-badge {
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 6px;
                    font-size: 0.8rem;
                    margin: 2px 4px 2px 0;
                    border: 1px solid;
                    white-space: nowrap;
                }
                .signature-section {
                    margin-top: 50px;
                    padding-top: 30px;
                    border-top: 2px dashed #cbd5e1;
                    page-break-inside: avoid;
                }
                .signature-line {
                    margin-top: 60px;
                    text-align: center;
                }
                .signature-line .line {
                    border-bottom: 2px solid #1e293b;
                    width: 300px;
                    margin: 0 auto 10px;
                    height: 50px;
                }
                .signature-line .name {
                    font-weight: 600;
                    color: #1e293b;
                    font-size: 1rem;
                    margin-top: 5px;
                }
                .signature-line .label {
                    color: #64748b;
                    font-size: 0.85rem;
                    margin-top: 5px;
                }
                @media print {
                    .scoring-card {
                        page-break-inside: avoid;
                    }
                    .signature-section {
                        page-break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>SCORING HISTORY</h1>
                <div class="judge-info">Judge: <strong>${judgeName.toUpperCase()}</strong></div>
                <div class="date">Date: ${currentDate}</div>
            </div>
            <div class="print-content">
                ${content}
            </div>
            <div class="signature-section">
                <div class="signature-line">
                    <div class="line"></div>
                    <div class="name">${judgeName.toUpperCase()}</div>
                    <div class="label">Judge Signature</div>
                </div>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printHTML);
    printWindow.document.close();
    
    // Wait for content to load, then print
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
        }, 250);
    };
}
</script>
<?php include 'includes/footer.php'; ?>

