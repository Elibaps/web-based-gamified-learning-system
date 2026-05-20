<?php
/**
 * award_xp.php
 * AJAX endpoint — awards XP to the logged-in user after a battle win.
 * Handles level-up logic server-side.
 * Expects: POST { xp: <int> }
 */
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

// Clamp XP to a sane range to prevent tampering
$xpGain = max(0, min(500, (int)($_POST['xp'] ?? 0)));

// Fetch current level and exp
$stmt = $conn->prepare(
    "SELECT user_id, level, exp FROM users WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

$newExp   = $user['exp']   + $xpGain;
$newLevel = $user['level'];
$leveledUp = false;

// Level-up loop
while ($newExp >= $newLevel * 100) {
    $newExp  -= $newLevel * 100;
    $newLevel++;
    $leveledUp = true;
}

// Persist updated stats
$stmt = $conn->prepare(
    "UPDATE users SET exp = ?, level = ? WHERE user_id = ?"
);
$stmt->bind_param("iii", $newExp, $newLevel, $user['user_id']);
$stmt->execute();
$stmt->close();

// Update session cache
$_SESSION['user_id'] = $user['user_id'];

header('Content-Type: application/json');
echo json_encode([
    'success'   => true,
    'xpGained'  => $xpGain,
    'newExp'    => $newExp,
    'newLevel'  => $newLevel,
    'leveledUp' => $leveledUp,
]);
