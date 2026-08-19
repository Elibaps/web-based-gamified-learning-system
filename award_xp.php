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
// Allow optional coin rewards for Story Mode interactables
$coinsGain = max(0, min(1000, (int)($_POST['coins'] ?? 0)));
$course = trim($_POST['course'] ?? '');
$lesson_slug = trim($_POST['lesson_slug'] ?? '');
$allowed_courses = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Java', 'C++'];
$allowed_lessons  = ['intro', 'basics', 'syntax', 'practice', 'quiz'];

// Fetch current level, exp, and coins
$stmt = $conn->prepare(
    "SELECT user_id, level, exp, coins FROM users WHERE username = ?"
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

// Record lesson progress if valid course and lesson data were provided
if (in_array($course, $allowed_courses, true) && in_array($lesson_slug, $allowed_lessons, true)) {
    $progStmt = $conn->prepare(
        "INSERT IGNORE INTO user_progress (user_id, course, lesson_slug) VALUES (?, ?, ?)"
    );
    $progStmt->bind_param("iss", $user['user_id'], $course, $lesson_slug);
    $progStmt->execute();
    $progStmt->close();
}

// Persist updated stats (also add coins if provided)
if ($coinsGain > 0) {
    $stmt = $conn->prepare(
        "UPDATE users SET exp = ?, level = ?, coins = coins + ? WHERE user_id = ?"
    );
    $stmt->bind_param("iiii", $newExp, $newLevel, $coinsGain, $user['user_id']);
} else {
    $stmt = $conn->prepare(
        "UPDATE users SET exp = ?, level = ? WHERE user_id = ?"
    );
    $stmt->bind_param("iii", $newExp, $newLevel, $user['user_id']);
}
$stmt->execute();
$stmt->close();

// Fetch updated coin total for response
$coinsStmt = $conn->prepare("SELECT coins FROM users WHERE user_id = ?");
$coinsStmt->bind_param("i", $user['user_id']);
$coinsStmt->execute();
$coinRow = $coinsStmt->get_result()->fetch_assoc();
$coinsStmt->close();
$newCoins = (int)($coinRow['coins'] ?? $user['coins']);

// Update session cache
$_SESSION['user_id'] = $user['user_id'];

header('Content-Type: application/json');
echo json_encode([
    'success'   => true,
    'xpGained'  => $xpGain,
    'newExp'    => $newExp,
    'newLevel'  => $newLevel,
    'leveledUp' => $leveledUp,
    'newCoins'  => $newCoins,
]);
