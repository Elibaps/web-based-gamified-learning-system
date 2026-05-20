<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    // Validation
    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $username, $email, $hashed);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: login.php?registered=1');
            exit();
        } else {
            // errno 1062 = duplicate entry (unique key violation)
            $error = ($conn->errno === 1062)
                ? 'That username or email is already taken.'
                : 'Registration failed. Please try again.';
            $stmt->close();
        }
    }
}

$pageTitle = 'Register — CodeNest';
include 'includes/head.php';
?>
<body class="auth-page">

<div class="auth-container">
  <!-- LOGO -->
  <img src="images/logo.png" class="logo" alt="CodeNest Logo">

  <h2 style="color:var(--primary-color); margin-bottom:10px;">Create Account</h2>

  <?php if ($error !== ''): ?>
    <p class="error shake"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <form method="POST" class="auth-form" id="registerForm">
    <input type="text"  name="username" placeholder="Username" required
           value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
           autocomplete="username">

    <input type="email" name="email" placeholder="Email" required
           value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
           autocomplete="email">

    <input type="password" name="password" placeholder="Password (min 6 chars)" required
           autocomplete="new-password">

    <button type="submit" class="btn">Register</button>
  </form>

  <p class="link">
    Already have an account? <a href="login.php">Login</a>
  </p>
</div>

<?php include 'includes/footer.php'; ?>
