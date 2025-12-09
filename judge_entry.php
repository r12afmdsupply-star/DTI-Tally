<?php
require_once 'config.php';
$pageTitle = "Judge Entry - DTI Sports Fest";
include 'includes/header.php';
?>
<style>
    :root {
        --primary: #2563eb;
        --success: #22c55e;
        --warning: #fbbf24;
        --danger: #ef4444;
        --background: #ffffff;
        --surface: #f8fafc;
        --border: #e2e8f0;
        --text: #1e293b;
        --text-muted: #64748b;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 0;
    }

    .entry-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        margin-top: 30px;
    }

    .entry-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        text-align: center;
        position: relative;
        max-width: 500px;
        margin: 0 auto;
    }

    .entry-card h1 {
        font-size: 2rem;
        margin-bottom: 10px;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .entry-card p {
        color: var(--text-muted);
        margin-bottom: 30px;
        font-size: 1rem;
    }

    .back-link {
        position: fixed;
        top: 20px;
        left: 20px;
        font-size: 0.9rem;
        color: #fff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        background: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        z-index: 1000;
        font-weight: 600;
    }

    .back-link i {
        font-size: 0.85rem;
    }

    .form-group {
        margin-bottom: 25px;
        text-align: left;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--text);
        font-size: 1rem;
    }

    .form-group input {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-group input.valid {
        border-color: var(--success);
        background: #f0fdf4;
    }

    .form-group input.invalid {
        border-color: var(--danger);
        background: #fef2f2;
    }

    .validation-message {
        margin-top: 10px;
        padding: 10px;
        border-radius: 8px;
        font-size: 0.9rem;
        display: none;
    }

    .validation-message.show {
        display: block;
    }

    .validation-message.success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid var(--success);
    }

    .validation-message.warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid var(--warning);
    }

    .validation-message.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid var(--danger);
    }

    .btn-primary {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-primary:hover:not(:disabled) {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .judge-list {
        margin-top: 20px;
        padding: 15px;
        background: var(--surface);
        border-radius: 10px;
        text-align: left;
    }

    .judge-list h3 {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 10px;
    }

    .judge-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .judge-list li {
        padding: 8px 12px;
        margin: 5px 0;
        background: white;
        border-radius: 6px;
        font-size: 0.9rem;
        color: var(--text);
    }
</style>

<div class="entry-container">
    <div class="entry-card">
        <a href="scoresheet.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Landing Page
        </a>
        <h1><i class="fas fa-gavel"></i> Judge Entry</h1>
        <p>Enter your name to access the judge dashboard and scoring interface</p>
        
        <form id="judgeEntryForm">
            <div class="form-group">
                <label for="judgeNameInput">Judge Name:</label>
                <input 
                    type="text" 
                    id="judgeNameInput" 
                    placeholder="Enter your full name (e.g., John Smith)" 
                    autocomplete="name"
                    required
                >
                <div id="validationMessage" class="validation-message"></div>
            </div>
            
            <button type="submit" class="btn-primary" id="submitBtn">
                <i class="fas fa-arrow-right"></i> Continue to Dashboard
            </button>
        </form>

        <div class="judge-list" id="judgeListContainer" style="display: none;">
            <h3><i class="fas fa-users"></i> Registered Judges:</h3>
            <ul id="judgeList"></ul>
        </div>
    </div>
</div>

<script>

    // Load existing judges from database
    async function loadJudges() {
        try {
            const response = await fetch('database_handler.php?action=judge_scores');
            const result = await response.json();
            
            if (result.success && result.data) {
                // Get unique judge names
                const judgeNames = [...new Set(result.data.map(score => score.judge_name).filter(Boolean))];
                
                if (judgeNames.length > 0) {
                    const judgeList = document.getElementById('judgeList');
                    const container = document.getElementById('judgeListContainer');
                    
                    judgeList.innerHTML = judgeNames.slice(0, 10).map(name => 
                        `<li><i class="fas fa-user-tie"></i> ${name}</li>`
                    ).join('');
                    
                    if (judgeNames.length > 10) {
                        judgeList.innerHTML += `<li style="color: var(--text-muted); font-style: italic;">... and ${judgeNames.length - 10} more</li>`;
                    }
                    
                    container.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error loading judges:', error);
        }
    }

    // Validate judge name using session API (PHP validation)
    async function validateJudgeName(name) {
        if (!name || name.trim().length < 2) {
            return { success: false, message: 'Please enter a valid judge name (at least 2 characters)' };
        }

        const trimmedName = name.trim();
        
        try {
            // Use session API to validate judge (PHP validation)
            const response = await fetch('database_handler.php?action=judge_auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ judge_name: trimmedName })
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error validating judge:', error);
            return { success: false, message: 'Error verifying judge. Please try again or contact administrator.' };
        }
    }

    // Show validation message (visual feedback only, no button disable)
    function showValidation(message, type) {
        const validationDiv = document.getElementById('validationMessage');
        const input = document.getElementById('judgeNameInput');
        
        validationDiv.textContent = message;
        validationDiv.className = `validation-message show ${type}`;
        
        // Update input styling
        input.classList.remove('valid', 'invalid');
        if (type === 'success') {
            input.classList.add('valid');
        } else if (type === 'error') {
            input.classList.add('invalid');
        }
    }

    // Form submission with PHP validation
    document.getElementById('judgeEntryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const name = document.getElementById('judgeNameInput').value.trim();
        
        if (!name) {
            alert('Please enter your judge name');
            return;
        }

        // Show loading
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="loading-spinner"></div> Verifying...';
        submitBtn.disabled = true;

        // Validate using PHP (session API)
        const result = await validateJudgeName(name);
        
        if (!result.success) {
            // Validation failed - show alert and prevent access
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            alert('❌ Not Authorized\n\n' + result.message + '\n\nPlease enter a registered judge name.');
            showValidation('❌ ' + result.message, 'error');
            return;
        }
        
        // Validation passed - session is already created on server
        // Redirect to judge dashboard
        setTimeout(() => {
            window.location.href = 'judge_dashboard.php';
        }, 500);
    });

    // Real-time validation as user types (visual feedback only, no button disable)
    let validationTimeout;
    document.getElementById('judgeNameInput').addEventListener('input', function() {
        const name = this.value.trim();
        
        clearTimeout(validationTimeout);
        
        if (!name) {
            document.getElementById('validationMessage').className = 'validation-message';
            this.classList.remove('valid', 'invalid');
            return;
        }
        
        // Debounce validation (optional visual feedback)
        validationTimeout = setTimeout(async () => {
            const result = await validateJudgeName(name);
            if (result.success) {
                showValidation('✓ ' + result.message, 'success');
            } else {
                showValidation('', 'error'); // Clear message, will show on submit
            }
        }, 500);
    });

    // Load judges list on page load
    document.addEventListener('DOMContentLoaded', async function() {
        loadJudges();
        
        // Check if there's an active session
        try {
            const response = await fetch('database_handler.php?action=judge_auth');
            const result = await response.json();
            
            if (result.success && result.validated && result.judge_name) {
                // Session exists - pre-fill the name
                document.getElementById('judgeNameInput').value = result.judge_name;
                const validationResult = await validateJudgeName(result.judge_name);
                if (validationResult.success) {
                    showValidation('✓ ' + validationResult.message, 'success');
                }
            }
        } catch (error) {
            console.error('Error checking session:', error);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>

