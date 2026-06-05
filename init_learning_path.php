<?php
// Database initialization script for Learning Path system
// Run this once to set up all required tables and initial data

header('Content-Type: text/plain');

include 'db.php';

echo "=== Learning Path System Initialization ===\n\n";

$errors = [];
$success = [];

// 1. CREATE LEARNING_PATHS TABLE
echo "1. Creating learning_paths table...\n";
$sql1 = "CREATE TABLE IF NOT EXISTS learning_paths (
    path_id INT AUTO_INCREMENT PRIMARY KEY,
    path_name VARCHAR(255) NOT NULL UNIQUE,
    path_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql1)) {
    $success[] = "✓ learning_paths table created/verified";
    echo "   ✓ Success\n";
} else {
    $errors[] = "✗ Error creating learning_paths: " . $conn->error;
    echo "   ✗ Error: " . $conn->error . "\n";
}

// 2. CREATE PATH_LESSONS TABLE
echo "\n2. Creating path_lessons table...\n";
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

if ($conn->query($sql2)) {
    $success[] = "✓ path_lessons table created/verified";
    echo "   ✓ Success\n";
} else {
    $errors[] = "✗ Error creating path_lessons: " . $conn->error;
    echo "   ✗ Error: " . $conn->error . "\n";
}

// 3. CREATE USER_PATH_PROGRESS TABLE
echo "\n3. Creating user_path_progress table...\n";
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

if ($conn->query($sql3)) {
    $success[] = "✓ user_path_progress table created/verified";
    echo "   ✓ Success\n";
} else {
    $errors[] = "✗ Error creating user_path_progress: " . $conn->error;
    echo "   ✗ Error: " . $conn->error . "\n";
}

// 4. INSERT DEFAULT LEARNING PATH
echo "\n4. Checking/inserting default learning path...\n";
$checkPath = $conn->query("SELECT path_id FROM learning_paths WHERE path_name = 'Web Development Fundamentals' LIMIT 1");
$pathId = null;

if ($checkPath && $checkPath->num_rows > 0) {
    $pathRow = $checkPath->fetch_assoc();
    $pathId = $pathRow['path_id'];
    echo "   ℹ Path already exists (ID: $pathId)\n";
} else {
    $stmt = $conn->prepare(
        "INSERT INTO learning_paths (path_name, path_description) VALUES (?, ?)"
    );
    $pathName = "Web Development Fundamentals";
    $pathDesc = "Master the foundations of web development: HTML for structure, CSS for styling, JavaScript for interactivity, and PHP for backend logic.";
    $stmt->bind_param("ss", $pathName, $pathDesc);
    
    if ($stmt->execute()) {
        $pathId = $conn->insert_id;
        $success[] = "✓ Learning path created (ID: $pathId)";
        echo "   ✓ Path created (ID: $pathId)\n";
    } else {
        $errors[] = "✗ Error inserting learning path: " . $stmt->error;
        echo "   ✗ Error: " . $stmt->error . "\n";
    }
    $stmt->close();
}

// 5. INSERT PATH LESSONS
if ($pathId) {
    echo "\n5. Adding lessons to path...\n";
    
    // Clear existing path lessons for this path first
    $conn->query("DELETE FROM path_lessons WHERE path_id = $pathId");
    
    $pathLessons = [
        ['course' => 'HTML', 'slug' => 'intro', 'order' => 1, 'time' => 20],
        ['course' => 'CSS', 'slug' => 'basics', 'order' => 2, 'time' => 25],
        ['course' => 'JavaScript', 'slug' => 'intro', 'order' => 3, 'time' => 30],
        ['course' => 'PHP', 'slug' => 'intro', 'order' => 4, 'time' => 35],
        ['course' => 'JavaScript', 'slug' => 'basics', 'order' => 5, 'time' => 30]
    ];
    
    $lessonCount = 0;
    foreach ($pathLessons as $lesson) {
        $stmt = $conn->prepare(
            "INSERT INTO path_lessons (path_id, course, lesson_slug, lesson_order, time_estimate) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issii", $pathId, $lesson['course'], $lesson['slug'], $lesson['order'], $lesson['time']);
        
        if ($stmt->execute()) {
            $lessonCount++;
            echo "   ✓ Added: {$lesson['course']} - {$lesson['slug']} (Order {$lesson['order']})\n";
        } else {
            echo "   ✗ Error adding lesson: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    $success[] = "✓ $lessonCount lessons added to path";
}

// 6. INITIALIZE USER PROGRESS
echo "\n6. Initializing user progress...\n";
$usersResult = $conn->query("SELECT user_id FROM users");

if ($usersResult) {
    $userCount = 0;
    while ($user = $usersResult->fetch_assoc()) {
        $user_id = $user['user_id'];
        
        // Get all path_lessons for this path
        $pathLessonsResult = $conn->query(
            "SELECT path_lesson_id, lesson_order FROM path_lessons WHERE path_id = $pathId"
        );
        
        if ($pathLessonsResult) {
            $progressCount = 0;
            while ($pathLesson = $pathLessonsResult->fetch_assoc()) {
                $path_lesson_id = $pathLesson['path_lesson_id'];
                
                // Check if progress already exists
                $check = $conn->prepare(
                    "SELECT progress_id FROM user_path_progress WHERE user_id = ? AND path_lesson_id = ?"
                );
                $check->bind_param("ii", $user_id, $path_lesson_id);
                $check->execute();
                $existsResult = $check->get_result();
                $exists = $existsResult->num_rows > 0;
                $check->close();
                
                if (!$exists) {
                    // First lesson unlocked, rest locked
                    $locked = ($pathLesson['lesson_order'] > 1) ? 1 : 0;
                    $stmt = $conn->prepare(
                        "INSERT INTO user_path_progress (user_id, path_lesson_id, locked, completed, completion_percentage) 
                         VALUES (?, ?, ?, 0, 0)"
                    );
                    $stmt->bind_param("iii", $user_id, $path_lesson_id, $locked);
                    
                    if ($stmt->execute()) {
                        $progressCount++;
                    } else {
                        echo "   ✗ Error initializing progress for user $user_id: " . $stmt->error . "\n";
                    }
                    $stmt->close();
                } else {
                    $progressCount++;
                }
            }
            $userCount++;
        }
    }
    $success[] = "✓ Progress initialized for $userCount users";
    echo "   ✓ Initialized progress for $userCount users\n";
} else {
    echo "   ℹ No users found to initialize\n";
}

// 7. SUMMARY
echo "\n=== INITIALIZATION COMPLETE ===\n";
echo "\nSUCCESSES (" . count($success) . "):\n";
foreach ($success as $msg) {
    echo "  $msg\n";
}

if (!empty($errors)) {
    echo "\nERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
}

echo "\n✓ Learning Path system is ready!\n";
echo "→ Access the learning path at: learning_path.php?path_id=1\n";
?>
