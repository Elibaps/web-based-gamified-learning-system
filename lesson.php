<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// ── Whitelist allowed courses ─────────────────────────────────────────────
$allowed_courses = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Java', 'C++'];
$course = (isset($_GET['course']) && in_array($_GET['course'], $allowed_courses, true))
    ? $_GET['course']
    : 'HTML';

// ── Whitelist allowed lesson slugs ────────────────────────────────────────
$allowed_slugs = ['intro', 'basics', 'syntax', 'practice', 'quiz'];
$lesson_slug = (isset($_GET['lesson']) && in_array($_GET['lesson'], $allowed_slugs, true))
    ? $_GET['lesson']
    : 'intro';

// ── Fetch user_id for progress tracking ──────────────────────────────────
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

// ── Fetch lesson content from DB ──────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT title, content FROM lessons WHERE course = ? AND slug = ?"
);
$stmt->bind_param("ss", $course, $lesson_slug);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Fetch sidebar items for this course ───────────────────────────────────
$stmt = $conn->prepare(
    "SELECT slug, title FROM lessons WHERE course = ? ORDER BY sort_order ASC"
);
$stmt->bind_param("s", $course);
$stmt->execute();
$sidebarLessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Mark lesson as completed (skip practice/quiz — action-based only) ─────
if ($user_id && !in_array($lesson_slug, ['practice', 'quiz'], true)) {
    $stmt = $conn->prepare(
        "INSERT IGNORE INTO user_progress (user_id, course, lesson_slug)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $user_id, $course, $lesson_slug);
    $stmt->execute();
    $stmt->close();
}

$safeCourse = htmlspecialchars($course, ENT_QUOTES, 'UTF-8');
$pageTitle  = $safeCourse . ' Lessons — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">

<?php include 'includes/navbar_simple.php'; ?>

<div class="lesson-page">

  <!-- SIDEBAR -->
  <div class="lesson-sidebar">
    <h2><?php echo $safeCourse; ?></h2>

    <?php foreach ($sidebarLessons as $item):
        $isActive = ($item['slug'] === $lesson_slug) ? 'active' : '';
        $href = 'lesson.php?course=' . urlencode($course) . '&lesson=' . urlencode($item['slug']);
    ?>
      <a href="<?php echo $href; ?>" class="lesson-item <?php echo $isActive; ?>">
        <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- LESSON CONTENT -->
  <div class="lesson-content">
    <?php if ($lesson): ?>
      <?php
      // Lesson HTML is authored/seeded content (trusted), not user input.
      // Safe to output as raw HTML — never echoed from user-submitted data.
      echo $lesson['content'];
      ?>

      <?php if ($lesson_slug === 'practice'): ?>
        <button onclick="startBattle('<?php echo $safeCourse; ?>')">⚔️ Start Practice Battle</button>
      <?php elseif ($lesson_slug === 'quiz'): ?>
        <button onclick="startQuiz('<?php echo $safeCourse; ?>')">📝 Start Quiz</button>
      <?php endif; ?>

    <?php else: ?>
      <h1>Lesson Not Found</h1>
      <p>This lesson is not available yet. Check back soon!</p>
    <?php endif; ?>
  </div>

</div>

<?php include 'includes/footer.php'; ?>