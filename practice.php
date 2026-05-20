<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Practice Hub — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 60px 20px;">
  <h1 style="color: var(--primary-color); text-shadow: var(--text-shadow-glow); font-size: 3rem;">Practice Hub</h1>
  <p style="color: #94a3b8; font-size: 1.2rem; margin-top: 20px;">Select a language to start a practice battle!</p>
  
  <div class="course-grid" style="margin-top: 40px; justify-items: center;">
      <?php
      $topics = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Java', 'C++'];
      foreach ($topics as $topic) {
          echo "<div class='course-card' onclick=\"startBattle('$topic')\" style='width:260px;'>";
          echo "<div class='course-content'>";
          echo "<h3 style='margin-bottom:0;'>Practice $topic</h3>";
          echo "</div></div>";
      }
      ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
