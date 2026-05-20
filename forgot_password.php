<?php
$pageTitle = 'Forgot Password — CodeNest';
include 'includes/head.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if ($email !== '') {
        $message = "A password reset link has been sent to " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "!";
    }
}
?>
<body class="auth-page">

<div class="auth-container">
  <img src="images/logo.png" class="logo" alt="CodeNest Logo" onerror="this.style.display='none'">
  
  <h2 style="color: #4ade80; margin-bottom: 20px;">Forgot Password</h2>
  <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">Enter your email to receive a secure reset link.</p>

  <?php if ($message !== ''): ?>
    <p style="color: #4ade80; font-size: 14px; padding: 10px; border: 2px solid #4ade80; background: #000; margin-bottom: 15px;"><?php echo $message; ?></p>
  <?php endif; ?>

  <form method="POST" class="auth-form">
    <input type="email" name="email" placeholder="Email Address" required>
    <button type="submit" class="btn">Reset Password</button>
  </form>

  <p class="link">
    Remember your password? <a href="login.php">Login here</a>
  </p>
</div>

</body>
</html>
