<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'];
if (!$user_id) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $urow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $user_id = $urow['user_id'] ?? null;
    if ($user_id) {
        $_SESSION['user_id'] = $user_id;
    }
}

if (!$user_id) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to identify user']);
    exit();
}

$action = $_REQUEST['action'] ?? 'status';
$match_id = isset($_REQUEST['match_id']) ? (int)$_REQUEST['match_id'] : 0;

// Ensure PvP table exists.
$conn->query(
    "CREATE TABLE IF NOT EXISTS pvp_matches (
        match_id INT AUTO_INCREMENT PRIMARY KEY,
        player1_id INT NOT NULL,
        player1_name VARCHAR(100) NOT NULL,
        player2_id INT DEFAULT NULL,
        player2_name VARCHAR(100) DEFAULT NULL,
        player1_score INT NOT NULL DEFAULT 0,
        player2_score INT NOT NULL DEFAULT 0,
        current_idx INT NOT NULL DEFAULT 0,
        status ENUM('waiting','playing','finished') NOT NULL DEFAULT 'waiting',
        questions TEXT NOT NULL,
        winner_id INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

function normalizeAnswer($value) {
    return trim(strtolower(preg_replace('/\s+/', ' ', $value)));
}

switch ($action) {
    case 'join':
        // Look for a waiting match from another player.
        $stmt = $conn->prepare(
            "SELECT * FROM pvp_matches WHERE status = 'waiting' AND player1_id != ? ORDER BY created_at ASC LIMIT 1"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $match = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($match) {
            $stmt = $conn->prepare(
                "UPDATE pvp_matches SET player2_id = ?, player2_name = ?, status = 'playing', updated_at = NOW() WHERE match_id = ?"
            );
            $stmt->bind_param("isi", $user_id, $username, $match['match_id']);
            $stmt->execute();
            $stmt->close();

            $match['player2_id']   = $user_id;
            $match['player2_name'] = $username;
            $match['status']       = 'playing';
        } else {
            // Create a fresh waiting match.
            $questions = [];
            $qstmt = $conn->prepare(
                "SELECT question_text, answer FROM questions ORDER BY RAND() LIMIT 10"
            );
            $qstmt->execute();
            $questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $qstmt->close();
            $questionJson = json_encode($questions, JSON_UNESCAPED_UNICODE);

            $stmt = $conn->prepare(
                "INSERT INTO pvp_matches (player1_id, player1_name, questions) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iss", $user_id, $username, $questionJson);
            $stmt->execute();
            $match_id = $stmt->insert_id;
            $stmt->close();

            $match = [
                'match_id'      => $match_id,
                'player1_id'    => $user_id,
                'player1_name'  => $username,
                'player2_id'    => null,
                'player2_name'  => null,
                'player1_score' => 0,
                'player2_score' => 0,
                'current_idx'   => 0,
                'status'        => 'waiting',
                'questions'     => $questionJson,
                'winner_id'     => null,
            ];
        }

        $slot = ($match['player1_id'] === $user_id) ? 'player1' : 'player2';
        echo json_encode([
            'success' => true,
            'match'   => $match,
            'slot'    => $slot,
            'waiting' => $match['status'] === 'waiting',
        ]);
        break;

    case 'status':
        if (!$match_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing match_id']);
            exit();
        }
        $stmt = $conn->prepare("SELECT * FROM pvp_matches WHERE match_id = ?");
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $match = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$match) {
            http_response_code(404);
            echo json_encode(['error' => 'Match not found']);
            exit();
        }

        $slot = ($match['player1_id'] === $user_id) ? 'player1' : 'player2';
        $opponent = null;
        if ($slot === 'player1') {
            $opponent = $match['player2_name'] ?: 'Waiting for opponent...';
        } else {
            $opponent = $match['player1_name'];
        }

        echo json_encode([
            'success'     => true,
            'match'       => $match,
            'slot'        => $slot,
            'opponent'    => $opponent,
            'player_name' => $username,
        ]);
        break;

    case 'answer':
        $answer = $_POST['answer'] ?? '';
        if (!$match_id || !strlen($answer)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing match_id or answer']);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM pvp_matches WHERE match_id = ?");
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $match = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$match || $match['status'] !== 'playing') {
            http_response_code(400);
            echo json_encode(['error' => 'Match not active']);
            exit();
        }

        $slot = ($match['player1_id'] === $user_id) ? 'player1' : 'player2';
        if ($slot === 'player2' && !$match['player2_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'You are not yet joined in this match']);
            exit();
        }

        $questions = json_decode($match['questions'], true);
        $idx = (int)$match['current_idx'];
        $correct = false;
        $answerNormalized = normalizeAnswer($answer);
        $expected = normalizeAnswer($questions[$idx]['answer'] ?? '');

        if ($answerNormalized === $expected) {
            $correct = true;
            if ($slot === 'player1') {
                $match['player1_score']++;
            } else {
                $match['player2_score']++;
            }
            $match['current_idx'] = min($idx + 1, count($questions) - 1);
        }

        if ($match['player1_score'] >= 5 || $match['player2_score'] >= 5) {
            $match['status'] = 'finished';
            $match['winner_id'] = ($match['player1_score'] >= 5) ? $match['player1_id'] : $match['player2_id'];
        }

        $update = $conn->prepare(
            "UPDATE pvp_matches SET player1_score = ?, player2_score = ?, current_idx = ?, status = ?, winner_id = ?, updated_at = NOW() WHERE match_id = ?"
        );
        $update->bind_param(
            "iiiisi",
            $match['player1_score'],
            $match['player2_score'],
            $match['current_idx'],
            $match['status'],
            $match['winner_id'],
            $match['match_id']
        );
        $update->execute();
        $update->close();

        echo json_encode([
            'success'       => true,
            'correct'       => $correct,
            'match'         => $match,
            'next_question' => $questions[$match['current_idx']] ?? null,
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
