<?php
$pageTitle = 'Forgot Password — CodeNest';
include 'includes/head.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email !== '') {
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $message = "A password reset link has been sent to {$safeEmail}.";
    }
}
?>
<body class="auth-page forgot-password-page">

<div class="auth-container">
    <img src="assets/logo.png" class="logo" alt="CodeNest Logo">

    <h1 class="forgot-page-title">Forgot Password</h1>

    <p class="forgot-page-subtitle">
        Enter your email to receive a secure reset link.
    </p>

    <?php if ($message !== ''): ?>
        <p class="password-reset-message">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="auth-form" autocomplete="off">
        <div class="rpg-input">
            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
                autocomplete="email"
                spellcheck="false"
            >
        </div>

        <button type="submit" class="btn">Reset Password</button>
    </form>

    <p class="login-link">
        Remember your password? <a href="login.php">Login here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
