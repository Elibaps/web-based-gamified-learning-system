<?php
// Requires: session_start() called + $_SESSION['username'] is set
$usernameForNav = (string)($_SESSION['username'] ?? 'U');
$navInitial = strtoupper(substr($usernameForNav, 0, 1));
$navAvatarExists = file_exists(__DIR__ . '/../images/player.png');
?>
<div class="navbar">
  <div class="nav-left">
    <a href="dashboard.php" class="logo-text" aria-label="CodeNest Dashboard">CodeNest</a>

    <div class="nav-item dropdown">
      Learn ▾
      <div class="dropdown-menu">
        <div class="menu-column">
          <h4>Web Dev</h4>
          <a href="battle.php?topic=HTML">HTML</a>
          <a href="battle.php?topic=CSS">CSS</a>
          <a href="battle.php?topic=JavaScript">JavaScript</a>
        </div>

        <div class="menu-column">
          <h4>Languages</h4>
          <a href="battle.php?topic=PHP">PHP</a>
          <a href="battle.php?topic=Java">Java</a>
          <a href="battle.php?topic=C%2B%2B">C++</a>
        </div>

        <div class="menu-column">
          <h4>Coming Soon</h4>
          <span>Python</span>
          <span>AI</span>
          <span>Game Dev</span>
        </div>
      </div>
    </div>

    <a href="practice.php" class="nav-item" style="text-decoration:none; color:inherit;">Practice</a>
    <a href="build.php" class="nav-item" style="text-decoration:none; color:inherit;">Build</a>
    <a href="community.php" class="nav-item" style="text-decoration:none; color:inherit;">Community</a>
  </div>

  <div class="nav-right">
    <div class="profile-menu">
      <button id="themeToggleBtn" class="theme-toggle-btn" type="button" aria-label="Toggle theme">🌞</button>
    </div>
  </div>

  <div class="house-menu" id="houseMenu">
    <button class="house-trigger" type="button" aria-label="Account menu" aria-haspopup="true" aria-expanded="false"></button>
    <div class="profile-dropdown">
      <a href="profile.php">My Profile</a>
      <a href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>
