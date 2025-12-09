<?php
require_once __DIR__ . '/../config.php';
$pageTitle = "DTI INTEGRATED SCORE AND RESULT MONITORING SYSTEM - ScoreSheet";
include 'includes/header.php';
?>

<!-- Navigation Tabs -->
<nav class="nav-tabs">
    <button class="tab-btn active" data-tab="scoresheet">
        <i class="fas fa-table"></i> ScoreSheet
    </button>
    <button class="tab-btn" data-tab="games">
        <i class="fas fa-gamepad"></i> Events
    </button>
    <button class="tab-btn" data-tab="teams">
        <i class="fas fa-users"></i> Teams
    </button>
    <button class="tab-btn" data-tab="leaderboards">
        <i class="fas fa-trophy"></i> Leaderboards
    </button>
    <button class="tab-btn" data-tab="overall-rankings">
        <i class="fas fa-medal"></i> Overall Rankings
    </button>
    <button class="tab-btn" data-tab="judge">
        <i class="fas fa-gavel"></i> Judge
    </button>
    <button class="tab-btn" data-tab="history">
        <i class="fas fa-history"></i> History
    </button>
</nav>

<!-- ScoreSheet Tab -->
<div class="tab-content active" id="scoresheet">
    <div class="scoresheet-container">
        <!-- Game Selection -->
        <div class="game-selection">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-gamepad"></i> Select Game</h2>
            </div>
            <div class="game-grid" id="gameGrid">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-spinner fa-spin"></i> Loading games...
                </div>
            </div>
        </div>

        <!-- Score Entry Form -->
        <div class="score-entry" id="scoreEntry" style="display: none;">
            <h2><i class="fas fa-plus-circle"></i> Add Score</h2>
            <form id="addScoreForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="selectedGame">Game:</label>
                        <input type="text" id="selectedGame" readonly>
                        <input type="hidden" id="selectedGameId">
                    </div>
                    <div class="form-group">
                        <label for="teamSelect">Team:</label>
                        <select id="teamSelect" required>
                            <option value="">Select Team</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="placement">Placement:</label>
                        <select id="placement" required>
                            <option value="">Select Placement</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="points">Points:</label>
                        <input type="number" id="points" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="scorerName">Scorer:</label>
                    <input type="text" id="scorerName" placeholder="Who recorded this score?" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Score
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelScoreBtn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Scores Table
        <div class="scores-table">
            <h2><i class="fas fa-table"></i> Current Scores</h2>
            <div class="table-container">
                <table class="excel-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th>Total Points</th>
                            <th>Games Played</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scoresTable">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin"></i> Loading scores...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> -->
    </div>
</div>

<!-- Games Tab -->
<div class="tab-content" id="games">
    <div class="games-container">
        <!-- Add Game Form -->
        <div class="add-game-form">
            <h2><i class="fas fa-plus-circle"></i> Add New Event</h2>
            <form id="addGameForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="gameName">Event Name:</label>
                        <input type="text" id="gameName" placeholder="Enter Event Name" required>
                        <div id="gameNameError" style="color: #e74c3c; font-size: 0.85rem; margin-top: 5px; display: none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="gameCategory">Category:</label>
                        <select id="gameCategory" required>
                            <option value="scorer">Sport</option>
                            <option value="judge">Event</option>
                        </select>
                        <small style="color: var(--text-muted); margin-top: 5px; display: block;">
                            <strong>Sport:</strong> Uses points system based on placement<br>
                            <strong>Event:</strong> Uses criteria-based scoring (0-20 each)
                        </small>
                    </div>
                </div>

                <!-- Event Type (only visible for event category) -->
                <div class="form-group" id="judgeEventTypeContainer" style="display: none;">
                    <label for="judgeEventType">Event Type:</label>
                    <select id="judgeEventType">
                        <option value="">Select Event Type</option>
                        <option value="Performance">Performance Evaluation</option>
                        <option value="Creative">Creative Presentation</option>
                        <option value="Sportsmanship">Sportsmanship</option>
                        <option value="Teamwork">Teamwork Assessment</option>
                        <option value="Strategy">Strategy & Planning</option>
                        <option value="Presentation">Oral Presentation</option>
                        <option value="Talent">Talent Show</option>
                        <option value="Cultural">Cultural Event</option>
                        <option value="Beauty">Beauty Pageant</option>
                        <option value="Cheerleading">Cheerleading Competition</option>
                        <option value="Dance">Dance Competition</option>
                        <option value="Cooking">Cooking Contest</option>
                        <option value="Art">Art & Design</option>
                        <option value="Other">Other (Specify in description)</option>
                    </select>
                    <small style="color: var(--text-muted); margin-top: 5px; display: block;">
                        Select the type of event to help categorize and identify the scoring criteria
                    </small>
                </div>

                <!-- Authorized Judges (only visible for judge category) -->
                <div class="form-group" id="authorizedJudgeContainer" style="display: none;">
                    <label>Authorized Judges:</label>
                    <div id="judgesList" style="margin-bottom: 10px;">
                        <div class="judge-entry" style="display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px;">
                            <input type="text" class="judge-name-input" placeholder="Enter judge name (e.g., John Smith)" data-judge-index="0">
                            <button type="button" class="btn btn-danger btn-sm remove-judge-entry" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addJudgeBtn">
                        <i class="fas fa-plus"></i> Add Judge
                    </button>
                    <small style="color: var(--text-muted); margin-top: 10px; display: block;">
                        <i class="fas fa-shield-alt"></i> <strong>Security:</strong> Only these judges will be authorized to score this game/event. Leave empty to allow any judge.
                    </small>
                </div>

                <div class="form-group">
                    <label for="gameDescription">Description (optional):</label>
                    <textarea id="gameDescription" placeholder="Brief description of the Event"></textarea>
                </div>

                <!-- Beauty Pageant Categories (only visible for Beauty Pageant event type) -->
                <div class="form-group" id="beautyPageantCategoriesContainer" style="display: none;">
                    <h3 style="margin-bottom: 15px; color: #8b5cf6; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-crown"></i> Beauty Pageant Categories
                    </h3>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">
                        Add categories for the Beauty Pageant. Each category has a percentage weight, and contains multiple criteria. Category percentages must total 100%. Criteria within each category are scored and scaled to the category's percentage.
                    </p>
                    <div style="background: #f0f9ff; border-left: 4px solid #2563eb; padding: 12px; margin-bottom: 15px; border-radius: 6px;">
                        <strong style="color: #1e40af; display: block; margin-bottom: 8px;">
                            <i class="fas fa-info-circle"></i> Scoring System:
                        </strong>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                            • Each <strong>Category</strong> has a percentage (e.g., 50%, 25%, 25%)<br>
                            • Each category contains <strong>Criteria</strong> that sum to 100% within that category<br>
                            • Category Score = (Criteria Scores / Max Possible) × Category %<br>
                            • Final Score = Sum of all Category Scores (max 100 points)
                        </div>
                    </div>
                    
                    <div id="beautyCategoriesList">
                        <!-- Categories will be added here dynamically -->
                    </div>
                    <button type="button" class="btn btn-secondary" id="addBeautyCategoryBtn" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                    <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px; border: 2px solid #fbbf24;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #92400e;">Total Category Percentage: <span id="totalCategoryPercentage">0</span>%</strong>
                                <div style="font-size: 0.85rem; color: #78350f; margin-top: 5px;">
                                    All category percentages must total 100%
                                </div>
                            </div>
                            <div id="totalCategoryError" style="color: #ef4444; font-weight: 600; display: none;">
                                Total must equal 100%!
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Criteria System (only visible for judge category) -->
                <div class="points-system" id="criteriaSystemContainer" style="display: none;">
                    <h3><i class="fas fa-list-check"></i> Custom Criteria & Percentages</h3>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">
                        Define custom criteria with percentage weights. Total must equal 100%.
                    </p>
                    <div class="points-grid" id="criteriaGrid">
                        <div class="point-entry">
                            <input type="text" placeholder="Criteria Name (e.g., Performance)" class="criteria-name-input" value="Performance">
                            <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="20" step="1">
                            <span class="percentage-label">%</span>
                            <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Criteria Name (e.g., Teamwork)" class="criteria-name-input" value="Teamwork">
                            <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="20" step="1">
                            <span class="percentage-label">%</span>
                            <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Criteria Name (e.g., Strategy)" class="criteria-name-input" value="Strategy">
                            <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="20" step="1">
                            <span class="percentage-label">%</span>
                            <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Criteria Name (e.g., Sportsmanship)" class="criteria-name-input" value="Sportsmanship">
                            <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="20" step="1">
                            <span class="percentage-label">%</span>
                            <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Criteria Name (e.g., Overall)" class="criteria-name-input" value="Overall">
                            <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="20" step="1">
                            <span class="percentage-label">%</span>
                            <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding: 12px; background: #f0f9ff; border-radius: 8px; border: 2px solid #2563eb;">
                        <div>
                            <strong>Total Percentage: <span id="totalPercentage">100</span>%</strong>
                        </div>
                        <div id="percentageError" style="color: #ef4444; font-weight: 600; display: none;">
                            Total must equal 100%!
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addCriteriaEntryBtn" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Add Criteria
                    </button>
                </div>

                <!-- Points System (only visible for scorer category) -->
                <div class="points-system" id="pointsSystemContainer">
                    <h3><i class="fas fa-star"></i> Points System</h3>
                    <div class="points-grid" id="pointsGrid">
                        <div class="point-entry">
                            <input type="text" placeholder="Placement (e.g., 1st)" class="placement-input" value="1st">
                            <input type="number" placeholder="Points" class="points-input" min="0" value="10">
                            <button type="button" class="btn btn-danger btn-sm remove-point-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Placement (e.g., 2nd)" class="placement-input" value="2nd">
                            <input type="number" placeholder="Points" class="points-input" min="0" value="8">
                            <button type="button" class="btn btn-danger btn-sm remove-point-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="point-entry">
                            <input type="text" placeholder="Placement (e.g., 3rd)" class="placement-input" value="3rd">
                            <input type="number" placeholder="Points" class="points-input" min="0" value="6">
                            <button type="button" class="btn btn-danger btn-sm remove-point-entry">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addPointEntryBtn">
                        <i class="fas fa-plus"></i> Add Placement
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Event
                </button>
            </form>
        </div>

        <!-- Games List -->
        <div class="games-list">
            <h3><i class="fas fa-gamepad"></i> Registered Events</h3>
            <div class="games-grid" id="gamesGrid">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-spinner fa-spin"></i> Loading games...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Teams Tab -->
<div class="tab-content" id="teams">
    <div class="teams-container">
        <!-- Add Team Form -->
        <div class="add-team-form">
            <h2><i class="fas fa-user-plus"></i> Add New Team</h2>
            <form id="addTeamForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="teamName">Team Name:</label>
                        <input type="text" id="teamName" placeholder="Enter team name (e.g., RO HipHop, GSC Basketball)" required>
                    </div>
                    <div class="form-group">
                        <label for="teamCode">Team Code:</label>
                        <select id="teamCode" required>
                            <option value="">Select Team Code</option>
                            <option value="RO">RO - Regional Office</option>
                            <option value="GSC">GSC - General Santos City</option>
                            <option value="SK">SK - Sultan Kudarat</option>
                            <option value="SC">SC - South Cotabato</option>
                            <option value="SAR">SAR - Sarangani</option>
                            <option value="SOC">SOC - Socsargen</option>
                            <option value="COT">COT - Cotabato</option>
                        </select>
                        <small style="color: var(--text-muted); margin-top: 5px; display: block;">Select team code category - this determines overall rankings</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="teamEventType">
                            <i class="fas fa-calendar-alt"></i> Event Type Specification (optional):
                        </label>
                        <select id="teamEventType">
                            <option value="">All Events (No Restriction)</option>
                            <option value="Performance">Performance Evaluation</option>
                            <option value="Creative">Creative Presentation</option>
                            <option value="Sportsmanship">Sportsmanship</option>
                            <option value="Teamwork">Teamwork Assessment</option>
                            <option value="Strategy">Strategy & Planning</option>
                            <option value="Presentation">Oral Presentation</option>
                            <option value="Talent">Talent Show</option>
                            <option value="Cultural">Cultural Event</option>
                            <option value="Beauty">Beauty Pageant</option>
                            <option value="Cheerleading">Cheerleading Competition</option>
                            <option value="Dance">Dance Competition</option>
                            <option value="Cooking">Cooking Contest</option>
                            <option value="Art">Art & Design</option>
                            <option value="Other">Other</option>
                        </select>
                        <small style="color: var(--text-muted); margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Specify which event type this team is registered for. When creating an event of this type, you'll see this team in the selection list.
                        </small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="teamColor">Team Color:</label>
                        <div class="color-swatches team-color-swatches" style="margin-bottom: 8px; display: flex; gap: 12px; flex-wrap: wrap;">
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#e74c3c" data-name="Red" style="background: #e74c3c;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#2563eb" data-name="Blue" style="background: #2563eb;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#facc15" data-name="Yellow" style="background: #facc15; color: #222;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#ffd700" data-name="Gold" style="background: #ffd700; color: #222;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#000000" data-name="Black" style="background: #000000; color: #fff;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#8b5cf6" data-name="Violet" style="background: #8b5cf6;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#fb923c" data-name="Orange" style="background: #fb923c;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#22c55e" data-name="Green" style="background: #22c55e;" onclick="selectTeamColorSwatch(this)"></button>
                            <button type="button" class="color-swatch-simple team-color-swatch" data-color="#ffffff" data-name="White" style="background: #ffffff; color: #222; border: 1px solid #ccc;" onclick="selectTeamColorSwatch(this)"></button>
                        </div>
                        <input type="hidden" id="teamColor" name="teamColor" value="#2563eb">
                        <div id="selectedTeamColorName" style="margin-top: 8px; font-weight: bold; color: #333;">Selected: Blue</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="teamMembers">Team Members (optional):</label>
                    <textarea id="teamMembers" placeholder="Enter team member names, separated by commas"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Team
                </button>
            </form>
        </div>

        <!-- Teams List -->
        <div class="teams-list">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3><i class="fas fa-users"></i> Registered Teams</h3>
                <div class="team-filter-controls" style="display: flex; gap: 10px; align-items: center;">
                    <select id="teamCodeFilter" class="team-filter" aria-label="Filter teams by code">
                        <option value="">All Team Codes</option>
                        <option value="RO">RO - Regional Office</option>
                        <option value="GSC">GSC - General Santos City</option>
                        <option value="SK">SK - Sultan Kudarat</option>
                        <option value="SC">SC - South Cotabato</option>
                        <option value="SAR">SAR - Sarangani</option>
                        <option value="SOC">SOC - Socsargen</option>
                        <option value="COT">COT - Cotabato</option>
                        <option value="MAG">MAG - Maguindanao</option>
                        <option value="LAN">LAN - Lanao</option>
                        <option value="SUK">SUK - Sulu</option>
                        <option value="TWI">TWI - Tawi-Tawi</option>
                        <option value="BAS">BAS - Basilan</option>
                    </select>
                    <button type="button" class="btn btn-secondary" id="clearTeamFilterBtn">
                        <i class="fas fa-times"></i> Clear Filter
                    </button>
                </div>
            </div>
            <div class="teams-grid" id="teamsGrid">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-spinner fa-spin"></i> Loading teams...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboards Tab -->
<div class="tab-content" id="leaderboards">
    <div class="leaderboards-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-trophy"></i> Final Leaderboard</h2>
            <div class="leaderboard-controls" style="display: flex; gap: 10px; align-items: center;">
                <select id="leaderboardGameFilter" class="leaderboard-filter" aria-label="Filter leaderboard by game">
                    <option value="">All Games</option>
                </select>
            </div>
        </div>
        <div class="leaderboard-table-container" id="leaderboardPrintArea">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Team</th>
                        <th>Total Points</th>
                        <th>Games Played</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="leaderboardTable">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">
                            <i class="fas fa-spinner fa-spin"></i> Loading leaderboard...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Overall Rankings Tab -->
<div class="tab-content" id="overall-rankings">
    <div class="overall-rankings-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-medal"></i> Overall Rankings</h2>
            <div style="display: flex; gap: 10px; align-items: center;">
                <label for="overallTypeFilter" style="font-size: 0.85rem; color: var(--text-muted);">View:</label>
                <select id="overallTypeFilter" class="form-control" style="width: 190px;">
                    <option value="all">All Categories (Muse, Escort, Pair)</option>
                    <option value="muse">Muse (Female)</option>
                    <option value="escort">Escort (Male)</option>
                    <option value="pair">Pair</option>
                </select>
            </div>
        </div>
        <div class="table-container">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Team</th>
                        <th>Category</th>
                        <th>Event</th>
                    </tr>
                </thead>
                <tbody id="overallRankingsTable">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">
                            <i class="fas fa-spinner fa-spin"></i> Loading rankings...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Event Tab -->
<div class="tab-content" id="judge">
    <div class="judge-container">
        <h2><i class="fas fa-gavel"></i> Event Scoring System</h2>

        <!-- Event Selection -->
        <div class="judge-selection">
            <h3><i class="fas fa-user-tie"></i> Event Scoring Access</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                Judges must enter their name and validate it before accessing the event scoring interface.
            </p>
            <div class="judge-actions">
                        <a href="judge_entry.php" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Open Judge Dashboard
                        </a>
            </div>
        </div>

        <!-- Beauty Pageant Category Breakdown (raw, printable) -->
        <div class="judge-history" id="judgeCategoryBreakdownContainer" style="margin-top: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;"><i class="fas fa-list-alt"></i> Beauty Category Criteria Breakdown</h3>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-secondary" id="exportJudgeCategoryBreakdownBtn" style="background: #22c55e; color: white;" onmouseover="this.style.background='#16a34a'" onmouseout="this.style.background='#22c55e'">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                    <button type="button" class="btn btn-primary" id="printJudgeCategoryBreakdownBtn">
                        <i class="fas fa-print"></i> Print Category Breakdown
                    </button>
                </div>
            </div>
            <div id="judgeCategoryBreakdownContent" class="judge-printable-breakdown" style="background: #ffffff; border-radius: 10px; padding: 15px; border: 1px solid #e5e7eb;">
                <div style="text-align:center; padding: 20px; color: var(--text-muted);">
                    <i class="fas fa-spinner fa-spin"></i> Loading Beauty Pageant breakdown...
            </div>
        </div>
    </div>

    </div>
</div>

<!-- History Tab -->
<div class="tab-content" id="history">
    <div class="history-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-history"></i> Score History</h2>
            <button id="printHistoryBtn" class="btn btn-primary">
                <i class="fas fa-print"></i> Print History
            </button>
        </div>
        <div class="table-container">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Game</th>
                        <th>Team</th>
                        <th>Placement</th>
                        <th>Points</th>
                        <th>Scorer</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">
                            <i class="fas fa-spinner fa-spin"></i> Loading history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script>
    // SIMPLE and CLEAN initialization - NO button freezing
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing ScoreSheet System...');

        // Initialize ScoreSheet if available
        if (typeof ScoreSheet !== 'undefined') {
            window.scoresheet = new ScoreSheet();

            // Override the startScoreEntry method to ensure points system works
            if (window.scoresheet.startScoreEntry) {
                const originalStartScoreEntry = window.scoresheet.startScoreEntry;
                window.scoresheet.startScoreEntry = function(game) {
                    originalStartScoreEntry.call(this, game);
                    setupPlacementOptions(game);
                };
            }

            // Override addGame method to prevent duplicates
            if (window.scoresheet.addGame) {
                const originalAddGame = window.scoresheet.addGame;
                window.scoresheet.addGame = async function(gameData) {
                    // Check for duplicate game name
                    const existingGames = await this.fetchGames();
                    const duplicateGame = existingGames.find(game =>
                        game.name.toLowerCase() === gameData.name.toLowerCase()
                    );

                    if (duplicateGame) {
                        document.getElementById('gameNameError').style.display = 'block';
                        document.getElementById('gameNameError').textContent = `Error: A game named "${gameData.name}" already exists!`;
                        return false;
                    }

                    // Clear any previous error
                    document.getElementById('gameNameError').style.display = 'none';

                    // Proceed with original addGame
                    return originalAddGame.call(this, gameData);
                };
            }

            // Override renderTeams to show simplified team cards
            if (window.scoresheet.renderTeams) {
                const originalRenderTeams = window.scoresheet.renderTeams;
                window.scoresheet.renderTeams = function(teams) {
                    // Call original method first
                    originalRenderTeams.call(this, teams);

                    // Then simplify the team cards
                    simplifyTeamCards();
                };
            }
        }

        setupEventListeners();
        loadJudgeName();
        refreshJudgeHistory();
        setupTeamColorSelection();
    });

    // Setup all event listeners
    function setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');

                if (tabId === 'judge') {
                    refreshJudgeHistory();
                    if (window.scoresheet && window.scoresheet.renderJudgeCategoryCriteriaBreakdown) {
                        window.scoresheet.renderJudgeCategoryCriteriaBreakdown();
                    }
                } else if (tabId === 'teams') {
                    // Simplify team cards when teams tab is opened
                    setTimeout(simplifyTeamCards, 100);
                }
            });
        });

        // Refresh buttons
        document.getElementById('refreshJudgeHistoryBtn')?.addEventListener('click', refreshJudgeHistory);
        document.getElementById('overallTypeFilter')?.addEventListener('change', function() {
            if (window.scoresheet && window.scoresheet.renderOverallRankings) {
                window.scoresheet.renderOverallRankings();
            }
        });

        document.getElementById('printJudgeCategoryBreakdownBtn')?.addEventListener('click', function() {
            const contentEl = document.getElementById('judgeCategoryBreakdownContent');
            if (!contentEl) return;
            const printWindow = window.open('', '_blank');
            if (!printWindow) return;

            printWindow.document.write('<html><head><title>Beauty Category Breakdown</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">');
            printWindow.document.write('<style>body{font-family:system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", sans-serif; padding:20px;} table{width:100%; border-collapse:collapse;} th,td{border:1px solid #e5e7eb; padding:6px 8px; font-size:0.8rem;} h3,h4{margin:0 0 6px 0;} .judge-print-block{page-break-after:always;} .judge-print-block:last-child{page-break-after:auto;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(contentEl.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        });

        // Export to Excel function
        document.getElementById('exportJudgeCategoryBreakdownBtn')?.addEventListener('click', function() {
            exportJudgeCategoryBreakdownToExcel();
        });

        function exportJudgeCategoryBreakdownToExcel() {
            if (!window.scoresheet) {
                alert('ScoreSheet system not initialized. Please wait for data to load.');
                return;
            }

            // Get the raw data from ScoreSheet instance
            const games = window.scoresheet.games || [];
            const teams = window.scoresheet.teams || [];
            const judgeScores = window.scoresheet.judgeScores || [];

            // Filter Beauty Pageant judge events
            const beautyGames = games.filter(game => {
                if ((game.category || 'scorer') !== 'judge') return false;
                let ps = game.points_system;
                if (!ps) return false;
                if (typeof ps === 'string') {
                    try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
                }
                return ps.type === 'beauty_pageant' && ps.categories && ps.categories.length > 0;
            });

            if (beautyGames.length === 0) {
                alert('No Beauty Pageant events found to export.');
                return;
            }

            // Build Excel data structure
            let csvRows = [];
            
            beautyGames.forEach(game => {
                let ps = game.points_system;
                if (typeof ps === 'string') {
                    try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
                }
                const categories = (ps && ps.categories) ? ps.categories : [];
                if (!categories.length) return;

                // Game header
                csvRows.push([game.name]);
                csvRows.push(['Beauty Pageant – Category Criteria Breakdown (Raw)']);
                csvRows.push([]); // Empty row

                // Get judge scores for this game
                const gameJudgeScores = judgeScores.filter(js => {
                    const jsGameId = js.game_id || js.gameId;
                    return jsGameId == game.id;
                });

                // Get judge names
                const judgeNames = new Set();
                gameJudgeScores.forEach(score => {
                    if (score.judge_name) judgeNames.add(score.judge_name);
                });
                if (judgeNames.size > 0) {
                    csvRows.push(['Assigned Judges:', Array.from(judgeNames).join(', ')]);
                    csvRows.push([]);
                }

                // Process each category
                categories.forEach((category, catIdx) => {
                    const categoryName = category.name || ('Category ' + (catIdx + 1));
                    const categoryPercentage = category.categoryPercentage || 0;

                    csvRows.push(['Category:', categoryName]);
                    csvRows.push(['Score Points:', categoryPercentage.toFixed(0)]);
                    csvRows.push([]);

                    // Get teams with scores for this category
                    const scoresByTeam = {};
                    gameJudgeScores.forEach(score => {
                        const teamId = score.team_id || score.teamId || score.teamId_alt;
                        if (!teamId) return;
                        if (!scoresByTeam[teamId]) {
                            scoresByTeam[teamId] = [];
                        }
                        scoresByTeam[teamId].push(score);
                    });

                    // Process each team
                    Object.keys(scoresByTeam).forEach(teamId => {
                        const team = teams.find(t => t.id == teamId);
                        if (!team) return;

                        // Get criteria details for this team and category
                        const criteriaDetails = window.scoresheet.getBeautyCategoryCriteriaDetailsForTeam(
                            teamId,
                            category,
                            catIdx,
                            categories,
                            gameJudgeScores
                        );

                        if (!criteriaDetails || criteriaDetails.length === 0) return;

                        csvRows.push(['Team:', team.name]);
                        csvRows.push(['Team Code:', team.code || 'N/A']);
                        csvRows.push([]);

                        // Table header
                        csvRows.push(['Criteria', 'Score Points', 'Judge', 'Role', 'Score']);

                        // Extract raw scores from judge scores data
                        const teamScores = gameJudgeScores.filter(s => {
                            const sTeamId = s.team_id || s.teamId || s.teamId_alt;
                            return sTeamId == teamId;
                        });

                        // Calculate global criteria index
                        let globalCriteriaIndex = 1;
                        for (let i = 0; i < catIdx; i++) {
                            globalCriteriaIndex += (categories[i].criteria || []).length;
                        }

                        // Process each criteria
                        criteriaDetails.forEach((detail, critIdx) => {
                            const criteriaNum = globalCriteriaIndex + critIdx;
                            
                            // Collect all scores for this criteria first
                            const criteriaScores = [];
                            
                            teamScores.forEach(score => {
                                const judgeName = score.judge_name || score.judgeName || 'Unknown';
                                const participantType = score.participant_type || 'single';
                                
                                // Try to get score from details_json first (beauty pageant format)
                                let criteriaScore = null;
                                if (score.details_json && score.details_json.type === 'beauty_pageant') {
                                    const catDetail = (score.details_json.categories || []).find(c => c.index === catIdx);
                                    if (catDetail && catDetail.criteria && catDetail.criteria[critIdx]) {
                                        criteriaScore = catDetail.criteria[critIdx].score || 0;
                                    }
                                } else {
                                    // Fallback to criteria1, criteria2, etc.
                                    criteriaScore = parseInt(score[`criteria${criteriaNum}`] || 0);
                                }

                                if (criteriaScore !== null && criteriaScore !== undefined) {
                                    // Map participant type to role label
                                    const roleLabels = {
                                        'muse': 'Muse',
                                        'escort': 'Escort',
                                        'pair': 'Pair',
                                        'single': 'Single'
                                    };
                                    const role = roleLabels[participantType] || participantType;

                                    criteriaScores.push({
                                        judgeName: judgeName,
                                        role: role,
                                        score: criteriaScore,
                                        roleOrder: participantType === 'muse' ? 1 : 
                                                  participantType === 'escort' ? 2 : 
                                                  participantType === 'pair' ? 3 : 4
                                    });
                                }
                            });

                            // Sort by role (Muse first, then Escort, then Pair, then Single), then by judge name
                            criteriaScores.sort((a, b) => {
                                if (a.roleOrder !== b.roleOrder) {
                                    return a.roleOrder - b.roleOrder;
                                }
                                return a.judgeName.localeCompare(b.judgeName);
                            });

                            // Add sorted scores to csvRows
                            criteriaScores.forEach(item => {
                                csvRows.push([
                                    detail.name,
                                    detail.percentage.toFixed(0),
                                    item.judgeName,
                                    item.role,
                                    item.score
                                ]);
                            });
                        });

                        csvRows.push([]); // Empty row between teams
                    });

                    csvRows.push([]); // Empty row between categories
                });

                csvRows.push([]); // Empty row between games
            });

            // Convert to CSV string
            const csvContent = csvRows.map(row => {
                return row.map(cell => {
                    // Escape quotes and wrap in quotes if contains comma, quote, or newline
                    if (cell === null || cell === undefined) return '';
                    const cellStr = String(cell);
                    if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                        return '"' + cellStr.replace(/"/g, '""') + '"';
                    }
                    return cellStr;
                }).join(',');
            }).join('\n');

            // Add BOM for UTF-8 (Excel compatibility)
            const BOM = '\uFEFF';
            const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
            
            // Create download link
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'Beauty_Category_Breakdown_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Cancel score button
        document.getElementById('cancelScoreBtn')?.addEventListener('click', function() {
            if (window.scoresheet && window.scoresheet.cancelScoreEntry) {
                window.scoresheet.cancelScoreEntry();
            }
        });

        // Clear filter button
        document.getElementById('clearTeamFilterBtn')?.addEventListener('click', function() {
            if (window.scoresheet && window.scoresheet.clearTeamFilter) {
                window.scoresheet.clearTeamFilter();
            }
        });

        // Debug buttons
        document.getElementById('debugInfoBtn')?.addEventListener('click', function() {
            if (window.scoresheet && window.scoresheet.showDebugInfo) {
                window.scoresheet.showDebugInfo();
            }
        });

        document.getElementById('debugJudgeBtn')?.addEventListener('click', function() {
            console.log('Judge Scores in localStorage:', JSON.parse(localStorage.getItem('scoresheet-judge-scores') || '[]'));
            alert('Check console for judge scores data');
        });

        // Judge scoring
        document.getElementById('startJudgeBtn')?.addEventListener('click', startJudgeScoring);

        // Print buttons
        document.getElementById('printHistoryBtn')?.addEventListener('click', printHistory);

        // Point entry management
        document.getElementById('addPointEntryBtn')?.addEventListener('click', addPointEntry);

        // Remove point entry events
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-point-entry')) {
                removePointEntry(e.target);
            }
        });

        // Placement change event - FIXED: This will update points when placement is selected
        document.addEventListener('change', function(e) {
            if (e.target.id === 'placement') {
                updatePointsBasedOnPlacement();
            }
        });

        // Game name input - clear error when typing
        document.getElementById('gameName')?.addEventListener('input', function() {
            document.getElementById('gameNameError').style.display = 'none';
        });

        // Game category change - show/hide points system, judge event type, criteria system, and authorized judge
        document.getElementById('gameCategory')?.addEventListener('change', function() {
            togglePointsSystem(this.value);
            toggleJudgeEventType(this.value);
            toggleCriteriaSystem(this.value);
            toggleAuthorizedJudge(this.value);
            // Hide beauty pageant categories if category changes away from judge
            if (this.value !== 'judge') {
                toggleBeautyPageantCategories('');
            } else {
                // If switching to judge category, check event type and update visibility accordingly
                const eventType = document.getElementById('judgeEventType')?.value || '';
                toggleBeautyPageantCategories(eventType);
            }
        });

        // Judge event type change - show/hide beauty pageant categories
        document.getElementById('judgeEventType')?.addEventListener('change', function() {
            toggleBeautyPageantCategories(this.value);
        });

        // Initialize visibility
        const initialCategory = document.getElementById('gameCategory')?.value || 'scorer';
        togglePointsSystem(initialCategory);
        toggleJudgeEventType(initialCategory);
        toggleCriteriaSystem(initialCategory);
        toggleAuthorizedJudge(initialCategory);
        // Initialize Beauty Pageant categories visibility
        const initialEventType = document.getElementById('judgeEventType')?.value || '';
        toggleBeautyPageantCategories(initialEventType);

        // Criteria entry management
        document.getElementById('addCriteriaEntryBtn')?.addEventListener('click', addCriteriaEntry);

        // Remove criteria entry events
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-criteria-entry') || e.target.closest('.remove-criteria-entry')) {
                const btn = e.target.classList.contains('remove-criteria-entry') ? e.target : e.target.closest('.remove-criteria-entry');
                removeCriteriaEntry(btn);
            }
        });

        // Calculate total percentage when criteria percentages change
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('criteria-percentage-input')) {
                calculateTotalPercentage();
            }
        });

        // Judge entry management
        document.getElementById('addJudgeBtn')?.addEventListener('click', addJudgeEntry);

        // Remove judge entry events
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-judge-entry') || e.target.closest('.remove-judge-entry')) {
                const btn = e.target.classList.contains('remove-judge-entry') ? e.target : e.target.closest('.remove-judge-entry');
                removeJudgeEntry(btn);
            }
        });

        // Beauty Pageant category management
        document.getElementById('addBeautyCategoryBtn')?.addEventListener('click', addBeautyCategory);
        
        // Calculate total category percentage when category percentage changes
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('beauty-category-percentage-input')) {
                calculateTotalCategoryPercentage();
            }
        });

        // Remove beauty category events
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-beauty-category') || e.target.closest('.remove-beauty-category')) {
                const btn = e.target.classList.contains('remove-beauty-category') ? e.target : e.target.closest('.remove-beauty-category');
                removeBeautyCategory(btn);
            }
            // Remove beauty criteria events
            if (e.target.classList.contains('remove-beauty-criteria') || e.target.closest('.remove-beauty-criteria')) {
                const btn = e.target.classList.contains('remove-beauty-criteria') ? e.target : e.target.closest('.remove-beauty-criteria');
                removeBeautyCriteria(btn);
            }
            // Add beauty criteria events
            if (e.target.classList.contains('add-beauty-criteria') || e.target.closest('.add-beauty-criteria')) {
                const btn = e.target.classList.contains('add-beauty-criteria') ? e.target : e.target.closest('.add-beauty-criteria');
                const categoryId = btn.getAttribute('data-category-id');
                addBeautyCriteria(categoryId);
            }
        });

        // Calculate total percentage when beauty criteria percentages change
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('beauty-criteria-percentage-input')) {
                const categoryId = e.target.closest('.beauty-category').getAttribute('data-category-id');
                calculateBeautyCategoryTotal(categoryId);
            }
            // Calculate total category percentage when category percentage changes
            if (e.target.classList.contains('beauty-category-percentage-input')) {
                calculateTotalCategoryPercentage();
            }
        });

        // Handle scoring type change for beauty categories
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('beauty-category-scoring-type')) {
                const categoryDiv = e.target.closest('.beauty-category');
                const selectedType = e.target.value;
                const hints = categoryDiv.querySelectorAll('.scoring-type-hint-single, .scoring-type-hint-muse_escort, .scoring-type-hint-pair');
                
                hints.forEach(hint => {
                    hint.style.display = 'none';
                });
                
                if (selectedType === 'single') {
                    categoryDiv.querySelector('.scoring-type-hint-single').style.display = 'inline';
                } else if (selectedType === 'muse_escort') {
                    categoryDiv.querySelector('.scoring-type-hint-muse_escort').style.display = 'inline';
                } else if (selectedType === 'pair') {
                    categoryDiv.querySelector('.scoring-type-hint-pair').style.display = 'inline';
                }
            }
        });
    }

    // Function to toggle points system visibility based on category
    function togglePointsSystem(category) {
        const pointsSystemContainer = document.getElementById('pointsSystemContainer');
        if (pointsSystemContainer) {
            if (category === 'scorer') {
                pointsSystemContainer.style.display = 'block';
            } else {
                pointsSystemContainer.style.display = 'none';
            }
        }
    }

    // Function to toggle judge event type visibility based on category
    function toggleJudgeEventType(category) {
        const judgeEventTypeContainer = document.getElementById('judgeEventTypeContainer');
        if (judgeEventTypeContainer) {
            if (category === 'judge') {
                judgeEventTypeContainer.style.display = 'block';
            } else {
                judgeEventTypeContainer.style.display = 'none';
                // Clear the value when hidden
                document.getElementById('judgeEventType').value = '';
            }
        }
    }

    // Function to toggle criteria system visibility based on category
    function toggleCriteriaSystem(category) {
        const criteriaSystemContainer = document.getElementById('criteriaSystemContainer');
        if (criteriaSystemContainer) {
            if (category === 'judge') {
                // Check if event type is Beauty Pageant - if so, hide this section
                const eventType = document.getElementById('judgeEventType')?.value || '';
                if (eventType === 'Beauty') {
                    criteriaSystemContainer.style.display = 'none';
                } else {
                    criteriaSystemContainer.style.display = 'block';
                }
            } else {
                criteriaSystemContainer.style.display = 'none';
            }
        }
    }

    // Function to toggle authorized judge field visibility based on category
    function toggleAuthorizedJudge(category) {
        const authorizedJudgeContainer = document.getElementById('authorizedJudgeContainer');
        if (authorizedJudgeContainer) {
            if (category === 'judge') {
                authorizedJudgeContainer.style.display = 'block';
            } else {
                authorizedJudgeContainer.style.display = 'none';
                // Reset judges list when hidden
                resetJudgesList();
            }
        }
    }

    // Function to toggle Beauty Pageant categories visibility based on event type
    function toggleBeautyPageantCategories(eventType) {
        const beautyCategoriesContainer = document.getElementById('beautyPageantCategoriesContainer');
        const criteriaSystemContainer = document.getElementById('criteriaSystemContainer');
        
        if (beautyCategoriesContainer) {
            if (eventType === 'Beauty') {
                beautyCategoriesContainer.style.display = 'block';
                // Hide Custom Criteria & Percentages section for Beauty Pageant
                if (criteriaSystemContainer) {
                    criteriaSystemContainer.style.display = 'none';
                }
                // Initialize with one category if empty
                if (document.getElementById('beautyCategoriesList').children.length === 0) {
                    addBeautyCategory();
                }
            } else {
                beautyCategoriesContainer.style.display = 'none';
                // Clear categories when hidden
                document.getElementById('beautyCategoriesList').innerHTML = '';
                // Show Custom Criteria & Percentages section for other event types (if category is judge)
                const category = document.getElementById('gameCategory')?.value;
                if (criteriaSystemContainer && category === 'judge') {
                    criteriaSystemContainer.style.display = 'block';
                }
            }
        }
    }

    // Add judge entry
    function addJudgeEntry() {
        const judgesList = document.getElementById('judgesList');
        if (!judgesList) return;

        const existingEntries = judgesList.querySelectorAll('.judge-entry');
        const judgeIndex = existingEntries.length;

        const newEntry = document.createElement('div');
        newEntry.className = 'judge-entry';
        newEntry.style.cssText = 'display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px;';
        newEntry.innerHTML = `
                <input type="text" class="judge-name-input" placeholder="Enter judge name (e.g., John Smith)" data-judge-index="${judgeIndex}">
                <button type="button" class="btn btn-danger btn-sm remove-judge-entry">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        judgesList.appendChild(newEntry);

        // Show remove buttons if more than one entry
        updateJudgeRemoveButtons();
    }

    // Remove judge entry
    function removeJudgeEntry(button) {
        const judgeEntry = button.closest('.judge-entry');
        const judgesList = document.getElementById('judgesList');
        const allEntries = judgesList.querySelectorAll('.judge-entry');

        if (allEntries.length > 1) {
            judgeEntry.remove();
            // Re-index remaining entries
            reindexJudgeEntries();
            updateJudgeRemoveButtons();
        } else {
            alert('At least one judge entry is required. You can leave it empty if you want to allow any judge.');
        }
    }

    // Re-index judge entries
    function reindexJudgeEntries() {
        const judgesList = document.getElementById('judgesList');
        const entries = judgesList.querySelectorAll('.judge-entry');
        entries.forEach((entry, index) => {
            const input = entry.querySelector('.judge-name-input');
            if (input) {
                input.setAttribute('data-judge-index', index);
            }
        });
    }

    // Update remove buttons visibility
    function updateJudgeRemoveButtons() {
        const judgesList = document.getElementById('judgesList');
        const entries = judgesList.querySelectorAll('.judge-entry');

        entries.forEach(entry => {
            const removeBtn = entry.querySelector('.remove-judge-entry');
            if (removeBtn) {
                removeBtn.style.display = entries.length > 1 ? 'block' : 'none';
            }
        });
    }

    // Reset judges list to default
    function resetJudgesList() {
        const judgesList = document.getElementById('judgesList');
        if (judgesList) {
            judgesList.innerHTML = `
                    <div class="judge-entry" style="display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px;">
                        <input type="text" class="judge-name-input" placeholder="Enter judge name (e.g., John Smith)" data-judge-index="0">
                        <button type="button" class="btn btn-danger btn-sm remove-judge-entry" style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
        }
    }

    // Add criteria entry
    function addCriteriaEntry() {
        const criteriaGrid = document.getElementById('criteriaGrid');
        const newEntry = document.createElement('div');
        newEntry.className = 'point-entry';
        newEntry.innerHTML = `
                <input type="text" placeholder="Criteria Name" class="criteria-name-input" value="">
                <input type="number" placeholder="Percentage" class="criteria-percentage-input" min="0" max="100" value="0" step="1">
                <span class="percentage-label">%</span>
                <button type="button" class="btn btn-danger btn-sm remove-criteria-entry">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        criteriaGrid.appendChild(newEntry);
        calculateTotalPercentage();
    }

    // Remove criteria entry
    function removeCriteriaEntry(button) {
        const criteriaEntry = button.closest('.point-entry');
        if (document.querySelectorAll('#criteriaGrid .point-entry').length > 1) {
            criteriaEntry.remove();
            calculateTotalPercentage();
        } else {
            alert('At least one criteria is required!');
        }
    }

    // Calculate total percentage
    function calculateTotalPercentage() {
        const percentageInputs = document.querySelectorAll('.criteria-percentage-input');
        let total = 0;
        percentageInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalPercentageSpan = document.getElementById('totalPercentage');
        const percentageError = document.getElementById('percentageError');

        if (totalPercentageSpan) {
            totalPercentageSpan.textContent = total;

            if (total === 100) {
                totalPercentageSpan.style.color = '#22c55e';
                if (percentageError) percentageError.style.display = 'none';
            } else {
                totalPercentageSpan.style.color = '#ef4444';
                if (percentageError) percentageError.style.display = 'block';
            }
        }
    }

    // NEW: Function to simplify team cards - remove stats, show only name, code, and buttons
    function simplifyTeamCards() {
        const teamsGrid = document.getElementById('teamsGrid');
        const teamCards = teamsGrid.querySelectorAll('.team-card');

        teamCards.forEach(card => {
            // Remove any existing stats sections
            const statsSections = card.querySelectorAll('.team-stats, .team-scores-details, .team-stat, .team-stat-value');
            statsSections.forEach(section => section.remove());

            // Find or create actions container
            let actionsContainer = card.querySelector('.team-actions');
            if (!actionsContainer) {
                actionsContainer = document.createElement('div');
                actionsContainer.className = 'team-actions';
                actionsContainer.style.marginTop = '15px';
                actionsContainer.style.display = 'flex';
                actionsContainer.style.gap = '8px';
                actionsContainer.style.justifyContent = 'center';
                card.appendChild(actionsContainer);
            }

            // Clear existing actions
            actionsContainer.innerHTML = '';

            // Add View Details button
            const viewDetailsBtn = document.createElement('button');
            viewDetailsBtn.type = 'button';
            viewDetailsBtn.className = 'btn btn-primary btn-sm';
            viewDetailsBtn.innerHTML = '<i class="fas fa-eye"></i> View Details';
            viewDetailsBtn.onclick = function() {
                const teamId = card.getAttribute('data-team-id');
                const teamName = card.querySelector('.team-name')?.textContent;
                if (teamId && teamName) {
                    showTeamDetails(teamId, teamName);
                }
            };

            // Add Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-danger btn-sm';
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
            deleteBtn.onclick = function() {
                const teamId = card.getAttribute('data-team-id');
                const teamName = card.querySelector('.team-name')?.textContent;
                if (teamId && teamName) {
                    deleteTeam(teamId, teamName);
                }
            };

            actionsContainer.appendChild(viewDetailsBtn);
            actionsContainer.appendChild(deleteBtn);
        });
    }

    // NEW: Function to show team details
    function showTeamDetails(teamId, teamName) {
        // You can implement a modal or redirect to team details page
        alert(`Viewing details for: ${teamName}\nTeam ID: ${teamId}\n\nThis would show detailed team statistics, scores, and performance history.`);

        // Alternative: Redirect to team details page
        // window.location.href = `team_details.php?team_id=${teamId}`;
    }

    // Function to delete team - Fixed to properly work with ScoreSheet class
    function deleteTeam(teamId, teamName) {
        // Always use confirm first
        if (!confirm(`Are you sure you want to delete team "${teamName}"?\n\nThis action cannot be undone!`)) {
            return;
        }

        // Try to use ScoreSheet class method first
        if (window.scoresheet && typeof window.scoresheet.deleteTeam === 'function') {
            window.scoresheet.deleteTeam(parseInt(teamId));
        } else {
            // Fallback: direct API call
            fetch('database_handler.php?action=teams', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: parseInt(teamId)
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        alert(`Team "${teamName}" has been deleted successfully!`);
                        // Refresh teams list and all data
                        if (window.scoresheet && window.scoresheet.loadAllData) {
                            window.scoresheet.loadAllData().then(() => {
                                // Simplify team cards after refresh
                                setTimeout(simplifyTeamCards, 200);
                            });
                        } else {
                            // Reload page if ScoreSheet not available
                            window.location.reload();
                        }
                    } else {
                        alert(`Error deleting team: ${result.message || 'Unknown error'}`);
                    }
                })
                .catch(error => {
                    console.error('Error deleting team:', error);
                    alert('Error deleting team: ' + error.message + '\nPlease check console for details.');
                });
        }
    }

    // FIXED: Function to setup placement options based on game's points system
    function setupPlacementOptions(game) {
        const placementSelect = document.getElementById('placement');
        const pointsInput = document.getElementById('points');

        // Clear existing options
        placementSelect.innerHTML = '<option value="">Select Placement</option>';
        pointsInput.value = '';

        if (!game || !game.points_system) {
            console.error('No points system found for game:', game);
            return;
        }

        // Parse points system
        let pointsSystem;
        try {
            pointsSystem = typeof game.points_system === 'string' ?
                JSON.parse(game.points_system) :
                game.points_system;
        } catch (error) {
            console.error('Error parsing points system:', error);
            return;
        }

        // Add placement options based on points system
        Object.keys(pointsSystem).forEach(placement => {
            const option = document.createElement('option');
            option.value = placement;
            option.textContent = placement;
            placementSelect.appendChild(option);
        });

        console.log('Placement options setup for game:', game.name, 'Points system:', pointsSystem);
    }

    // FIXED: Function to update points based on selected placement
    function updatePointsBasedOnPlacement() {
        const placementSelect = document.getElementById('placement');
        const pointsInput = document.getElementById('points');
        const selectedGameId = document.getElementById('selectedGameId').value;

        if (!placementSelect.value || !selectedGameId) {
            pointsInput.value = '';
            return;
        }

        const points = window.scoresheet && typeof window.scoresheet.getPointsForPlacement === 'function' ?
            window.scoresheet.getPointsForPlacement(selectedGameId, placementSelect.value) :
            null;

        if (points !== null && !isNaN(points)) {
            pointsInput.value = points;
            console.log(`Points set to ${points} for placement ${placementSelect.value}`);
        } else {
            pointsInput.value = '';
            console.warn(`No points defined for placement: ${placementSelect.value}`);
        }
    }

    // Point entry management
    function addPointEntry() {
        const pointsGrid = document.getElementById('pointsGrid');
        const newEntry = document.createElement('div');
        newEntry.className = 'point-entry';
        newEntry.innerHTML = `
                <input type="text" placeholder="Placement (e.g., 1st)" class="placement-input">
                <input type="number" placeholder="Points" class="points-input" min="0">
                <button type="button" class="btn btn-danger btn-sm remove-point-entry">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        pointsGrid.appendChild(newEntry);
    }

    function removePointEntry(button) {
        const pointEntry = button.closest('.point-entry');
        if (document.querySelectorAll('.point-entry').length > 1) {
            pointEntry.remove();
        }
    }

    // Print functions
    function printHistory() {
        window.print();
    }

    // Judge functions
    function startJudgeScoring() {
        // Redirect to judge entry page for validation
        window.location.href = 'judge_entry.php';
    }

    // Load judge scores from database - ENHANCED: Now shows detailed information including game, participant type, and all criteria
    async function refreshJudgeHistory() {
        try {
            const response = await fetch('database_handler.php?action=judge_scores');
            const result = await response.json();

            const table = document.getElementById('judgeHistoryTable');

            if (!result.success || !result.data || result.data.length === 0) {
                table.innerHTML = `
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fas fa-inbox"></i> No judge scores recorded yet
                            </td>
                        </tr>
                    `;
                return;
            }

            const judgeScores = result.data;
            let teams = [];
            let games = [];

            if (window.scoresheet) {
                if (window.scoresheet.teams) {
                teams = window.scoresheet.teams;
                }
                if (window.scoresheet.games) {
                    games = window.scoresheet.games;
                }
            }

            // If games not loaded yet, fetch them
            if (games.length === 0) {
                try {
                    const gamesResponse = await fetch('database_handler.php?action=games');
                    const gamesResult = await gamesResponse.json();
                    if (gamesResult.success && gamesResult.data) {
                        games = gamesResult.data;
                    }
                } catch (e) {
                    console.warn('Could not fetch games:', e);
                }
            }

            const sorted = [...judgeScores].sort((a, b) => {
                // Sort by timestamp (newest first), then by game name, then by team name
                const dateCompare = new Date(b.timestamp) - new Date(a.timestamp);
                if (dateCompare !== 0) return dateCompare;
                
                const gameA = games.find(g => g.id === a.game_id);
                const gameB = games.find(g => g.id === b.game_id);
                const gameCompare = (gameA?.name || '').localeCompare(gameB?.name || '');
                if (gameCompare !== 0) return gameCompare;
                
                const teamA = teams.find(t => t.id === a.team_id);
                const teamB = teams.find(t => t.id === b.team_id);
                return (teamA?.name || '').localeCompare(teamB?.name || '');
            });

            table.innerHTML = sorted.map(score => {
                const team = teams.find(t => t.id === score.team_id);
                const game = games.find(g => g.id === score.game_id);
                const teamColor = team ? team.color : '#2563eb';
                
                // Get game name or show placeholder
                const gameName = game ? game.name : (score.game_name || (score.game_id ? `Event #${score.game_id}` : 'No Event'));
                
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

                // Color coding for total score
                let scoreColor = '#ef4444'; // red for poor
                if (score.total_score >= 90) scoreColor = '#22c55e'; // green for excellent
                else if (score.total_score >= 70) scoreColor = '#fbbf24'; // yellow for good
                else if (score.total_score >= 50) scoreColor = '#f97316'; // orange for fair

                // Format date
                const dateObj = new Date(score.timestamp);
                const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();

                return `
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px;">
                                <strong style="color: #1e293b;">${escapeHtml(score.judge_name || 'Unknown Judge')}</strong>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 600; color: #475569;">
                                    ${escapeHtml(gameName)}
                                </div>
                                ${game && game.judge_event_type ? `<small style="color: #94a3b8; font-size: 0.85rem;">${escapeHtml(game.judge_event_type)}</small>` : ''}
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 14px; height: 14px; border-radius: 50%; background-color: ${teamColor}; border: 2px solid ${teamColor}; box-shadow: 0 0 0 2px rgba(0,0,0,0.1);"></div>
                                    <div style="font-weight: 500;">${escapeHtml(team ? team.name : 'Unknown Team')}</div>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #475569; font-size: 0.95rem;">${escapeHtml(team ? team.code : 'N/A')}</strong>
                            </td>
                            <td style="padding: 12px;">
                                <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; ${participantBadge}">
                                    ${escapeHtml(participantType)}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: #1e293b; font-size: 1rem;">${score.criteria1 || 0}</strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: #1e293b; font-size: 1rem;">${score.criteria2 || 0}</strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: #1e293b; font-size: 1rem;">${score.criteria3 || 0}</strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: #1e293b; font-size: 1rem;">${score.criteria4 || 0}</strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: #1e293b; font-size: 1rem;">${score.criteria5 || 0}</strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: ${scoreColor}; font-size: 1.2em; font-weight: 700; padding: 6px 12px; background: ${scoreColor}15; border-radius: 8px; display: inline-block; min-width: 50px;">
                                    ${score.total_score || 0}
                                </strong>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-size: 0.9rem; color: #64748b;">
                                    ${formattedDate}
                                </div>
                            </td>
                        </tr>
                    `;
            }).join('');

            const judgeAverageRows = buildJudgeAverageRows(judgeScores);
            renderJudgeFinalResults(judgeAverageRows);
            renderJudgeAverageLeaderboard(judgeAverageRows);

        } catch (error) {
            console.error('Error loading judge scores:', error);
            const table = document.getElementById('judgeHistoryTable');
            table.innerHTML = `
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 40px; color: var(--error-red);">
                            <i class="fas fa-exclamation-triangle"></i> Error loading judge scores: ${error.message}
                        </td>
                    </tr>
                `;
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function buildJudgeAverageRows(judgeScores) {
        if (!judgeScores || judgeScores.length === 0) {
            return [];
        }

        const teams = (window.scoresheet && window.scoresheet.teams) ? window.scoresheet.teams : [];
        const games = (window.scoresheet && window.scoresheet.games) ? window.scoresheet.games : [];

        // Group scores by game first to handle rank-based scoring
        const scoresByGame = {};
        judgeScores.forEach(score => {
            const gameId = score.game_id || 'no_game';
            if (!scoresByGame[gameId]) {
                scoresByGame[gameId] = [];
            }
            scoresByGame[gameId].push(score);
        });

        const results = [];

        // Process each game separately
        Object.keys(scoresByGame).forEach(gameId => {
            const gameScores = scoresByGame[gameId];
            const game = games.find(g => g.id == gameId);
            const scoringFormula = game?.scoring_formula || 'legacy';

            if (scoringFormula === 'beauty_pageant_formula' && window.scoresheet) {
                // Use rank-based calculation for Beauty Pageant
                const rankBasedScores = window.scoresheet.calculateRankBasedScoresForGame(parseInt(gameId), gameScores);
                
                // Create result entries for each team
                Object.keys(rankBasedScores).forEach(teamId => {
                    const team = teams.find(t => t.id == teamId);
                    const finalScore = rankBasedScores[teamId];
                    
                    // Count judges for this team
                    const teamScores = gameScores.filter(s => (s.team_id || s.teamId || s.teamId_alt) == teamId);
                    const judgeCount = new Set(teamScores.map(s => (s.judge_name || s.judgeName).toLowerCase())).size || 1;
                    
                    results.push({
                        gameName: game ? game.name : (gameId !== 'no_game' ? `Game #${gameId}` : 'Unassigned Game'),
                        teamName: team ? team.name : 'Unknown Team',
                        teamCode: team ? (team.code || 'N/A') : 'N/A',
                        judgeCount,
                        finalScore: finalScore
                    });
                });
            } else {
                // Use traditional averaging for other formulas
                const grouped = {};
                gameScores.forEach(score => {
            const teamKey = score.team_id || 'no_team';
                    const key = `${gameId}-${teamKey}`;

            if (!grouped[key]) {
                grouped[key] = {
                    team_id: score.team_id,
                    game_id: score.game_id,
                    total: 0,
                    judges: new Set()
                };
            }

            grouped[key].total += parseFloat(score.total_score) || 0;
            if (score.judge_name) {
                grouped[key].judges.add((score.judge_name || '').toLowerCase());
            }
        });

                Object.values(grouped).forEach(item => {
            const judgeCount = item.judges.size || 1;
            const finalScore = judgeCount > 0 ? (item.total / judgeCount) : 0;
            const team = teams.find(t => t.id === item.team_id);
            const game = games.find(g => g.id === item.game_id);

                    results.push({
                gameName: game ? game.name : (item.game_id ? `Game #${item.game_id}` : 'Unassigned Game'),
                teamName: team ? team.name : 'Unknown Team',
                teamCode: team ? (team.code || 'N/A') : 'N/A',
                judgeCount,
                finalScore: Math.round(finalScore * 100) / 100
                    });
                });
            }
        });

        return results.sort((a, b) => b.finalScore - a.finalScore);
    }

    function renderJudgeFinalResults(judgeAverageRows) {
        const finalTable = document.getElementById('judgeFinalResultsTable');
        if (!finalTable) return;

        if (!judgeAverageRows || judgeAverageRows.length === 0) {
            finalTable.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            <i class="fas fa-info-circle"></i> No judge scores submitted yet
                        </td>
                    </tr>
                `;
            return;
        }

        finalTable.innerHTML = judgeAverageRows.map(row => `
                <tr>
                    <td><strong>${row.gameName}</strong></td>
                    <td>${row.teamName}</td>
                    <td>${row.teamCode}</td>
                    <td>${row.judgeCount}</td>
                    <td><strong style="color: #2563eb; font-size: 1.05rem;">${row.finalScore}</strong></td>
                </tr>
            `).join('');
    }

    function renderJudgeAverageLeaderboard(judgeAverageRows) {
        const table = document.getElementById('judgeAverageTable');
        if (!table) return;

        if (!judgeAverageRows || judgeAverageRows.length === 0) {
            table.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            <i class="fas fa-info-circle"></i> No judge averages to display yet
                        </td>
                    </tr>
                `;
            return;
        }

        table.innerHTML = judgeAverageRows.map((row, index) => `
                <tr>
                    <td class="${index < 3 ? `rank-${index + 1}` : ''}">${index + 1}</td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <strong>${row.teamName}</strong>
                            <small style="color: var(--text-muted); font-size: 0.8rem;">${row.teamCode}</small>
                        </div>
                    </td>
                    <td>${row.gameName}</td>
                    <td>${row.judgeCount}</td>
                    <td><strong style="color: #2563eb; font-size: 1.1rem;">${row.finalScore}</strong></td>
                </tr>
            `).join('');
    }

    // Team color selection
    function setupTeamColorSelection() {
        const teamColorButtons = document.querySelectorAll('.team-color-swatch');
        teamColorButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                selectTeamColorSwatch(this);
            });
        });

        // Set default
        if (teamColorButtons.length > 1) {
            selectTeamColorSwatch(teamColorButtons[1]);
        }
    }

    function selectTeamColorSwatch(btn) {
        document.querySelectorAll('.team-color-swatch').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('teamColor').value = btn.getAttribute('data-color');
        document.getElementById('selectedTeamColorName').textContent = 'Selected: ' + btn.getAttribute('data-name');
    }

    function loadJudgeName() {
        const savedJudgeName = localStorage.getItem('current-judge-name');
        if (savedJudgeName) {
            document.getElementById('judgeName').value = savedJudgeName;
        }
    }

    // Auto-refresh when returning from judgescore.php
    window.addEventListener('focus', refreshJudgeHistory);

    // Beauty Pageant Category Management
    let beautyCategoryCounter = 0;

    function addBeautyCategory() {
        const categoriesList = document.getElementById('beautyCategoriesList');
        if (!categoriesList) return;

        const categoryId = `beauty-category-${beautyCategoryCounter++}`;
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'beauty-category';
        categoryDiv.setAttribute('data-category-id', categoryId);
        categoryDiv.style.cssText = 'margin-bottom: 25px; padding: 20px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 12px;';

        categoryDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #6b21a8;">
                        <i class="fas fa-tag"></i> Category Name:
                    </label>
                    <input type="text" class="beauty-category-name-input" placeholder="Enter category name (e.g., Evening Gown, Talent)" style="width: 100%; padding: 10px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>
                <div style="flex: 0 0 150px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #6b21a8;">
                        <i class="fas fa-percentage"></i> Category %
                    </label>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <input type="number" class="beauty-category-percentage-input" placeholder="%" min="0" max="100" value="0" step="1" style="width: 100%; padding: 10px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                    </div>
                </div>
                <div style="flex: 0 0 280px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #6b21a8;">
                        <i class="fas fa-users"></i> Scoring Type:
                    </label>
                    <select class="beauty-category-scoring-type" style="width: 100%; padding: 10px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem; background: white;">
                        <option value="single">Single Participant</option>
                        <option value="muse_escort">Muse & Escort (Separate)</option>
                        <option value="pair">Pair Scoring</option>
                    </select>
                    <small style="display: block; margin-top: 5px; color: #6b21a8; font-size: 0.85rem;">
                        <span class="scoring-type-hint-single">Score as a single participant</span>
                        <span class="scoring-type-hint-muse_escort" style="display: none;">Score female (Muse) and male (Escort) separately</span>
                        <span class="scoring-type-hint-pair" style="display: none;">Score the pair together as one unit</span>
                    </small>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-beauty-category" style="align-self: flex-start;">
                    <i class="fas fa-trash"></i> Remove Category
                </button>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <label style="font-weight: 600; color: #6b21a8;">
                        <i class="fas fa-list-check"></i> Criteria for this Category:
                    </label>
                    <button type="button" class="btn btn-secondary btn-sm add-beauty-criteria" data-category-id="${categoryId}">
                        <i class="fas fa-plus"></i> Add Criteria
                    </button>
                </div>
                <div class="beauty-criteria-list" data-category-id="${categoryId}" style="margin-bottom: 15px;">
                    <!-- Criteria will be added here -->
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f3e8ff; border-radius: 8px; border: 2px solid #8b5cf6; margin-bottom: 10px;">
                    <div>
                        <strong>Criteria Total: <span class="beauty-category-total" data-category-id="${categoryId}">0</span>% (must equal 100%)</strong>
                    </div>
                    <div class="beauty-category-error" data-category-id="${categoryId}" style="color: #ef4444; font-weight: 600; display: none;">
                        Criteria total must equal 100%!
                    </div>
                </div>
                <div style="padding: 10px; background: #dbeafe; border-radius: 8px; border: 2px solid #2563eb;">
                    <div style="font-size: 0.9rem; color: #1e40af;">
                        <strong>Category Points:</strong> <span class="beauty-category-points" data-category-id="${categoryId}">0</span> pts (if perfect score in all criteria)
                    </div>
                </div>
            </div>
        `;

        categoriesList.appendChild(categoryDiv);
        // Add initial criteria to the new category
        addBeautyCriteria(categoryId);
        // Calculate total category percentage
        calculateTotalCategoryPercentage();
    }

    function removeBeautyCategory(button) {
        const categoryDiv = button.closest('.beauty-category');
        const categoriesList = document.getElementById('beautyCategoriesList');
        const allCategories = categoriesList.querySelectorAll('.beauty-category');

        if (allCategories.length > 1) {
            categoryDiv.remove();
            // Recalculate total category percentage
            calculateTotalCategoryPercentage();
        } else {
            alert('At least one category is required for Beauty Pageant events!');
        }
    }

    function addBeautyCriteria(categoryId) {
        const criteriaList = document.querySelector(`.beauty-criteria-list[data-category-id="${categoryId}"]`);
        if (!criteriaList) return;

        const criteriaEntry = document.createElement('div');
        criteriaEntry.className = 'beauty-criteria-entry';
        criteriaEntry.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px; padding: 10px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;';

        criteriaEntry.innerHTML = `
            <input type="text" class="beauty-criteria-name-input" placeholder="Criteria Name" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <input type="number" class="beauty-criteria-percentage-input" placeholder="%" min="0" max="100" value="0" step="1" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; width: 80px;">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-beauty-criteria">
                <i class="fas fa-trash"></i>
            </button>
        `;

        criteriaList.appendChild(criteriaEntry);
        calculateBeautyCategoryTotal(categoryId);
    }

    function removeBeautyCriteria(button) {
        const criteriaEntry = button.closest('.beauty-criteria-entry');
        const criteriaList = criteriaEntry.closest('.beauty-criteria-list');
        const allCriteria = criteriaList.querySelectorAll('.beauty-criteria-entry');

        if (allCriteria.length > 1) {
            criteriaEntry.remove();
            const categoryId = criteriaList.getAttribute('data-category-id');
            calculateBeautyCategoryTotal(categoryId);
        } else {
            alert('At least one criteria is required per category!');
        }
    }

    function calculateBeautyCategoryTotal(categoryId) {
        const criteriaList = document.querySelector(`.beauty-criteria-list[data-category-id="${categoryId}"]`);
        if (!criteriaList) return;

        const percentageInputs = criteriaList.querySelectorAll('.beauty-criteria-percentage-input');
        let total = 0;
        percentageInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalSpan = document.querySelector(`.beauty-category-total[data-category-id="${categoryId}"]`);
        const errorDiv = document.querySelector(`.beauty-category-error[data-category-id="${categoryId}"]`);
        const categoryDiv = document.querySelector(`.beauty-category[data-category-id="${categoryId}"]`);
        const categoryPercentageInput = categoryDiv?.querySelector('.beauty-category-percentage-input');
        const categoryPercentage = parseFloat(categoryPercentageInput?.value || 0);
        const pointsSpan = document.querySelector(`.beauty-category-points[data-category-id="${categoryId}"]`);

        if (totalSpan) {
            totalSpan.textContent = total;

            if (total === 100) {
                totalSpan.style.color = '#22c55e';
                if (errorDiv) errorDiv.style.display = 'none';
            } else {
                totalSpan.style.color = '#ef4444';
                if (errorDiv) errorDiv.style.display = 'block';
            }
        }
        
        // Update category points display
        if (pointsSpan) {
            pointsSpan.textContent = categoryPercentage;
            if (total === 100 && categoryPercentage > 0) {
                pointsSpan.style.color = '#22c55e';
            } else {
                pointsSpan.style.color = '#64748b';
            }
        }
    }
    
    // Calculate total of all category percentages (should equal 100%)
    function calculateTotalCategoryPercentage() {
        const categoryInputs = document.querySelectorAll('.beauty-category-percentage-input');
        let total = 0;
        categoryInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        // Update display if there's a total category percentage element
        const totalDisplay = document.getElementById('totalCategoryPercentage');
        const totalError = document.getElementById('totalCategoryError');
        
        if (totalDisplay) {
            totalDisplay.textContent = total;
            if (Math.abs(total - 100) < 0.01) {
                totalDisplay.style.color = '#22c55e';
                if (totalError) totalError.style.display = 'none';
            } else {
                totalDisplay.style.color = '#ef4444';
                if (totalError) totalError.style.display = 'block';
            }
        }
        
        // Also update category points for each category
        document.querySelectorAll('.beauty-category').forEach(categoryDiv => {
            const categoryId = categoryDiv.getAttribute('data-category-id');
            calculateBeautyCategoryTotal(categoryId);
        });
    }
</script>
<?php include 'includes/footer.php'; ?>