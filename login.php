<?php
session_start();
include 'db.php';

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$notice = isset($_GET['registered'])
    ? 'Account created! You can log in now.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare(
            'SELECT user_id, username, password FROM users WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];

            header('Location: dashboard.php');
            exit();
        }

        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Login — CodeNest';
include 'includes/head.php';
?>
<body class="auth-page login-page">

<div class="auth-container">
    <img src="assets/logo.png" class="logo" alt="CodeNest Logo">

    <?php if ($notice !== ''): ?>
        <p class="auth-message success">
            <?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="auth-message shake">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="auth-form" id="loginForm">
        <div class="rpg-input">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                autocomplete="username"
                spellcheck="false"
                value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="rpg-input">
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password"
            >
        </div>

        <label class="remember-me">
            <input type="checkbox" name="remember_me" value="1">
            <span class="pixel-checkbox" aria-hidden="true"></span>
            <span class="remember-text">Remember me</span>
        </label>

        <button type="submit" class="btn">Login</button>
    </form>

    <p class="forgot-password">
        <a href="forgot_password.php">Forgot Password?</a>
    </p>

    <p class="register-link">
        Don't have an account? <a href="register.php">Register</a>
    </p>
</div>

<div id="loadingScreen" aria-hidden="true">
    <p>Entering CodeNest...</p>
</div>

<script>
const loginForm = document.getElementById('loginForm');
const loadingScreen = document.getElementById('loadingScreen');

if (loginForm && loadingScreen) {
    loginForm.addEventListener('submit', function () {
        loadingScreen.style.display = 'flex';
    });
}
</script>

<?php include 'includes/footer.php'; ?>
