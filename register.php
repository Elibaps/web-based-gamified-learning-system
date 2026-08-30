<?php
session_start();
include 'db.php';

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {

    $stmt = $conn->prepare(
        'INSERT INTO users (username, email, password)
         VALUES (?, ?, ?)'
    );

    $stmt->bind_param(
        'sss',
        $username,
        $email,
        $hashedPassword
    );

    $stmt->execute();

    $stmt->close();

    header('Location: login.php?registered=1');
    exit();

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() === 1062) {

        $error = 'That username or email is already taken.';

    } else {

        $error = 'Registration failed. Please try again.';

            }
        }
    }
}

$pageTitle = 'Register — CodeNest';
include 'includes/head.php';
?>
<body class="auth-page register-page">

<div class="auth-container">
    <img src="assets/logo.png" class="logo" alt="CodeNest Logo">

    <h1 class="register-title">Create Account</h1>

    <?php if ($error !== ''): ?>
        <p class="auth-message shake">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="auth-form" id="registerForm">
        <div class="rpg-input">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                minlength="3"
                autocomplete="username"
                spellcheck="false"
                value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="rpg-input">
            <input
                type="email"
                name="email"
                placeholder="Email"
                required
                autocomplete="email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="rpg-input">
            <input
                type="password"
                name="password"
                placeholder="Password (min 6 chars)"
                required
                minlength="6"
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn">Register</button>
    </form>

    <p class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
