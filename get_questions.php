<?php
/**
 * get_questions.php
 * AJAX endpoint — returns JSON array of questions for a given course.
 * Requires an active session.
 */
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include 'db.php';

$allowed = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Java', 'C++'];
$topic   = (isset($_GET['topic']) && in_array($_GET['topic'], $allowed, true))
    ? $_GET['topic']
    : 'HTML';

$stmt = $conn->prepare(
    "SELECT question_text, answer
     FROM   questions
     WHERE  course = ?
     ORDER BY RAND()
     LIMIT 10"
);
$stmt->bind_param("s", $topic);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: application/json');
echo json_encode($questions);
