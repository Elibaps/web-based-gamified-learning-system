<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT level, exp FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$level = $row['level'] ?? 1;
$exp = $row['exp'] ?? 0;

$pageTitle = 'My Profile — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 60px 20px;">
  <div style="background: var(--card-bg); border: 4px solid var(--primary-color); box-shadow: 6px 6px 0 var(--shadow-color); padding: 40px; max-width: 500px; margin: 0 auto;">
      <img src="images/player.png" style="width: 120px; image-rendering: pixelated; margin-bottom: 20px;" alt="Avatar">
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2rem;"><?php echo htmlspecialchars($username); ?></h1>
      <p style="color: inherit; font-size: 1.2rem; margin: 10px 0;">Level <?php echo $level; ?></p>
      <p style="color: #94a3b8;">Total XP: <?php echo $exp; ?></p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
