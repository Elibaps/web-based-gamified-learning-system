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
    "SELECT username, exp FROM users ORDER BY exp DESC LIMIT 5"
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

<!-- TOP BAR -->
<div class="top-bar">

    <div class="profile">
        <img src="images/player.png" class="profile-pic" alt="Player Avatar">
        <div>
            <h3><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p>Level <?php echo $level; ?></p>
            <div class="xp-bar">
                <div class="xp-fill" data-percent="<?php echo $percent; ?>"></div>
            </div>
            <small><?php echo $exp; ?> / <?php echo $expNeeded; ?> XP</small>
        </div>
    </div>

    <div class="mini-leaderboard">
        <h3>🏆 Top Players</h3>
        <?php foreach ($leaders as $leader): ?>
            <div class="leader">
                <?php echo htmlspecialchars($leader['username'], ENT_QUOTES, 'UTF-8'); ?>
                &mdash; <?php echo (int)$leader['exp']; ?> XP
            </div>
        <?php endforeach; ?>
    </div>

    <div class="top-actions">
        <a href="pvp.php" style="text-decoration:none;"><button class="pvp-btn">⚔️ PvP Arena</button></a>
        <a href="minigame.php" style="text-decoration:none;"><button class="pvp-btn" style="margin-top:10px; background: #ff00ff; border-color:#ff00ff;">🎮 Mini-Game</button></a>
        <a href="tutorial.php" style="text-decoration:none;"><button class="pvp-btn" style="margin-top:10px; background: #00ffff; border-color:#00ffff; color:black;">📖 Tutorial</button></a>
        <a href="story.php" style="text-decoration:none;"><button class="pvp-btn" style="margin-top:10px; background: #ffb000; border-color:#ffb000; color:black;">📜 Story Intro</button></a>
    </div>

</div>

<!-- DAILY TECH TRIVIA -->
<div class="dashboard-wrapper" style="padding-bottom: 0;">
    <div style="background: var(--card-bg); border: 4px solid var(--primary-color); box-shadow: 6px 6px 0 var(--shadow-color); padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="font-size: 3rem;">💡</div>
        <div>
            <h3 style="color: var(--primary-color); margin-bottom: 5px;">Daily Tech Trivia</h3>
            <p style="color: #94a3b8;" id="triviaText">Loading trivia...</p>
        </div>
    </div>
</div>

<!-- LANGUAGE SELECTION -->
<div class="dashboard-wrapper">

  <!-- SEARCH + FILTER -->
  <div class="top-controls">
    <input class="search-bar" id="searchBar" placeholder="Search courses..."
           oninput="filterCourses()" autocomplete="off">

    <div class="filter active-filter" onclick="filterByTag(this,'all')">All</div>
    <div class="filter" onclick="filterByTag(this,'beginner')">Beginner</div>
    <div class="filter" onclick="filterByTag(this,'intermediate')">Intermediate</div>
    <div class="filter" onclick="filterByTag(this,'advanced')">Advanced</div>
  </div>

  <!-- COURSES -->
  <div class="course-grid" id="courseGrid">

    <div class="course-card" data-tag="beginner" data-name="html"
         onclick="openLesson('HTML')">
      <img src="images/html.png" alt="HTML">
      <div class="course-content">
        <div class="course-title">HTML</div>
        <div class="course-desc">Create the structure of websites</div>
        <div class="badge-easy">Beginner</div>
      </div>
    </div>

    <div class="course-card" data-tag="beginner" data-name="css"
         onclick="openLesson('CSS')">
      <img src="images/css.png" alt="CSS">
      <div class="course-content">
        <div class="course-title">CSS</div>
        <div class="course-desc">Design and layout beautifully</div>
        <div class="badge-easy">Beginner</div>
      </div>
    </div>

    <div class="course-card" data-tag="beginner" data-name="javascript"
         onclick="openLesson('JavaScript')">
      <img src="images/js.png" alt="JavaScript">
      <div class="course-content">
        <div class="course-title">JavaScript</div>
        <div class="course-desc">Add logic and interactivity</div>
        <div class="badge-easy">Beginner</div>
      </div>
    </div>

    <div class="course-card" data-tag="intermediate" data-name="php"
         onclick="openLesson('PHP')">
      <img src="images/php.png" alt="PHP">
      <div class="course-content">
        <div class="course-title">PHP</div>
        <div class="course-desc">Backend web development</div>
        <div class="badge-hard">Intermediate</div>
      </div>
    </div>

    <div class="course-card" data-tag="intermediate" data-name="java"
         onclick="openLesson('Java')">
      <img src="images/java.png" alt="Java">
      <div class="course-content">
        <div class="course-title">Java</div>
        <div class="course-desc">Object-oriented programming</div>
        <div class="badge-hard">Intermediate</div>
      </div>
    </div>

    <div class="course-card" data-tag="advanced" data-name="c++"
         onclick="openLesson('C++')">
      <img src="images/cpp.png" alt="C++">
      <div class="course-content">
        <div class="course-title">C++</div>
        <div class="course-desc">High-performance programming</div>
        <div class="badge-adv">Advanced</div>
      </div>
    </div>

  </div>
</div>

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