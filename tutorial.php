<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Tutorial — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="padding: 60px 20px;">
  <div style="background: var(--card-bg); border: 4px solid var(--primary-color); box-shadow: 6px 6px 0 var(--shadow-color); padding: 40px; max-width: 800px; margin: 0 auto;">
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px; text-align: center;">How to Play</h1>
      
      <div style="color: inherit; line-height: 1.8; font-size: 1.1rem;">
        <h3 style="color: var(--primary-color); margin-top: 20px;">1. Learning Paths & Courses</h3>
        <p>Choose a programming language from the Dashboard. Read through the lessons to gain knowledge!</p>

        <h3 style="color: var(--primary-color); margin-top: 20px;">2. Earning XP</h3>
        <p>You can earn Experience Points (XP) by answering questions correctly in Practice Battles, Quizzes, or by playing Mini-Games.</p>

        <h3 style="color: var(--primary-color); margin-top: 20px;">3. Leveling Up</h3>
        <p>As you gain XP, you will level up. Your level and total XP are displayed on the Leaderboard. Compete with your friends!</p>

        <h3 style="color: var(--primary-color); margin-top: 20px;">4. Battles vs Quizzes</h3>
        <p><strong>Battles:</strong> Type the correct answer before the timer runs out to damage the boss.<br>
        <strong>Quizzes:</strong> A relaxed multiple-choice mode to test your knowledge.</p>
      </div>

      <div style="text-align: center; margin-top: 40px;">
        <a href="dashboard.php"><button class="btn" style="width: auto; padding: 10px 20px;">Return to Dashboard</button></a>
      </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
