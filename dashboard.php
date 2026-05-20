<?php 
session_start(); 
include "db.php"; 

if(!isset($_SESSION['username'])){
    header("Location: login.html"); 
    exit(); 
} 

$username = $_SESSION['username']; 

$sql = "SELECT * FROM users WHERE username='$username'"; 
$result = mysqli_query($conn, $sql); 
$row = mysqli_fetch_assoc($result); 

// XP CALCULATION
$level = $row['level']; 
$exp = $row['exp']; 
$expNeeded = $level * 100; 
$percent = ($exp / $expNeeded) * 100; 
?>

<!DOCTYPE html>
<html>

<head>
    <title>CodeNest Dashboard</title>

    <!-- FIXED CSS LINK -->
    <link rel="stylesheet" href="./UI.css?v=1">

</head>

<body class="dashboard-page">
    <div class="navbar">
  <div class="nav-left">
    <span class="logo-text">🪙 CodeNest</span>

    <div class="nav-item dropdown">
      Learn ▾
      <div class="dropdown-menu">
        <div class="menu-column">
          <h4>Web Dev</h4>
          <p onclick="startBattle('HTML')">HTML</p>
          <p onclick="startBattle('CSS')">CSS</p>
          <p onclick="startBattle('JavaScript')">JavaScript</p>
        </div>

        <div class="menu-column">
          <h4>Languages</h4>
          <p onclick="startBattle('PHP')">PHP</p>
          <p onclick="startBattle('Java')">Java</p>
          <p onclick="startBattle('C++')">C++</p>
        </div>

        <div class="menu-column">
          <h4>Coming Soon</h4>
          <p>Python</p>
          <p>AI</p>
          <p>Game Dev</p>
        </div>
      </div>
    </div>

    <div class="nav-item">Practice</div>
    <div class="nav-item">Build</div>
    <div class="nav-item">Community</div>
  </div>

  <div class="profile-menu">
  <img src="images/player.png" class="nav-avatar">

  <div class="profile-dropdown">
    <a href="#">My Profile</a>
    <a href="#">Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</div>
</div>
<!-- TOP BAR -->
<div class="top-bar">

    <div class="profile">

        <img src="images/player.png"
        class="profile-pic">

        <div>

            <h3>
              <?php echo $row['username']; ?>
            </h3>

            <p>
              Level <?php echo $level; ?>
            </p>

            <div class="xp-bar">
                <div class="xp-fill"
                style="width: <?php echo $percent; ?>%;">
                </div>
            </div>

            <small>
              <?php echo $exp; ?>
              /
              <?php echo $expNeeded; ?>
              XP
            </small>

        </div>

    </div>

    <div class="mini-leaderboard">

        <h3>🏆 Top Players</h3>

        <?php 
        $leaders = mysqli_query(
          $conn,
          "SELECT username, exp
          FROM users
          ORDER BY exp DESC
          LIMIT 5"
        );

        while($user = mysqli_fetch_assoc($leaders)){

            echo "
            <div class='leader'>
              {$user['username']}
              - {$user['exp']} XP
            </div>";
        }
        ?>

    </div>

    <div class="top-actions">

        <button class="pvp-btn">
          ⚔️ PvP (Soon)
        </button>
    </div>

</div>
    <!-- LANGUAGE SELECTION -->
    <div class="dashboard-wrapper">

  <!-- SEARCH + FILTER -->
  <div class="top-controls">
    <input class="search-bar" placeholder="Search...">

    <div class="filter">Popular</div>
    <div class="filter">Web Dev</div>
    <div class="filter">Data Science</div>
    <div class="filter">AI</div>
  </div>

  <!-- COURSES -->
  <div class="course-grid">

    <div class="course-card" onclick="openLesson('HTML')">
  <img src="images/html.png">
  <div class="course-content">
    <div class="course-title">HTML</div>
    <div class="course-desc">Create the structure of websites</div>
    <div class="badge-easy">Beginner</div>
  </div>
</div>

<div class="course-card" onclick="openLesson('CSS')">
  <img src="images/css.png">
  <div class="course-content">
    <div class="course-title">CSS</div>
    <div class="course-desc">Design and layout beautifully</div>
    <div class="badge-easy">Beginner</div>
  </div>
</div>

<div class="course-card" onclick="openLesson('JavaScript')">
  <img src="images/js.png">
  <div class="course-content">
    <div class="course-title">JavaScript</div>
    <div class="course-desc">Add logic and interactivity</div>
    <div class="badge-easy">Beginner</div>
  </div>
</div>

<div class="course-card" onclick="openLesson('PHP')">
  <img src="images/php.png">
  <div class="course-content">
    <div class="course-title">PHP</div>
    <div class="course-desc">Backend web development</div>
    <div class="badge-hard">Intermediate</div>
  </div>
</div>

<div class="course-card" onclick="openLesson('Java')">
  <img src="images/java.png">
  <div class="course-content">
    <div class="course-title">Java</div>
    <div class="course-desc">Object-oriented programming</div>
    <div class="badge-hard">Intermediate</div>
  </div>
</div>

<div class="course-card" onclick="openLesson('C++')">
  <img src="images/cpp.png">
  <div class="course-content">
    <div class="course-title">C++</div>
    <div class="course-desc">High-performance programming</div>
    <div class="badge-adv">Advanced</div>
  </div>
</div>

</div>
</div>
</div>
<div class="feature-section">

  <div class="feature-box">
    <img src="images/practice.png">

    <div>
      <h2>Practice your coding chops</h2>

      <p>
        Sharpen your skills with coding battles and quizzes.
      </p>
    </div>
  </div>

  <div class="feature-box reverse">
    <img src="images/community.png">

    <div>
      <h2>Join a coding community</h2>

      <p>
        Compete with friends and climb the leaderboard.
      </p>
    </div>
  </div>

</div>

<script>

function startBattle(language){
    localStorage.setItem("selectedLanguage", language); 
    window.location.href = "battle.php?topic=" + language;
}

function openQuiz(language){

    localStorage.setItem(
        "quizLanguage",
        language
    );

    window.location.href =
        "quiz.php?topic=" + language;
}

</script>
 <!-- LEARNING PATHS -->

<div class="learning-section">

  <div class="section-header">
    <h2>Your Learning Paths</h2>
    <p>Continue your coding journey.</p>
  </div>

  <div class="path-grid">

    <!-- HTML -->
    <div class="path-card">

      <img src="images/html.png" class="path-image">

      <div class="path-content">

        <h3>HTML Roadmap</h3>

        <p>
          Learn website structure, tags,
          forms, media, and semantic HTML.
        </p>

        <div class="progress-container">
          <div class="progress-fill" style="width: 45%;"></div>
        </div>

        <small>45% Complete</small>

        <div class="path-buttons">
          <button onclick="startBattle('HTML')">
            Continue
          </button>

          <button onclick="openQuiz('HTML')">
            Quiz
          </button>
        </div>

      </div>
    </div>

    <!-- CSS -->
    <div class="path-card">

      <img src="images/css.png" class="path-image">

      <div class="path-content">

        <h3>CSS Roadmap</h3>

        <p>
          Master layouts, flexbox, grid,
          animations, and responsive design.
        </p>

        <div class="progress-container">
          <div class="progress-fill" style="width: 30%;"></div>
        </div>

        <small>30% Complete</small>

        <div class="path-buttons">
          <button onclick="startBattle('CSS')">
            Continue
          </button>

          <button onclick="openQuiz('CSS')">
            Quiz
          </button>
        </div>

      </div>
    </div>

    <!-- JAVASCRIPT -->
    <div class="path-card">

      <img src="images/js.png" class="path-image">

      <div class="path-content">

        <h3>JavaScript Roadmap</h3>

        <p>
          Build interactive websites,
          logic systems, and dynamic apps.
        </p>

        <div class="progress-container">
          <div class="progress-fill" style="width: 15%;"></div>
        </div>

        <small>15% Complete</small>

        <div class="path-buttons">
          <button onclick="startBattle('JavaScript')">
            Continue
          </button>

          <button onclick="openQuiz('JavaScript')">
            Quiz
          </button>
        </div>

      </div>
    </div>

  </div>
</div>
<script>

document.querySelectorAll(
".card, .course-card, .path-card"
).forEach(card => {

  card.addEventListener("click", function(e) {

    const ripple =
      document.createElement("span");

    const rect =
      card.getBoundingClientRect();

    const size =
      Math.max(rect.width, rect.height);

    ripple.style.width =
      ripple.style.height =
      size + "px";

    ripple.style.left =
      (e.clientX - rect.left - size / 2) + "px";

    ripple.style.top =
      (e.clientY - rect.top - size / 2) + "px";

    ripple.classList.add("ripple");

    this.appendChild(ripple);

    setTimeout(() => ripple.remove(), 600);

  });

});

</script>
<script>
function openLesson(language){

  localStorage.setItem("selectedLesson", language);

  window.location.href = "lesson.php?course=" + language;
}
</script>
</body>
</html>