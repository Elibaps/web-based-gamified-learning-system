<?php
/**
 * pvp_admin.php
 * Admin utilities for testing and debugging the PvP system
 * NOT FOR PRODUCTION - Remove in production environment
 */
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Only allow admin users (customize as needed)
$admin_usernames = ['123', 'admin']; // Add your admin usernames
if (!in_array($_SESSION['username'], $admin_usernames)) {
    echo "Access denied. Admin only.";
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
if ($action === 'reset') {
    // Clear all PvP data (for testing)
    $conn->query("TRUNCATE TABLE player_answers");
    $conn->query("TRUNCATE TABLE match_questions");
    $conn->query("TRUNCATE TABLE pvp_matches");
    $conn->query("TRUNCATE TABLE pvp_queue");
    
    echo "✓ All PvP data cleared!";
    exit();
}

if ($action === 'status') {
    // Show PvP system status
    header('Content-Type: application/json');
    
    $queue_count = $conn->query("SELECT COUNT(*) as cnt FROM pvp_queue WHERE matched = 0")->fetch_assoc()['cnt'];
    $active_matches = $conn->query("SELECT COUNT(*) as cnt FROM pvp_matches WHERE status = 'active'")->fetch_assoc()['cnt'];
    $completed_matches = $conn->query("SELECT COUNT(*) as cnt FROM pvp_matches WHERE status = 'completed'")->fetch_assoc()['cnt'];
    
    $recent_matches = $conn->query(
        "SELECT m.*, u1.username as p1_name, u2.username as p2_name 
         FROM pvp_matches m
         LEFT JOIN users u1 ON m.player1_id = u1.user_id
         LEFT JOIN users u2 ON m.player2_id = u2.user_id
         ORDER BY m.created_at DESC LIMIT 10"
    )->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'queue_waiting' => $queue_count,
        'active_matches' => $active_matches,
        'completed_matches' => $completed_matches,
        'recent_matches' => $recent_matches
    ]);
    exit();
}

// HTML interface
$pageTitle = 'PvP Admin — CodeNest';
include 'includes/head.php';
?>
<body style="background: var(--bg-primary); color: var(--text-color); padding: 20px;">
<?php include 'includes/navbar.php'; ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <h1 style="color: var(--primary-color);">PvP System Admin Panel</h1>
    
    <div style="background: var(--bg-color); border: 2px solid var(--primary-color); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2>System Status</h2>
        <div id="statusContainer" style="color: #94a3b8;">
            <p>Loading...</p>
        </div>
    </div>
    
    <div style="background: var(--bg-color); border: 2px solid var(--primary-color); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2>Database Tables</h2>
        
        <h3>PvP Queue</h3>
        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-secondary);">
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Queue ID</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">User</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Joined At</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Matched</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $queue = $conn->query("SELECT * FROM pvp_queue ORDER BY joined_at DESC LIMIT 20");
                    while ($row = $queue->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['queue_id']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['joined_at']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . ($row['matched'] ? '✓' : '✗') . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <h3>Recent Matches</h3>
        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-secondary);">
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Match ID</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Player 1</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Player 2</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Score</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Status</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #444;">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $matches = $conn->query(
                        "SELECT m.*, u1.username as p1_name, u2.username as p2_name 
                         FROM pvp_matches m
                         LEFT JOIN users u1 ON m.player1_id = u1.user_id
                         LEFT JOIN users u2 ON m.player2_id = u2.user_id
                         ORDER BY m.created_at DESC LIMIT 20"
                    );
                    while ($row = $matches->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['match_id']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['p1_name']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars($row['p2_name']) . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . $row['player1_score'] . "-" . $row['player2_score'] . "</td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'><span style='color: " . ($row['status'] === 'completed' ? 'var(--primary-color)' : '#f59e0b') . ";'>" . htmlspecialchars($row['status']) . "</span></td>";
                        echo "<td style='padding: 10px; border: 1px solid #444;'>" . htmlspecialchars(substr($row['created_at'], 0, 16)) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="background: var(--bg-color); border: 2px solid var(--danger-color); padding: 20px; border-radius: 8px;">
        <h2>Danger Zone</h2>
        <button onclick="if(confirm('Clear all PvP data? This cannot be undone.')) window.location.href='pvp_admin.php?action=reset'" 
                style="background: var(--danger-color); color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
            🗑️ Clear All PvP Data
        </button>
        <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 10px;">This will delete all queue entries, matches, and answers. Use only for testing.</p>
    </div>
</div>

<script>
async function updateStatus() {
    try {
        const response = await fetch('pvp_admin.php?action=status');
        const data = await response.json();
        
        document.getElementById('statusContainer').innerHTML = `
            <p><strong>Players in Queue:</strong> ${data.queue_waiting}</p>
            <p><strong>Active Matches:</strong> ${data.active_matches}</p>
            <p><strong>Completed Matches:</strong> ${data.completed_matches}</p>
        `;
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

// Update status on page load and every 5 seconds
updateStatus();
setInterval(updateStatus, 5000);
</script>

</body>
</html>
