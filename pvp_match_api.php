<?php
/**
 * pvp_match_api.php
 * Backend API for PvP matchmaking and live match data
 */
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$username = $_SESSION['username'];

// Get user ID
$user_result = $conn->query("SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];

// ============================================================
// ACTION: Join Queue
// ============================================================
if ($action === 'join_queue') {
    // Remove from queue if already there
    $conn->query("DELETE FROM pvp_queue WHERE user_id = $user_id");
    
    // Add to queue
    $stmt = $conn->prepare("INSERT INTO pvp_queue (user_id, username) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $username);
    $stmt->execute();
    $stmt->close();
    
    // Check if there are 2+ players waiting
    $waiting = $conn->query("SELECT COUNT(*) as cnt FROM pvp_queue WHERE matched = 0")->fetch_assoc();
    
    if ($waiting['cnt'] >= 2) {
        // Get the two oldest players in queue
        $players = $conn->query("
            SELECT queue_id, user_id, username 
            FROM pvp_queue 
            WHERE matched = 0 
            ORDER BY joined_at ASC 
            LIMIT 2
        ")->fetch_all(MYSQLI_ASSOC);
        
        if (count($players) === 2) {
            $p1_id = $players[0]['user_id'];
            $p1_name = $players[0]['username'];
            $p2_id = $players[1]['user_id'];
            $p2_name = $players[1]['username'];
            $p1_queue_id = $players[0]['queue_id'];
            $p2_queue_id = $players[1]['queue_id'];
            
            // Create match
            $match_token = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("
                INSERT INTO pvp_matches 
                (player1_id, player2_id, player1_username, player2_username, status, match_token) 
                VALUES (?, ?, ?, ?, 'active', ?)
            ");
            $stmt->bind_param("iisss", $p1_id, $p2_id, $p1_name, $p2_name, $match_token);
            $stmt->execute();
            $match_id = $stmt->insert_id;
            $stmt->close();
            
            // Mark both as matched in queue
            $conn->query("UPDATE pvp_queue SET matched = 1 WHERE queue_id IN ($p1_queue_id, $p2_queue_id)");
            
            // Get questions for the match
            $questions = $conn->query(
                "SELECT question_id, question_text, answer 
                 FROM questions 
                 ORDER BY RAND() 
                 LIMIT 5"
            )->fetch_all(MYSQLI_ASSOC);
            
            // Store questions in match_questions table
            $stmt = $conn->prepare("
                INSERT INTO match_questions 
                (match_id, question_id, question_text, answer, question_order) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($questions as $idx => $q) {
                $order = $idx + 1;
                $qid = $q['question_id'];
                $qtext = $q['question_text'];
                $answer = $q['answer'];
                $stmt->bind_param("iissi", $match_id, $qid, $qtext, $answer, $order);
                $stmt->execute();
            }
            $stmt->close();
            
            echo json_encode([
                'matched' => true,
                'match_id' => $match_id,
                'opponent' => ($p1_id === $user_id) ? $p2_name : $p1_name
            ]);
        } else {
            echo json_encode(['matched' => false, 'message' => 'Waiting for opponent...']);
        }
    } else {
        echo json_encode(['matched' => false, 'message' => 'Waiting for opponent...']);
    }
}

// ============================================================
// ACTION: Check Queue Status
// ============================================================
else if ($action === 'check_queue') {
    $player_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    
    // Check if player is matched - simpler query
    $match = $conn->query(
        "SELECT match_id FROM pvp_matches 
         WHERE (player1_id = $player_id OR player2_id = $player_id) AND status = 'active'"
    )->fetch_assoc();
    
    if ($match) {
        echo json_encode([
            'matched' => true,
            'match_id' => $match['match_id']
        ]);
    } else {
        // Count queue position
        $user_joined = $conn->query(
            "SELECT joined_at FROM pvp_queue WHERE user_id = $player_id"
        )->fetch_assoc();
        
        if ($user_joined) {
            $position_result = $conn->query(
                "SELECT COUNT(*) as pos FROM pvp_queue WHERE joined_at <= '" . $user_joined['joined_at'] . "' AND matched = 0"
            )->fetch_assoc();
            $position = $position_result['pos'] ?? 1;
        } else {
            $position = 0;
        }
        
        echo json_encode([
            'matched' => false,
            'queue_position' => $position
        ]);
    }
}

// ============================================================
// ACTION: Leave Queue
// ============================================================
else if ($action === 'leave_queue') {
    $player_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $user_id;
    $conn->query("DELETE FROM pvp_queue WHERE user_id = $player_id");
    
    echo json_encode(['success' => true]);
}

// ============================================================
// ACTION: Submit Answer
// ============================================================
else if ($action === 'submit_answer') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $match_id = (int)$data['match_id'];
    $question_id = (int)$data['question_id'];
    $is_correct = $data['is_correct'] ? 1 : 0;
    
    // Verify match exists and player is in it
    $match = $conn->query(
        "SELECT * FROM pvp_matches WHERE match_id = $match_id AND (player1_id = $user_id OR player2_id = $user_id)"
    )->fetch_assoc();
    
    if (!$match) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid match']);
        exit();
    }
    
    // Record answer (ignore duplicates)
    $stmt = $conn->prepare("
        INSERT IGNORE INTO player_answers 
        (match_id, player_id, question_id, is_correct) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiii", $match_id, $user_id, $question_id, $is_correct);
    $stmt->execute();
    $stmt->close();
    
    // Update score if correct
    if ($is_correct) {
        if ($match['player1_id'] === $user_id) {
            $conn->query("UPDATE pvp_matches SET player1_score = player1_score + 1 WHERE match_id = $match_id");
            $new_score = $match['player1_score'] + 1;
        } else {
            $conn->query("UPDATE pvp_matches SET player2_score = player2_score + 1 WHERE match_id = $match_id");
            $new_score = $match['player2_score'] + 1;
        }
    }
    
    // Get updated match state
    $updated_match = $conn->query("SELECT * FROM pvp_matches WHERE match_id = $match_id")->fetch_assoc();
    
    // Check for winner
    $winner = null;
    if ($updated_match['player1_score'] >= 5) {
        $winner = $updated_match['player1_id'];
    } elseif ($updated_match['player2_score'] >= 5) {
        $winner = $updated_match['player2_id'];
    }
    
    if ($winner) {
        $conn->query("
            UPDATE pvp_matches 
            SET status = 'completed', winner_id = $winner, completed_at = NOW() 
            WHERE match_id = $match_id
        ");
        
        // Award XP to winner
        if ($winner === $user_id) {
            $conn->query("UPDATE users SET exp = exp + 50 WHERE user_id = $user_id");
        }
    }
    
    echo json_encode([
        'success' => true,
        'new_score' => $is_correct ? ($new_score ?? 0) : ($match['player1_id'] === $user_id ? $match['player1_score'] : $match['player2_score']),
        'winner_id' => $winner,
        'match_complete' => $winner ? true : false
    ]);
}

// ============================================================
// ACTION: Get Opponent Progress
// ============================================================
else if ($action === 'get_progress') {
    $match_id = (int)$_GET['match_id'];
    $player_id = (int)$_GET['player_id'];
    
    // Get match data
    $match = $conn->query(
        "SELECT * FROM pvp_matches WHERE match_id = $match_id"
    )->fetch_assoc();
    
    if (!$match) {
        http_response_code(404);
        echo json_encode(['error' => 'Match not found']);
        exit();
    }
    
    // Determine opponent ID
    $opponent_id = ($match['player1_id'] === $player_id) ? $match['player2_id'] : $match['player1_id'];
    
    // Get opponent's answers for each question
    $answers = $conn->query(
        "SELECT question_id, is_correct FROM player_answers 
         WHERE match_id = $match_id AND player_id = $opponent_id"
    )->fetch_all(MYSQLI_ASSOC);
    
    $answer_map = [];
    foreach ($answers as $ans) {
        $answer_map[$ans['question_id']] = $ans['is_correct'];
    }
    
    // Get opponent score
    $opponent_score = ($opponent_id === $match['player1_id']) ? $match['player1_score'] : $match['player2_score'];
    
    echo json_encode([
        'opponent_id' => $opponent_id,
        'opponent_name' => ($opponent_id === $match['player1_id']) ? $match['player1_username'] : $match['player2_username'],
        'opponent_score' => $opponent_score,
        'answers' => $answer_map,
        'status' => $match['status'],
        'winner_id' => $match['winner_id']
    ]);
}

// ============================================================
// ACTION: Get Match Data
// ============================================================
else if ($action === 'get_match') {
    $match_id = (int)$_GET['match_id'];
    
    $match = $conn->query(
        "SELECT m.*, q.question_id, q.question_text, q.answer, q.question_order 
         FROM pvp_matches m
         LEFT JOIN match_questions q ON m.match_id = q.match_id
         WHERE m.match_id = $match_id
         ORDER BY q.question_order ASC"
    )->fetch_all(MYSQLI_ASSOC);
    
    if (!$match) {
        http_response_code(404);
        echo json_encode(['error' => 'Match not found']);
        exit();
    }
    
    // Group questions
    $match_data = [
        'match_id' => $match[0]['match_id'],
        'player1_id' => $match[0]['player1_id'],
        'player2_id' => $match[0]['player2_id'],
        'player1_username' => $match[0]['player1_username'],
        'player2_username' => $match[0]['player2_username'],
        'player1_score' => $match[0]['player1_score'],
        'player2_score' => $match[0]['player2_score'],
        'status' => $match[0]['status'],
        'winner_id' => $match[0]['winner_id'],
        'questions' => []
    ];
    
    foreach ($match as $row) {
        if ($row['question_id']) {
            $match_data['questions'][] = [
                'question_id' => $row['question_id'],
                'question_text' => $row['question_text'],
                'answer' => $row['answer'],
                'order' => $row['question_order']
            ];
        }
    }
    
    echo json_encode($match_data);
}

// ============================================================
// ACTION: End Match
// ============================================================
else if ($action === 'end_match') {
    $data = json_decode(file_get_contents('php://input'), true);
    $match_id = (int)$data['match_id'];
    
    $conn->query("
        UPDATE pvp_matches 
        SET status = 'completed', completed_at = NOW() 
        WHERE match_id = $match_id
    ");
    
    echo json_encode(['success' => true]);
}

else {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
}
?>
