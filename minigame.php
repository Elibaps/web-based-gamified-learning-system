<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Bug Smasher — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 20px;">
  <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6);">Code Bug Smasher</h1>
  <p style="color: inherit; margin-bottom: 20px;">Click the red bugs before time runs out! Earn 1 XP per bug.</p>
  
  <div style="display: flex; justify-content: space-between; max-width: 600px; margin: 0 auto 10px auto;">
    <span style="color: var(--primary-color); font-weight: bold;">Score: <span id="mgScore">0</span></span>
    <span style="color: var(--primary-color); font-weight: bold;">Time: <span id="mgTime">30</span>s</span>
  </div>

  <div id="gameArea" style="width: 600px; height: 400px; background: var(--bg-color); border: 4px solid var(--primary-color); margin: 0 auto; position: relative; overflow: hidden; cursor: crosshair;">
    <button id="startBtn" class="btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px;">Start Game</button>
  </div>
</div>

<script>
let score = 0;
let time = 30;
let gameInterval;
let bugInterval;
let isPlaying = false;

document.getElementById('startBtn').addEventListener('click', function() {
    this.style.display = 'none';
    startGame();
});

function startGame() {
    score = 0;
    time = 30;
    isPlaying = true;
    document.getElementById('mgScore').innerText = score;
    document.getElementById('mgTime').innerText = time;
    
    gameInterval = setInterval(() => {
        time--;
        document.getElementById('mgTime').innerText = time;
        if(time <= 0) endGame();
    }, 1000);
    
    bugInterval = setInterval(spawnBug, 800);
}

function spawnBug() {
    if(!isPlaying) return;
    const area = document.getElementById('gameArea');
    const bug = document.createElement('div');
    bug.innerHTML = "🐞";
    bug.style.position = 'absolute';
    bug.style.fontSize = '30px';
    bug.style.userSelect = 'none';
    
    const x = Math.random() * (area.clientWidth - 40);
    const y = Math.random() * (area.clientHeight - 40);
    bug.style.left = x + 'px';
    bug.style.top = y + 'px';
    
    bug.onclick = function() {
        if(!isPlaying) return;
        score++;
        document.getElementById('mgScore').innerText = score;
        this.remove();
        
        // Spawn a floating +1 XP
        const floatText = document.createElement('div');
        floatText.innerText = "+1 XP";
        floatText.style.position = 'absolute';
        floatText.style.color = '#4ade80';
        floatText.style.left = x + 'px';
        floatText.style.top = y + 'px';
        floatText.style.transition = 'all 1s';
        area.appendChild(floatText);
        setTimeout(() => { floatText.style.top = (y - 50) + 'px'; floatText.style.opacity = '0'; }, 50);
        setTimeout(() => floatText.remove(), 1000);
    };
    
    area.appendChild(bug);
    
    setTimeout(() => {
        if (bug.parentNode) bug.remove();
    }, 1500);
}

async function endGame() {
    isPlaying = false;
    clearInterval(gameInterval);
    clearInterval(bugInterval);
    
    document.getElementById('gameArea').innerHTML = `<div style="color:#4ade80; padding-top: 150px; font-size: 24px;">Game Over!<br>You smashed ${score} bugs.</div>`;
    
    if (score > 0) {
        try {
            const resp = await fetch("award_xp.php", {
              method:  "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body:    `xp=${score}`,
            });
            const data = await resp.json();
            if (data.leveledUp) {
              alert(`🎉 LEVEL UP! You are now Level ${data.newLevel}!`);
            }
        } catch (e) {
            console.error(e);
        }
    }
    
    setTimeout(() => {
        window.location.href = "dashboard.php";
    }, 3000);
}
</script>

</body>
</html>
