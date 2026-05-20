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

<div class="story-layout">

  <!-- ══════════════════════════════════════════
       LEFT PANEL — Pokémon-style character scene
  ══════════════════════════════════════════ -->
  <div class="story-left">

    <!-- Scene environment -->
    <div class="story-scene">
      <!-- Background grid overlay -->
      <div class="scene-grid"></div>

      <!-- Enemy / NPC side -->
      <div class="scene-npc">
        <div class="npc-info-bar">
          <span class="npc-name-tag">GUARDIAN</span>
          <span class="npc-name">CYPH-3R</span>
          <div class="npc-hp-row">
            <span class="hp-label-small">HP</span>
            <div class="npc-hp-bar"><div id="npcHP" style="width:100%"></div></div>
          </div>
        </div>
        <div class="sprite-wrap npc-wrap">
          <div class="sprite-glow npc-glow"></div>
          <img src="images/boss.png" class="scene-sprite npc-sprite" alt="CYPH-3R" id="npcSprite">
        </div>
      </div>

      <!-- Player side -->
      <div class="scene-player">
        <div class="sprite-wrap player-wrap">
          <div class="sprite-glow player-glow"></div>
          <img src="images/player.png" class="scene-sprite player-sprite" alt="You" id="playerSprite">
        </div>
        <div class="player-info-bar">
          <span class="npc-name"><?php echo $username; ?></span>
          <span class="npc-name-tag player-tag">HACKER</span>
          <div class="npc-hp-row">
            <span class="hp-label-small">XP</span>
            <div class="npc-hp-bar player-hp-bar"><div id="playerXP" style="width:40%"></div></div>
          </div>
        </div>
      </div>

      <!-- Stage label -->
      <div class="stage-label">📡 SECTOR 01 — MAINFRAME CORE</div>
    </div>

    <!-- Pokémon-style dialogue box -->
    <div class="dialogue-box" id="dialogueBox">
      <div class="dialogue-speaker" id="dialogueSpeaker">CYPH-3R</div>
      <div class="dialogue-text" id="dialogueText"></div>
      <div class="dialogue-cursor" id="dialogueCursor">▼</div>
    </div>

    <!-- Action buttons (Pokémon-style 2x2 grid) -->
    <div class="story-actions" id="storyActions" style="display:none;">
      <button class="action-btn" onclick="chooseAction('code')">⚔️ CODE</button>
      <button class="action-btn" onclick="chooseAction('study')">📖 STUDY</button>
      <button class="action-btn" onclick="chooseAction('run')">🏃 RUN</button>
      <button class="action-btn" onclick="chooseAction('hint')">💡 HINT</button>
    </div>

  </div><!-- /.story-left -->

  <!-- ══════════════════════════════════════════
       RIGHT PANEL — Code Challenge
  ══════════════════════════════════════════ -->
  <div class="story-right" id="storyRight">

    <div class="code-panel-header">
      <span class="code-panel-title">💻 MISSION TERMINAL</span>
      <div class="code-panel-badges">
        <span class="badge-lang" id="langBadge">HTML</span>
        <span class="badge-difficulty">⭐ STAGE 1</span>
      </div>
    </div>

    <!-- Objective -->
    <div class="code-objective" id="codeObjective">
      <span class="obj-label">▶ OBJECTIVE:</span>
      <span id="objText">Awaiting briefing from CYPH-3R...</span>
    </div>

    <!-- Code Editor -->
    <div class="editor-wrapper">
      <div class="editor-gutter" id="editorGutter">
        <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span>
        <span>6</span><span>7</span><span>8</span><span>9</span><span>10</span>
      </div>
      <textarea id="codeEditor" class="code-editor" spellcheck="false" placeholder="// Write your code here..."></textarea>
    </div>

    <!-- Toolbar -->
    <div class="editor-toolbar">
      <button class="tool-btn" id="runBtn" onclick="runCode()">▶ RUN CODE</button>
      <button class="tool-btn tool-btn-secondary" onclick="clearEditor()">⌫ CLEAR</button>
      <button class="tool-btn tool-btn-hint" id="hintBtn" onclick="showHint()">💡 HINT</button>
    </div>

    <!-- Output console -->
    <div class="code-output" id="codeOutput">
      <div class="output-label">OUTPUT</div>
      <div id="outputContent" class="output-content">Waiting for code execution...</div>
    </div>

  </div><!-- /.story-right -->

</div><!-- /.story-layout -->

<!-- ══════════════════════════════════════════
     BOTTOM PANEL — Story / Mission Log
══════════════════════════════════════════ -->
<div class="story-bottom">
  <div class="story-log-header">
    <span class="log-title">📜 MISSION LOG</span>
    <span class="log-chapter" id="logChapter">CHAPTER 1: THE BREACH</span>
    <button class="log-toggle" onclick="toggleLog()">▲</button>
  </div>
  <div class="story-log-content" id="storyLog">
    <div class="log-entry active-entry">
      <span class="log-entry-icon">🔴</span>
      <span><strong>MISSION START:</strong> The CodeNest mainframe has gone offline. Hostile AI entity "CYPH-3R" has corrupted the core systems. Your mission: repair each sector by solving code challenges. Every correct solution weakens CYPH-3R's grip on the system.</span>
    </div>
    <div class="log-entry" id="logEntry2" style="opacity:0.3;">
      <span class="log-entry-icon">⬜</span>
      <span><strong>SECTOR 01:</strong> Destroy the HTML firewall by outputting the correct page structure.</span>
    </div>
    <div class="log-entry" id="logEntry3" style="opacity:0.3;">
      <span class="log-entry-icon">⬜</span>
      <span><strong>SECTOR 02:</strong> Override CSS lockdown — style the terminal to restore visual output.</span>
    </div>
    <div class="log-entry" id="logEntry4" style="opacity:0.3;">
      <span class="log-entry-icon">⬜</span>
      <span><strong>SECTOR 03:</strong> JavaScript encryption broken — write a function to decrypt CYPH-3R's data.</span>
    </div>
    <div class="log-entry" id="logEntry5" style="opacity:0.3;">
      <span class="log-entry-icon">⬜</span>
      <span><strong>FINAL:</strong> Defeat CYPH-3R by mastering all four core languages. Restore the mainframe.</span>
    </div>
  </div>
</div>

<!-- Victory overlay -->
<div class="story-overlay" id="storyOverlay" style="display:none;">
  <div class="story-overlay-card">
    <div class="overlay-big-icon" id="overlayBigIcon">🏆</div>
    <h2 id="overlayHeading">SECTOR CLEARED!</h2>
    <p id="overlaySubtext">CYPH-3R's defenses weakened.</p>
    <div class="overlay-xp-gain" id="overlayXP">+25 XP Earned</div>
    <div class="overlay-buttons">
      <button class="tool-btn" onclick="nextStage()">NEXT SECTOR ▶</button>
      <button class="tool-btn tool-btn-secondary" onclick="window.location.href='dashboard.php'">↩ BASE</button>
    </div>
  </div>
</div>

<script src="storylogic.js"></script>
<?php include 'includes/footer.php'; ?>
