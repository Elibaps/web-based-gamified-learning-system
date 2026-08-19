<?php
// story/story_state.php
// GET: returns story objects for current user
// POST: set object state (object_key, state)

session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit();
}
include_once __DIR__ . '/../db.php';

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$u) { http_response_code(404); echo json_encode(['error'=>'User not found']); exit(); }
$user_id = (int)$u['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $gstmt = $conn->prepare("SELECT object_key, area, state, data, updated_at FROM story_objects WHERE user_id = ?");
    $gstmt->bind_param("i", $user_id);
    $gstmt->execute();
    $rows = $gstmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $gstmt->close();
    echo json_encode(['objects' => $rows]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $object_key = trim($_POST['object_key'] ?? '');
    $state = trim($_POST['state'] ?? 'broken');
    if ($object_key === '') { http_response_code(400); echo json_encode(['error'=>'Missing object_key']); exit(); }
    // Upsert
    $ustmt = $conn->prepare("INSERT INTO story_objects (user_id, object_key, state) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE state = VALUES(state), updated_at = CURRENT_TIMESTAMP");
    $ustmt->bind_param("iss", $user_id, $object_key, $state);
    $ok = $ustmt->execute();
    $ustmt->close();
    if ($ok) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'error'=>'DB error']);
    exit();
}

http_response_code(405);
echo json_encode(['error'=>'Method Not Allowed']);
