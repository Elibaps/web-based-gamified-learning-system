<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'Minigames — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="dashboard-wrapper" style="text-align: center; padding: 20px;">
  <div id="menuScreen" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 500px;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); margin-bottom: 30px;">Choose a Minigame</h1>
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;">
      <!-- Bug Smasher Option -->
      <div style="background: var(--card-bg); border: 4px solid var(--primary-color); padding: 30px; border-radius: 8px; cursor: pointer; transition: all 0.3s; width: 250px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="selectGame('bugSmasher')">
        <div style="font-size: 60px; margin-bottom: 15px;">🐞</div>
        <h2 style="color: var(--primary-color); margin-bottom: 10px;">Bug Smasher</h2>
        <p style="color: inherit; margin: 10px 0; font-size: 14px;">Click bugs before time runs out!<br/>1 XP per bug</p>
        <span style="color: var(--primary-color); font-size: 12px; font-weight: bold;">⏱️ 30 seconds</span>
      </div>

      <!-- Quiz Challenge Option -->
      <div style="background: var(--card-bg); border: 4px solid var(--primary-color); padding: 30px; border-radius: 8px; cursor: pointer; transition: all 0.3s; width: 250px;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="selectGame('quiz')">
        <div style="font-size: 60px; margin-bottom: 15px;">📚</div>
        <h2 style="color: var(--primary-color); margin-bottom: 10px;">Quiz Challenge</h2>
        <p style="color: inherit; margin: 10px 0; font-size: 14px;">Answer questions correctly!<br/>10 XP per correct answer</p>
        <span style="color: var(--primary-color); font-size: 12px; font-weight: bold;">❓ 10 questions</span>
      </div>
    </div>
  </div>

  <!-- Bug Smasher Game Screen -->
  <div id="bugSmasherScreen" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
      <button class="btn" onclick="backToMenu()" style="width: 100px;">← Back</button>
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); flex: 1; margin: 0;">Bug Smasher</h1>
      <div style="width: 100px;"></div>
    </div>
    <p style="color: inherit; margin-bottom: 20px;">Click the bugs before time runs out! Earn 1 XP per bug.</p>
    
    <div style="display: flex; justify-content: space-between; max-width: 600px; margin: 0 auto 10px auto;">
      <span style="color: var(--primary-color); font-weight: bold;">Score: <span id="mgScore">0</span></span>
      <span style="color: var(--primary-color); font-weight: bold;">Time: <span id="mgTime">30</span>s</span>
    </div>

    <div id="gameArea" style="width: 600px; height: 400px; background: var(--bg-color); border: 4px solid var(--primary-color); margin: 0 auto; position: relative; overflow: hidden; cursor: crosshair;">
      <button id="startBtn" class="btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px;">Start Game</button>
    </div>
  </div>

  <!-- Quiz Topic Selection Screen -->
  <div id="quizTopicScreen" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <button class="btn" onclick="backToMenu()" style="width: 100px;">← Back</button>
      <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); flex: 1; margin: 0;">Select a Topic</h1>
      <div style="width: 100px;"></div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; max-width: 600px; margin: 0 auto;">
      <button class="btn" onclick="startQuiz('HTML')" style="padding: 20px; font-size: 14px;">HTML</button>
      <button class="btn" onclick="startQuiz('CSS')" style="padding: 20px; font-size: 14px;">CSS</button>
      <button class="btn" onclick="startQuiz('JavaScript')" style="padding: 20px; font-size: 14px;">JavaScript</button>
      <button class="btn" onclick="startQuiz('React')" style="padding: 20px; font-size: 14px;">React</button>
      <button class="btn" onclick="startQuiz('Python')" style="padding: 20px; font-size: 14px;">Python</button>
      <button class="btn" onclick="startQuiz('Database')" style="padding: 20px; font-size: 14px;">Database</button>
    </div>
  </div>
</div>

<script>
// Menu Navigation
function selectGame(game) {
  document.getElementById('menuScreen').style.display = 'none';
  document.getElementById('quizTopicScreen').style.display = 'none';
  if (game === 'bugSmasher') {
    document.getElementById('bugSmasherScreen').style.display = 'block';
  } else if (game === 'quiz') {
    document.getElementById('quizTopicScreen').style.display = 'block';
  }
}

function backToMenu() {
  document.getElementById('menuScreen').style.display = 'flex';
  document.getElementById('bugSmasherScreen').style.display = 'none';
  document.getElementById('quizTopicScreen').style.display = 'none';
  
  // Reset bug smasher game
  score = 0;
  time = 30;
  isPlaying = false;
  clearInterval(gameInterval);
  clearInterval(bugInterval);
  document.getElementById('mgScore').innerText = '0';
  document.getElementById('mgTime').innerText = '30';
  document.getElementById('gameArea').innerHTML = '<button id="startBtn" class="btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px;">Start Game</button>';
  document.getElementById('startBtn').addEventListener('click', function() {
    this.style.display = 'none';
    startGame();
  });
}

function startQuiz(topic) {
  window.location.href = 'quiz.php?topic=' + encodeURIComponent(topic);
}

// Bug Smasher Game Logic
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
