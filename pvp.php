<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
$pageTitle = 'PvP Arena — CodeNest';
include 'includes/head.php';
?>
<body class="battle-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 800px; text-align: center;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px;">PvP Arena</h1>
    <p style="color: #94a3b8; margin-bottom: 20px;">Race against 'GhostHacker' to answer 5 questions correctly!</p>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div style="width: 45%; border: 4px solid var(--primary-color); background: var(--bg-color); padding: 15px;">
            <h3 style="color: inherit; margin-bottom: 10px;">You (<span id="playerScore">0</span>/5)</h3>
            <div class="hp-bar" style="width: 100%;"><div id="playerProgress" style="width: 0%; background: #4ade80;"></div></div>
        </div>
        <div style="width: 45%; border: 4px solid #ff0000; background: var(--bg-color); padding: 15px;">
            <h3 style="color: inherit; margin-bottom: 10px;">GhostHacker (<span id="botScore">0</span>/5)</h3>
            <div class="hp-bar" style="width: 100%; border-color: var(--danger-color);"><div id="botProgress" style="width: 0%; background: var(--danger-color);"></div></div>
        </div>
    </div>

    <div class="battle-box" style="width: 100%; text-align: left; margin-bottom: 20px;">
        <h3 id="questionText" style="color: var(--primary-color); margin-bottom: 20px; font-size: 1.5rem;">Connecting to Arena...</h3>
        <div id="optionsContainer" style="display: flex; flex-direction: column; gap: 10px;"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    let questions = [];
    try {
        const res = await fetch(`get_questions.php?topic=HTML`); // Mixed later, HTML for now
        questions = await res.json();
    } catch (err) {
        questions = [{ question_text: "What does HTML stand for?", answer: "hypertext markup language" }];
    }
    
    if (!questions.length) return;
    
    const allAnswers = questions.map(q => q.answer);
    
    let currentIdx = 0;
    let playerScore = 0;
    let botScore = 0;
    let botTimer = null;
    let gameActive = true;
    
    function loadQuestion() {
        if (!gameActive) return;
        if (currentIdx >= questions.length) currentIdx = 0; // loop if needed
        
        const q = questions[currentIdx];
        document.getElementById("questionText").innerText = q.question_text;
        
        let options = [q.answer];
        let availableWrong = allAnswers.filter(a => a !== q.answer).sort(() => Math.random() - 0.5);
        for(let i=0; i<3 && i<availableWrong.length; i++) options.push(availableWrong[i]);
        options.sort(() => Math.random() - 0.5);
        
        const container = document.getElementById("optionsContainer");
        container.innerHTML = "";
        
        options.forEach(opt => {
            let btn = document.createElement("button");
            btn.className = "btn";
            btn.style.textAlign = "left";
            btn.style.textTransform = "none";
            btn.innerText = opt;
            btn.onclick = () => handleAnswer(opt === q.answer);
            container.appendChild(btn);
        });

        // Bot answers after random delay (3 to 6 seconds)
        clearTimeout(botTimer);
        const botDelay = Math.floor(Math.random() * 3000) + 3000;
        botTimer = setTimeout(() => {
            if (!gameActive) return;
            // Bot has 70% chance to be correct
            if (Math.random() < 0.7) {
                botScore++;
                updateProgress();
                checkWin();
                if(gameActive) { currentIdx++; loadQuestion(); }
            }
        }, botDelay);
    }
    
    function handleAnswer(isCorrect) {
        if (!gameActive) return;
        if (isCorrect) {
            playerScore++;
            updateProgress();
            checkWin();
            if (gameActive) { currentIdx++; loadQuestion(); }
        } else {
            // Penalty: Disable buttons briefly
            const btns = document.querySelectorAll('#optionsContainer button');
            btns.forEach(b => b.disabled = true);
            setTimeout(() => { if (gameActive) btns.forEach(b => b.disabled = false); }, 1500);
        }
    }

    function updateProgress() {
        document.getElementById("playerScore").innerText = playerScore;
        document.getElementById("botScore").innerText = botScore;
        document.getElementById("playerProgress").style.width = (playerScore / 5 * 100) + "%";
        document.getElementById("botProgress").style.width = (botScore / 5 * 100) + "%";
    }

    async function checkWin() {
        if (playerScore >= 5 || botScore >= 5) {
            gameActive = false;
            clearTimeout(botTimer);
            const won = playerScore >= 5;
            document.getElementById("questionText").innerText = won ? "You Won the Match!" : "GhostHacker Won!";
            document.getElementById("optionsContainer").innerHTML = "";

            if (won) {
                try {
                    await fetch("award_xp.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `xp=50`
                    });
                } catch(e) {}
                setTimeout(() => { alert("Victory! You earned 50 XP!"); window.location.href = "dashboard.php"; }, 1000);
            } else {
                setTimeout(() => { alert("Defeat! Better luck next time."); window.location.href = "dashboard.php"; }, 1000);
            }
        }
    }
    
    setTimeout(loadQuestion, 1000);
});
</script>

</body>
</html>
