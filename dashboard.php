<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// ── Fetch user data (prepared statement, prevents SQL injection) ──────────
$stmt = $conn->prepare(
    "SELECT user_id, username, level, exp, coins FROM users WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    // User deleted mid-session
    session_destroy();
    header('Location: login.php');
    exit();
}

// Cache user_id in session to avoid repeat queries
$_SESSION['user_id'] = $row['user_id'];
$user_id  = $row['user_id'];

// ── XP calculation ────────────────────────────────────────────────────────
$level     = (int)$row['level'];
$exp       = (int)$row['exp'];
$expNeeded = $level * 100;
$percent   = $expNeeded > 0 ? min(100, round(($exp / $expNeeded) * 100)) : 0;

// ── Leaderboard ───────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT username, level, exp FROM users ORDER BY level DESC, exp DESC LIMIT 5"
);
$stmt->execute();
$leaders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Learning path progress (dynamic, from user_progress table) ────────────
$stmt = $conn->prepare("
    SELECT  l.course,
            COUNT(DISTINCT l.lesson_id)    AS total,
            COUNT(DISTINCT up.progress_id) AS completed
    FROM    lessons l
    LEFT JOIN user_progress up
           ON  up.course      = l.course
           AND up.lesson_slug = l.slug
           AND up.user_id     = ?
    GROUP BY l.course
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$progressRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$progress = [];
foreach ($progressRows as $p) {
    $pct = $p['total'] > 0 ? round(($p['completed'] / $p['total']) * 100) : 0;
    $progress[$p['course']] = [
        'percent'   => $pct,
        'completed' => (int)$p['completed'],
        'total'     => (int)$p['total'],
    ];
}

$pageTitle = 'Dashboard — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">

<?php include 'includes/navbar.php'; ?>

<!-- ======================================================
     BIG DASHBOARD HERO
     Static artwork = PNG assets
     Dynamic player/leaderboard values = PHP + HTML/CSS
     ====================================================== -->
<section class="top-bar dashboard-hero" aria-label="Player dashboard overview">

    <section class="player-status-card" aria-label="Player status">
        <div class="player-status-content">

            <div class="player-summary">
                <img src="assets/player.png" class="profile-pic" alt="Player Avatar">

                <div class="player-identity">
                    <h2><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <div class="player-level-badge">LEVEL <?php echo $level; ?></div>
                </div>
            </div>

            <div class="player-xp">
                <span class="player-xp-label">XP PROGRESS</span>

                <div class="xp-meter" aria-label="XP progress">
                    <div class="xp-meter-track">
                        <div class="xp-fill" data-percent="<?php echo $percent; ?>"></div>
                    </div>
                </div>

                <small><?php echo $exp; ?> / <?php echo $expNeeded; ?> XP</small>
            </div>

        </div>
    </section>

    <section class="mini-leaderboard" aria-label="Top players">
        <div class="leaderboard-list">
            <?php foreach ($leaders as $index => $leader): ?>
                <?php $rank = $index + 1; ?>

                <div class="leader">
                    <div class="leader-rank">
                        <?php if ($rank <= 3): ?>
                            <img
                                src="assets/leaderboard-top-<?php echo $rank; ?>.png"
                                alt="Rank <?php echo $rank; ?>"
                            >
                        <?php else: ?>
                            <span><?php echo $rank; ?></span>
                        <?php endif; ?>
                    </div>

                    <span class="leader-name">
                        <?php echo htmlspecialchars($leader['username'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>

                    <span class="leader-level">
                        LV. <?php echo (int)$leader['level']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <a class="leaderboard-view-button" href="leaderboard.php">
            <span>VIEW LEADERBOARD</span>
        </a>
    </section>

    <nav class="top-actions" aria-label="Dashboard actions">

        <a class="dashboard-action learning-path-action"
           href="learning_path.php?path_id=1">
            <img src="assets/learningpath-icon.png"
                 class="dashboard-action-icon"
                 alt=""
                 aria-hidden="true">
            <span>LEARNING PATH</span>
        </a>

        <a class="dashboard-action matchmaking-action"
           href="pvp.php">
            <img src="assets/matchmaking-icon.png"
                 class="dashboard-action-icon"
                 alt=""
                 aria-hidden="true">
            <span>MATCHMAKING</span>
        </a>

        <a class="dashboard-action minigame-action"
           href="minigame.php">
            <img src="assets/minigame-icon.png"
                 class="dashboard-action-icon"
                 alt=""
                 aria-hidden="true">
            <span>MINI-GAME</span>
        </a>

        <a class="dashboard-action story-action"
           href="story.php">
            <img src="assets/story-icon.png"
                 class="dashboard-action-icon"
                 alt=""
                 aria-hidden="true">
            <span>STORY INTRO</span>
        </a>

    </nav>

</section>

<!-- DAILY TECH TRIVIA -->
<section class="daily-trivia-section" aria-labelledby="dailyTriviaTitle">
    <div class="daily-trivia-panel">

        <div class="daily-trivia-icon-wrap" aria-hidden="true">
            <img
                src="assets/dailytrivia-icon.png"
                class="daily-trivia-icon"
                alt=""
            >
        </div>

        <div class="daily-trivia-content">
            <h3 id="dailyTriviaTitle" class="daily-trivia-title">
                DAILY TECH TRIVIA
            </h3>

            <div class="daily-trivia-text-frame">
                <p id="triviaText">Loading trivia...</p>
            </div>
        </div>

    </div>
</section>

<!-- LANGUAGE SELECTION / COURSES -->
<section class="courses-section" aria-labelledby="coursesHeading">
  <div class="courses-panel">
    <h2 id="coursesHeading" class="sr-only">Courses</h2>

    <div class="courses-controls">
      <label class="courses-search-wrap" for="searchBar">
        <span class="courses-search-icon" aria-hidden="true"></span>
        <input
          class="search-bar"
          id="searchBar"
          type="search"
          placeholder="Search courses..."
          oninput="filterCourses()"
          autocomplete="off"
        >
      </label>

      <div class="courses-filters" aria-label="Course difficulty filters">
        <button class="filter active-filter" type="button" onclick="filterByTag(this,'all')">ALL</button>
        <button class="filter" type="button" onclick="filterByTag(this,'beginner')">BEGINNER</button>
        <button class="filter" type="button" onclick="filterByTag(this,'intermediate')">INTERMEDIATE</button>
        <button class="filter" type="button" onclick="filterByTag(this,'advanced')">ADVANCED</button>
      </div>
    </div>

    <?php
    $courseCards = [
        [
            'name' => 'HTML',
            'slug' => 'html',
            'tag' => 'beginner',
            'difficulty' => 'Beginner',
            'logo' => 'html-logo.png',
            'desc' => 'Create the structure of websites.',
        ],
        [
            'name' => 'CSS',
            'slug' => 'css',
            'tag' => 'beginner',
            'difficulty' => 'Beginner',
            'logo' => 'css-logo.png',
            'desc' => 'Design and layout beautifully.',
        ],
        [
            'name' => 'JavaScript',
            'slug' => 'javascript',
            'tag' => 'beginner',
            'difficulty' => 'Beginner',
            'logo' => 'javascript-logo.png',
            'desc' => 'Add logic and interactivity.',
        ],
        [
            'name' => 'PHP',
            'slug' => 'php',
            'tag' => 'intermediate',
            'difficulty' => 'Intermediate',
            'logo' => 'php-logo.png',
            'desc' => 'Backend web development.',
        ],
        [
            'name' => 'Java',
            'slug' => 'java',
            'tag' => 'intermediate',
            'difficulty' => 'Intermediate',
            'logo' => 'java-logo.png',
            'desc' => 'Object-oriented programming.',
        ],
        [
            'name' => 'C++',
            'slug' => 'c++',
            'tag' => 'advanced',
            'difficulty' => 'Advanced',
            'logo' => 'cpp-logo.png',
            'desc' => 'High-performance programming.',
        ],
    ];
    ?>

    <div class="course-grid" id="courseGrid">
      <?php foreach ($courseCards as $course): ?>
        <?php
          $courseProgress = $progress[$course['name']] ?? ['percent' => 0, 'completed' => 0, 'total' => 0];
          $coursePct = (int)$courseProgress['percent'];
          $courseDone = (int)$courseProgress['completed'];
          $courseTotal = (int)$courseProgress['total'];

          $safeName = htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8');
          $safeDesc = htmlspecialchars($course['desc'], ENT_QUOTES, 'UTF-8');
          $safeDifficulty = htmlspecialchars($course['difficulty'], ENT_QUOTES, 'UTF-8');
          $safeTag = htmlspecialchars($course['tag'], ENT_QUOTES, 'UTF-8');
          $safeSlug = htmlspecialchars($course['slug'], ENT_QUOTES, 'UTF-8');
          $safeLogo = htmlspecialchars($course['logo'], ENT_QUOTES, 'UTF-8');
        ?>

        <article
          class="course-card"
          data-tag="<?php echo $safeTag; ?>"
          data-name="<?php echo $safeSlug; ?>"
          tabindex="0"
          role="button"
          onclick="openLesson('<?php echo $safeName; ?>')"
          onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openLesson('<?php echo $safeName; ?>');}"
        >
          <div class="course-card-top">
            <div class="course-logo-box">
              <img
                src="assets/<?php echo $safeLogo; ?>"
                class="course-logo"
                alt="<?php echo $safeName; ?> logo"
              >
            </div>

            <div class="course-heading">
              <h3 class="course-title"><?php echo $safeName; ?></h3>
              <span class="course-difficulty difficulty-<?php echo $safeTag; ?>">
                <?php echo $safeDifficulty; ?>
              </span>
            </div>
          </div>

          <p class="course-desc"><?php echo $safeDesc; ?></p>

          <div class="course-progress-meta">
            <span><?php echo $courseDone; ?> / <?php echo $courseTotal; ?> Lessons</span>
          </div>

          <div
            class="course-progress"
            role="progressbar"
            aria-label="<?php echo $safeName; ?> course progress"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="<?php echo $coursePct; ?>"
          >
            <span class="course-progress-fill" style="width: <?php echo $coursePct; ?>%;"></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURE SECTION -->
<div class="feature-section">
  <div class="feature-box">
    <img src="images/practice.png" alt="Practice">
    <div>
      <h2>Practice your coding chops</h2>
      <p>Sharpen your skills with coding battles and quizzes.</p>
    </div>
  </div>

  <div class="feature-box reverse">
    <img src="images/community.png" alt="Community">
    <div>
      <h2>Join a coding community</h2>
      <p>Compete with friends and climb the leaderboard.</p>
    </div>
  </div>
</div>

<!-- LEARNING PATHS -->
<div class="learning-section">
  <div class="dashboard-wrapper">
    <div class="section-header">
      <h2>Your Learning Paths</h2>
      <p>Continue your coding journey.</p>
    </div>

    <div class="path-grid">
      <?php
      $pathCourses = [
          ['name' => 'HTML',       'img' => 'html.png', 'desc' => 'Learn website structure, tags, forms, media, and semantic HTML.'],
          ['name' => 'CSS',        'img' => 'css.png',  'desc' => 'Master layouts, flexbox, grid, animations, and responsive design.'],
          ['name' => 'JavaScript', 'img' => 'js.png',   'desc' => 'Build interactive websites, logic systems, and dynamic apps.'],
      ];
      foreach ($pathCourses as $pc):
          $pct   = $progress[$pc['name']]['percent']   ?? 0;
          $done  = $progress[$pc['name']]['completed'] ?? 0;
          $total = $progress[$pc['name']]['total']     ?? 0;
          $safe  = htmlspecialchars($pc['name'], ENT_QUOTES, 'UTF-8');
      ?>
        <div class="path-card">
          <img src="images/<?php echo $pc['img']; ?>" class="path-image" alt="<?php echo $safe; ?>">
          <div class="path-content">
            <h3><?php echo $safe; ?> Roadmap</h3>
            <p><?php echo $pc['desc']; ?></p>
            <div class="progress-container">
              <div class="progress-fill" style="width:<?php echo $pct; ?>%"></div>
            </div>
            <small><?php echo $pct; ?>% Complete (<?php echo $done; ?>/<?php echo $total; ?> lessons)</small>
            <div class="path-buttons">
              <button onclick="startBattle('<?php echo $safe; ?>')">Continue</button>
              <button onclick="startQuiz('<?php echo $safe; ?>')">Quiz</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
/* ── Trivia Array ────────────────────────────────────────── */
const trivia = [
    "The first computer bug was an actual real-life moth found in 1947.",
    "HTML stands for HyperText Markup Language and was created by Tim Berners-Lee in 1993.",
    "JavaScript was created in just 10 days by Brendan Eich in 1995.",
    "Python is named after the British comedy group Monty Python, not the snake.",
    "PHP originally stood for Personal Home Page.",
    "C++ was created as an extension of the C programming language to add object-oriented features."
];
document.addEventListener("DOMContentLoaded", function() {
    const triviaBox = document.getElementById("triviaText");
    if(triviaBox) {
        const randomFact = trivia[Math.floor(Math.random() * trivia.length)];
        triviaBox.innerText = randomFact;
    }
});

/* ── Search & Filter ─────────────────────────────────────── */
function filterCourses() {
    const q = document.getElementById("searchBar").value.toLowerCase();
    document.querySelectorAll(".course-card").forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? "" : "none";
    });
}

function filterByTag(el, tag) {
    document.querySelectorAll(".filter").forEach(f => f.classList.remove("active-filter"));
    el.classList.add("active-filter");
    document.querySelectorAll(".course-card").forEach(card => {
        card.style.display = (tag === "all" || card.dataset.tag === tag) ? "" : "none";
    });
}

/* ── Ripple effect ───────────────────────────────────────── */
document.querySelectorAll(".card, .course-card, .path-card").forEach(card => {
    card.addEventListener("click", function (e) {
        const ripple = document.createElement("span");
        const rect   = card.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height);
        ripple.style.cssText = `width:${size}px;height:${size}px;` +
            `left:${e.clientX - rect.left - size / 2}px;` +
            `top:${e.clientY  - rect.top  - size / 2}px;`;
        ripple.classList.add("ripple");
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});

/* ── Animate XP bar ─────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", function () {
    const xpFill = document.querySelector(".xp-fill");
    if (xpFill) {
        const target = xpFill.getAttribute("data-percent");
        // Brief delay so CSS transition fires after initial paint
        setTimeout(() => { xpFill.style.width = target + "%"; }, 150);
    }
});
</script>

<?php include 'includes/footer.php'; ?>