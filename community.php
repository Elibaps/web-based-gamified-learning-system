<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Community — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 100px 20px;">
  <h1 style="color: var(--primary-color); text-shadow: var(--text-shadow-glow); font-size: 3rem;">Community Forums & Global Leaderboard</h1>
  <p style="color: #94a3b8; font-size: 1.2rem; margin-top: 20px;">The community hub is under construction!</p>
  <div style="margin-top: 40px; font-size: 4rem;">👥</div>
</div>

<?php include 'includes/footer.php'; ?>
