<?php 
session_start(); 
include "db.php"; 

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username']; 
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE username='$username'"; 
    $result = mysqli_query($conn, $sql); 

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result); 

        if (password_verify($password, $row['password'])) {

            $_SESSION['username'] = $username; 
            header("Location: dashboard.php"); 
            exit(); 

        } else {
            $error = "Invalid username or password!"; 
        }

    } else {
        $error = "Invalid username or password!"; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeNest</title>
    <link rel="stylesheet" href="./UI.css?v=1">
</head>

<body class="auth-page">

<div class="auth-container">
  <!-- LOGO -->
  <img src="images/logo.png" class="logo">

  <?php if($error != ""): ?>
    <p class="error shake"><?php echo $error; ?></p>
  <?php endif; ?>

  <form method="POST" class="auth-form" id="loginForm">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit" class="btn">Login</button>
  </form>

  <p class="link">
    Don’t have an account? <a href="register.html">Register</a>
  </p>

</div>
  </div>
<!-- LOADING SCREEN -->
<div id="loadingScreen">
  <p>Entering CodeNest...</p>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function() {
  document.getElementById("loadingScreen").style.display = "flex";
});
</script>

</body>
</html>