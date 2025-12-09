// DTI Teambangan ng Bayan ScoreSheet JavaScript - PHP API Integration

class ScoreSheet {
    constructor() {
        this.teams = [];
        this.scores = [];
        this.games = [];
        this.judgeScores = [];
        this.teamRankings = [];
        this.selectedGame = null;
        this.selectedGameName = '';
        this.leaderboardFilterGameId = '';
        this.teamCodeFilter = '';
        
        this.init();
    }

    init() {
        console.log('Initializing ScoreSheet...');
        this.setupEventListeners();
        
        // Load all data and ensure it's displayed
        this.loadAllData();
    }
    
    async loadAllData() {
        try {
            console.log('Loading all data...');
            
            // Show loading state
            this.showLoadingState();
            
            // Load teams first
            await this.loadTeams();
            console.log('Teams loaded:', this.teams.length);
            
            // Load games
            await this.loadGames();
            console.log('Games loaded:', this.games.length);
            
            // Load scores
            await this.loadScores();
            console.log('Scores loaded:', this.scores.length);
            
            // Load judge scores from database (preferred) or localStorage as fallback
            await this.loadJudgeScores();
            
            console.log('All data loaded successfully!');
            console.log('Final state - Teams:', this.teams.length, 'Games:', this.games.length, 'Judge Scores:', this.judgeScores.length);
            
            // Update debug info
            this.updateDebugInfo();
            
            // Render all displays
            this.renderAllDisplays();
            
            // Hide loading state
            this.hideLoadingState();
            
        } catch (error) {
            console.error('Error loading data:', error);
            this.hideLoadingState();
        }
    }
    
    showLoadingState() {
        // Loading states are already in the HTML
    }
    
    hideLoadingState() {
        // Loading states will be replaced by actual data
    }
    
    renderAllDisplays() {
        console.log('Rendering all displays...');
        this.renderGameGrid();
        this.renderGames();
        this.renderTeams();
        this.renderScoresTable();
        this.updateLeaderboards();
        this.renderOverallRankings();
        this.renderHistory();
        console.log('All displays rendered!');
    }
    
    updateDebugInfo() {
        if (document.getElementById('teamsCount')) {
            document.getElementById('teamsCount').textContent = this.teams.length;
        }
        if (document.getElementById('gamesCount')) {
            document.getElementById('gamesCount').textContent = this.games.length;
        }
        if (document.getElementById('scoresCount')) {
            document.getElementById('scoresCount').textContent = this.scores.length;
        }
    }
    
    showDebugInfo() {
        const debugInfo = {
            teams: this.teams,
            games: this.games,
            scores: this.scores,
            judgeScores: this.judgeScores,
            teamRankings: this.teamRankings
        };
        console.log('Debug Info:', debugInfo);
        alert('Debug info logged to console. Press F12 to see details.');
    }

    // Test API functionality
    async testAPI() {
        try {
            const actions = ['teams', 'games', 'scores'];
            for (const action of actions) {
                try {
                    const result = await this.apiCall(`database_handler.php?action=${action}`);
                    console.log(`API ${action}:`, result);
                } catch (error) {
                    console.error(`API ${action} failed:`, error);
                }
            }
            alert('API test completed. Check console for results.');
        } catch (error) {
            console.error('API test failed:', error);
            alert('API test failed: ' + error.message);
        }
    }

    async testJudgeAPI() {
        try {
            const result = await this.apiCall('database_handler.php?action=judge_scores');
            console.log('Judge API:', result);
            alert('Judge API test completed. Check console for results.');
        } catch (error) {
            console.error('Judge API test failed:', error);
            alert('Judge API test failed. Using localStorage fallback.');
        }
    }

    setupEventListeners() {
        // Add score form
        const addScoreForm = document.getElementById('addScoreForm');
        if (addScoreForm && !addScoreForm.hasAttribute('data-listener-added')) {
            addScoreForm.setAttribute('data-listener-added', 'true');
            addScoreForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addScore();
            });
        }

        // Add team form
        const addTeamForm = document.getElementById('addTeamForm');
        if (addTeamForm && !addTeamForm.hasAttribute('data-listener-added')) {
            addTeamForm.setAttribute('data-listener-added', 'true');
            addTeamForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addTeam();
            });
        }

        // Add game form
        const addGameForm = document.getElementById('addGameForm');
        if (addGameForm && !addGameForm.hasAttribute('data-listener-added')) {
            addGameForm.setAttribute('data-listener-added', 'true');
            addGameForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addGame();
            });
        }

        // Placement change handler
        const placementSelect = document.getElementById('placement');
        if (placementSelect) {
            placementSelect.addEventListener('change', (e) => {
                this.updatePoints();
            });
        }

        // Team select change handler
        const teamSelect = document.getElementById('teamSelect');
        if (teamSelect) {
            teamSelect.addEventListener('change', (e) => {
                if (this.selectedGame) {
                    this.populatePlacementOptions(this.selectedGame);
                    document.getElementById('placement').value = '';
                    document.getElementById('points').value = '';
                }
            });
        }

        // Leaderboard game filter change handler
        const leaderboardFilterEl = document.getElementById('leaderboardGameFilter');
        if (leaderboardFilterEl) {
            leaderboardFilterEl.addEventListener('change', (e) => {
                const value = e.target.value;
                this.leaderboardFilterGameId = value ? parseInt(value) : '';
                this.updateLeaderboards();
            });
        }

        // Team code filter change handler
        const teamCodeFilterEl = document.getElementById('teamCodeFilter');
        if (teamCodeFilterEl) {
            teamCodeFilterEl.addEventListener('change', (e) => {
                const value = e.target.value;
                this.teamCodeFilter = value;
                this.renderTeams();
            });
        }
    }

    // API Helper Methods
    async apiCall(url, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            console.log('Making API call:', url, options);
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'API call failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            // Don't show alert for failed API calls to avoid blocking the UI
            throw error;
        }
    }

    // Load data from database
    async loadTeams() {
        try {
            console.log('Loading teams from database...');
            const result = await this.apiCall('database_handler.php?action=teams');
            console.log('Teams loaded:', result);
            this.teams = result.data || [];
            console.log('Teams array:', this.teams);
            this.populateTeamSelects();
            this.renderTeams();
            this.renderOverallRankings();
            this.updateDebugInfo();
        } catch (error) {
            console.error('Failed to load teams from API, using empty array:', error);
            this.teams = [];
            this.renderTeams();
            this.renderOverallRankings();
            this.updateDebugInfo();
        }
    }

    async loadGames() {
        try {
            console.log('Loading games from database...');
            const result = await this.apiCall('database_handler.php?action=games');
            console.log('Games loaded:', result);
            this.games = result.data || [];
            console.log('Games array:', this.games);
            this.updateDebugInfo();
        } catch (error) {
            console.error('Failed to load games from API, using empty array:', error);
            this.games = [];
            this.updateDebugInfo();
        }
    }

    async loadScores() {
        try {
            const result = await this.apiCall('database_handler.php?action=scores');
            this.scores = result.data || [];
            this.renderScoresTable();
            this.updateLeaderboards();
            this.renderHistory();
            this.renderOverallRankings();
            this.updateDebugInfo();
        } catch (error) {
            console.error('Failed to load scores from API, using empty array:', error);
            this.scores = [];
            this.renderScoresTable();
            this.updateLeaderboards();
            this.renderHistory();
            this.renderOverallRankings();
            this.updateDebugInfo();
        }
    }

    loadJudgeScoresFromStorage() {
        try {
            const storedScores = localStorage.getItem('scoresheet-judge-scores');
            this.judgeScores = storedScores ? JSON.parse(storedScores) : [];
            console.log('Judge scores loaded from localStorage:', this.judgeScores.length);
        } catch (error) {
            console.error('Failed to load judge scores from localStorage:', error);
            this.judgeScores = [];
        }
    }

    // Load judge scores from database
    async loadJudgeScores() {
        try {
            const result = await this.apiCall('database_handler.php?action=judge_scores');
            if (result.success && result.data) {
                // Convert database format to expected format
                this.judgeScores = result.data.map(score => {
                    let details = null;
                    if (score.details_json) {
                        try {
                            details = typeof score.details_json === 'string'
                                ? JSON.parse(score.details_json)
                                : score.details_json;
                        } catch (e) {
                            console.error('Failed to parse details_json for judge score', score.id, e);
                        }
                    }
                    return {
                    id: score.id,
                    teamId: score.team_id,
                    team_id: score.team_id, // Support both formats
                    teamId_alt: score.team_id, // Support both formats
                    gameId: score.game_id, // CRITICAL: Include game_id
                    game_id: score.game_id, // Support both formats
                    judgeName: score.judge_name,
                    judge_name: score.judge_name, // Support both formats
                    criteria1: score.criteria1,
                    criteria2: score.criteria2,
                    criteria3: score.criteria3,
                    criteria4: score.criteria4,
                    criteria5: score.criteria5,
                    totalScore: score.total_score,
                    total_score: score.total_score, // Support both formats
                    participant_type: score.participant_type || null, // CRITICAL: Include participant_type
                        details_json: details,
                    timestamp: score.timestamp,
                    team_name: score.team_name,
                    team_code: score.team_code,
                    team_color: score.team_color
                    };
                });
                console.log('Judge scores loaded from database:', this.judgeScores.length);
                console.log('Sample judge score:', this.judgeScores[0]);
            } else {
                console.log('No judge scores found or API error');
                this.judgeScores = [];
            }
        } catch (error) {
            console.error('Failed to load judge scores from database:', error);
            // Fallback to localStorage if database fails
            this.loadJudgeScoresFromStorage();
        }
    }

    // Team Management
    async addTeam() {
        const name = document.getElementById('teamName').value.trim();
        const code = document.getElementById('teamCode').value;
        const color = document.getElementById('teamColor').value;
        const members = document.getElementById('teamMembers').value.trim();
        const eventType = document.getElementById('teamEventType').value || null;

        if (!name || !code) {
            alert('Please enter team name and select team code');
            return;
        }

        try {
            const data = {
                name: name,
                code: code,
                color: color,
                members: members,
                event_type: eventType
            };

            console.log('Saving team to database:', data);
            const result = await this.apiCall('database_handler.php?action=teams', 'POST', data);
            console.log('Team save result:', result);
            this.clearTeamForm();
            await this.loadAllData(); // Reload all data to ensure everything is updated
            alert('Team added successfully!');
        } catch (error) {
            console.error('Failed to add team:', error);
            alert('Failed to add team: ' + error.message);
        }
    }

    async deleteTeam(teamId) {
        // Ensure teamId is an integer
        teamId = parseInt(teamId);
        
        const team = this.teams.find(t => t.id === teamId);
        if (!team) {
            console.error('Team not found:', teamId);
            alert('Team not found. Please refresh the page and try again.');
            return;
        }

        if (confirm(`Are you sure you want to delete team "${team.name}"?\n\nThis action cannot be undone and will also delete all associated scores!`)) {
            try {
                const result = await this.apiCall('database_handler.php?action=teams', 'DELETE', { id: teamId });
                if (result.success) {
                    alert(`Team "${team.name}" has been deleted successfully!`);
                    // Reload all data to refresh displays
                    await this.loadAllData();
                    // Refresh judge history if visible
                    if (typeof refreshJudgeHistory === 'function') {
                        refreshJudgeHistory();
                    }
                } else {
                    throw new Error(result.message || 'Delete operation failed');
                }
            } catch (error) {
                console.error('Failed to delete team:', error);
                alert('Failed to delete team: ' + (error.message || 'Unknown error occurred'));
            }
        }
    }

    // Game Management
    async addGame() {
        const name = document.getElementById('gameName').value.trim();
        const description = document.getElementById('gameDescription').value.trim();
        const category = document.getElementById('gameCategory').value;
        const judgeEventType = document.getElementById('judgeEventType')?.value.trim() || '';
        
        // Collect authorized judges (multiple)
        let authorizedJudges = [];
        if (category === 'judge') {
            const judgeInputs = document.querySelectorAll('.judge-name-input');
            judgeInputs.forEach(input => {
                const judgeName = input.value.trim();
                if (judgeName) {
                    authorizedJudges.push(judgeName);
                }
            });
        }

        if (!name) {
            alert('Please enter a game name');
            return;
        }

        if (!category) {
            alert('Please select a category (Sport or Event)');
            return;
        }

        // Event type is recommended for event category
        if (category === 'judge' && !judgeEventType) {
            const confirmContinue = confirm('No event type selected. Do you want to continue without specifying an event type?');
            if (!confirmContinue) {
                return;
            }
        }

        // Collect points system (only required for sport category)
        let pointsSystem = {};
        let hasValidPoints = false;
        
        if (category === 'scorer') {
            const pointsGrid = document.getElementById('pointsGrid');
            if (!pointsGrid) {
                alert('Points system container not found. Please refresh the page.');
                return;
            }
            
            const pointsEntries = pointsGrid.querySelectorAll('.point-entry');
            console.log('Found points entries:', pointsEntries.length);
            
            pointsEntries.forEach(entry => {
                const placementInput = entry.querySelector('.placement-input');
                const pointsInput = entry.querySelector('.points-input');
                
                if (placementInput && pointsInput) {
                    const placement = placementInput.value.trim();
                    const points = parseInt(pointsInput.value);
                    
                    console.log('Processing entry:', placement, points);
                    
                    if (placement && !isNaN(points) && points >= 0) {
                        pointsSystem[placement] = points;
                        hasValidPoints = true;
                    }
                }
            });

            // Points system is required for sport category
            if (!hasValidPoints) {
                alert('Please add at least one valid placement and points for the sport category');
                return;
            }
        }

        // Collect criteria system (only for event category)
        let criteriaSystem = {};
        if (category === 'judge') {
            // Check if this is a Beauty Pageant event
            if (judgeEventType === 'Beauty') {
                // Collect Beauty Pageant categories
                const beautyCategories = [];
                const categoryDivs = document.querySelectorAll('.beauty-category');
                
                if (categoryDivs.length === 0) {
                    alert('Please add at least one category for the Beauty Pageant event');
                    return;
                }
                
                let validationError = false;
                
                // Validate total category percentage
                let totalCategoryPercentage = 0;
                categoryDivs.forEach(categoryDiv => {
                    const categoryPct = parseFloat(categoryDiv.querySelector('.beauty-category-percentage-input')?.value || 0);
                    totalCategoryPercentage += categoryPct;
                });
                
                if (Math.abs(totalCategoryPercentage - 100) > 0.01) {
                    alert(`Total category percentage must equal 100%. Current total: ${totalCategoryPercentage}%`);
                    return;
                }
                
                for (const categoryDiv of categoryDivs) {
                    const categoryName = categoryDiv.querySelector('.beauty-category-name-input')?.value.trim();
                    if (!categoryName) {
                        alert('Please enter a name for all categories');
                        validationError = true;
                        break;
                    }
                    
                    // Get category percentage
                    const categoryPercentage = parseFloat(categoryDiv.querySelector('.beauty-category-percentage-input')?.value || 0);
                    if (categoryPercentage <= 0) {
                        alert(`Please set a category percentage for "${categoryName}"`);
                        validationError = true;
                        break;
                    }
                    
                    // Get scoring type for this category
                    const scoringType = categoryDiv.querySelector('.beauty-category-scoring-type')?.value || 'single';
                    
                    const criteriaEntries = categoryDiv.querySelectorAll('.beauty-criteria-entry');
                    const criteriaList = [];
                    let criteriaTotalPercentage = 0;
                    
                    criteriaEntries.forEach(entry => {
                        const criteriaName = entry.querySelector('.beauty-criteria-name-input')?.value.trim();
                        const percentage = parseFloat(entry.querySelector('.beauty-criteria-percentage-input')?.value) || 0;
                        
                        if (criteriaName && percentage > 0) {
                            criteriaList.push({
                                name: criteriaName,
                                percentage: percentage,
                                maxPoints: percentage
                            });
                            criteriaTotalPercentage += percentage;
                        }
                    });
                    
                    if (criteriaList.length === 0) {
                        alert(`Please add at least one criteria for category "${categoryName}"`);
                        validationError = true;
                        break;
                    }
                    
                    if (Math.abs(criteriaTotalPercentage - 100) > 0.01) {
                        alert(`Category "${categoryName}" criteria total must equal 100%. Current total: ${criteriaTotalPercentage}%`);
                        validationError = true;
                        break;
                    }
                    
                    beautyCategories.push({
                        name: categoryName,
                        categoryPercentage: categoryPercentage, // Category weight (e.g., 50%, 25%, 25%)
                        scoring_type: scoringType,
                        criteria: criteriaList,
                        criteriaTotalPercentage: criteriaTotalPercentage // Should always be 100%
                    });
                }
                
                if (validationError) {
                    return;
                }
                
                // Store Beauty Pageant structure (no global muse_escort_mode anymore)
                criteriaSystem = {
                    type: 'beauty_pageant',
                    categories: beautyCategories
                };
            } else {
                // Regular event criteria system
            const criteriaEntries = document.querySelectorAll('#criteriaGrid .point-entry');
            const criteriaList = [];
            let totalPercentage = 0;
            
            criteriaEntries.forEach(entry => {
                const criteriaName = entry.querySelector('.criteria-name-input')?.value.trim();
                const percentage = parseFloat(entry.querySelector('.criteria-percentage-input')?.value) || 0;
                
                if (criteriaName && percentage > 0) {
                    criteriaList.push({
                        name: criteriaName,
                        percentage: percentage,
                        maxPoints: percentage // Each percentage point = 1 point (so 20% = 20 points max)
                    });
                    totalPercentage += percentage;
                }
            });
            
            // Validate total percentage
            if (criteriaList.length === 0) {
                alert('Please add at least one criteria for the event category');
                return;
            }
            
            if (Math.abs(totalPercentage - 100) > 0.01) {
                alert(`Total percentage must equal 100%. Current total: ${totalPercentage}%`);
                return;
            }
            
            // Convert to object format for storage
            criteriaSystem = {
                criteria: criteriaList,
                totalPercentage: totalPercentage
            };
            }
        }

        // Determine scoring formula
        let scoringFormula = 'legacy'; // Default
        if (category === 'judge') {
            if (judgeEventType === 'Beauty') {
                scoringFormula = 'beauty_pageant_formula'; // Use rank-based formula for Beauty Pageant
            } else {
                scoringFormula = 'new_formula'; // Use ranking formula for other judge events
            }
        }

        try {
            const data = {
                name: name,
                description: description,
                category: category,
                judge_event_type: category === 'judge' ? judgeEventType : null,
                authorized_judges: category === 'judge' ? authorizedJudges : null,
                scoring_formula: scoringFormula,
                points_system: category === 'scorer' ? pointsSystem : (category === 'judge' ? criteriaSystem : {})
            };

            console.log('Saving game to database:', data);
            const result = await this.apiCall('database_handler.php?action=games', 'POST', data);
            console.log('Game save result:', result);
            this.clearGameForm();
            await this.loadAllData(); // Reload all data
            alert('Game added successfully!');
        } catch (error) {
            console.error('Failed to add game:', error);
            alert('Failed to add game: ' + error.message);
        }
    }

    selectGame(gameId, gameName) {
        console.log('Selecting game:', gameId, gameName);
        
        // Check if game category is event
        const game = this.games.find(g => g.id === gameId);
        if (game && (game.category || 'scorer') === 'judge') {
            alert('Event category games use the Event scoring interface. Please go to the Event tab to score teams.');
            return;
        }
        
        this.selectedGame = gameId;
        this.selectedGameName = gameName;
        
        document.getElementById('selectedGame').value = gameName;
        document.getElementById('selectedGameId').value = gameId;
        document.getElementById('scoreEntry').style.display = 'block';
        
        this.populatePlacementOptions(gameId);
        document.getElementById('placement').value = '';
        document.getElementById('points').value = '';
        document.getElementById('scoreEntry').scrollIntoView({ behavior: 'smooth' });
        console.log('Score entry form should now be visible');
    }

    populatePlacementOptions(gameId) {
        const placementSelect = document.getElementById('placement');
        if (!placementSelect) return;
        
        placementSelect.innerHTML = '<option value="">Select Placement</option>';
        const game = this.games.find(g => g.id === gameId);
        if (game) {
            // Only show placement options for sport category games
            const category = game.category || 'scorer';
            if (category === 'judge') {
                placementSelect.innerHTML = '<option value="">Event games use criteria-based scoring</option>';
                return;
            }
            
            const pointsSystem = typeof game.points_system === 'string' ? 
                JSON.parse(game.points_system) : game.points_system || {};
            
            const usedPlacements = this.scores
                .filter(s => s.game_id === gameId)
                .map(s => s.placement);
            
            let currentTeamPlacement = null;
            const teamId = parseInt(document.getElementById('teamSelect').value);
            if (teamId) {
                const existingScore = this.scores.find(s => s.game_id === gameId && s.team_id === teamId);
                if (existingScore) {
                    currentTeamPlacement = existingScore.placement;
                }
            }
            
            Object.keys(pointsSystem).forEach(placement => {
                if (!usedPlacements.includes(placement) || placement === currentTeamPlacement) {
                    const option = document.createElement('option');
                    option.value = placement;
                    option.textContent = placement;
                    placementSelect.appendChild(option);
                }
            });
        }
    }

    getPointsForPlacement(gameId, placement) {
        if (!gameId || !placement) return null;
        const game = this.games.find(g => g.id == gameId);
        if (!game) return null;

        let pointsSystem = game.points_system || {};
        if (typeof pointsSystem === 'string' && pointsSystem.trim() !== '') {
            try {
                pointsSystem = JSON.parse(pointsSystem);
            } catch (error) {
                console.error('Failed to parse points system JSON:', error);
                pointsSystem = {};
            }
        }

        const value = pointsSystem ? pointsSystem[placement] : null;
        return (value !== undefined && value !== null) ? parseInt(value) : null;
    }

    updatePoints() {
        const placement = document.getElementById('placement')?.value;
        const pointsInput = document.getElementById('points');
        
        if (!pointsInput) return;
        
        if (placement && this.selectedGame) {
            const game = this.games.find(g => g.id === this.selectedGame);
            if (game) {
                const pointsSystem = typeof game.points_system === 'string' ? 
                    JSON.parse(game.points_system) : game.points_system || {};
                const points = pointsSystem[placement];
                pointsInput.value = points || 0;
            }
        } else {
            pointsInput.value = '';
        }
    }

    // Score Management
    async addScore() {
        const teamId = parseInt(document.getElementById('teamSelect').value);
        const placement = document.getElementById('placement').value;
        const pointsInput = document.getElementById('points');
        let points = parseInt(pointsInput?.value);
        const scorerName = document.getElementById('scorerName').value.trim();

        if (!teamId || !placement || !scorerName) {
            alert('Please fill in all required fields');
            return;
        }

        // Prevent multiple attempts per team per sport
        const existingScore = this.scores.find(score =>
            (score.team_id === teamId || score.teamId === teamId) &&
            (score.game_id === this.selectedGame || score.gameId === this.selectedGame)
        );

        if (existingScore) {
            alert('Each team can only be scored once per event. This team already has a recorded score for the selected event.');
            return;
        }

        if (isNaN(points)) {
            const calculated = this.getPointsForPlacement(this.selectedGame, placement);
            if (calculated !== null && !isNaN(calculated)) {
                points = calculated;
                if (pointsInput) {
                    pointsInput.value = points;
                }
            }
        }

        if (isNaN(points)) {
            alert('Unable to determine points for the selected placement. Please review the event points system.');
            return;
        }

        const team = this.teams.find(t => t.id === teamId);
        if (!team) {
            alert('Selected team not found');
            return;
        }

        try {
            // Debug: Log the selected game and available games
            console.log('Selected Game ID:', this.selectedGame);
            console.log('Available Games:', this.games);
            console.log('Selected Game Object:', this.games.find(g => g.id == this.selectedGame));
            
            const data = {
                team_id: teamId,
                game_id: this.selectedGame,
                placement: placement,
                points: points,
                scorer: scorerName
            };

            console.log('Adding score with data:', data);
            await this.apiCall('database_handler.php?action=scores', 'POST', data);
            this.clearScoreForm();
            this.cancelScoreEntry();
            await this.loadAllData(); // Reload all data
            alert('Score added successfully!');
        } catch (error) {
            console.error('Failed to add score:', error);
            alert('Failed to add score: ' + error.message);
        }
    }

    // Rendering Methods
    populateTeamSelects() {
        const teamSelect = document.getElementById('teamSelect');
        
        if (teamSelect) {
            teamSelect.innerHTML = '<option value="">Select Team</option>';
            this.teams.forEach(team => {
                const option = document.createElement('option');
                option.value = team.id;
                option.textContent = team.name;
                teamSelect.appendChild(option);
            });
        }
    }

    renderGameGrid() {
        const gameGrid = document.getElementById('gameGrid');
        if (!gameGrid) return;
        
        console.log('Rendering game grid with games:', this.games);
        
        if (this.games.length === 0) {
            gameGrid.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">No games created yet. Go to the Games tab to add games.</div>';
            return;
        }

        const gamesHTML = this.games.map(game => {
            const category = game.category || 'scorer';
            const categoryLabel = category === 'judge' ? 'Event' : 'Sport';
            const categoryIcon = category === 'judge' ? 'fa-gavel' : 'fa-star';
            const categoryColor = category === 'judge' ? '#8b5cf6' : '#2563eb';
            
            const pointsSystem = typeof game.points_system === 'string' ? 
                JSON.parse(game.points_system) : game.points_system || {};
            const pointsText = category === 'scorer' && Object.keys(pointsSystem).length > 0
                ? Object.entries(pointsSystem)
                    .map(([placement, points]) => `${placement}: ${points}pts`)
                    .join(' | ')
                : category === 'judge' 
                    ? 'Criteria-based scoring for events (0-20 each)' 
                    : 'No points system defined';
            
            const judgeEventType = game.judge_event_type || '';
            
            return `
                <div class="game-card" onclick="${category === 'scorer' ? `scoresheet.selectGame(${game.id}, '${game.name.replace(/'/g, "&#39;")}')` : `alert('Event category games use the Event scoring interface. Please go to the Event tab.');`}">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <span style="font-size: 0.85rem; padding: 5px 12px; background: ${categoryColor}; color: white; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas ${categoryIcon}"></i> ${categoryLabel} Category
                        </span>
                        ${category === 'judge' && judgeEventType ? `
                        <span style="font-size: 0.75rem; padding: 4px 10px; background: #f3e8ff; color: #6b21a8; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fas fa-tag"></i> ${judgeEventType}
                        </span>
                        ` : ''}
                    </div>
                    <h3 style="margin-bottom: 10px;">${game.name}</h3>
                    <p>${pointsText}</p>
                    ${game.description ? `<p style="font-style: italic; margin-top: 8px; color: var(--text-muted);">${game.description}</p>` : ''}
                    ${category === 'judge' ? `<p style="margin-top: 10px; color: #8b5cf6; font-weight: 600; padding: 8px; background: #f3e8ff; border-radius: 6px;"><i class="fas fa-info-circle"></i> Use Event tab for scoring</p>` : ''}
                </div>
            `;
        }).join('');
        
        console.log('Rendering games HTML:', gamesHTML);
        gameGrid.innerHTML = gamesHTML;
    }

    renderGames() {
        const gamesGrid = document.getElementById('gamesGrid');
        if (!gamesGrid) return;
        
        if (this.games.length === 0) {
            gamesGrid.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">No games created yet</div>';
            return;
        }

        gamesGrid.innerHTML = this.games.map(game => {
            const category = game.category || 'scorer';
            const categoryLabel = category === 'judge' ? 'Event' : 'Sport';
            const categoryIcon = category === 'judge' ? 'fa-gavel' : 'fa-star';
            const categoryColor = category === 'judge' ? '#8b5cf6' : '#2563eb';
            const judgeEventType = game.judge_event_type || '';
            
            const pointsSystem = typeof game.points_system === 'string' ? 
                JSON.parse(game.points_system) : game.points_system || {};
            const pointsList = Object.entries(pointsSystem)
                .map(([placement, points]) => `<span class="point-tag">${placement}: ${points}pts</span>`)
                .join('');
            
            return `
                <div class="game-management-card">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                        <span style="font-size: 0.9rem; padding: 6px 14px; background: ${categoryColor}; color: white; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas ${categoryIcon}"></i> ${categoryLabel} Category
                        </span>
                        ${category === 'judge' && judgeEventType ? `
                        <span style="font-size: 0.8rem; padding: 5px 12px; background: #f3e8ff; color: #6b21a8; border-radius: 8px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fas fa-tag"></i> ${judgeEventType}
                        </span>
                        ` : ''}
                    </div>
                    <h4 style="margin-bottom: 12px;">${game.name}</h4>
                    ${game.description ? `<div class="game-description">${game.description}</div>` : ''}
                    ${category === 'scorer' && pointsList ? `
                    <div class="game-points">
                        <h5>Points System:</h5>
                        <div class="points-list">
                            ${pointsList}
                        </div>
                    </div>
                    ` : category === 'judge' ? `
                    <div class="game-points" style="background: #f3e8ff; border-color: #8b5cf6; border-left: 4px solid #8b5cf6;">
                        <h5 style="color: #6b21a8; margin-bottom: 8px;"><i class="fas fa-gavel"></i> Event Category${judgeEventType ? ` - ${judgeEventType}` : ''}</h5>
                        <p style="color: #6b21a8; margin: 0; font-size: 0.9rem;">This game uses criteria-based scoring (0-20 points per criterion, total 100 points)</p>
                    </div>
                    ` : ''}
                    <div class="game-actions">
                        <button class="btn btn-danger" onclick="scoresheet.deleteGame(${game.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        this.populateLeaderboardGameFilter();
    }

    // NEW: Calculate rank-based scores for all teams in a game (Beauty Pageant Formula)
    calculateRankBasedScoresForGame(gameId, allGameJudgeScores) {
        if (!allGameJudgeScores || allGameJudgeScores.length === 0) return {};
        
        const game = this.games.find(g => g.id == gameId);
        if (!game) return {};
        
        const pointsSystem = typeof game.points_system === 'string' ? JSON.parse(game.points_system) : (game.points_system || {});
        
        // Check if this is a category-based Beauty Pageant structure
        if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories && pointsSystem.categories.length > 0) {
            return this.calculateCategoryBasedRankScores(gameId, allGameJudgeScores, pointsSystem.categories);
        }
        
        // Legacy: flat criteria list (for backward compatibility)
        const criteriaList = pointsSystem.criteria || [];
        const criteriaCount = Math.min(criteriaList.length, 5);
        
        if (criteriaCount === 0) return {};
        
        // Get original criteria percentages
        const originalPercentages = {};
        for (let i = 0; i < criteriaCount; i++) {
            const criteria = criteriaList[i];
            originalPercentages[i + 1] = criteria.percentage || 0;
        }
        
        // Group scores by team and judge
        const scoresByTeam = {};
        const judges = new Set();
        
        allGameJudgeScores.forEach(score => {
            const teamId = score.team_id || score.teamId || score.teamId_alt;
            const judgeName = score.judge_name || score.judgeName;
            
            if (!teamId || !judgeName) return;
            
            if (!scoresByTeam[teamId]) {
                scoresByTeam[teamId] = {};
            }
            if (!scoresByTeam[teamId][judgeName]) {
                scoresByTeam[teamId][judgeName] = {};
            }
            
            judges.add(judgeName);
            
            // Store criteria scores
            for (let i = 1; i <= criteriaCount; i++) {
                scoresByTeam[teamId][judgeName][i] = parseInt(score[`criteria${i}`] || 0);
            }
        });
        
        const teamIds = Object.keys(scoresByTeam);
        const judgeArray = Array.from(judges);
        
        if (teamIds.length === 0 || judgeArray.length === 0) return {};
        
        // Step 1: For each criteria and each judge, rank all teams (1=best score, 2=second, etc.)
        // Step 2: Average ranks per criteria across all judges
        const teamAvgRanks = {}; // {teamId: {criteria1: avgRank, criteria2: avgRank, ...}}
        
        teamIds.forEach(teamId => {
            teamAvgRanks[teamId] = {};
            
            for (let criteriaNum = 1; criteriaNum <= criteriaCount; criteriaNum++) {
                const ranks = [];
                
                // For each judge, rank this team among all teams for this criteria
                judgeArray.forEach(judgeName => {
                    const thisTeamScore = scoresByTeam[teamId][judgeName]?.[criteriaNum] || 0;
                    
                    // Count how many teams scored better (higher score = better, so lower rank)
                    let rank = 1;
                    teamIds.forEach(otherTeamId => {
                        if (otherTeamId !== teamId) {
                            const otherTeamScore = scoresByTeam[otherTeamId][judgeName]?.[criteriaNum] || 0;
                            if (otherTeamScore > thisTeamScore) {
                                rank++;
                            } else if (otherTeamScore === thisTeamScore && otherTeamId < teamId) {
                                // Tie-breaker: use team ID for consistent ranking
                                rank++;
                            }
                        }
                    });
                    
                    ranks.push(rank);
                });
                
                // Average the ranks across all judges
                const avgRank = ranks.length > 0 ? ranks.reduce((sum, r) => sum + r, 0) / ranks.length : teamIds.length;
                teamAvgRanks[teamId][criteriaNum] = avgRank;
            }
        });
        
        // Step 3: Convert averaged ranks to weights (inverse - lower rank = higher weight)
        // Step 4: Calculate final scores using original criteria percentages
        const finalScores = {};
        
        teamIds.forEach(teamId => {
            // For each criteria, calculate inverse weights
            const inverseWeights = {};
            let totalInverse = 0;
            
            for (let criteriaNum = 1; criteriaNum <= criteriaCount; criteriaNum++) {
                const avgRank = teamAvgRanks[teamId][criteriaNum];
                // Inverse: rank 1 (best) gets highest weight
                inverseWeights[criteriaNum] = teamIds.length + 1 - avgRank;
                totalInverse += inverseWeights[criteriaNum];
            }
            
            // Calculate final score
            // Based on green table: Step 4 applies original percentages adjusted by rank performance
            let finalScore = 0;
            
            if (totalInverse > 0) {
                // Calculate rank-based weights that redistribute original percentages
                // Better ranks get proportionally more weight from their original percentage
                const rankBasedWeights = {};
                let totalRankWeight = 0;
                
                for (let criteriaNum = 1; criteriaNum <= criteriaCount; criteriaNum++) {
                    const originalPct = originalPercentages[criteriaNum] || 0;
                    const normalizedInverse = inverseWeights[criteriaNum] / totalInverse;
                    
                    // Redistribute: better rank gets more of the original percentage
                    // Formula: originalPct * (1 + rankBonus) where rankBonus favors better ranks
                    const rankBonus = (criteriaCount - teamAvgRanks[teamId][criteriaNum] + 1) / criteriaCount;
                    rankBasedWeights[criteriaNum] = (originalPct / 100) * (0.5 + 0.5 * rankBonus);
                    totalRankWeight += rankBasedWeights[criteriaNum];
                }
                
                // Normalize to ensure weights sum to 1
                if (totalRankWeight > 0) {
                    for (let criteriaNum = 1; criteriaNum <= criteriaCount; criteriaNum++) {
                        const normalizedWeight = rankBasedWeights[criteriaNum] / totalRankWeight;
                        
                        // Calculate score based on rank: better rank = higher score
                        // Convert rank to score: rank 1 (best) = 100, rank N (worst) = lower score
                        const maxRank = teamIds.length;
                        const avgRank = teamAvgRanks[teamId][criteriaNum];
                        const rankScore = ((maxRank - avgRank + 1) / maxRank) * 100;
                        
                        // Apply normalized weight
                        finalScore += rankScore * normalizedWeight;
                    }
                }
            }
            
            finalScores[teamId] = Math.round(finalScore * 100) / 100;
        });
        
        return finalScores;
    }
    
    // Calculate rank-based scores with category structure
    calculateCategoryBasedRankScores(gameId, allGameJudgeScores, categories) {
        // Group scores by team and judge
        const scoresByTeam = {};
        const judges = new Set();
        
        // Map criteria to their category and position
        let globalCriteriaIndex = 1;
        const criteriaMapping = {}; // {criteriaIndex: {categoryIndex, criteriaIndexInCategory, categoryPercentage, criteriaPercentage}}
        
        categories.forEach((category, catIdx) => {
            category.criteria.forEach((criteria, critIdx) => {
                criteriaMapping[globalCriteriaIndex] = {
                    categoryIndex: catIdx,
                    criteriaIndexInCategory: critIdx,
                    categoryPercentage: category.categoryPercentage || 0,
                    criteriaPercentage: criteria.percentage || 0,
                    categoryName: category.name,
                    criteriaName: criteria.name
                };
                globalCriteriaIndex++;
            });
        });
        
        const maxCriteriaIndex = globalCriteriaIndex - 1;
        
        allGameJudgeScores.forEach(score => {
            const teamId = score.team_id || score.teamId || score.teamId_alt;
            const judgeName = score.judge_name || score.judgeName;
            
            if (!teamId || !judgeName) return;
            
            if (!scoresByTeam[teamId]) {
                scoresByTeam[teamId] = {};
            }
            if (!scoresByTeam[teamId][judgeName]) {
                scoresByTeam[teamId][judgeName] = {};
            }
            
            judges.add(judgeName);
            
            // Store criteria scores (criteria1, criteria2, etc.)
            for (let i = 1; i <= maxCriteriaIndex; i++) {
                scoresByTeam[teamId][judgeName][i] = parseInt(score[`criteria${i}`] || 0);
            }
        });
        
        const teamIds = Object.keys(scoresByTeam);
        const judgeArray = Array.from(judges);
        
        if (teamIds.length === 0 || judgeArray.length === 0) return {};
        
        // Step 1: For each criteria and each judge, rank all teams
        // Step 2: Average ranks per criteria across all judges
        const teamAvgRanks = {};
        
        teamIds.forEach(teamId => {
            teamAvgRanks[teamId] = {};
            
            for (let criteriaNum = 1; criteriaNum <= maxCriteriaIndex; criteriaNum++) {
                const ranks = [];
                
                judgeArray.forEach(judgeName => {
                    const thisTeamScore = scoresByTeam[teamId][judgeName]?.[criteriaNum] || 0;
                    
                    let rank = 1;
                    teamIds.forEach(otherTeamId => {
                        if (otherTeamId !== teamId) {
                            const otherTeamScore = scoresByTeam[otherTeamId][judgeName]?.[criteriaNum] || 0;
                            if (otherTeamScore > thisTeamScore) {
                                rank++;
                            } else if (otherTeamScore === thisTeamScore && otherTeamId < teamId) {
                                rank++;
                            }
                        }
                    });
                    
                    ranks.push(rank);
                });
                
                const avgRank = ranks.length > 0 ? ranks.reduce((sum, r) => sum + r, 0) / ranks.length : teamIds.length;
                teamAvgRanks[teamId][criteriaNum] = avgRank;
            }
        });
        
        // Step 3 & 4: Calculate category scores, then final scores
        const finalScores = {};
        
        teamIds.forEach(teamId => {
            let finalScore = 0;
            
            // Process each category
            categories.forEach((category, catIdx) => {
                let categoryScore = 0;
                let categoryMaxScore = 0;
                
                // Calculate scores for all criteria in this category
                category.criteria.forEach((criteria, critIdx) => {
                    // Find the global criteria index
                    let globalCritIdx = 1;
                    for (let i = 0; i < catIdx; i++) {
                        globalCritIdx += categories[i].criteria.length;
                    }
                    globalCritIdx += critIdx;
                    
                    const avgRank = teamAvgRanks[teamId][globalCritIdx];
                    const maxRank = teamIds.length;
                    
                    // Convert rank to score: rank 1 (best) = 100, rank N (worst) = lower
                    const rankScore = ((maxRank - avgRank + 1) / maxRank) * 100;
                    
                    // Criteria contributes: rankScore × (criteriaPercentage / 100)
                    const criteriaContribution = rankScore * (criteria.percentage / 100);
                    categoryScore += criteriaContribution;
                    categoryMaxScore += 100 * (criteria.percentage / 100); // Max possible for this category
                });
                
                // Category score = (categoryScore / categoryMaxScore) × categoryPercentage
                // This scales the category score to the category's percentage weight
                const categoryContribution = (categoryScore / categoryMaxScore) * (category.categoryPercentage || 0);
                finalScore += categoryContribution;
            });
            
            finalScores[teamId] = Math.round(finalScore * 100) / 100;
        });
        
        return finalScores;
    }

    // Helper function to calculate final score for a game based on scoring formula
    calculateGameFinalScore(gameId, gameJudgeScores) {
        if (!gameJudgeScores || gameJudgeScores.length === 0) return 0;
        
        // Find the game to get scoring_formula and criteria system
        const game = this.games.find(g => g.id == gameId);
        if (!game) {
            // Fallback to averaging if game not found
            const totalScore = gameJudgeScores.reduce((sum, s) => sum + (parseInt(s.totalScore || s.total_score || 0)), 0);
            const judgeCount = new Set(gameJudgeScores.map(s => s.judge_name || s.judgeName).filter(Boolean)).size || 1;
            return judgeCount > 0 ? totalScore / judgeCount : 0;
        }
        
        const scoringFormula = game.scoring_formula || 'legacy';
        const pointsSystem = typeof game.points_system === 'string' ? JSON.parse(game.points_system) : (game.points_system || {});
        const criteriaList = pointsSystem.criteria || [];
        
        if (scoringFormula === 'beauty_pageant_formula') {
            // BEAUTY PAGEANT FORMULA
            // Use full category/criteria structure (including details_json when present)
            // instead of the old 5-criteria rank-based shortcut.
            const allGameScores = this.judgeScores.filter(js => {
                const jsGameId = js.game_id || js.gameId;
                return jsGameId == gameId;
            });
            
            if (allGameScores.length === 0) return 0;
            
            // Get the team ID from the first score in gameJudgeScores
            const currentTeamId = gameJudgeScores[0]?.team_id || gameJudgeScores[0]?.teamId || gameJudgeScores[0]?.teamId_alt;
            if (!currentTeamId) return 0;
            
            // If this is a Beauty Pageant with categories, sum category scores
            if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories && pointsSystem.categories.length > 0) {
                let finalScore = 0;
                pointsSystem.categories.forEach((cat, idx) => {
                    finalScore += this.calculateCategoryScoreForTeam(
                        currentTeamId,
                        cat,
                        idx,
                        pointsSystem.categories,
                        allGameScores
                    );
                });
                return Math.round(finalScore * 100) / 100;
            }

            // Fallback to legacy rank-based calculation if categories are missing
            const rankBasedScores = this.calculateRankBasedScoresForGame(gameId, allGameScores);
            return rankBasedScores[currentTeamId] || 0;
        } else if (scoringFormula === 'new_formula') {
            // RANKING FORMULA: Average criteria first, then apply weights
            const judgeCount = gameJudgeScores.length;
            if (judgeCount === 0) return 0;
            
            // Step 1: Average each criterion across all judges
            const averagedCriteria = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0};
            
            gameJudgeScores.forEach(score => {
                averagedCriteria[1] += (parseInt(score.criteria1 || 0));
                averagedCriteria[2] += (parseInt(score.criteria2 || 0));
                averagedCriteria[3] += (parseInt(score.criteria3 || 0));
                averagedCriteria[4] += (parseInt(score.criteria4 || 0));
                averagedCriteria[5] += (parseInt(score.criteria5 || 0));
            });
            
            // Calculate averages
            for (let i = 1; i <= 5; i++) {
                averagedCriteria[i] = averagedCriteria[i] / judgeCount;
            }
            
            // Step 2: Apply weights from criteria system and sum
            let finalScore = 0;
            for (let i = 0; i < Math.min(criteriaList.length, 5); i++) {
                const criteria = criteriaList[i];
                const percentage = criteria.percentage || 0;
                const weight = percentage / 100; // Convert percentage to decimal (e.g., 40% = 0.40)
                const criteriaNum = i + 1;
                const averagedValue = averagedCriteria[criteriaNum];
                finalScore += averagedValue * weight;
            }
            
            return Math.round(finalScore * 100) / 100;
        } else {
            // AVERAGING FORMULA (legacy): Sum total_score, then average
            const totalScore = gameJudgeScores.reduce((sum, s) => sum + (parseInt(s.totalScore || s.total_score || 0)), 0);
            const judgeCount = new Set(gameJudgeScores.map(s => s.judge_name || s.judgeName).filter(Boolean)).size || 1;
            return judgeCount > 0 ? Math.round((totalScore / judgeCount) * 100) / 100 : 0;
        }
    }

    renderScoresTable() {
        const table = document.getElementById('scoresTable');
        if (!table) return;
        
        if (this.teams.length === 0) {
            table.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No teams registered yet</td></tr>';
            return;
        }

        const teamRankings = this.teams.map(team => {
            // Get regular game scores
            const teamGameScores = this.scores.filter(s => s.team_id === team.id);
            const gamePoints = teamGameScores.reduce((sum, score) => sum + (score.points || 0), 0);
            const gameGamesPlayed = teamGameScores.length;
            
            // Get judge scores (support both teamId and team_id formats)
            const teamJudgeScores = this.judgeScores.filter(js => 
                js.teamId === team.id || js.team_id === team.id || js.teamId_alt === team.id
            );
            
            // Group judge scores by game_id
            const judgeScoresByGame = {};
            teamJudgeScores.forEach(judgeScore => {
                const gameId = judgeScore.game_id || judgeScore.gameId || 'no_game';
                if (!judgeScoresByGame[gameId]) {
                    judgeScoresByGame[gameId] = [];
                }
                judgeScoresByGame[gameId].push(judgeScore);
            });
            
            // Calculate final score for each game using the appropriate formula
            let judgePoints = 0;
            let judgeGamesPlayed = 0;
            Object.entries(judgeScoresByGame).forEach(([gameId, gameScores]) => {
                if (gameId !== 'no_game') {
                    const finalScore = this.calculateGameFinalScore(parseInt(gameId), gameScores);
                    judgePoints += finalScore;
                    judgeGamesPlayed += 1;
                }
            });
            
            // Calculate total points (game points + judge final scores)
            const totalPoints = gamePoints + judgePoints;
            const totalGamesPlayed = gameGamesPlayed + judgeGamesPlayed;
            
            return {
                ...team,
                total_points: totalPoints,
                games_played: totalGamesPlayed
            };
        }).sort((a, b) => b.total_points - a.total_points);

        table.innerHTML = teamRankings.map((team, index) => {
            const rankClass = index < 3 ? `rank-${index + 1}` : '';
            return `
                <tr>
                    <td class="${rankClass}">${index + 1}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${team.color || '#2563eb'};"></div>
                            <div>
                                <div style="font-weight: 600;">${team.name}</div>
                                <div style="font-size: 0.8rem; color: #2563eb; font-weight: 600; background: rgba(37, 99, 235, 0.1); padding: 2px 8px; border-radius: 12px; display: inline-block; margin-top: 2px;">
                                    <i class="fas fa-tag" style="margin-right: 3px; font-size: 0.7rem;"></i>${team.code || 'N/A'}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><strong>${team.total_points || 0}</strong></td>
                    <td>${team.games_played || 0}</td>
                    <td>
                        <button class="btn btn-primary" onclick="scoresheet.viewTeamDetails(${team.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    renderTeams() {
        const grid = document.getElementById('teamsGrid');
        if (!grid) return;
        
        if (this.teams.length === 0) {
            grid.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">No teams registered yet</div>';
            return;
        }

        // Filter teams by team code if filter is active
        const filteredTeams = this.teamCodeFilter ? 
            this.teams.filter(team => team.code === this.teamCodeFilter) : 
            this.teams;

        if (filteredTeams.length === 0 && this.teamCodeFilter) {
            grid.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">No teams found for the selected team code</div>';
            return;
        }

        grid.innerHTML = filteredTeams.map(team => {
            // Show event type if specified
            let eventTypeDisplay = '';
            if (team.event_type) {
                eventTypeDisplay = `
                    <div style="margin-top: 8px; padding: 6px 12px; background: #f0f9ff; border-radius: 8px; border-left: 3px solid #2563eb; display: inline-block;">
                        <div style="font-size: 0.85rem; color: #1e40af; font-weight: 600;">
                            <i class="fas fa-calendar-check" style="margin-right: 6px;"></i>Event Type: ${team.event_type}
                        </div>
                    </div>
                `;
            }
            
            return `
            <div class="team-card" data-team-id="${team.id}" style="border-left-color: ${team.color || '#2563eb'};">
                <h4>
                    <div class="team-color" style="background-color: ${team.color || '#2563eb'};"></div>
                    <div>
                        <div class="team-name" style="font-weight: 600;">${team.name}</div>
                        <div style="font-size: 0.9rem; color: #2563eb; font-weight: 600; margin-top: 2px; background: rgba(37, 99, 235, 0.1); padding: 4px 12px; border-radius: 16px; display: inline-block; border: 1px solid rgba(37, 99, 235, 0.2);">
                            <i class="fas fa-tag" style="margin-right: 4px;"></i>${team.code || 'N/A'}
                        </div>
                        ${eventTypeDisplay}
                    </div>
                </h4>
                ${team.members ? `<div class="team-members" style="margin-top: 10px;"><strong>Members:</strong> ${team.members}</div>` : ''}
                <div class="team-actions" style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                    <button class="btn btn-primary btn-sm" onclick="scoresheet.viewTeamDetails(${team.id})">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="scoresheet.deleteTeam(${team.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            `;
        }).join('');
    }

    renderOverallRankings() {
        const table = document.getElementById('overallRankingsTable');
        if (!table) return;
        
        console.log('Rendering overall rankings...');
        console.log('Teams data:', this.teams);
        
        if (this.teams.length === 0) {
            table.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">No teams registered yet</td></tr>';
            return;
        }

        // Check if we have Beauty Pageant participant-type scores (muse/escort/pair)
        const hasBeautyParticipantTypes = this.judgeScores.some(js => 
            js && (js.participant_type === 'muse' || js.participant_type === 'escort' || js.participant_type === 'pair')
        );

        if (hasBeautyParticipantTypes) {
            // Overall category winners per participant type (Muse, Escort, Pair) across Beauty events
            const participantMeta = {
                muse:   { label: 'Muse (Female)', color: '#ec4899' },
                escort: { label: 'Escort (Male)', color: '#3b82f6' },
                pair:   { label: 'Pair',          color: '#f59e0b' }
            };

            // Optional filter from dropdown in Overall Rankings tab
            const filterEl = document.getElementById('overallTypeFilter');
            const filterValue = filterEl ? (filterEl.value || 'all') : 'all';

            // Collect Beauty Pageant games with category structures
            const beautyGames = this.games.filter(game => {
                if (!game.points_system) return false;
                let ps = game.points_system;
                if (typeof ps === 'string') {
                    try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
                }
                return ps.type === 'beauty_pageant' && ps.categories && ps.categories.length > 0;
            });

            const categoriesByType = { muse: [], escort: [], pair: [] };

            beautyGames.forEach(game => {
                let ps = game.points_system;
                if (typeof ps === 'string') {
                    try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
                }
                const categories = ps.categories || [];
                if (!categories.length) return;

                const gameId = game.id;
                const gameJudgeScores = this.judgeScores.filter(js => {
                    const jsGameId = js.game_id || js.gameId;
                    return jsGameId == gameId;
                });

                // For each participant type and category, find the winning team
                Object.keys(participantMeta).forEach(pt => {
                    if (filterValue !== 'all' && filterValue !== pt) return;
                    categories.forEach((category, catIdx) => {
                        const categoryName = category.name || ('Category ' + (catIdx + 1));
                        const entries = [];

                        this.teams.forEach(team => {
                            const teamId = team.id;
                            const filteredScores = gameJudgeScores.filter(s => {
                                const sTeamId = s.team_id || s.teamId || s.teamId_alt;
                                const sPt = s.participant_type || 'single';
                                return sTeamId == teamId && sPt === pt;
                            });

                            if (!filteredScores.length) return;

                            const categoryScore = this.calculateParticipantTypeCategoryScore(
                                filteredScores,
                                category,
                                catIdx
                            );

                            if (categoryScore > 0) {
                                entries.push({
                                    team: team,
                                    score: categoryScore
                                });
                            }
                        });

                        if (entries.length > 0) {
                            entries.sort((a, b) => b.score - a.score);

                            categoriesByType[pt].push({
                                gameName: game.name,
                                categoryName: categoryName,
                                entries: entries
                            });
                        }
                    });
                });
            });

            let html = '';
            Object.keys(participantMeta).forEach(pt => {
                if (filterValue !== 'all' && filterValue !== pt) return;
                const categorySets = categoriesByType[pt];
                if (!categorySets || categorySets.length === 0) return;

                const meta = participantMeta[pt];

                categorySets.forEach(set => {
                    // Section header row for this participant type + category
                    html += ''
                        + '<tr>'
                        +   '<td colspan="4" style="background:#f9fafb; font-weight:600; color:#0f172a;">'
                        +       '<span style="color:' + meta.color + '; margin-right:6px;"><i class="fas fa-crown"></i></span>'
                        +       meta.label + ' – ' + set.categoryName
                        +   '</td>'
                        + '</tr>';

                    set.entries.forEach(function(entry, index) {
                        const rankClass = index < 3 ? ('rank-' + (index + 1)) : '';
                        let winnerBadge = '';
                        if (index === 0) {
                            winnerBadge = '<span style="margin-left:6px; font-size:0.75rem; color:#b45309; background:#fef3c7; padding:2px 8px; border-radius:999px;"><i class="fas fa-crown"></i> Champion</span>';
                        } else if (index === 1) {
                            winnerBadge = '<span style="margin-left:6px; font-size:0.75rem; color:#4b5563; background:#e5e7eb; padding:2px 8px; border-radius:999px;"><i class="fas fa-medal"></i> 2nd</span>';
                        } else if (index === 2) {
                            winnerBadge = '<span style="margin-left:6px; font-size:0.75rem; color:#1f2937; background:#fee2e2; padding:2px 8px; border-radius:999px;"><i class="fas fa-award"></i> 3rd</span>';
                        }
                        html += ''
                            + '<tr>'
                            +   '<td class="' + rankClass + '">' + (index + 1) + '</td>'
                            +   '<td>'
                            +       '<div style="display: flex; align-items: center; gap: 8px;">'
                            +           '<div style="width: 10px; height: 10px; border-radius: 50%; background-color: ' + (entry.team.color || '#2563eb') + ';"></div>'
                            +           '<div>'
                            +               '<strong>' + entry.team.name + '</strong>' + winnerBadge + '<br>'
                            +               '<small style="color: #64748b;">' + (entry.team.code || 'N/A') + '</small>'
                            +           '</div>'
                            +       '</div>'
                            +   '</td>'
                            +   '<td>' + set.categoryName + '</td>'
                            +   '<td>' + set.gameName + '</td>'
                            + '</tr>';
                    });
                });
            });

            if (!html) {
                table.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">No rankings available yet. Add teams and scores to see rankings.</td></tr>';
            } else {
                table.innerHTML = html;
            }

            return;
        }

        // Legacy overall ranking when there are no Muse/Escort/Pair scores:
        // Calculate team rankings using the same logic as leaderboards
        const teamRankings = this.teams.map(team => {
            // Get regular game scores
            const relevantScores = this.scores.filter(s => s.team_id === team.id);
            const gamePoints = relevantScores.reduce((sum, score) => sum + (score.points || 0), 0);
            const gameGamesPlayed = relevantScores.length;
            
            // Get judge scores (support both teamId and team_id formats)
            const teamJudgeScores = this.judgeScores.filter(js => 
                js.teamId === team.id || js.team_id === team.id || js.teamId_alt === team.id
            );
            
            // Group judge scores by game_id
            const judgeScoresByGame = {};
            teamJudgeScores.forEach(judgeScore => {
                const gameId = judgeScore.game_id || judgeScore.gameId || 'no_game';
                if (!judgeScoresByGame[gameId]) {
                    judgeScoresByGame[gameId] = [];
                }
                judgeScoresByGame[gameId].push(judgeScore);
            });
            
            // Calculate final score for each game using the appropriate formula
            let judgePoints = 0;
            let judgeGamesPlayed = 0;
            Object.entries(judgeScoresByGame).forEach(([gameId, gameScores]) => {
                if (gameId !== 'no_game') {
                    const finalScore = this.calculateGameFinalScore(parseInt(gameId), gameScores);
                    judgePoints += finalScore;
                    judgeGamesPlayed += 1;
                }
            });
            
            // Calculate total points (game points + judge final scores)
            const displayTotalPoints = gamePoints + judgePoints;
            const displayGamesPlayed = gameGamesPlayed + judgeGamesPlayed;
            
            return {
                ...team,
                totalPoints: displayTotalPoints,
                gamesPlayed: displayGamesPlayed
            };
        });

        // Group teams by code and sum their points
        const codeGroups = {};
        
        teamRankings.forEach(team => {
            const code = team.code || 'N/A';
            if (!codeGroups[code]) {
                codeGroups[code] = {
                    code: code,
                    totalPoints: 0,
                    totalGamesPlayed: 0,
                    teamCount: 0,
                    teamNames: [],
                    teamDetails: []
                };
            }
            codeGroups[code].totalPoints += team.totalPoints;
            codeGroups[code].totalGamesPlayed += team.gamesPlayed;
            codeGroups[code].teamCount += 1;
            codeGroups[code].teamNames.push(team.name);
            codeGroups[code].teamDetails.push({
                name: team.name,
                points: team.totalPoints,
                games: team.gamesPlayed
            });
        });
        
        // Convert to array and sort by total points
        const rankings = Object.values(codeGroups)
            .filter(group => group.totalPoints > 0)
            .sort((a, b) => b.totalPoints - a.totalPoints);
        
        console.log('Overall rankings calculated:', rankings);

        if (rankings.length === 0) {
            table.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">No rankings available yet. Add teams and scores to see rankings.</td></tr>';
            return;
        }

        table.innerHTML = rankings.map((ranking, index) => {
            const rankClass = index < 3 ? `rank-${index + 1}` : '';
            const teamNamesText = ranking.teamNames.join(', ');
            const teamDetailsText = ranking.teamDetails.map(td => `${td.name} (${td.points}pts)`).join(', ');
            return `
                <tr>
                    <td class="${rankClass}">${index + 1}</td>
                    <td>
                        <strong>${ranking.code}</strong>
                        <br><small style="color: var(--text-muted);">${ranking.teamCount} team(s): ${teamNamesText}</small>
                        <br><small style="color: var(--text-muted); font-size: 0.75rem;">${teamDetailsText}</small>
                    </td>
                    <td><strong>${ranking.totalPoints}</strong></td>
                    <td>${ranking.totalGamesPlayed}</td>
                </tr>
            `;
        }).join('');
    }

    updateLeaderboards() {
        const table = document.getElementById('leaderboardTable');
        if (!table) return;
        
        if (this.teams.length === 0) {
            table.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No teams registered yet</td></tr>';
            return;
        }

        const filterGameId = this.leaderboardFilterGameId;
        const teamRankings = this.teams.map(team => {
            // Get regular game scores
            const relevantScores = this.scores.filter(s => s.team_id === team.id && (!filterGameId || s.game_id === filterGameId));
            const gamePoints = relevantScores.reduce((sum, score) => sum + (score.points || 0), 0);
            const gameGamesPlayed = relevantScores.length;
            
            // Get judge scores (always included in total, not filtered by game)
            const teamJudgeScores = this.judgeScores.filter(js => 
                js.teamId === team.id || js.team_id === team.id || js.teamId_alt === team.id
            );
            
            // Group judge scores by game_id
            const judgeScoresByGame = {};
            teamJudgeScores.forEach(judgeScore => {
                const gameId = judgeScore.game_id || judgeScore.gameId || 'no_game';
                if (!judgeScoresByGame[gameId]) {
                    judgeScoresByGame[gameId] = [];
                }
                judgeScoresByGame[gameId].push(judgeScore);
            });
            
            // Calculate final score for each game using the appropriate formula
            let judgePoints = 0;
            let judgeGamesPlayed = 0;
            Object.entries(judgeScoresByGame).forEach(([gameId, gameScores]) => {
                if (gameId !== 'no_game') {
                    const finalScore = this.calculateGameFinalScore(parseInt(gameId), gameScores);
                    judgePoints += finalScore;
                    judgeGamesPlayed += 1;
                }
            });
            
            // Calculate total points (game points + judge final scores)
            const displayTotalPoints = gamePoints + judgePoints;
            const displayGamesPlayed = gameGamesPlayed + judgeGamesPlayed;
            
            return {
                ...team,
                totalPoints: displayTotalPoints,
                gamesPlayed: displayGamesPlayed,
                gameDetails: this.getTeamGameDetails(team.id, filterGameId)
            };
        }).sort((a, b) => b.totalPoints - a.totalPoints);

        table.innerHTML = teamRankings.map((team, index) => {
            const rankClass = index < 3 ? `rank-${index + 1}` : '';
            return `
                <tr>
                    <td class="${rankClass}">${index + 1}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${team.color || '#2563eb'};"></div>
                            <div>
                                <div style="font-weight: 600;">${team.name}</div>
                                <div style="font-size: 0.8rem; color: #2563eb; font-weight: 600; background: rgba(37, 99, 235, 0.1); padding: 2px 8px; border-radius: 12px; display: inline-block; margin-top: 2px;">
                                    <i class="fas fa-tag" style="margin-right: 3px; font-size: 0.7rem;"></i>${team.code || 'N/A'}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><strong>${team.totalPoints}</strong></td>
                    <td>${team.gamesPlayed}</td>
                    <td>${team.gameDetails}</td>
                </tr>
            `;
        }).join('');
        
        // Show event details if a specific game is selected
        if (filterGameId) {
            const selectedGame = this.games.find(g => g.id == filterGameId);
            if (selectedGame) {
                // Reload judge scores to ensure we have latest data
                this.loadJudgeScores().then(() => {
                    setTimeout(() => {
                        this.renderEventDetailsInLeaderboard(selectedGame);
                    }, 100);
                });
            }
        } else {
            // Hide event details if no game selected
            const eventDetailsContainer = document.getElementById('eventDetailsContainer');
            if (eventDetailsContainer) {
                eventDetailsContainer.remove();
            }
        }
        
        // Also update overall rankings when leaderboards update
        this.renderOverallRankings();
    }

    renderEventDetailsInLeaderboard(game) {
        console.log('Rendering event details for game:', game);
        console.log('All judge scores:', this.judgeScores);
        console.log('Looking for game_id:', game.id, 'Type:', typeof game.id);
        
        // Remove existing event details if any
        let eventDetailsContainer = document.getElementById('eventDetailsContainer');
        if (eventDetailsContainer) {
            eventDetailsContainer.remove();
        }
        
        // Get all judge scores for this game - try multiple field names and type conversions
        const gameJudgeScores = this.judgeScores.filter(js => {
            const jsGameId = js.game_id || js.gameId;
            // Try both string and number comparison
            return jsGameId == game.id || jsGameId == parseInt(game.id) || parseInt(jsGameId) == game.id || parseInt(jsGameId) == parseInt(game.id);
        });
        
        console.log('Game judge scores found:', gameJudgeScores.length);
        console.log('Game judge scores:', gameJudgeScores);
        
        if (gameJudgeScores.length === 0) {
            console.log('No judge scores found for this game');
            return; // Don't show anything if no scores
        }
        
        // Find the leaderboard container
        const leaderboardContainer = document.querySelector('.leaderboards-container');
        if (!leaderboardContainer) {
            console.log('Leaderboard container not found');
            return;
        }
        
        // Create new container
        eventDetailsContainer = document.createElement('div');
        eventDetailsContainer.id = 'eventDetailsContainer';
        eventDetailsContainer.style.cssText = 'margin-top: 30px; padding: 25px; background: #f8fafc; border-radius: 12px; border: 2px solid #e2e8f0;';
        
        // Parse points system
        const pointsSystem = typeof game.points_system === 'string' ? 
            JSON.parse(game.points_system) : 
            (game.points_system || {});
        
        const eventType = game.judge_event_type || (game.category === 'judge' ? 'Event' : 'Sport');
        let html = `
            <h3 style="margin-bottom: 20px; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-trophy" style="color: #2563eb;"></i>
                Final Leaderboards for ${game.name} (${eventType})
            </h3>
        `;
        
        // Check if this is a Beauty Pageant with categories
        if (pointsSystem.type === 'beauty_pageant' && pointsSystem.categories && pointsSystem.categories.length > 0) {
            html += this.renderBeautyPageantCategories(game, gameJudgeScores, pointsSystem.categories);
        } else if (game.category === 'judge' && pointsSystem.criteria && pointsSystem.criteria.length > 0) {
            // Regular judge event with criteria
            html += this.renderJudgeEventCategories(game, gameJudgeScores, pointsSystem.criteria);
        } else {
            html += '<p style="color: var(--text-muted);">This event does not use category-based scoring.</p>';
        }
        
        // Participant-specific scores section removed
        
        eventDetailsContainer.innerHTML = html;
        
        // Insert after the Event Average Leaderboard section
        const judgeAverageSection = document.querySelector('.leaderboard-table-container:last-of-type');
        if (judgeAverageSection && judgeAverageSection.parentNode) {
            judgeAverageSection.parentNode.insertBefore(eventDetailsContainer, judgeAverageSection.nextSibling);
        } else if (leaderboardContainer) {
            leaderboardContainer.appendChild(eventDetailsContainer);
        }
        
        console.log('Event details container added to page');
    }

    renderBeautyPageantCategories(game, gameJudgeScores, categories) {
        // Group scores by team
        const scoresByTeam = {};
        gameJudgeScores.forEach(score => {
            const teamId = score.team_id || score.teamId || score.teamId_alt;
            if (!teamId) return;
            
            if (!scoresByTeam[teamId]) {
                scoresByTeam[teamId] = [];
            }
            scoresByTeam[teamId].push(score);
        });
        
        // Calculate category scores for each team
        const teamCategoryScores = {};
        Object.keys(scoresByTeam).forEach(teamId => {
            const team = this.teams.find(t => t.id == teamId);
            if (!team) return;
            
            teamCategoryScores[teamId] = {
                team: team,
                categories: {}
            };
            
            // Calculate score for each category
            categories.forEach((category, catIdx) => {
                const categoryScores = this.calculateCategoryScoreForTeam(
                    teamId, 
                    category, 
                    catIdx, 
                    categories, 
                    gameJudgeScores
                );
                teamCategoryScores[teamId].categories[catIdx] = {
                    categoryName: category.name || `Category ${catIdx + 1}`,
                    categoryPercentage: category.categoryPercentage || 0,
                    score: categoryScores
                };
            });
        });
        
        // Render category breakdown table
        let html = '<div style="margin-bottom: 25px;"><h4 style="margin-bottom: 15px; color: #475569; font-size: 1.1rem;">Category Breakdown</h4>';
        html += '<div style="overflow-x: auto;"><table class="excel-table">';
        html += '<thead><tr><th>Rank</th><th>Team</th>';
        
        categories.forEach((category, idx) => {
            html += `<th>${category.name || `Category ${idx + 1}`}<br><small style="font-weight: normal; color: #64748b;">${category.categoryPercentage || 0}%</small></th>`;
        });
        html += '<th>Final Score</th></tr></thead><tbody>';
        
        // Sort teams by final score
        const sortedTeams = Object.keys(teamCategoryScores).sort((a, b) => {
            const scoreA = Object.values(teamCategoryScores[a].categories).reduce((sum, cat) => sum + (cat.score || 0), 0);
            const scoreB = Object.values(teamCategoryScores[b].categories).reduce((sum, cat) => sum + (cat.score || 0), 0);
            return scoreB - scoreA;
        });
        
        // Calculate max scores for each category to normalize progress bars
        // AND find the category leader (team with highest score in each category)
        const maxCategoryScores = {};
        const categoryLeaders = {}; // {catIdx: teamId with highest score}
        categories.forEach((category, catIdx) => {
            let maxScore = 0;
            let leaderTeamId = null;
            sortedTeams.forEach(teamId => {
                const catData = teamCategoryScores[teamId].categories[catIdx];
                if (catData && catData.score > maxScore) {
                    maxScore = catData.score;
                    leaderTeamId = teamId;
                }
            });
            maxCategoryScores[catIdx] = maxScore || 1; // Avoid division by zero
            categoryLeaders[catIdx] = leaderTeamId; // Team with highest score in this category
        });
        
        sortedTeams.forEach((teamId, rank) => {
            const teamData = teamCategoryScores[teamId];
            const finalScore = Object.values(teamData.categories).reduce((sum, cat) => sum + (cat.score || 0), 0);
            const rankClass = rank < 3 ? `rank-${rank + 1}` : '';
            const isLeader = rank === 0;
            
            html += `<tr>`;
            html += `<td class="${rankClass}">${rank + 1}</td>`;
            html += `<td>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${teamData.team.color || '#2563eb'};"></div>
                    <div>
                        <strong>${teamData.team.name}</strong><br>
                        <small style="color: #64748b;">${teamData.team.code || 'N/A'}</small>
                    </div>
                </div>
            </td>`;
            
            categories.forEach((category, catIdx) => {
                const catData = teamData.categories[catIdx];
                const score = catData.score || 0;
                const maxScore = maxCategoryScores[catIdx];
                const percentage = maxScore > 0 ? (score / maxScore) * 100 : 0;
                // Only show "Leading" for the team with the highest score in THIS specific category
                const isCategoryLeader = categoryLeaders[catIdx] === teamId;
                
                // Uniform bar style; only the top team in each category
                // gets the crown + "Leading" label.
                    html += `<td style="padding: 8px;">
                        <div style="position: relative;">
                            <div style="background: #e2e8f0; border-radius: 8px; height: 24px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%); height: 100%; width: ${percentage}%; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: width 0.3s ease;">
                                    <span style="color: white; font-weight: 600; font-size: 0.85rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${score.toFixed(2)}</span>
                                </div>
                            </div>
                        ${isCategoryLeader ? `<div style="position: absolute; top: -18px; left: 0; font-size: 0.7rem; color: #2563eb; font-weight: 600;">
                            <i class="fas fa-crown" style="margin-right: 3px;"></i>Leading
                        </div>` : ''}
                        </div>
                    </td>`;
            });
            
            // Final score - show for all teams; highlight leader
            if (isLeader) {
                html += `<td style="text-align: center; padding: 8px;">
                    <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 10px 15px; border-radius: 8px; font-weight: 700; font-size: 1.05em; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);">
                        ${finalScore.toFixed(2)}
                    </div>
                </td>`;
            } else {
                html += `<td style="text-align: center; padding: 8px;">
                    <div style="padding: 6px 10px; border-radius: 999px; background: #e2e8f0; color: #1e293b; font-weight: 600; font-size: 0.9rem;">
                        ${finalScore.toFixed(2)}
                    </div>
                </td>`;
            }
            html += `</tr>`;
        });
        
        html += '</tbody></table></div></div>';

        // Add participant-type-specific category breakdowns
        html += this.renderParticipantTypeCategoryBreakdown(game, gameJudgeScores, categories, teamCategoryScores);

        // After the main breakdown table, show the winner of each category
        html += this.renderBeautyCategoryWinners(game, gameJudgeScores, categories, teamCategoryScores);

        return html;
    }

    /**
     * Render participant-type-specific category breakdowns (Muse, Escort, Pair)
     * Shows individual participant scores per category
     */
    renderParticipantTypeCategoryBreakdown(game, gameJudgeScores, categories, teamCategoryScores) {
        // Check if there are participant-type-specific scores
        const hasParticipantTypes = gameJudgeScores.some(js => 
            js.participant_type === 'muse' || js.participant_type === 'escort' || js.participant_type === 'pair' || js.participant_type === 'single'
        );
        
        if (!hasParticipantTypes) return '';

        // Group scores by participant type and category
        const participantTypeScores = {
            muse: {},
            escort: {},
            pair: {},
            single: {}
        };

        categories.forEach((category, catIdx) => {
            ['muse', 'escort', 'pair', 'single'].forEach(pt => {
                participantTypeScores[pt][catIdx] = {};
            });
        });

        // Check which participant types have scores (we'll calculate later)
        gameJudgeScores.forEach(score => {
            const participantType = score.participant_type || 'single';
            if (!['muse', 'escort', 'pair', 'single'].includes(participantType)) return;

            const teamId = score.team_id || score.teamId || score.teamId_alt;
            if (!teamId) return;

            const d = score.details_json;
            if (!d || d.type !== 'beauty_pageant' || !d.categories) return;

            d.categories.forEach(catDetail => {
                const catIdx = catDetail.index;
                if (catIdx === undefined || !categories[catIdx]) return;

                // Mark that this participant type has data for this category/team
                if (!participantTypeScores[participantType][catIdx][teamId]) {
                    participantTypeScores[participantType][catIdx][teamId] = true;
                }
            });
        });

        // Build HTML
        let html = '<div style="margin-top: 30px; margin-bottom: 25px;">';
        html += '<h4 style="margin-bottom: 15px; color: #1e293b; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">';
        html += '<i class="fas fa-users" style="color: #6366f1;"></i>Participant-Specific Category Breakdown';
        html += '</h4>';

        // Render breakdown per participant type
        const participantTypeLabels = {
            muse: { label: 'Muse (Female)', icon: 'fa-venus', color: '#ec4899' },
            escort: { label: 'Escort (Male)', icon: 'fa-mars', color: '#3b82f6' },
            pair: { label: 'Pair', icon: 'fa-heart', color: '#f59e0b' },
            single: { label: 'Single', icon: 'fa-user', color: '#10b981' }
        };

        ['muse', 'escort', 'pair', 'single'].forEach(pt => {
            const ptLabel = participantTypeLabels[pt];
            let hasData = false;

            // Check if this participant type has any scores
            categories.forEach((category, catIdx) => {
                if (Object.keys(participantTypeScores[pt][catIdx]).length > 0) {
                    hasData = true;
                }
            });

            if (!hasData) return;

            html += `<div style="margin-bottom: 25px; padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">`;
            html += `<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">`;
            html += `<i class="fas ${ptLabel.icon}" style="color: ${ptLabel.color}; font-size: 1.1rem;"></i>`;
            html += `<h5 style="margin: 0; color: #1e293b; font-size: 1rem; font-weight: 600;">${ptLabel.label}</h5>`;
            html += `</div>`;

            html += '<div style="overflow-x: auto;"><table class="excel-table">';
            html += '<thead><tr><th>Rank</th><th>Team</th>';

            categories.forEach((category, idx) => {
                html += `<th>${category.name || `Category ${idx + 1}`}<br><small style="font-weight: normal; color: #64748b;">${category.categoryPercentage || 0}%</small></th>`;
            });
            html += '<th>Total Score</th></tr></thead><tbody>';

            // Calculate scores per team for this participant type
            const teamScoresForType = {};
            Object.keys(teamCategoryScores).forEach(teamId => {
                let totalScore = 0;
                const categoryScores = {};

                categories.forEach((category, catIdx) => {
                    // Filter gameJudgeScores to only include scores for this participant type and team
                    const filteredScores = gameJudgeScores.filter(s => {
                        const sTeamId = s.team_id || s.teamId || s.teamId_alt;
                        const sParticipantType = s.participant_type || 'single';
                        return sTeamId == teamId && sParticipantType === pt;
                    });

                    if (filteredScores.length > 0) {
                        // Calculate category score using only this participant type's scores
                        // We need to calculate directly from details_json for this specific category
                        const categoryScore = this.calculateParticipantTypeCategoryScore(
                            filteredScores,
                            category,
                            catIdx
                        );
                        categoryScores[catIdx] = categoryScore;
                        totalScore += categoryScore;
                    } else {
                        categoryScores[catIdx] = 0;
                    }
                });

                if (totalScore > 0 || Object.values(categoryScores).some(s => s > 0)) {
                    teamScoresForType[teamId] = {
                        team: teamCategoryScores[teamId].team,
                        categoryScores: categoryScores,
                        totalScore: totalScore
                    };
                }
            });

            // Determine the leading team per category for this participant type
            const categoryLeaders = {}; // {catIdx: teamId}
            categories.forEach((category, catIdx) => {
                let maxScore = -Infinity;
                let leaderId = null;

                Object.keys(teamScoresForType).forEach(teamId => {
                    const score = teamScoresForType[teamId].categoryScores[catIdx] || 0;
                    if (score > maxScore) {
                        maxScore = score;
                        leaderId = teamId;
                    }
                });

                // Only mark a leader if the best score is actually > 0
                if (leaderId !== null && maxScore > 0) {
                    categoryLeaders[catIdx] = leaderId;
                }
            });

            // Sort by total score
            const sortedTeams = Object.keys(teamScoresForType).sort((a, b) => {
                return teamScoresForType[b].totalScore - teamScoresForType[a].totalScore;
            });

            if (sortedTeams.length === 0) {
                html += `<tr><td colspan="${categories.length + 3}" style="text-align: center; padding: 20px; color: #64748b;">No ${ptLabel.label} scores available</td></tr>`;
            } else {
                sortedTeams.forEach((teamId, rank) => {
                    const teamData = teamScoresForType[teamId];
                    html += `<tr>`;
                    html += `<td>${rank + 1}</td>`;
                    html += `<td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${teamData.team.color || '#2563eb'};"></div>
                            <div>
                                <strong>${teamData.team.name}</strong><br>
                                <small style="color: #64748b;">${teamData.team.code || 'N/A'}</small>
                            </div>
                        </div>
                    </td>`;

                    categories.forEach((category, catIdx) => {
                        const score = teamData.categoryScores[catIdx] || 0;
                        const isCategoryLeader = categoryLeaders[catIdx] === teamId;
                        html += `<td style="text-align: center; padding: 8px;">
                            <div style="display: inline-block; position: relative; min-width: 72px; padding-top: 16px;">
                                ${isCategoryLeader ? `<div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); font-size: 0.7rem; color: ${ptLabel.color}; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <i class="fas fa-crown"></i><span>Leading</span>
                                </div>` : ''}
                            <div style="padding: 6px 10px; border-radius: 6px; background: ${score > 0 ? '#e0e7ff' : '#f1f5f9'}; color: #1e293b; font-weight: 600; font-size: 0.9rem;">
                                ${score.toFixed(2)}
                                </div>
                            </div>
                        </td>`;
                    });

                    html += `<td style="text-align: center; padding: 8px;">
                        <div style="padding: 8px 12px; border-radius: 8px; background: linear-gradient(135deg, ${ptLabel.color} 0%, ${ptLabel.color}dd 100%); color: white; font-weight: 700; font-size: 0.95rem;">
                            ${teamData.totalScore.toFixed(2)}
                        </div>
                    </td>`;
                    html += `</tr>`;
                });
            }

            html += '</tbody></table></div>';
            html += `</div>`;
        });

        html += '</div>';
        return html;
    }

    /**
     * Render a winner card for each Beauty Pageant category with its criteria breakdown.
     *
     * This answers the requirement: "display the winner of each category and its criterias per category".
     */
    renderBeautyCategoryWinners(game, gameJudgeScores, categories, teamCategoryScores, options = {}) {
        if (!categories || categories.length === 0) return '';

        // Safety check
        if (!teamCategoryScores || Object.keys(teamCategoryScores).length === 0) return '';

        const rawMode = options.raw === true; // used for Judge tab printable view

        let html = '<div style="margin-top: 10px; margin-bottom: 25px;">';
        html += '<h4 style="margin-bottom: 15px; color: #1e293b; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">';
        if (rawMode) {
            html += '<i class="fas fa-list-alt" style="color: #2563eb;"></i>Category Criteria Breakdown';
        } else {
        html += '<i class="fas fa-crown" style="color: #f59e0b;"></i>Category Winners & Criteria Breakdown';
        }
        html += '</h4>';

        categories.forEach((category, catIdx) => {
            // Find winner team for this category
            let winnerTeamId = null;
            let bestScore = -Infinity;

            Object.keys(teamCategoryScores).forEach(teamId => {
                const catData = teamCategoryScores[teamId].categories[catIdx];
                if (!catData) return;
                const score = catData.score || 0;
                if (score > bestScore) {
                    bestScore = score;
                    winnerTeamId = teamId;
                }
            });

            if (!winnerTeamId) return;

            const winnerData = teamCategoryScores[winnerTeamId];
            const team = winnerData.team;
            const winnerCategoryData = winnerData.categories[catIdx];

            // Get criteria details for the winner in this category
            const criteriaDetails = this.getBeautyCategoryCriteriaDetailsForTeam(
                winnerTeamId,
                category,
                catIdx,
                categories,
                gameJudgeScores
            );

            const categoryName = category.name || `Category ${catIdx + 1}`;
            const categoryPercentage = category.categoryPercentage || 0;
            const finalCategoryScore = winnerCategoryData && winnerCategoryData.score ? winnerCategoryData.score : 0;

            html += `<div style="margin-bottom: 16px; padding: 16px; border-radius: 10px; background: #f9fafb; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(15,23,42,0.04);">`;
            html += `<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">`;
            html += `<div style="display: flex; align-items: center; gap: 10px;">`;
            html += `<div style="width: 32px; height: 32px; border-radius: 999px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #92400e;">`;
            html += `<i class="fas fa-trophy"></i>`;
            html += `</div>`;
            html += `<div>`;
            html += `<div style="font-weight: 700; color: #111827;">${categoryName}</div>`;
            if (rawMode) {
                html += `<div style="font-size: 0.8rem; color: #6b7280;">Score Points: ${categoryPercentage.toFixed(0)}</div>`;
            } else {
            html += `<div style="font-size: 0.8rem; color: #6b7280;">Score Points: ${categoryPercentage.toFixed(0)} • Winner</div>`;
            }
            html += `</div>`;
            html += `</div>`; // left

            if (!rawMode) {
            html += `<div style="text-align: right;">`;
            html += `<div style="font-size: 0.8rem; color: #6b7280; margin-bottom: 4px;">Category Score</div>`;
            html += `<div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: linear-gradient(135deg,#2563eb,#1e40af); color: white; font-weight: 700;">`;
            html += `<i class="fas fa-star"></i><span>${finalCategoryScore.toFixed(2)}</span>`;
            html += `</div>`;
            html += `</div>`; // right
            }
            html += `</div>`; // header

            // Winner team info
            html += `<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">`;
            html += `<div style="width: 14px; height: 14px; border-radius: 50%; background-color: ${team.color || '#2563eb'};"></div>`;
            html += `<div>`;
            html += `<div style="font-weight: 600; color: #111827;">${team.name}</div>`;
            html += `<div style="font-size: 0.8rem; color: #6b7280;">${team.code || 'N/A'}</div>`;
            html += `</div>`;
            html += `</div>`;

            // Criteria table
            if (criteriaDetails.length > 0) {
                html += `<div style="overflow-x: auto; margin-top: 4px;">`;
                html += `<table class="excel-table" style="background: white; margin: 0;">`;
                html += `<thead>`;
                html += `<tr>`;
                html += `<th>Criteria</th>`;
                html += `<th>Score Points</th>`;
                html += `<th>Judge Scores</th>`;
                html += `</tr>`;
                html += `</thead>`;
                html += `<tbody>`;

                criteriaDetails.forEach(detail => {
                    html += `<tr>`;
                    html += `<td style="font-weight: 600; color: #1e293b;">${detail.name}</td>`;
                    html += `<td style="text-align:center; font-weight: 600; color: #475569;">${detail.percentage.toFixed(0)}</td>`;
                    html += `<td style="padding: 10px; white-space: normal; word-wrap: break-word; line-height: 1.6;">${detail.rawScoresLabel}</td>`;
                    html += `</tr>`;
                });

                html += `</tbody>`;
                html += `</table>`;
                html += `</div>`;
            } else {
                html += `<div style="font-size: 0.85rem; color: #6b7280;">No criteria details available for this category.</div>`;
            }

            html += `</div>`; // card
        });

        html += '</div>';
        return html;
    }

    /**
     * Render raw category criteria breakdown for Beauty Pageant events (for Judge tab / printable).
     */
    renderJudgeCategoryCriteriaBreakdown() {
        const container = document.getElementById('judgeCategoryBreakdownContent');
        if (!container) return;

        if (!this.games || this.games.length === 0) {
            container.innerHTML = '<div style="padding: 20px; color: var(--text-muted);">No events available.</div>';
            return;
        }

        // Filter Beauty Pageant judge events
        const beautyGames = this.games.filter(game => {
            if ((game.category || 'scorer') !== 'judge') return false;
            let ps = game.points_system;
            if (!ps) return false;
            if (typeof ps === 'string') {
                try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
            }
            return ps.type === 'beauty_pageant' && ps.categories && ps.categories.length > 0;
        });

        if (beautyGames.length === 0) {
            container.innerHTML = '<div style="padding: 20px; color: var(--text-muted);">No Beauty Pageant events found yet.</div>';
            return;
        }

        let html = '';

        beautyGames.forEach(game => {
            let ps = game.points_system;
            if (typeof ps === 'string') {
                try { ps = JSON.parse(ps); } catch (e) { ps = {}; }
            }
            const categories = (ps && ps.categories) ? ps.categories : [];
            if (!categories.length) return;

            // All judge scores for this game
            const gameJudgeScores = this.judgeScores.filter(js => {
                const jsGameId = js.game_id || js.gameId;
                return jsGameId == game.id;
            });
            if (!gameJudgeScores.length) return;

            // Rebuild teamCategoryScores (same logic as renderEventDetailsInLeaderboard)
            const scoresByTeam = {};
            gameJudgeScores.forEach(score => {
                const teamId = score.team_id || score.teamId || score.teamId_alt;
                if (!teamId) return;
                if (!scoresByTeam[teamId]) {
                    scoresByTeam[teamId] = [];
                }
                scoresByTeam[teamId].push(score);
            });

            const teamCategoryScores = {};
            Object.keys(scoresByTeam).forEach(teamId => {
                const team = this.teams.find(t => t.id == teamId);
                if (!team) return;

                teamCategoryScores[teamId] = {
                    team: team,
                    categories: {}
                };

                categories.forEach((category, catIdx) => {
                    const categoryScores = this.calculateCategoryScoreForTeam(
                        teamId,
                        category,
                        catIdx,
                        categories,
                        gameJudgeScores
                    );
                    teamCategoryScores[teamId].categories[catIdx] = {
                        categoryName: category.name || ('Category ' + (catIdx + 1)),
                        categoryPercentage: category.categoryPercentage || 0,
                        score: categoryScores
                    };
                });
            });

            html += `<section class="judge-print-block" style="margin-bottom: 30px; page-break-inside: avoid;">`;
            html += `<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">`;
            html += `<div>`;
            html += `<h3 style="margin:0 0 4px 0; font-size:1.1rem; color:#111827;"><i class="fas fa-crown"></i> ${game.name}</h3>`;
            html += `<p style="margin:0; font-size:0.85rem; color:#6b7280;">Beauty Pageant – Category Criteria Breakdown (Raw)</p>`;
            html += `</div>`;

            // Assigned judge names for signature lines
            const judgeNames = this.getJudgeNamesForGame(game, gameJudgeScores);
            if (judgeNames.length > 0) {
                html += `<div style="font-size:0.8rem; color:#4b5563; text-align:right;">Assigned Judges:<br>${judgeNames.join(', ')}</div>`;
            }

            html += `</div>`; // header

            // Raw category breakdown (no winners or category scores) for ALL teams
            html += '<div style="margin-top: 10px; margin-bottom: 10px;">';
            html += '<h4 style="margin-bottom: 10px; color: #1e293b; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">';
            html += '<i class="fas fa-list-alt" style="color: #2563eb;"></i>Category Criteria (All Teams)</h4>';

            categories.forEach((category, catIdx) => {
                const categoryName = category.name || ('Category ' + (catIdx + 1));
                const categoryPercentage = category.categoryPercentage || 0;

                html += '<div style="margin-bottom: 14px; padding: 12px; border-radius: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">';
                html += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">';
                html += '<div>';
                html += '<div style="font-weight:700; color:#111827;">' + categoryName + '</div>';
                html += '<div style="font-size:0.8rem; color:#6b7280;">Score Points: ' + categoryPercentage.toFixed(0) + '</div>';
                html += '</div>';
                html += '</div>'; // header

                // For each team, render its criteria table (if it has scores)
                Object.keys(teamCategoryScores).forEach(teamId => {
                    const teamData = teamCategoryScores[teamId];
                    const team = teamData.team;

                    const criteriaDetails = this.getBeautyCategoryCriteriaDetailsForTeam(
                        teamId,
                        category,
                        catIdx,
                        categories,
                        gameJudgeScores
                    );

                    if (!criteriaDetails || criteriaDetails.length === 0) {
                        return;
                    }

                    html += '<div style="margin-top:6px; margin-bottom:8px;">';
                    html += '<div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">';
                    html += '<div style="width: 10px; height: 10px; border-radius: 50%; background-color: ' + (team.color || '#2563eb') + ';"></div>';
                    html += '<div>';
                    html += '<div style="font-weight:600; color:#111827; font-size:0.9rem;">' + team.name + '</div>';
                    html += '<div style="font-size:0.75rem; color:#6b7280;">' + (team.code || 'N/A') + '</div>';
                    html += '</div>';
                    html += '</div>';

                    html += '<div style="overflow-x:auto; margin-top:2px;">';
                    html += '<table class="excel-table" style="background:white; margin:0;">';
                    html += '<thead><tr>';
                    html += '<th>Criteria</th><th>Score Points</th><th>Judge Scores</th>';
                    html += '</tr></thead><tbody>';

                    criteriaDetails.forEach(detail => {
                        html += '<tr>';
                        html += '<td style="font-weight:600; color:#1e293b;">' + detail.name + '</td>';
                        html += '<td style="text-align:center; font-weight:600; color:#475569;">' + detail.percentage.toFixed(0) + '</td>';
                        html += '<td style="padding:10px; white-space:normal; word-wrap:break-word; line-height:1.6;">' + (detail.rawScoresLabel || '') + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    html += '</div>'; // team block
                });

                html += '</div>'; // category block
            });

            html += '</div>'; // wrapper

            // Signature lines
            if (judgeNames.length > 0) {
                html += `<div style="margin-top:16px; padding-top:12px; border-top:1px dashed #cbd5f5;">`;
                html += `<h4 style="margin:0 0 8px 0; font-size:0.9rem; color:#111827;"><i class="fas fa-pen"></i> Judge Signatures</h4>`;
                html += `<div style="display:flex; flex-wrap:wrap; gap:20px;">`;
                judgeNames.forEach(name => {
                    html += `<div style="flex:1 1 220px;">`;
                    html += `<div style="border-bottom:1px solid #cbd5f5; height:26px;"></div>`;
                    html += `<div style="margin-top:4px; font-size:0.8rem; color:#4b5563;">${name}</div>`;
                    html += `</div>`;
                });
                html += `</div></div>`;
            }

            html += `</section>`;
        });

        if (!html) {
            container.innerHTML = '<div style="padding: 20px; color: var(--text-muted);">No Beauty Pageant breakdowns available yet.</div>';
        } else {
            container.innerHTML = html;
        }
    }

    getJudgeNamesForGame(game, gameJudgeScores) {
        const names = new Set();

        // From authorized_judges field if available
        if (game.authorized_judges) {
            let auth = game.authorized_judges;
            if (typeof auth === 'string') {
                try {
                    const parsed = JSON.parse(auth);
                    if (Array.isArray(parsed)) auth = parsed;
                } catch (e) {
                    auth = auth.split(',').map(s => s.trim()).filter(Boolean);
                }
            }
            if (Array.isArray(auth)) {
                auth.forEach(n => {
                    if (n && typeof n === 'string') names.add(n.trim());
                });
            }
        }

        // From actual judge scores
        (gameJudgeScores || []).forEach(js => {
            const name = js.judge_name || js.judgeName;
            if (name && typeof name === 'string') {
                names.add(name.trim());
            }
        });

        return Array.from(names);
    }

    /**
     * Helper to compute per-criteria averages and contributions for a given team and Beauty category.
     */
    getBeautyCategoryCriteriaDetailsForTeam(teamId, category, categoryIndex, allCategories, allGameScores) {
        const details = [];

        if (!category || !category.criteria || category.criteria.length === 0) {
            return details;
        }

        // Get scores for this team
        const teamScores = allGameScores.filter(s => {
            const sTeamId = s.team_id || s.teamId || s.teamId_alt;
            return sTeamId == teamId;
        });

        if (teamScores.length === 0) return details;

        const beautyScores = teamScores.filter(s => s.details_json && s.details_json.type === 'beauty_pageant');

        // If we have structured Beauty JSON, use it
        if (beautyScores.length > 0) {
            const criteriaStats = {}; // {critIdx: {judgeScores: [{judge, score}], points, name}}

            beautyScores.forEach(score => {
                const d = score.details_json;
                const judgeName = score.judge_name || score.judgeName || 'Unknown';
                const participantType = score.participant_type || 'single';
                const catDetail = (d.categories || []).find(c => c.index === categoryIndex);
                if (!catDetail || !catDetail.criteria) return;

                catDetail.criteria.forEach(c => {
                    const critIdx = c.index;
                    const def = category.criteria[critIdx] || {};
                    if (!criteriaStats[critIdx]) {
                        criteriaStats[critIdx] = {
                            judgeScores: [],
                            points: def.percentage || def.maxPoints || 0,
                            name: def.name || `Criteria ${critIdx + 1}`
                        };
                    }
                    criteriaStats[critIdx].judgeScores.push({
                        judge: judgeName,
                        participantType: participantType,
                        score: Number(c.score || 0)
                    });
                });
            });

            Object.keys(criteriaStats).forEach(key => {
                const stat = criteriaStats[key];
                const judgeScores = stat.judgeScores;
                const scores = judgeScores.map(js => js.score);
                const avgScore = scores.length > 0
                    ? scores.reduce((sum, v) => sum + v, 0) / scores.length
                    : 0;

                // Group scores by participant type first, then by judge name
                const participantTypeGroups = {}; // {participantType: {judgeName: [scores]}}
                judgeScores.forEach(js => {
                    const judgeName = js.judge;
                    const pt = js.participantType || 'single';
                    if (!participantTypeGroups[pt]) {
                        participantTypeGroups[pt] = {};
                    }
                    if (!participantTypeGroups[pt][judgeName]) {
                        participantTypeGroups[pt][judgeName] = [];
                    }
                    participantTypeGroups[pt][judgeName].push(js.score);
                });

                // Define participant type order and styling
                const ptOrder = ['muse', 'escort', 'pair', 'single'];
                const ptStyles = {
                    muse: { label: 'Muse', color: '#ec4899', bgColor: '#fce7f3', borderColor: '#f472b6' },
                    escort: { label: 'Escort', color: '#3b82f6', bgColor: '#dbeafe', borderColor: '#60a5fa' },
                    pair: { label: 'Pair', color: '#f59e0b', bgColor: '#fef3c7', borderColor: '#fbbf24' },
                    single: { label: 'Single', color: '#10b981', bgColor: '#d1fae5', borderColor: '#34d399' }
                };

                // Create HTML rows for each participant type
                const participantTypeRows = [];
                ptOrder.forEach(pt => {
                    if (!participantTypeGroups[pt] || Object.keys(participantTypeGroups[pt]).length === 0) return;
                    
                    const style = ptStyles[pt];
                    const judgeBadgesForType = [];
                    
                    // Get all judge names and sort them for consistent display
                    const judgeNames = Object.keys(participantTypeGroups[pt]).sort();
                    
                    judgeNames.forEach(judgeName => {
                        const scoresList = participantTypeGroups[pt][judgeName];
                        const scoresText = scoresList.join(', ');
                        
                        judgeBadgesForType.push(`<span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; margin: 2px; border-radius: 6px; background: linear-gradient(135deg, ${style.bgColor} 0%, ${style.bgColor}dd 100%); border: 1px solid ${style.borderColor}; font-size: 0.8rem;"><strong style="color: ${style.color};">${judgeName}</strong><span style="color: #6b7280; font-size: 0.75rem;">(${style.label}):</span><span style="color: #1e293b; font-weight: 600;">${scoresText}</span></span>`);
                    });
                    
                    if (judgeBadgesForType.length > 0) {
                        participantTypeRows.push(`<div style="margin-bottom: 4px;">${judgeBadgesForType.join(' ')}</div>`);
                    }
                });
                
                const judgeBadgesHtml = participantTypeRows.join('');

                details.push({
                    name: stat.name,
                    percentage: stat.points,
                    avgScore: avgScore,
                    rawScoresLabel: judgeBadgesHtml || '0',
                    contribution: avgScore
                });
            });

            return details;
        }

        // Fallback: legacy path using criteria1..criteriaN
        let globalCriteriaIndex = 1;
        for (let i = 0; i < categoryIndex; i++) {
            globalCriteriaIndex += allCategories[i].criteria.length;
        }

        category.criteria.forEach((criteria, critIdx) => {
            const criteriaNum = globalCriteriaIndex + critIdx;
            const criteriaName = criteria.name || `Criteria ${critIdx + 1}`;
            const criteriaPoints = criteria.percentage || criteria.maxPoints || 0;

            // Collect judge names, participant types, and scores together
            const judgeScorePairs = teamScores.map(s => ({
                judge: s.judge_name || s.judgeName || 'Unknown',
                participantType: s.participant_type || 'single',
                score: parseInt(s[`criteria${criteriaNum}`] || 0)
            }));

            const scores = judgeScorePairs.map(js => js.score);
            const avgScore = scores.length > 0
                ? scores.reduce((sum, s) => sum + s, 0) / scores.length
                : 0;

            // Group scores by participant type first, then by judge name
            const participantTypeGroups = {}; // {participantType: {judgeName: [scores]}}
            judgeScorePairs.forEach(js => {
                const judgeName = js.judge;
                const pt = js.participantType || 'single';
                if (!participantTypeGroups[pt]) {
                    participantTypeGroups[pt] = {};
                }
                if (!participantTypeGroups[pt][judgeName]) {
                    participantTypeGroups[pt][judgeName] = [];
                }
                participantTypeGroups[pt][judgeName].push(js.score);
            });

            // Define participant type order and styling
            const ptOrder = ['muse', 'escort', 'pair', 'single'];
            const ptStyles = {
                muse: { label: 'Muse', color: '#ec4899', bgColor: '#fce7f3', borderColor: '#f472b6' },
                escort: { label: 'Escort', color: '#3b82f6', bgColor: '#dbeafe', borderColor: '#60a5fa' },
                pair: { label: 'Pair', color: '#f59e0b', bgColor: '#fef3c7', borderColor: '#fbbf24' },
                single: { label: 'Single', color: '#10b981', bgColor: '#d1fae5', borderColor: '#34d399' }
            };

            // Create HTML rows for each participant type
            const participantTypeRows = [];
            ptOrder.forEach(pt => {
                if (!participantTypeGroups[pt] || Object.keys(participantTypeGroups[pt]).length === 0) return;
                
                const style = ptStyles[pt];
                const judgeBadgesForType = [];
                
                // Get all judge names and sort them for consistent display
                const judgeNames = Object.keys(participantTypeGroups[pt]).sort();
                
                judgeNames.forEach(judgeName => {
                    const scoresList = participantTypeGroups[pt][judgeName];
                    const scoresText = scoresList.join(', ');
                    
                    judgeBadgesForType.push(`<span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; margin: 2px; border-radius: 6px; background: linear-gradient(135deg, ${style.bgColor} 0%, ${style.bgColor}dd 100%); border: 1px solid ${style.borderColor}; font-size: 0.8rem;"><strong style="color: ${style.color};">${judgeName}</strong><span style="color: #6b7280; font-size: 0.75rem;">(${style.label}):</span><span style="color: #1e293b; font-weight: 600;">${scoresText}</span></span>`);
                });
                
                if (judgeBadgesForType.length > 0) {
                    participantTypeRows.push(`<div style="margin-bottom: 4px;">${judgeBadgesForType.join(' ')}</div>`);
                }
            });
            
            const judgeBadgesHtml = participantTypeRows.join('');

            details.push({
                name: criteriaName,
                percentage: criteriaPoints,
                avgScore: avgScore,
                rawScoresLabel: judgeBadgesHtml || '0',
                contribution: avgScore
            });
        });

        return details;
    }

    renderJudgeEventCategories(game, gameJudgeScores, criteriaList) {
        // Group scores by team
        const scoresByTeam = {};
        gameJudgeScores.forEach(score => {
            const teamId = score.team_id || score.teamId || score.teamId_alt;
            if (!teamId) return;
            
            if (!scoresByTeam[teamId]) {
                scoresByTeam[teamId] = [];
            }
            scoresByTeam[teamId].push(score);
        });
        
        // Calculate criteria scores for each team
        const teamCriteriaScores = {};
        Object.keys(scoresByTeam).forEach(teamId => {
            const team = this.teams.find(t => t.id == teamId);
            if (!team) return;
            
            const teamScores = scoresByTeam[teamId];
            const judgeCount = new Set(teamScores.map(s => s.judge_name || s.judgeName)).size || 1;
            
            // Average each criteria across judges
            const criteriaAverages = {};
            criteriaList.forEach((criteria, idx) => {
                const criteriaNum = idx + 1;
                const sum = teamScores.reduce((acc, s) => acc + (parseInt(s[`criteria${criteriaNum}`] || 0)), 0);
                criteriaAverages[criteriaNum] = sum / judgeCount;
            });
            
            // Calculate weighted final score
            let finalScore = 0;
            criteriaList.forEach((criteria, idx) => {
                const criteriaNum = idx + 1;
                const percentage = criteria.percentage || 0;
                finalScore += (criteriaAverages[criteriaNum] || 0) * (percentage / 100);
            });
            
            teamCriteriaScores[teamId] = {
                team: team,
                criteria: criteriaAverages,
                finalScore: finalScore
            };
        });
        
        // Render criteria breakdown table
        let html = '<div style="margin-bottom: 25px;"><h4 style="margin-bottom: 15px; color: #475569; font-size: 1.1rem;">Criteria Breakdown</h4>';
        html += '<div style="overflow-x: auto;"><table class="excel-table">';
        html += '<thead><tr><th>Rank</th><th>Team</th>';
        
        criteriaList.forEach((criteria, idx) => {
            html += `<th>${criteria.name || `Criteria ${idx + 1}`}<br><small style="font-weight: normal; color: #64748b;">${criteria.percentage || 0}%</small></th>`;
        });
        html += '<th>Final Score</th></tr></thead><tbody>';
        
        // Sort teams by final score
        const sortedTeams = Object.keys(teamCriteriaScores).sort((a, b) => 
            teamCriteriaScores[b].finalScore - teamCriteriaScores[a].finalScore
        );
        
        // Calculate max scores for each criteria to normalize progress bars
        const maxCriteriaScores = {};
        criteriaList.forEach((criteria, idx) => {
            const criteriaNum = idx + 1;
            let maxScore = 0;
            sortedTeams.forEach(teamId => {
                const score = teamCriteriaScores[teamId].criteria[criteriaNum] || 0;
                if (score > maxScore) {
                    maxScore = score;
                }
            });
            maxCriteriaScores[criteriaNum] = maxScore || 1; // Avoid division by zero
        });
        
        sortedTeams.forEach((teamId, rank) => {
            const teamData = teamCriteriaScores[teamId];
            const rankClass = rank < 3 ? `rank-${rank + 1}` : '';
            const isLeader = rank === 0;
            
            html += `<tr>`;
            html += `<td class="${rankClass}">${rank + 1}</td>`;
            html += `<td>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${teamData.team.color || '#2563eb'};"></div>
                    <div>
                        <strong>${teamData.team.name}</strong><br>
                        <small style="color: #64748b;">${teamData.team.code || 'N/A'}</small>
                    </div>
                </div>
            </td>`;
            
            criteriaList.forEach((criteria, idx) => {
                const criteriaNum = idx + 1;
                const score = teamData.criteria[criteriaNum] || 0;
                const maxScore = maxCriteriaScores[criteriaNum];
                const percentage = maxScore > 0 ? (score / maxScore) * 100 : 0;
                const isCriteriaLeader = sortedTeams[0] === teamId || score === maxScore;
                
                if (isCriteriaLeader) {
                    // Show progress bar for leader
                    html += `<td style="padding: 8px;">
                        <div style="position: relative;">
                            <div style="background: #e2e8f0; border-radius: 8px; height: 24px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%); height: 100%; width: ${percentage}%; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: width 0.3s ease;">
                                    <span style="color: white; font-weight: 600; font-size: 0.85rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${score.toFixed(2)}</span>
                                </div>
                            </div>
                            <div style="position: absolute; top: -18px; left: 0; font-size: 0.7rem; color: #2563eb; font-weight: 600;">
                                <i class="fas fa-crown" style="margin-right: 3px;"></i>Leader
                            </div>
                        </div>
                    </td>`;
                } else {
                    // Show empty/minimal for non-leaders
                    html += `<td style="padding: 8px;">
                        <div style="background: #f1f5f9; border-radius: 8px; height: 24px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.85rem;">
                            —
                        </div>
                    </td>`;
                }
            });
            
            // Final score - only show for leader
            if (isLeader) {
                html += `<td style="text-align: center; padding: 8px;">
                    <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 10px 15px; border-radius: 8px; font-weight: 700; font-size: 1.1em; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);">
                        ${teamData.finalScore.toFixed(2)}
                    </div>
                </td>`;
            } else {
                html += `<td style="text-align: center; padding: 8px;">
                    <div style="color: #94a3b8; font-size: 0.9rem;">—</div>
                </td>`;
            }
            html += `</tr>`;
        });
        
        html += '</tbody></table></div></div>';
        return html;
    }

    calculateCategoryScoreForTeam(teamId, category, categoryIndex, allCategories, allGameScores) {
        // Get scores for this team
        const teamScores = allGameScores.filter(s => {
            const sTeamId = s.team_id || s.teamId || s.teamId_alt;
            return sTeamId == teamId;
        });
        
        if (teamScores.length === 0) return 0;
        
        // If Beauty Pageant JSON details are present, use them for precise scoring
        const beautyScores = teamScores.filter(s => s.details_json && s.details_json.type === 'beauty_pageant');
        if (beautyScores.length > 0 && category && category.criteria && category.criteria.length > 0) {
            const criteriaStats = {}; // {critIdx: {sum, count, points}}
            
            beautyScores.forEach(score => {
                const details = score.details_json;
                const catDetail = (details.categories || []).find(c => c.index === categoryIndex);
                if (!catDetail || !catDetail.criteria) return;
                
                catDetail.criteria.forEach(c => {
                    const critIdx = c.index;
                    const criteriaDef = category.criteria[critIdx] || {};
                    const criteriaPoints = criteriaDef.percentage || criteriaDef.maxPoints || 0;
                    if (!criteriaStats[critIdx]) {
                        criteriaStats[critIdx] = { sum: 0, count: 0, points: criteriaPoints };
                    }
                    criteriaStats[critIdx].sum += Number(c.score || 0);
                    criteriaStats[critIdx].count += 1;
                    if (!criteriaStats[critIdx].points) {
                        criteriaStats[critIdx].points = criteriaPoints;
                    }
                });
            });
            
            let teamRawScore = 0;
            let maxPossibleScore = 0;
            
            Object.keys(criteriaStats).forEach(key => {
                const stat = criteriaStats[key];
                const points = stat.points || 0;
                if (points <= 0 || stat.count === 0) return;
                
                const avgScore = stat.sum / stat.count; // 0..points
                teamRawScore += avgScore;
                maxPossibleScore += points;
            });
            
            const categoryWeight = category.categoryPercentage || 0;
            if (maxPossibleScore > 0) {
                const normalizedCategory = teamRawScore / maxPossibleScore; // 0–1
                return normalizedCategory * categoryWeight;
            }
            return 0;
        }
        
        // Fallback: legacy behaviour using criteria1..criteriaN
        let globalCriteriaIndex = 1;
        for (let i = 0; i < categoryIndex; i++) {
            globalCriteriaIndex += allCategories[i].criteria.length;
        }
        
        let teamRawScore = 0;
        let maxPossibleScore = 0;
        
        category.criteria.forEach((criteria, critIdx) => {
            const criteriaNum = globalCriteriaIndex + critIdx;
            const criteriaPoints = criteria.percentage || criteria.maxPoints || 0;
            
            const teamCriteriaScores = teamScores.map(s => parseInt(s[`criteria${criteriaNum}`] || 0));
            const avgScore = teamCriteriaScores.length > 0
                ? teamCriteriaScores.reduce((sum, s) => sum + s, 0) / teamCriteriaScores.length
                : 0;
            
            const normalized = criteriaPoints > 0 ? (avgScore / criteriaPoints) : 0;
            const criteriaPointsEarned = normalized * criteriaPoints;
            
            teamRawScore += criteriaPointsEarned;
            maxPossibleScore += criteriaPoints;
        });
        
        const categoryWeight = category.categoryPercentage || 0;
        if (maxPossibleScore > 0) {
            const normalizedCategory = teamRawScore / maxPossibleScore; // 0–1
            return normalizedCategory * categoryWeight;
        }
        
        return 0;
    }

    /**
     * Calculate category score for a specific participant type
     * This ensures we only use scores from the filtered participant type records
     */
    calculateParticipantTypeCategoryScore(filteredScores, category, categoryIndex) {
        if (!filteredScores || filteredScores.length === 0) return 0;
        if (!category || !category.criteria || category.criteria.length === 0) return 0;

        const criteriaStats = {}; // {critIdx: {sum, count, points}}

        filteredScores.forEach(score => {
            // Parse details_json if it's a string
            let details = score.details_json;
            if (typeof details === 'string') {
                try {
                    details = JSON.parse(details);
                } catch (e) {
                    console.error('Failed to parse details_json:', e);
                    return;
                }
            }

            if (!details || details.type !== 'beauty_pageant') return;

            // Find the specific category in this score's details
            const catDetail = (details.categories || []).find(c => c.index === categoryIndex);
            if (!catDetail || !catDetail.criteria) return;

            // Process each criterion in this category
            catDetail.criteria.forEach((c, critArrayIdx) => {
                // Use the index from the stored data, or fall back to array index
                let critIdx = typeof c.index === 'number' ? c.index : critArrayIdx;
                
                // Ensure the index is within bounds
                if (critIdx < 0 || critIdx >= category.criteria.length) {
                    critIdx = critArrayIdx; // Fallback to array position
                }

                const criteriaDef = category.criteria[critIdx] || {};
                const criteriaPoints = criteriaDef.percentage || criteriaDef.maxPoints || 0;
                
                if (!criteriaStats[critIdx]) {
                    criteriaStats[critIdx] = { sum: 0, count: 0, points: criteriaPoints };
                }
                
                const scoreValue = Number(c.score || 0);
                criteriaStats[critIdx].sum += scoreValue;
                criteriaStats[critIdx].count += 1;
                
                // Always use the category definition's points (max possible)
                criteriaStats[critIdx].points = criteriaPoints;
            });
        });

        // Calculate weighted category score
        let teamRawScore = 0;
        let maxPossibleScore = 0;

        Object.keys(criteriaStats).forEach(key => {
            const stat = criteriaStats[key];
            const points = stat.points || 0;
            if (points <= 0 || stat.count === 0) return;

            // Average score across all judges for this criterion
            const avgScore = stat.sum / stat.count; // 0..points
            teamRawScore += avgScore;
            maxPossibleScore += points;
        });

        const categoryWeight = category.categoryPercentage || 0;
        if (maxPossibleScore > 0) {
            // Formula: (team_raw_score / max_possible_score) * category_weight
            const normalizedCategory = teamRawScore / maxPossibleScore; // 0–1
            return normalizedCategory * categoryWeight;
        }
        
        return 0;
    }

    renderMuseEscortScores(game, gameJudgeScores) {
        // Separate scores by participant type
        const museScores = gameJudgeScores.filter(s => s.participant_type === 'muse');
        const escortScores = gameJudgeScores.filter(s => s.participant_type === 'escort');
        const pairScores = gameJudgeScores.filter(s => s.participant_type === 'pair');
        
        let html = '<div style="margin-top: 25px; padding-top: 25px; border-top: 2px solid #e2e8f0;">';
        html += '<h4 style="margin-bottom: 15px; color: #1e293b; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">';
        html += '<i class="fas fa-venus-mars" style="color: #8b5cf6;"></i>Participant-Specific Scores</h4>';
        
        // Muse (Female) Scores
        if (museScores.length > 0) {
            html += this.renderParticipantTypeScores('Muse (Female)', museScores, '#fce7f3', '#9f1239');
        }
        
        // Escort (Male) Scores
        if (escortScores.length > 0) {
            html += this.renderParticipantTypeScores('Escort (Male)', escortScores, '#dbeafe', '#1e40af');
        }
        
        // Pair Scores
        if (pairScores.length > 0) {
            html += this.renderParticipantTypeScores('Pair', pairScores, '#fef3c7', '#92400e');
        }
        
        html += '</div>';
        return html;
    }

    renderParticipantTypeScores(title, scores, bgColor, textColor) {
        // Group scores by team
        const scoresByTeam = {};
        scores.forEach(score => {
            const teamId = score.team_id || score.teamId || score.teamId_alt;
            if (!teamId) return;
            
            if (!scoresByTeam[teamId]) {
                scoresByTeam[teamId] = [];
            }
            scoresByTeam[teamId].push(score);
        });
        
        // Calculate average scores per team
        const teamAverages = {};
        Object.keys(scoresByTeam).forEach(teamId => {
            const team = this.teams.find(t => t.id == teamId);
            if (!team) return;
            
            const teamScores = scoresByTeam[teamId];
            const judgeCount = new Set(teamScores.map(s => s.judge_name || s.judgeName)).size || 1;
            const totalScore = teamScores.reduce((sum, s) => sum + (parseInt(s.total_score || s.totalScore || 0)), 0);
            const avgScore = totalScore / judgeCount;
            
            teamAverages[teamId] = {
                team: team,
                score: avgScore,
                judgeCount: judgeCount
            };
        });
        
        // Sort by score
        const sortedTeams = Object.keys(teamAverages).sort((a, b) => 
            teamAverages[b].score - teamAverages[a].score
        );
        
        let html = `<div style="margin-bottom: 20px; padding: 15px; background: ${bgColor}; border-radius: 8px; border-left: 4px solid ${textColor};">
            <h5 style="margin: 0 0 12px 0; color: ${textColor}; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-crown"></i>${title}
            </h5>
            <div style="overflow-x: auto;">
                <table class="excel-table" style="background: white;">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th>Judges</th>
                            <th>Average Score</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        // Calculate max score for progress bar
        const maxScore = sortedTeams.length > 0 ? teamAverages[sortedTeams[0]].score : 1;
        
        sortedTeams.forEach((teamId, rank) => {
            const teamData = teamAverages[teamId];
            const rankClass = rank < 3 ? `rank-${rank + 1}` : '';
            const isLeader = rank === 0;
            const percentage = maxScore > 0 ? (teamData.score / maxScore) * 100 : 0;
            
            html += `<tr>
                <td class="${rankClass}">${rank + 1}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${teamData.team.color || '#2563eb'};"></div>
                        <div>
                            <strong>${teamData.team.name}</strong><br>
                            <small style="color: #64748b;">${teamData.team.code || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td style="text-align: center;">${teamData.judgeCount}</td>
                <td style="padding: 8px;">`;
            
            // Show progress bar for all teams, highlight leader with crown
                html += `<div style="position: relative;">
                    <div style="background: #e2e8f0; border-radius: 8px; height: 32px; position: relative; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, ${textColor} 0%, ${textColor}dd 100%); height: 100%; width: ${percentage}%; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: width 0.3s ease;">
                            <span style="color: white; font-weight: 700; font-size: 1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${teamData.score.toFixed(2)}</span>
                        </div>
                    </div>
                ${isLeader ? `<div style="position: absolute; top: -20px; left: 0; font-size: 0.75rem; color: ${textColor}; font-weight: 700;">
                        <i class="fas fa-crown" style="margin-right: 4px;"></i>Leader
                </div>` : ''}
                </div>`;
            
            html += `</td>
            </tr>`;
        });
        
        html += `</tbody></table></div></div>`;
        return html;
    }

    getTeamGameDetails(teamId, gameIdFilter = null) {
        const teamScores = this.scores.filter(s => s.team_id === teamId && (!gameIdFilter || s.game_id === gameIdFilter));
        if (teamScores.length === 0) return 'No games played';
        
        const gameDetails = teamScores.map(score => 
            `${score.game_name}: ${score.placement} (${score.points}pts)`
        ).join(', ');
        
        return gameDetails.length > 50 ? gameDetails.substring(0, 50) + '...' : gameDetails;
    }

    renderHistory() {
        const table = document.getElementById('historyTable');
        if (!table) return;
        
        if (this.scores.length === 0) {
            table.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">No scores recorded yet</td></tr>';
            return;
        }
        
        const sorted = [...this.scores].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        table.innerHTML = sorted.map(score => {
            return `
                <tr>
                    <td>${score.timestamp}</td>
                    <td>${score.game_name}</td>
                    <td>${score.team_name}</td>
                    <td>${score.placement}</td>
                    <td>${score.points}</td>
                    <td>${score.scorer}</td>
                </tr>
            `;
        }).join('');
    }

    // Utility Methods
    viewTeamDetails(teamId) {
        const team = this.teams.find(t => t.id === teamId);
        if (!team) return;

        const teamScores = this.scores.filter(s => s.team_id === teamId).sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        const teamJudgeScores = this.judgeScores.filter(js => 
            js.teamId === teamId || js.team_id === teamId || js.teamId_alt === teamId
        ).sort((a, b) => new Date(b.timestamp || b.date) - new Date(a.timestamp || a.date));
        
        // Calculate total points from both sources
        const gamePoints = teamScores.reduce((sum, score) => sum + (score.points || 0), 0);
        const judgePoints = teamJudgeScores.reduce((sum, judgeScore) => 
            sum + (parseInt(judgeScore.totalScore || judgeScore.total_score || 0) || 0), 0);
        const totalPoints = gamePoints + judgePoints;
        const totalGamesPlayed = teamScores.length + teamJudgeScores.length;
        
        let details = `Team: ${team.name}\n`;
        details += `Team Code: ${team.code || 'N/A'}\n`;
        details += `Total Points: ${totalPoints}\n`;
        details += `Games Played: ${totalGamesPlayed}\n\n`;
        details += 'Game Results:\n';
        
        teamScores.forEach(score => {
            details += `- ${score.game_name}: ${score.placement} (${score.points}pts) - ${score.timestamp}\n`;
        });
        
        teamJudgeScores.forEach(judgeScore => {
            const judgeName = judgeScore.judgeName || judgeScore.judge_name || 'Unknown Judge';
            const totalScore = judgeScore.totalScore || judgeScore.total_score || 0;
            const timestamp = judgeScore.timestamp || judgeScore.date || 'N/A';
            details += `- JUDGE SCORE: ${judgeName} (${totalScore}pts) - ${timestamp}\n`;
        });

        if (teamScores.length === 0 && teamJudgeScores.length === 0) {
            details += '- No games played yet\n';
        }

        alert(details);
    }

    async deleteGame(gameId) {
        const game = this.games.find(g => g.id === gameId);
        if (!game) return;

        if (confirm(`Are you sure you want to delete game "${game.name}"? This will also delete all associated scores.`)) {
            try {
                await this.apiCall('database_handler.php?action=games', 'DELETE', { id: gameId });
                await this.loadAllData(); // Reload all data
                alert('Game deleted successfully!');
            } catch (error) {
                console.error('Failed to delete game:', error);
                alert('Failed to delete game: ' + error.message);
            }
        }
    }

    cancelScoreEntry() {
        this.selectedGame = null;
        this.selectedGameName = '';
        const scoreEntry = document.getElementById('scoreEntry');
        if (scoreEntry) {
            scoreEntry.style.display = 'none';
        }
        this.clearScoreForm();
    }

    clearScoreForm() {
        const addScoreForm = document.getElementById('addScoreForm');
        if (addScoreForm) {
            addScoreForm.reset();
        }
        const pointsInput = document.getElementById('points');
        if (pointsInput) {
            pointsInput.value = '';
        }
    }

    clearTeamForm() {
        const addTeamForm = document.getElementById('addTeamForm');
        if (addTeamForm) {
            addTeamForm.reset();
        }

        // Re-apply default team color selection so hidden input/text stay in sync
        const defaultColorBtn = document.querySelector('.team-color-swatch[data-color="#2563eb"]')
            || document.querySelector('.team-color-swatch');
        
        if (defaultColorBtn) {
            if (typeof selectTeamColorSwatch === 'function') {
                selectTeamColorSwatch(defaultColorBtn);
            } else {
                // Fallback if helper isn't available yet
                document.querySelectorAll('.team-color-swatch').forEach(b => b.classList.remove('selected'));
                defaultColorBtn.classList.add('selected');
                const colorInput = document.getElementById('teamColor');
                if (colorInput) colorInput.value = defaultColorBtn.getAttribute('data-color') || '#2563eb';
                const colorLabel = document.getElementById('selectedTeamColorName');
                if (colorLabel) colorLabel.textContent = 'Selected: ' + (defaultColorBtn.getAttribute('data-name') || 'Blue');
            }
        }
    }

    clearGameForm() {
        const addGameForm = document.getElementById('addGameForm');
        if (addGameForm) {
            addGameForm.reset();
        }
        // Reset category to default
        const gameCategory = document.getElementById('gameCategory');
        if (gameCategory) {
            gameCategory.value = 'scorer';
            // Update points system visibility
            if (typeof togglePointsSystem === 'function') {
                togglePointsSystem('scorer');
            }
            // Update judge event type visibility
            if (typeof toggleJudgeEventType === 'function') {
                toggleJudgeEventType('scorer');
            }
            // Update criteria system visibility
            if (typeof toggleCriteriaSystem === 'function') {
                toggleCriteriaSystem('scorer');
            }
            // Update authorized judge visibility
            if (typeof toggleAuthorizedJudge === 'function') {
                toggleAuthorizedJudge('scorer');
            }
            // Hide and reset Beauty Pageant categories
            if (typeof toggleBeautyPageantCategories === 'function') {
                toggleBeautyPageantCategories('');
            }
        }
        // Reset judges list to default
        if (typeof resetJudgesList === 'function') {
            resetJudgesList();
        }
        // Clear Beauty Pageant categories
        const beautyCategoriesList = document.getElementById('beautyCategoriesList');
        if (beautyCategoriesList) {
            beautyCategoriesList.innerHTML = '';
        }
        // Reset criteria grid to default
        const criteriaGrid = document.getElementById('criteriaGrid');
        if (criteriaGrid) {
            criteriaGrid.innerHTML = `
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
            `;
            if (typeof calculateTotalPercentage === 'function') {
                calculateTotalPercentage();
            }
        }
        const pointsGrid = document.getElementById('pointsGrid');
        if (pointsGrid) {
            pointsGrid.innerHTML = `
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
            `;
        }
    }

    clearTeamFilter() {
        this.teamCodeFilter = '';
        const teamCodeFilter = document.getElementById('teamCodeFilter');
        if (teamCodeFilter) {
            teamCodeFilter.value = '';
        }
        this.renderTeams();
    }

    populateLeaderboardGameFilter() {
        const select = document.getElementById('leaderboardGameFilter');
        if (!select) return;
        
        const previous = this.leaderboardFilterGameId ? String(this.leaderboardFilterGameId) : '';
        const options = ['<option value="">All Games</option>'].concat(
            this.games.map(g => `<option value="${g.id}">${g.name}</option>`)
        );
        select.innerHTML = options.join('');
        
        const hasPrevious = previous && this.games.some(g => String(g.id) === previous);
        select.value = hasPrevious ? previous : '';
        this.leaderboardFilterGameId = hasPrevious ? parseInt(previous) : '';
    }
}

// Initialize the scoresheet system
const scoresheet = new ScoreSheet();