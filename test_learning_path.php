<?php
header('Content-Type: text/html; charset=utf-8');
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Path System - Integration Test</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #4ade80;
            padding: 20px;
            margin: 0;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { text-shadow: 0 0 10px rgba(74, 222, 128, 0.6); }
        h2 { color: #00ff00; margin-top: 30px; border-bottom: 2px solid #4ade80; padding-bottom: 10px; }
        .test { margin: 15px 0; padding: 15px; border: 2px solid #4ade80; border-radius: 4px; }
        .test.pass { background: rgba(74, 222, 128, 0.1); border-color: #00ff00; }
        .test.fail { background: rgba(255, 0, 0, 0.1); border-color: #ff0000; color: #ff0000; }
        .test.info { background: rgba(74, 222, 128, 0.05); border-color: #4ade80; }
        .status { font-weight: bold; padding: 3px 8px; border-radius: 3px; display: inline-block; }
        .status.pass { background: #00ff00; color: #000; }
        .status.fail { background: #ff0000; color: #fff; }
        .status.info { background: #4ade80; color: #000; }
        pre { background: rgba(0,0,0,0.5); padding: 10px; overflow-x: auto; border-radius: 4px; }
        a { color: #00ff00; text-decoration: none; border-bottom: 1px dashed #4ade80; }
        a:hover { color: #00ffff; }
        .actions { margin-top: 20px; padding: 20px; background: rgba(74, 222, 128, 0.05); border: 2px dashed #4ade80; border-radius: 4px; }
        .btn { display: inline-block; padding: 10px 20px; background: #4ade80; color: #000; text-decoration: none; border-radius: 4px; margin: 5px 5px 5px 0; font-weight: bold; border: 2px solid #4ade80; cursor: pointer; }
        .btn:hover { background: transparent; color: #4ade80; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #4ade80; padding: 8px; text-align: left; }
        th { background: rgba(74, 222, 128, 0.1); }
    </style>
</head>
<body>

<div class="container">
    <h1>🗺️ Learning Path System - Integration Test</h1>
    
    <?php
    $testsRun = 0;
    $testsPassed = 0;
    $testsFailed = 0;
    
    function test($name, $condition, $details = '') {
        global $testsRun, $testsPassed, $testsFailed;
        $testsRun++;
        
        $class = $condition ? 'pass' : 'fail';
        $status = $condition ? '✓ PASS' : '✗ FAIL';
        $statusClass = $condition ? 'pass' : 'fail';
        
        if ($condition) {
            $testsPassed++;
        } else {
            $testsFailed++;
        }
        
        echo "<div class='test $class'>";
        echo "<span class='status $statusClass'>$status</span> — $name";
        if ($details) {
            echo "<pre>$details</pre>";
        }
        echo "</div>";
    }
    
    // TEST 1: Check database tables exist
    echo "<h2>1️⃣ Database Table Verification</h2>";
    
    $tables = ['learning_paths', 'path_lessons', 'user_path_progress'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $result && $result->num_rows > 0;
        test("Table: $table exists", $exists);
        $allTablesExist = $allTablesExist && $exists;
    }
    
    // TEST 2: Check if data was inserted
    echo "<h2>2️⃣ Data Initialization</h2>";
    
    $pathResult = $conn->query("SELECT COUNT(*) as count FROM learning_paths");
    $pathCount = $pathResult ? $pathResult->fetch_assoc()['count'] : 0;
    test("Learning paths created", $pathCount > 0, "Found $pathCount path(s)");
    
    $lessonsResult = $conn->query("SELECT COUNT(*) as count FROM path_lessons");
    $lessonCount = $lessonsResult ? $lessonsResult->fetch_assoc()['count'] : 0;
    test("Path lessons created", $lessonCount >= 5, "Found $lessonCount lesson(s)");
    
    $usersResult = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $usersResult ? $usersResult->fetch_assoc()['count'] : 0;
    test("Users exist in database", $userCount > 0, "Found $userCount user(s)");
    
    // TEST 3: Check API endpoints availability
    echo "<h2>3️⃣ API Endpoints</h2>";
    
    test("learning_path_api.php file exists", file_exists('learning_path_api.php'));
    test("learning_path.php file exists", file_exists('learning_path.php'));
    test("init_learning_path.php file exists", file_exists('init_learning_path.php'));
    
    // TEST 4: Check file modifications
    echo "<h2>4️⃣ File Modifications</h2>";
    
    $quizContent = file_get_contents('quiz.php');
    test("quiz.php modified with unlock logic", strpos($quizContent, 'unlock_next') !== false);
    
    $lessonContent = file_get_contents('lesson.php');
    test("lesson.php modified with path link", strpos($lessonContent, 'learning_path.php') !== false);
    
    $dashboardContent = file_get_contents('dashboard.php');
    test("dashboard.php modified with path button", strpos($dashboardContent, 'Learning Path') !== false);
    
    // TEST 5: Sample data query
    echo "<h2>5️⃣ Sample Data</h2>";
    
    if ($pathCount > 0) {
        $samplePath = $conn->query("SELECT * FROM learning_paths LIMIT 1")->fetch_assoc();
        test("Sample learning path retrieved", $samplePath !== null, 
            "Path: {$samplePath['path_name']}\nDescription: {$samplePath['path_description']}");
        
        if ($lessonCount > 0) {
            $sampleLesson = $conn->query("SELECT * FROM path_lessons ORDER BY lesson_order ASC LIMIT 1")->fetch_assoc();
            test("Sample lesson retrieved", $sampleLesson !== null,
                "Lesson {$sampleLesson['lesson_order']}: {$sampleLesson['course']} - {$sampleLesson['lesson_slug']}\nTime: {$sampleLesson['time_estimate']} min");
        }
    }
    
    // TEST 6: User progress initialization
    echo "<h2>6️⃣ User Progress</h2>";
    
    if ($userCount > 0 && $lessonCount > 0) {
        $progressResult = $conn->query("SELECT COUNT(*) as count FROM user_path_progress");
        $progressCount = $progressResult ? $progressResult->fetch_assoc()['count'] : 0;
        $expectedProgress = $userCount * $lessonCount;
        test("User progress initialized", $progressCount > 0, 
            "Progress records: $progressCount (Expected ~$expectedProgress)");
        
        // Check first lesson is unlocked for all users
        $unlockedResult = $conn->query(
            "SELECT COUNT(*) as count FROM user_path_progress upp 
             JOIN path_lessons pl ON upp.path_lesson_id = pl.path_lesson_id 
             WHERE pl.lesson_order = 1 AND upp.locked = FALSE"
        );
        $unlockedCount = $unlockedResult ? $unlockedResult->fetch_assoc()['count'] : 0;
        test("First lessons are unlocked", $unlockedCount >= 1, 
            "Unlocked first lessons: $unlockedCount");
    }
    
    // TEST 7: Data integrity
    echo "<h2>7️⃣ Data Integrity</h2>";
    
    $duplicateResult = $conn->query(
        "SELECT COUNT(*) as count FROM path_lessons GROUP BY path_id, lesson_order HAVING COUNT(*) > 1"
    );
    $duplicateCount = $duplicateResult && $duplicateResult->num_rows > 0 ? $duplicateResult->num_rows : 0;
    test("No duplicate lessons in path", $duplicateCount === 0, 
        "Duplicate entries: $duplicateCount");
    
    // TEST 8: File sizes and content
    echo "<h2>8️⃣ File Analysis</h2>";
    
    $filesCheck = [
        'learning_path_api.php' => 500,
        'learning_path.php' => 1000,
        'init_learning_path.php' => 1000
    ];
    
    foreach ($filesCheck as $file => $minSize) {
        $size = file_exists($file) ? filesize($file) : 0;
        test("$file has content", $size > $minSize, "File size: " . number_format($size) . " bytes");
    }
    
    // Summary
    echo "<h2>📊 Test Summary</h2>";
    $color = $testsFailed === 0 ? 'pass' : 'fail';
    echo "<div class='test $color'>";
    echo "<p><strong>Total Tests:</strong> $testsRun</p>";
    echo "<p><strong>Passed:</strong> $testsPassed</p>";
    echo "<p><strong>Failed:</strong> $testsFailed</p>";
    if ($testsFailed === 0) {
        echo "<p style='color: #00ff00; font-size: 1.2em;'>✓ All tests passed!</p>";
    }
    echo "</div>";
    
    // Action buttons
    echo "<div class='actions'>";
    echo "<h3>🚀 Next Steps:</h3>";
    
    if ($testsFailed === 0) {
        echo "<p><strong>✓ System is ready!</strong> You can now:</p>";
        echo "<ol>";
        echo "<li><a href='dashboard.php' class='btn'>Go to Dashboard</a></li>";
        echo "<li><a href='learning_path.php?path_id=1' class='btn'>View Learning Path</a></li>";
        echo "<li><a href='lesson.php?course=HTML&lesson=intro' class='btn'>Start First Lesson</a></li>";
        echo "</ol>";
    } else {
        echo "<p><strong>⚠ Some tests failed.</strong> Please:</p>";
        echo "<ol>";
        echo "<li><a href='init_learning_path.php' class='btn'>Run Initialization Script</a></li>";
        echo "<li>Check database connection in db.php</li>";
        echo "<li>Verify all files were created correctly</li>";
        echo "</ol>";
    }
    
    echo "</div>";
    
    // Debug table
    echo "<h2>📋 Detailed Data</h2>";
    
    if ($pathCount > 0) {
        echo "<h3>Learning Paths</h3>";
        $paths = $conn->query("SELECT * FROM learning_paths LIMIT 5");
        if ($paths) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Lessons</th></tr>";
            while ($path = $paths->fetch_assoc()) {
                $lessonCount = $conn->query("SELECT COUNT(*) as count FROM path_lessons WHERE path_id = {$path['path_id']}")->fetch_assoc()['count'];
                echo "<tr>";
                echo "<td>{$path['path_id']}</td>";
                echo "<td>{$path['path_name']}</td>";
                echo "<td>$lessonCount</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    if ($lessonCount > 0) {
        echo "<h3>Path Lessons (First 10)</h3>";
        $lessons = $conn->query("SELECT * FROM path_lessons LIMIT 10");
        if ($lessons) {
            echo "<table>";
            echo "<tr><th>Order</th><th>Course</th><th>Lesson</th><th>Time (min)</th></tr>";
            while ($lesson = $lessons->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$lesson['lesson_order']}</td>";
                echo "<td>{$lesson['course']}</td>";
                echo "<td>{$lesson['lesson_slug']}</td>";
                echo "<td>{$lesson['time_estimate']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    ?>
</div>

</body>
</html>
<?php
?>
