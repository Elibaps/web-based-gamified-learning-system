<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_paths':
        getPaths();
        break;
    
    case 'get_path_progress':
        getPathProgress();
        break;
    
    case 'unlock_next':
        unlockNext();
        break;
    
    case 'check_prerequisites':
        checkPrerequisites();
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getPaths() {
    global $conn, $user_id;
    
    $sql = "SELECT 
                p.path_id,
                p.path_name,
                p.path_description,
                COUNT(pl.path_lesson_id) as total_lessons,
                SUM(CASE WHEN upp.completed = TRUE THEN 1 ELSE 0 END) as completed_lessons,
                SUM(pl.time_estimate) as total_time
            FROM learning_paths p
            LEFT JOIN path_lessons pl ON p.path_id = pl.path_id
            LEFT JOIN user_path_progress upp ON pl.path_lesson_id = upp.path_lesson_id AND upp.user_id = ?
            GROUP BY p.path_id, p.path_name, p.path_description";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $paths = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode(['success' => true, 'paths' => $paths]);
}

function getPathProgress() {
    global $conn, $user_id;
    
    $path_id = $_GET['path_id'] ?? 0;
    $request_user_id = $_GET['user_id'] ?? $user_id;
    
    if ((int)$request_user_id !== $user_id) {
        http_response_code(403);
        echo json_encode(['error' => 'Cannot access other user progress']);
        return;
    }
    
    $sql = "SELECT 
                pl.path_lesson_id,
                pl.lesson_order,
                pl.course,
                pl.lesson_slug,
                pl.time_estimate,
                upp.locked,
                upp.completed,
                upp.completion_percentage,
                upp.started_at,
                upp.completed_at,
                l.title
            FROM path_lessons pl
            LEFT JOIN user_path_progress upp ON pl.path_lesson_id = upp.path_lesson_id AND upp.user_id = ?
            LEFT JOIN lessons l ON pl.course = l.course AND pl.lesson_slug = l.slug
            WHERE pl.path_id = ?
            ORDER BY pl.lesson_order ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $path_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lessons = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Calculate overall progress
    $completed = 0;
    foreach ($lessons as $lesson) {
        if ($lesson['completed']) {
            $completed++;
        }
    }
    $total = count($lessons);
    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
    
    echo json_encode([
        'success' => true,
        'path_id' => $path_id,
        'lessons' => $lessons,
        'overall_progress' => [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage
        ]
    ]);
}

function unlockNext() {
    global $conn, $user_id;
    
    $path_lesson_id = $_POST['path_lesson_id'] ?? 0;
    
    // Get current lesson info
    $stmt = $conn->prepare(
        "SELECT path_id, lesson_order FROM path_lessons WHERE path_lesson_id = ?"
    );
    $stmt->bind_param("i", $path_lesson_id);
    $stmt->execute();
    $currentLesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$currentLesson) {
        http_response_code(404);
        echo json_encode(['error' => 'Lesson not found']);
        return;
    }
    
    // Mark current lesson as completed
    $stmt = $conn->prepare(
        "UPDATE user_path_progress SET completed = TRUE, completed_at = NOW() 
         WHERE user_id = ? AND path_lesson_id = ?"
    );
    $stmt->bind_param("ii", $user_id, $path_lesson_id);
    $stmt->execute();
    $stmt->close();
    
    // Get next lesson in path
    $nextOrder = $currentLesson['lesson_order'] + 1;
    $stmt = $conn->prepare(
        "SELECT path_lesson_id FROM path_lessons 
         WHERE path_id = ? AND lesson_order = ?"
    );
    $stmt->bind_param("ii", $currentLesson['path_id'], $nextOrder);
    $stmt->execute();
    $nextLesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($nextLesson) {
        // Unlock next lesson
        $nextPathLessonId = $nextLesson['path_lesson_id'];
        $stmt = $conn->prepare(
            "UPDATE user_path_progress SET locked = FALSE 
             WHERE user_id = ? AND path_lesson_id = ?"
        );
        $stmt->bind_param("ii", $user_id, $nextPathLessonId);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Next lesson unlocked',
            'next_path_lesson_id' => $nextPathLessonId
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Path completed',
            'next_path_lesson_id' => null
        ]);
    }
}

function checkPrerequisites() {
    global $conn, $user_id;
    
    $path_lesson_id = $_GET['path_lesson_id'] ?? 0;
    $request_user_id = $_GET['user_id'] ?? $user_id;
    
    if ((int)$request_user_id !== $user_id) {
        http_response_code(403);
        echo json_encode(['error' => 'Cannot access other user data']);
        return;
    }
    
    // Get lesson and its path
    $stmt = $conn->prepare(
        "SELECT pl.path_id, pl.lesson_order FROM path_lessons pl 
         WHERE pl.path_lesson_id = ?"
    );
    $stmt->bind_param("i", $path_lesson_id);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['error' => 'Lesson not found']);
        return;
    }
    
    // Check if all previous lessons are completed
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as locked_count FROM user_path_progress upp
         JOIN path_lessons pl ON upp.path_lesson_id = pl.path_lesson_id
         WHERE upp.user_id = ? AND pl.path_id = ? AND pl.lesson_order < ? AND upp.locked = TRUE"
    );
    $stmt->bind_param("iii", $user_id, $lesson['path_id'], $lesson['lesson_order']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $canUnlock = $result['locked_count'] == 0;
    
    echo json_encode([
        'success' => true,
        'can_unlock' => $canUnlock,
        'lesson_order' => $lesson['lesson_order']
    ]);
}
?>
