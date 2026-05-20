<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Whitelist allowed topics
$allowed_topics = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Java', 'C++'];
$topic = (isset($_GET['topic']) && in_array($_GET['topic'], $allowed_topics, true))
    ? $_GET['topic']
    : 'HTML';

$safeTopic = htmlspecialchars($topic, ENT_QUOTES, 'UTF-8');
$pageTitle  = $safeTopic . ' Battle — CodeNest';
include 'includes/head.php';
?>
<body class="battle-page">

<?php include 'includes/navbar_simple.php'; ?>

<!-- Floating particles background -->
<div class="battle-particles" id="battleParticles"></div>

<div class="battle-container">

  <!-- TOP: Battlefield area -->
  <div class="battlefield">

    <!-- BOSS SIDE -->
    <div class="combatant boss-side">
      <div class="combatant-info">
        <div class="combatant-name">
          <span class="name-tag enemy-tag">BOSS</span>
          <h3>Nisha</h3>
        </div>
        <div class="hp-bar-wrapper">
          <div class="hp-label">HP <span id="bossHPText">100</span>/100</div>
          <div class="hp-bar boss-hp-bar">
            <div id="bossHP" class="hp-fill boss-fill"></div>
          </div>
        </div>
      </div>
      <div class="sprite-container enemy-container">
        <div class="sprite-shadow"></div>
        <img src="images/boss.png" class="sprite enemy" alt="Enemy Boss">
        <div class="damage-popup" id="bossDmgPopup"></div>
      </div>
    </div>

    <!-- VS Divider -->
    <div class="vs-divider">
      <div class="vs-text">VS</div>
      <div class="vs-line"></div>
    </div>

    <!-- PLAYER SIDE -->
    <div class="combatant player-side">
      <div class="sprite-container player-container">
        <div class="sprite-shadow"></div>
        <img src="images/player.png" class="sprite player" alt="Player">
        <div class="damage-popup" id="playerDmgPopup"></div>
      </div>
      <div class="combatant-info">
        <div class="combatant-name">
          <span class="name-tag player-tag">YOU</span>
          <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        </div>
        <div class="hp-bar-wrapper">
          <div class="hp-label">HP <span id="playerHPText">100</span>/100</div>
          <div class="hp-bar player-hp-bar">
            <div id="playerHP" class="hp-fill player-fill"></div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.battlefield -->

  <!-- BOTTOM: Battle Command Box -->
  <div class="battle-command-box">

    <!-- Topic badge + question counter -->
    <div class="battle-meta">
      <span class="topic-badge"><?php echo $safeTopic; ?></span>
      <span class="question-counter">Question <span id="qNum">1</span> / <span id="qTotal">?</span></span>
      <span class="xp-earned">⚡ <span id="xpEarned">0</span> XP</span>
    </div>

    <!-- Timer bar -->
    <div class="timer-bar-wrapper">
      <div class="timer-bar">
        <div id="timerFill" class="timer-fill"></div>
      </div>
      <span class="timer-text" id="timerText">10s</span>
    </div>

    <!-- Question text -->
    <div class="question-panel">
      <p id="question" class="question-text">Loading battle questions...</p>
    </div>

    <!-- Answer input -->
    <div class="answer-area">
      <div class="answer-input-wrapper">
        <span class="input-prompt">&gt;_</span>
        <input type="text" id="answerInput" placeholder="Type your answer here..." autocomplete="off">
      </div>
      <button type="button" id="submitBtn" class="battle-btn">
        <span>ATTACK</span>
        <span class="btn-icon">⚔️</span>
      </button>
    </div>

    <!-- Feedback bar -->
    <div class="feedback-bar" id="feedbackBar"></div>

  </div><!-- /.battle-command-box -->

</div><!-- /.battle-container -->

<!-- End-battle overlay -->
<div class="battle-overlay" id="battleOverlay" style="display:none;">
  <div class="overlay-card">
    <div class="overlay-icon" id="overlayIcon"></div>
    <h2 id="overlayTitle"></h2>
    <p id="overlayMsg"></p>
    <div class="overlay-xp" id="overlayXP"></div>
    <button class="btn battle-btn" onclick="window.location.href='dashboard.php'">Return to Base</button>
  </div>
</div>

<script>
  const BATTLE_TOPIC = <?php echo json_encode($topic); ?>;
</script>
<script src="battlelogic.js"></script>
<?php include 'includes/footer.php'; ?>
