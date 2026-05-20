<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare(
            "SELECT user_id, username, password FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id']  = $row['user_id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Login — CodeNest';
include 'includes/head.php';
?>
<body class="auth-page">

<div class="auth-container">
  <!-- LOGO -->
  <img src="images/logo.png" class="logo" alt="CodeNest Logo">

  <?php if ($error !== ''): ?>
    <p class="error shake"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <form method="POST" class="auth-form" id="loginForm">
    <input type="text"     name="username" placeholder="Username" required
           autocomplete="username">
    <input type="password" name="password" placeholder="Password" required
           autocomplete="current-password">
    <button type="submit" class="btn">Login</button>
  </form>

  <p class="link" style="margin-bottom: 5px;">
    <a href="forgot_password.php">Forgot Password?</a>
  </p>
  <p class="link" style="margin-top: 5px;">
    Don't have an account? <a href="register.php">Register</a>
  </p>
</div>

<!-- LOADING SCREEN -->
<div id="loadingScreen">
  <p>Entering CodeNest...</p>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function () {
    document.getElementById("loadingScreen").style.display = "flex";
});
</script>

<?php include 'includes/footer.php'; ?>