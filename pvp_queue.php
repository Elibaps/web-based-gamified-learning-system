<?php
/**
 * pvp_queue.php
 * Waiting queue for PvP matchmaking
 * Users wait here until matched with an opponent
 */
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get user ID
$username = $_SESSION['username'];
$user_result = $conn->query("SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];

$pageTitle = 'PvP Queue — CodeNest';
include 'includes/head.php';
?>
<body class="battle-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 600px; text-align: center;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px;">
        🔍 Finding Opponent...
    </h1>
    
    <div style="margin-bottom: 30px;">
        <div style="font-size: 4rem; animation: pulse 2s infinite; margin-bottom: 20px;">⚔️</div>
        <p style="color: #94a3b8; font-size: 1.2rem; margin-bottom: 10px;">Waiting for an opponent to join</p>
        <p style="color: #64748b; font-size: 0.9rem;">Queue position: <span id="queuePos">checking...</span></p>
    </div>

    <div style="background: var(--bg-color); border: 2px solid var(--primary-color); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #cbd5e1; margin-bottom: 10px;">Match found in: <span id="countdown" style="font-size: 1.3rem; color: var(--primary-color); font-weight: bold;">0:00</span></p>
        <div id="matchMessage" style="color: var(--primary-color); font-weight: bold; margin-top: 10px; display: none;">
            🎮 Opponent found! Redirecting...
        </div>
    </div>

    <button id="cancelBtn" class="btn" style="background: var(--danger-color);" onclick="cancelQueue()">Cancel</button>
</div>

<style>
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
</style>

<script>
let matchCheckInterval;
let countdownInterval;
let seconds = 0;

function formatTime(s) {
    const mins = Math.floor(s / 60);
    const secs = s % 60;
    return mins + ':' + (secs < 10 ? '0' : '') + secs;
}

async function checkForMatch() {
    try {
        const response = await fetch('pvp_match_api.php?action=check_queue&user_id=<?php echo $user_id; ?>');
        const data = await response.json();
        
        if (data.matched && data.match_id) {
            clearInterval(matchCheckInterval);
            clearInterval(countdownInterval);
            document.getElementById('matchMessage').style.display = 'block';
            
            // Redirect to match page after 1 second
            setTimeout(() => {
                window.location.href = 'pvp_match.php?match_id=' + data.match_id;
            }, 1000);
        }
        
        // Update queue position
        if (data.queue_position) {
            document.getElementById('queuePos').innerText = data.queue_position + ' in queue';
        }
    } catch (error) {
        console.error('Error checking queue:', error);
    }
}

function cancelQueue() {
    if (confirm('Are you sure you want to leave the queue?')) {
        fetch('pvp_match_api.php?action=leave_queue&user_id=<?php echo $user_id; ?>', {
            method: 'POST'
        }).then(() => {
            window.location.href = 'dashboard.php';
        });
    }
}

// Start checking for matches every 1 second
matchCheckInterval = setInterval(checkForMatch, 1000);

// Start countdown timer
countdownInterval = setInterval(() => {
    seconds++;
    document.getElementById('countdown').innerText = formatTime(seconds);
}, 1000);

// Initial check
checkForMatch();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    clearInterval(matchCheckInterval);
    clearInterval(countdownInterval);
    fetch('pvp_match_api.php?action=leave_queue&user_id=<?php echo $user_id; ?>', {
        method: 'POST',
        keepalive: true
    });
});
</script>

</body>
</html>
