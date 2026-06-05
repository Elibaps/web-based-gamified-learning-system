<?php
// Setup Learning Path tables and initial data
include 'db.php';

// Create learning_paths table
$sql1 = "CREATE TABLE IF NOT EXISTS learning_paths (
    path_id INT AUTO_INCREMENT PRIMARY KEY,
    path_name VARCHAR(255) NOT NULL,
    path_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create path_lessons table (maps lessons to paths in order)
$sql2 = "CREATE TABLE IF NOT EXISTS path_lessons (
    path_lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    path_id INT NOT NULL,
    lesson_id INT,
    course VARCHAR(100),
    lesson_slug VARCHAR(100),
    lesson_order INT NOT NULL,
    time_estimate INT DEFAULT 15,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (path_id) REFERENCES learning_paths(path_id) ON DELETE CASCADE,
    UNIQUE KEY unique_path_lesson (path_id, lesson_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create user_path_progress table
$sql3 = "CREATE TABLE IF NOT EXISTS user_path_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    path_lesson_id INT NOT NULL,
    locked BOOLEAN DEFAULT TRUE,
    completed BOOLEAN DEFAULT FALSE,
    completion_percentage INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (path_lesson_id) REFERENCES path_lessons(path_lesson_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_path_lesson (user_id, path_lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$errors = [];

// Execute table creations
if (!$conn->query($sql1)) {
    $errors[] = "Error creating learning_paths: " . $conn->error;
}
if (!$conn->query($sql2)) {
    $errors[] = "Error creating path_lessons: " . $conn->error;
}
if (!$conn->query($sql3)) {
    $errors[] = "Error creating user_path_progress: " . $conn->error;
}

// Check if default path exists
$checkPath = $conn->query("SELECT path_id FROM learning_paths WHERE path_name = 'Web Development Fundamentals' LIMIT 1");
$pathExists = $checkPath && $checkPath->num_rows > 0;

if (!$pathExists) {
    // Insert default learning path
    $sql4 = "INSERT INTO learning_paths (path_name, path_description) VALUES (
        'Web Development Fundamentals',
        'Master the foundations of web development: HTML for structure, CSS for styling, JavaScript for interactivity, and PHP for backend logic.'
    )";
    
    if (!$conn->query($sql4)) {
        $errors[] = "Error inserting learning path: " . $conn->error;
    } else {
        $path_id = $conn->insert_id;
        
        // Define path lessons
        $pathLessons = [
            ['course' => 'HTML', 'slug' => 'intro', 'order' => 1, 'time' => 20],
            ['course' => 'CSS', 'slug' => 'basics', 'order' => 2, 'time' => 25],
            ['course' => 'JavaScript', 'slug' => 'intro', 'order' => 3, 'time' => 30],
            ['course' => 'PHP', 'slug' => 'intro', 'order' => 4, 'time' => 35],
            ['course' => 'JavaScript', 'slug' => 'basics', 'order' => 5, 'time' => 30]
        ];
        
        // Insert path lessons
        foreach ($pathLessons as $lesson) {
            $sql = "INSERT INTO path_lessons (path_id, course, lesson_slug, lesson_order, time_estimate) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issii", $path_id, $lesson['course'], $lesson['slug'], $lesson['order'], $lesson['time']);
            if (!$stmt->execute()) {
                $errors[] = "Error inserting path lesson: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Initialize user progress for all users and path_lessons
$users = $conn->query("SELECT user_id FROM users");
if ($users) {
    while ($user = $users->fetch_assoc()) {
        $user_id = $user['user_id'];
        
        // Get all path_lessons
        $pathLessons = $conn->query("SELECT path_lesson_id FROM path_lessons");
        if ($pathLessons) {
            while ($pathLesson = $pathLessons->fetch_assoc()) {
                $path_lesson_id = $pathLesson['path_lesson_id'];
                
                // Check if progress already exists
                $check = $conn->prepare("SELECT progress_id FROM user_path_progress WHERE user_id = ? AND path_lesson_id = ?");
                $check->bind_param("ii", $user_id, $path_lesson_id);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();
                
                if (!$exists) {
                    // First lesson unlocked, rest locked
                    $locked = ($pathLesson['path_lesson_id'] > 1) ? 1 : 0;
                    $sql = "INSERT INTO user_path_progress (user_id, path_lesson_id, locked, completed, completion_percentage) 
                            VALUES (?, ?, ?, 0, 0)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", $user_id, $path_lesson_id, $locked);
                    if (!$stmt->execute()) {
                        $errors[] = "Error initializing user progress: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Output results
header('Content-Type: application/json');
if (empty($errors)) {
    echo json_encode(['success' => true, 'message' => 'Learning path tables created and initialized successfully']);
} else {
    echo json_encode(['success' => false, 'errors' => $errors]);
}
?>
