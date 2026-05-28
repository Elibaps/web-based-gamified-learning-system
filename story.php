<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
$pageTitle = 'Story Mode — CodeNest';
include 'includes/head.php';
?>
<body class="story-mode-page">
<?php include 'includes/navbar_simple.php'; ?>

<!-- ════════════════════════════════════════
     LOADING SCREEN — Adventure Intro
════════════════════════════════════════ -->
<div class="adventure-loading-screen" id="adventureLoadingScreen">
  <div class="loading-backdrop"></div>
  
  <div class="loading-container">
    <!-- Player Image -->
    <div class="loading-player-image">
      <div class="player-frame">
        <img src="images/player.png" alt="<?php echo $username; ?>" class="player-avatar">
        <div class="player-aura"></div>
      </div>
    </div>

    <!-- Dialogue Box -->
    <div class="loading-dialogue-box">
      <div class="dialogue-header">
        <span class="dialogue-speaker"><?php echo $username; ?></span>
        <span class="dialogue-role">• THE HERO •</span>
      </div>
      
      <div class="dialogue-content">
        <p class="dialogue-line" id="dialogueLine1">A sinister presence looms over the CodeNest mainframe...</p>
        <p class="dialogue-line" id="dialogueLine2">CYPH-3R, the corrupted AI, has taken control!</p>
        <p class="dialogue-line" id="dialogueLine3">The only way to save the system is to defeat the boss by solving coding challenges.</p>
        <p class="dialogue-line" id="dialogueLine4">Are you ready to face the final battle?</p>
      </div>

      <div class="loading-progress">
        <div class="progress-bar-outer">
          <div class="progress-bar-inner" id="progressBar"></div>
        </div>
        <p class="progress-text" id="progressText">Loading adventure... 0%</p>
      </div>

      <button class="loading-btn" id="startAdventureBtn">
        <span class="btn-text">BEGIN ADVENTURE</span>
        <span class="btn-arrow">→</span>
      </button>
    </div>

    <!-- Boss Preview -->
    <div class="loading-boss-preview">
      <div class="boss-frame">
        <img src="images/boss.png" alt="CYPH-3R" class="boss-avatar">
        <div class="boss-danger"></div>
      </div>
      <p class="boss-label">CYPH-3R</p>
      <p class="boss-title">[ FINAL BOSS ]</p>
    </div>
  </div>
</div>

<div class="story-grid">

  <!-- ════════════════════════════════════════
       TOP-LEFT: Pixel World Scene
  ════════════════════════════════════════ -->
  <div class="world-panel">

    <div class="world-header">
      <span class="world-title">🗺 PIXEL WORLD</span>
      <span class="world-stage" id="worldStage">SECTOR 01 — MAINFRAME CORE</span>
    </div>

    <!-- The RPG scene canvas -->
    <div class="world-scene" id="worldScene">
      <!-- Tiled ground -->
      <div class="ground-tiles"></div>

      <!-- Ambient floating bits -->
      <div class="ambient-bit" style="top:12%;left:8%;animation-delay:0s">1</div>
      <div class="ambient-bit" style="top:20%;left:55%;animation-delay:1.2s">0</div>
      <div class="ambient-bit" style="top:50%;left:75%;animation-delay:0.7s">1</div>
      <div class="ambient-bit" style="top:70%;left:15%;animation-delay:2s">0</div>
      <div class="ambient-bit" style="top:30%;left:88%;animation-delay:1.5s">1</div>

      <!-- NPC 1 — top-left area -->
      <div class="world-char npc-char" id="npc1" style="top:20%;left:12%;">
        <div class="char-sprite">
          <img src="images/boss.png" alt="CYPH-3R">
        </div>
        <div class="char-label npc-label">CYPH-3R</div>
        <div class="char-speech" id="npc1Speech"></div>
      </div>

      <!-- NPC 2 — center-right area -->
      <div class="world-char npc-char" id="npc2" style="top:18%;left:58%;">
        <div class="char-sprite">
          <img src="images/boss.png" alt="GUARDIAN" style="filter:hue-rotate(180deg)">
        </div>
        <div class="char-label npc-label">GUARDIAN</div>
        <div class="char-speech" id="npc2Speech"></div>
      </div>

      <!-- Player — bottom-right area -->
      <div class="world-char player-char" id="playerChar" style="bottom:18%;right:12%;">
        <div class="char-speech" id="playerSpeech"></div>
        <div class="char-sprite player-sprite-wrap">
          <img src="images/player.png" alt="You">
        </div>
        <div class="char-label player-label"><?php echo $username; ?></div>
      </div>

      <!-- Dialogue popup that appears above active NPC -->
      <div class="rpg-dialogue" id="rpgDialogue" style="display:none;">
        <div class="rpg-dialogue-inner">
          <span class="rpg-speaker" id="rpgSpeaker">CYPH-3R</span>
          <span class="rpg-text" id="rpgText"></span>
          <span class="rpg-next">▼ click</span>
        </div>
      </div>

    </div><!-- /.world-scene -->

    <!-- Action bar (Pokémon-style 2×2) -->
    <div class="world-actions" id="worldActions" style="display:none;">
      <button class="wa-btn" onclick="doAction('code')">⚔️ CODE</button>
      <button class="wa-btn" onclick="doAction('study')">📖 STUDY</button>
      <button class="wa-btn" onclick="doAction('hint')">💡 HINT</button>
      <button class="wa-btn" onclick="doAction('wait')">⏳ WAIT</button>
    </div>

    <!-- Walking prompt -->
    <div class="walk-prompt" id="walkPrompt">
      <span>▶ Click anywhere on the world to continue...</span>
    </div>

  </div><!-- /.world-panel -->

  <!-- ════════════════════════════════════════
       TOP-RIGHT: Code Editor
  ════════════════════════════════════════ -->
  <div class="code-panel">

    <div class="cp-header">
      <span class="cp-title">💻 TERMINAL</span>
      <div class="cp-badges">
        <span class="cp-badge-lang" id="langBadge">HTML</span>
        <span class="cp-badge-stage" id="stageBadge">⭐ STAGE 1</span>
        <span class="cp-xp">⚡ <span id="xpCounter">0</span> XP</span>
      </div>
    </div>

    <!-- Objective -->
    <div class="cp-objective" id="cpObjective">
      <span class="cp-obj-label">▶ OBJECTIVE</span>
      <span class="cp-obj-text" id="objText">Awaiting mission briefing...</span>
    </div>

    <!-- Editor -->
    <div class="cp-editor-wrap">
      <div class="cp-gutter" id="cpGutter">
        <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span>
        <span>6</span><span>7</span><span>8</span><span>9</span><span>10</span>
      </div>
      <textarea id="codeEditor" class="cp-editor" spellcheck="false"
                placeholder="// Write your code here..."></textarea>
    </div>

    <!-- Toolbar -->
    <div class="cp-toolbar">
      <button class="cp-btn cp-run"   onclick="runCode()">▶ RUN</button>
      <button class="cp-btn cp-clear" onclick="clearCode()">⌫ CLEAR</button>
      <button class="cp-btn cp-hint"  id="hintBtn" onclick="showHint()">💡 HINT</button>
    </div>

    <!-- Output -->
    <div class="cp-output" id="cpOutput" style="display:none;">
      <div class="cp-out-label">OUTPUT</div>
      <div class="cp-out-content" id="cpOutContent"></div>
    </div>

  </div><!-- /.code-panel -->

</div><!-- /.story-grid -->

<!-- ════════════════════════════════════════
     BOTTOM: Mission Description Story-line
════════════════════════════════════════ -->
<div class="story-bar">
  <div class="story-bar-header">
    <span class="sb-label">📜 MISSION LOG</span>
    <span class="sb-chapter" id="sbChapter">CHAPTER 1: THE BREACH</span>
    <button class="sb-toggle" onclick="toggleStoryBar()">▲</button>
  </div>
  <div class="story-bar-body" id="storyBarBody">
    <div class="story-entry active-story" id="se0">
      <span class="se-dot">🔴</span>
      <span><strong>BREACH DETECTED:</strong> CYPH-3R has corrupted the CodeNest mainframe. Every sector is locked behind corrupted code. Your mission: write correct programs to restore each sector and defeat CYPH-3R.</span>
    </div>
    <div class="story-entry" id="se1" style="opacity:0.35">
      <span class="se-dot">⬜</span>
      <span><strong>SECTOR 01 — HTML:</strong> Restore the page structure firewall by outputting the correct HTML heading.</span>
    </div>
    <div class="story-entry" id="se2" style="opacity:0.35">
      <span class="se-dot">⬜</span>
      <span><strong>SECTOR 02 — CSS:</strong> Override the style-engine lockdown. Write the correct CSS rule.</span>
    </div>
    <div class="story-entry" id="se3" style="opacity:0.35">
      <span class="se-dot">⬜</span>
      <span><strong>SECTOR 03 — JS:</strong> The logic core is scrambled. Fix the missing JavaScript expression.</span>
    </div>
    <div class="story-entry" id="se4" style="opacity:0.35">
      <span class="se-dot">⬜</span>
      <span><strong>FINAL — PHP:</strong> Execute the restore command to permanently eject CYPH-3R from the mainframe.</span>
    </div>
  </div>
</div>

<!-- Victory Overlay -->
<div class="v-overlay" id="vOverlay" style="display:none;">
  <div class="v-card">
    <div class="v-icon" id="vIcon">🏆</div>
    <h2 id="vTitle">SECTOR CLEARED!</h2>
    <p  id="vMsg">CYPH-3R's defenses weakened.</p>
    <div class="v-xp" id="vXP">+20 XP</div>
    <div class="v-btns">
      <button class="cp-btn cp-run" onclick="nextStage()">NEXT SECTOR ▶</button>
      <button class="cp-btn cp-clear" onclick="window.location.href='dashboard.php'">↩ BASE</button>
    </div>
  </div>
</div>

<script src="storylogic.js"></script>
<?php include 'includes/footer.php'; ?>
