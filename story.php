<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Story — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 100px 20px;">
  <div style="background: var(--card-bg); border: 4px solid var(--primary-color); box-shadow: 6px 6px 0 var(--shadow-color); padding: 40px; max-width: 700px; margin: 0 auto; text-align: left;">
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2rem; margin-bottom: 20px; text-align: center;">SYSTEM BREACH DETECTED...</h1>
      
      <p style="color: var(--primary-color); font-size: 1.2rem; line-height: 1.8; margin-bottom: 20px; font-family: 'Courier New', monospace;">
        > Welcome, Hacker. <br><br>
        > The mainframe has been corrupted by legacy bugs and syntax errors.<br>
        > Your mission is to master the core languages (HTML, CSS, JS, PHP, C++, Java).<br>
        > Only by gaining XP and leveling up can you unlock the security overrides and restore the system.<br><br>
        > Your progress is auto-saved to the mainframe (Save-Point Active). Good luck.
      </p>

      <div style="text-align: center;">
          <a href="dashboard.php"><button class="btn" style="width: auto; padding: 10px 30px;">Accept Mission</button></a>
      </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
