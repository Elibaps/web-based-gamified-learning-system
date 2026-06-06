<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'] ?? null;

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

$path_id = $_GET['path_id'] ?? 1; // Default to first path

// Get path info
$stmt = $conn->prepare(
    "SELECT path_name, path_description FROM learning_paths WHERE path_id = ?"
);
$stmt->bind_param("i", $path_id);
$stmt->execute();
$pathInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get path lessons with progress
$stmt = $conn->prepare(
    "SELECT 
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
    ORDER BY pl.lesson_order ASC"
);
$stmt->bind_param("ii", $user_id, $path_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate progress
$completed = 0;
$currentLesson = null;
foreach ($lessons as $lesson) {
    if ($lesson['completed']) {
        $completed++;
    }
    if (!$lesson['locked'] && !$lesson['completed'] && !$currentLesson) {
        $currentLesson = $lesson;
    }
}
$total = count($lessons);
$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

$pageTitle = 'Learning Path — CodeNest';
include 'includes/head.php';
?>
<body class="learning-path-page">

<?php include 'includes/navbar.php'; ?>

<div class="learning-path-container">
    <!-- HEADER -->
    <div class="path-header">
        <h1 style="color: var(--primary-color); text-shadow: 0 0 10px rgba(74, 222, 128, 0.8); margin-bottom: 5px;">
            🗺️ <?php echo htmlspecialchars($pathInfo['path_name'], ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p style="color: var(--muted-color); margin-bottom: 20px;">
            <?php echo htmlspecialchars($pathInfo['path_description'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </div>

    <!-- PROGRESS BAR -->
    <div class="progress-container">
        <div class="progress-text">
            <span style="color: var(--primary-color); font-weight: bold;">Progress: <?php echo $percentage; ?>%</span>
            <span style="color: var(--muted-color); font-size: 0.9em;"><?php echo $completed; ?>/<?php echo $total; ?> Completed</span>
        </div>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%;">
                <span class="progress-text-bar"><?php echo $percentage; ?>%</span>
            </div>
        </div>
    </div>

    <!-- ROADMAP -->
    <div class="roadmap">
        <?php foreach ($lessons as $index => $lesson): 
            $isLocked = $lesson['locked'];
            $isCompleted = $lesson['completed'];
            $isCurrent = !$isLocked && !$isCompleted;
            
            // Determine status icon
            if ($isLocked) {
                $statusIcon = '🔒';
                $statusText = 'Locked';
                $statusClass = 'locked';
            } elseif ($isCompleted) {
                $statusIcon = '✅';
                $statusText = 'Completed';
                $statusClass = 'completed';
            } else {
                $statusIcon = '⭐';
                $statusText = 'Current';
                $statusClass = 'current';
            }
            
            $safeTitle = htmlspecialchars($lesson['title'] ?? $lesson['course'], ENT_QUOTES, 'UTF-8');
            $lessonUrl = 'lesson.php?course=' . urlencode($lesson['course']) . '&lesson=' . urlencode($lesson['lesson_slug']);
        ?>
            <div class="roadmap-item <?php echo $statusClass; ?>" data-order="<?php echo $lesson['lesson_order']; ?>">
                <!-- Node -->
                <div class="node" title="<?php echo $statusText; ?>">
                    <div class="node-icon"><?php echo $statusIcon; ?></div>
                    <?php if ($isCurrent): ?>
                        <div class="pulse"></div>
                    <?php endif; ?>
                </div>

                <!-- Connector (except for last item) -->
                <?php if ($index < count($lessons) - 1): ?>
                    <div class="connector"></div>
                <?php endif; ?>

                <!-- Lesson Info Card -->
                <div class="lesson-card">
                    <div class="lesson-header">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?php echo $lesson['lesson_order']; ?>. <?php echo $safeTitle; ?>
                        </h3>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusIcon; ?> <?php echo $statusText; ?></span>
                    </div>

                    <div class="lesson-meta">
                        <span class="course-tag"><?php echo htmlspecialchars($lesson['course'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="time-tag">⏱️ <?php echo $lesson['time_estimate']; ?> min</span>
                    </div>

                    <?php if ($isCompleted): ?>
                        <div class="completion-info">
                            <p style="color: var(--primary-color); margin: 5px 0;">✅ Completed on <?php echo date('M d, Y', strtotime($lesson['completed_at'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!$isLocked): ?>
                        <a href="<?php echo $lessonUrl; ?>" class="btn-connect">
                            <?php echo ($isCompleted ? '📖 Review' : '▶️ Start Lesson'); ?>
                        </a>
                    <?php else: ?>
                        <button class="btn-locked" disabled title="Complete previous lessons to unlock">
                            🔒 Locked
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- NEXT LESSON PREVIEW -->
    <?php if ($currentLesson): ?>
        <div class="next-lesson-preview">
            <div class="preview-content">
                <h2 style="color: var(--primary-color); margin-top: 0;">⭐ Next Lesson</h2>
                <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($currentLesson['title'] ?? $currentLesson['course'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p style="color: var(--muted-color); margin: 10px 0;">
                    Learn about <?php echo htmlspecialchars($currentLesson['course'], ENT_QUOTES, 'UTF-8'); ?>. 
                    Estimated time: <strong><?php echo $currentLesson['time_estimate']; ?> minutes</strong>
                </p>
                <a href="lesson.php?course=<?php echo urlencode($currentLesson['course']); ?>&lesson=<?php echo urlencode($currentLesson['lesson_slug']); ?>" class="btn-primary">
                    ▶️ Start Lesson Now
                </a>
            </div>
        </div>
    <?php elseif ($percentage === 100): ?>
        <div class="path-complete">
            <h2 style="color: var(--primary-color);">🏆 Path Complete!</h2>
            <p>Congratulations! You've completed all lessons in this learning path.</p>
            <a href="dashboard.php" class="btn-primary">Return to Dashboard</a>
        </div>
    <?php endif; ?>

    <!-- DEBUG INFO -->
    <div style="margin-top: 40px; padding: 15px; background: var(--card-bg); border: 2px solid var(--muted-color); border-radius: 6px; font-size: 0.9em; color: var(--muted-color);">
        <p style="margin: 0;">📊 Debug Info: User ID: <?php echo $user_id; ?> | Path ID: <?php echo $path_id; ?> | Lessons: <?php echo $total; ?></p>
    </div>
</div>

<style>
.learning-path-page {
    padding: 20px;
}

.learning-path-container {
    max-width: 1000px;
    margin: 0 auto;
}

.path-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 20px;
    border: 2px solid var(--primary-color);
    border-radius: 6px;
    background: var(--card-bg);
}

.progress-container {
    margin-bottom: 40px;
    padding: 20px;
    background: var(--card-bg);
    border: 2px solid var(--primary-color);
    border-radius: 6px;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: bold;
}

.progress-bar-bg {
    width: 100%;
    height: 30px;
    background: rgba(0, 0, 0, 0.5);
    border: 2px solid var(--primary-color);
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), rgba(74, 222, 128, 0.6));
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.3s ease;
    box-shadow: 0 0 10px rgba(74, 222, 128, 0.5);
}

.progress-text-bar {
    color: var(--bg-color);
    font-weight: bold;
    text-shadow: none;
}

.roadmap {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 40px;
}

.roadmap-item {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 20px;
    align-items: stretch;
}

.node {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.node-icon {
    font-size: 3em;
    text-align: center;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--card-bg);
    border: 3px solid var(--primary-color);
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(74, 222, 128, 0.4);
}

.roadmap-item.locked .node-icon {
    opacity: 0.5;
    border-color: var(--muted-color);
}

.roadmap-item.completed .node-icon {
    animation: pulse-complete 2s infinite;
}

.roadmap-item.current .node-icon {
    border-color: var(--primary-color);
    background: rgba(74, 222, 128, 0.1);
}

.pulse {
    position: absolute;
    width: 80px;
    height: 80px;
    border: 3px solid var(--primary-color);
    border-radius: 50%;
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

@keyframes pulse-complete {
    0%, 100% {
        box-shadow: 0 0 10px rgba(74, 222, 128, 0.4);
    }
    50% {
        box-shadow: 0 0 20px rgba(74, 222, 128, 0.8);
    }
}

.connector {
    width: 3px;
    background: linear-gradient(180deg, var(--primary-color), rgba(74, 222, 128, 0.2));
    margin: -20px 0;
    grid-column: 1;
    min-height: 20px;
}

.roadmap-item.locked .connector {
    background: var(--muted-color);
    opacity: 0.3;
}

.lesson-card {
    background: var(--card-bg);
    border: 2px solid var(--primary-color);
    border-radius: 6px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.roadmap-item.locked .lesson-card {
    border-color: var(--muted-color);
    opacity: 0.7;
    background: rgba(148, 163, 184, 0.05);
}

.lesson-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
    background: var(--primary-color);
    color: var(--bg-color);
}

.status-badge.locked {
    background: var(--muted-color);
}

.status-badge.completed {
    background: rgba(74, 222, 128, 0.3);
    color: var(--primary-color);
}

.status-badge.current {
    background: rgba(74, 222, 128, 0.5);
    color: var(--bg-color);
}

.lesson-meta {
    display: flex;
    gap: 10px;
    font-size: 0.9em;
}

.course-tag {
    background: rgba(74, 222, 128, 0.2);
    color: var(--primary-color);
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}

.time-tag {
    color: var(--muted-color);
}

.completion-info {
    background: rgba(74, 222, 128, 0.1);
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid var(--primary-color);
}

.btn-connect,
.btn-locked {
    align-self: flex-start;
    padding: 10px 20px;
    border: 2px solid var(--primary-color);
    background: var(--bg-color);
    color: var(--primary-color);
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    font-family: 'Minecraft', monospace;
}

.btn-connect:hover {
    background: var(--primary-color);
    color: var(--bg-color);
    box-shadow: 0 0 15px rgba(74, 222, 128, 0.5);
}

.btn-locked {
    opacity: 0.5;
    cursor: not-allowed;
    color: var(--muted-color);
    border-color: var(--muted-color);
}

.next-lesson-preview {
    background: var(--card-bg);
    border: 3px solid var(--primary-color);
    border-radius: 6px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 0 20px rgba(74, 222, 128, 0.3);
}

.preview-content {
    text-align: center;
}

.preview-content p {
    margin: 15px 0;
    color: var(--text-color);
}

.btn-primary {
    display: inline-block;
    padding: 15px 30px;
    background: var(--primary-color);
    color: var(--bg-color);
    border: 2px solid var(--primary-color);
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    text-decoration: none;
    font-family: 'Minecraft', monospace;
    text-transform: uppercase;
    font-size: 1em;
}

.btn-primary:hover {
    box-shadow: 0 0 20px rgba(74, 222, 128, 0.6);
    transform: scale(1.05);
}

.path-complete {
    background: var(--card-bg);
    border: 3px solid var(--primary-color);
    border-radius: 6px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 0 30px rgba(74, 222, 128, 0.4);
    margin-bottom: 40px;
}

.path-complete h2 {
    font-size: 2em;
    margin: 0 0 15px 0;
}

@media (max-width: 768px) {
    .roadmap-item {
        grid-template-columns: 60px 1fr;
    }
    
    .node-icon {
        font-size: 2em;
        width: 60px;
        height: 60px;
    }
    
    .pulse {
        width: 60px;
        height: 60px;
    }
    
    .lesson-card {
        padding: 15px;
    }
    
    .lesson-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .status-badge {
        align-self: flex-start;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
