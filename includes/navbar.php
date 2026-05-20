<?php
// Requires: session_start() called + $_SESSION['username'] is set
?>
<div class="navbar">
  <div class="nav-left">
    <a href="dashboard.php" class="logo-text" style="text-decoration:none;color:inherit;">🪙 CodeNest</a>

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
      <button id="themeToggleBtn" style="background: none; border: 2px solid var(--primary-color); color: var(--primary-color); font-size: 1.2rem; cursor: pointer; padding: 5px 10px; border-radius: var(--border-radius);">🌞</button>
    </div>

    <div class="profile-menu">
      <img src="images/player.png" class="nav-avatar" alt="Profile Avatar">
      <div class="profile-dropdown">
        <a href="profile.php">My Profile</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</div>
