<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Settings — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 60px 20px;">
  <div style="background: var(--card-bg); border: 4px solid var(--primary-color); box-shadow: 6px 6px 0 var(--shadow-color); padding: 40px; max-width: 500px; margin: 0 auto;">
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2rem; margin-bottom: 20px;">Settings</h1>
      
      <div style="text-align: left; margin-bottom: 20px;">
          <label style="display: block; color: inherit; margin-bottom: 8px;">Sound Effects</label>
          <button class="btn" style="width: auto;">Toggle Sound: ON</button>
      </div>

      <div style="text-align: left; margin-bottom: 20px;">
          <label style="display: block; color: inherit; margin-bottom: 8px;">Change Password</label>
          <input type="password" placeholder="New Password" style="width: 100%; padding: 10px; background: var(--bg-color); border: 4px solid var(--primary-color); color: var(--primary-color); font-family: 'Minecraft', monospace; outline: none; margin-bottom: 10px;">
          <button class="btn" style="width: auto;">Update</button>
      </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
