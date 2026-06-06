<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'PvP Arena — CodeNest';
include 'includes/head.php';

// Get user ID for queue management
$username = $_SESSION['username'];
$user_result = $conn->query("SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];
?>
<body class="battle-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 700px; text-align: center;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px;">⚔️ PvP Arena</h1>
    
    <div id="mainContent" style="background: var(--bg-color); border: 2px solid var(--primary-color); padding: 40px; border-radius: 12px;">
        <p style="color: #94a3b8; margin-bottom: 20px; font-size: 1.1rem;">Challenge players from around the world!</p>
        
        <div style="margin-bottom: 30px;">
            <p style="color: #cbd5e1; font-size: 0.95rem; margin-bottom: 15px;">
                ✨ Race to answer 5 questions correctly<br>
                🏆 Win to earn 50 XP and reputation points<br>
                ⚡ Real-time competition with live opponent updates
            </p>
        </div>
        
        <button id="findOpponentBtn" class="btn" onclick="findOpponent()" style="font-size: 1.1rem; padding: 15px 40px; width: 100%; margin-bottom: 15px;">
            🔍 Find Opponent
        </button>
        
        <button class="btn" onclick="window.location.href='dashboard.php'" style="background: var(--bg-secondary); width: 100%;">
            Back to Dashboard
        </button>
    </div>
</div>

<script>
const USER_ID = <?php echo $user_id; ?>;

async function findOpponent() {
    const btn = document.getElementById('findOpponentBtn');
    btn.disabled = true;
    btn.innerText = '⏳ Joining Queue...';
    
    try {
        const response = await fetch('pvp_match_api.php?action=join_queue', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.matched && data.match_id) {
            // Redirect to match page immediately
            window.location.href = 'pvp_match.php?match_id=' + data.match_id;
        } else {
            // Redirect to queue waiting page
            window.location.href = 'pvp_queue.php';
        }
    } catch (error) {
        console.error('Error finding opponent:', error);
        btn.disabled = false;
        btn.innerText = '🔍 Find Opponent';
        alert('Error finding opponent. Please try again.');
    }
}
</script>

</body>
</html>
