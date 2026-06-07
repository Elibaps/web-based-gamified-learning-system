<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'PvP Arena — CodeNest';
include 'includes/head.php';

// Get user ID for queue management
$username = $_SESSION['username'];
$user_result = $conn->query("SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];
?>
<body class="battle-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 800px; text-align: center;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px;">PvP Arena</h1>
    <p style="color: #94a3b8; margin-bottom: 20px;">Race against 'GhostHacker' to answer 5 questions correctly!</p>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div style="width: 45%; border: 4px solid var(--primary-color); background: var(--bg-color); padding: 15px;">
            <h3 style="color: inherit; margin-bottom: 10px;" id="playerNameLabel">You (<span id="playerScore">0</span>/5)</h3>
            <div class="hp-bar" style="width: 100%;"><div id="playerProgress" style="width: 0%; background: #4ade80;"></div></div>
        </div>
        <div style="width: 45%; border: 4px solid #ff0000; background: var(--bg-color); padding: 15px;">
            <h3 style="color: inherit; margin-bottom: 10px;" id="opponentNameLabel">Opponent (<span id="botScore">0</span>/5)</h3>
            <div class="hp-bar" style="width: 100%; border-color: var(--danger-color);"><div id="botProgress" style="width: 0%; background: var(--danger-color);"></div></div>
        </div>
    </div>

    <div class="battle-box" style="width: 100%; text-align: left; margin-bottom: 20px;">
        <div id="matchStatus" style="margin-bottom: 14px; font-size: 12px; letter-spacing: 1px; color: var(--muted-color);">Connecting to Arena...</div>
        <h3 id="questionText" style="color: var(--primary-color); margin-bottom: 20px; font-size: 1.5rem;">Waiting for opponent...</h3>
        <div id="optionsContainer" style="display: flex; flex-direction: column; gap: 10px;"></div>
        <div style="margin-top: 20px; display:flex; justify-content:flex-end;"><button id="leaveBtn" class="btn" style="background:#ff4444; border-color:#ff4444;">Leave Arena</button></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let matchId = null;
    let slot = null;
    let questions = [];
    let currentIdx = 0;
    let playerScore = 0;
    let opponentScore = 0;
    let gameActive = false;
    let statusTimer = null;

    const playerNameLabel = document.getElementById("playerNameLabel");
    const opponentNameLabel = document.getElementById("opponentNameLabel");
    const playerScoreEl = document.getElementById("playerScore");
    const opponentScoreEl = document.getElementById("botScore");
    const playerProgressEl = document.getElementById("playerProgress");
    const opponentProgressEl = document.getElementById("botProgress");
    const matchStatusEl = document.getElementById("matchStatus");
    const questionText = document.getElementById("questionText");
    const optionsContainer = document.getElementById("optionsContainer");
    const leaveBtn = document.getElementById("leaveBtn");

    async function joinArena() {
        matchStatusEl.textContent = 'Searching for an opponent...';
        try {
            const res = await fetch('pvp_api.php?action=join');
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Unable to join arena');

            matchId = data.match.match_id;
            slot = data.slot;
            questions = data.match.questions ? JSON.parse(data.match.questions) : [];
            currentIdx = data.match.current_idx || 0;
            playerScore = data.match.player1_score || 0;
            opponentScore = data.match.player2_score || 0;

            updateUI(data.match);
            if (data.match.status === 'playing') {
                startMatch();
            } else {
                setWaiting('Waiting for another player to enter the arena...');
                pollMatchStatus();
            }
        } catch (err) {
            setError('Unable to connect to PvP arena. Try again later.');
            console.error(err);
        }
    }

    async function pollMatchStatus() {
        if (!matchId) return;
        try {
            const res = await fetch(`pvp_api.php?action=status&match_id=${matchId}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Bad status response');

            updateUI(data.match);
            if (data.match.status === 'playing') {
                startMatch();
                return;
            }
        } catch (err) {
            console.error(err);
            setError('Connection issue while waiting for opponent.');
        }
        statusTimer = setTimeout(pollMatchStatus, 2000);
    }

    function updateUI(match) {
        const opponentName = slot === 'player1' ? (match.player2_name || 'Waiting...') : match.player1_name;
        opponentNameLabel.textContent = `${opponentName} (${match.player2_score || 0}/5)`;
        playerNameLabel.textContent = `${slot === 'player1' ? 'You' : 'You'} (${match.player1_score || 0}/5)`;

        if (slot === 'player2') {
            playerScoreEl.textContent = match.player2_score;
            opponentScoreEl.textContent = match.player1_score;
            playerProgressEl.style.width = `${Math.min(100, (match.player2_score / 5) * 100)}%`;
            opponentProgressEl.style.width = `${Math.min(100, (match.player1_score / 5) * 100)}%`;
        } else {
            playerScoreEl.textContent = match.player1_score;
            opponentScoreEl.textContent = match.player2_score;
            playerProgressEl.style.width = `${Math.min(100, (match.player1_score / 5) * 100)}%`;
            opponentProgressEl.style.width = `${Math.min(100, (match.player2_score / 5) * 100)}%`;
        }

        if (match.status === 'waiting') {
            setWaiting('Waiting for opponent to join the arena...');
        } else if (match.status === 'playing') {
            matchStatusEl.textContent = 'Match started! Answer first to score.';
            if (questions.length === 0 && match.questions) {
                questions = JSON.parse(match.questions);
            }
            renderQuestion();
        } else if (match.status === 'finished') {
            handleEnd(match);
        }
    }

    function setWaiting(text) {
        matchStatusEl.textContent = text;
        questionText.textContent = 'Waiting for opponent...';
        optionsContainer.innerHTML = '';
    }

    function setError(text) {
        matchStatusEl.textContent = text;
        questionText.textContent = 'Unable to continue.';
        optionsContainer.innerHTML = '';
    }

    function startMatch() {
        gameActive = true;
        renderQuestion();
        if (!statusTimer) {
            statusTimer = setTimeout(pollMatchStatus, 2000);
        }
    }

    function renderQuestion() {
        if (!gameActive || !questions.length) {
            questionText.textContent = 'Loading question...';
            return;
        }

        const q = questions[currentIdx];
        questionText.textContent = q.question_text;

        const options = [q.answer];
        const wrong = questions.map(item => item.answer).filter(a => a !== q.answer);
        wrong.sort(() => Math.random() - 0.5);
        for (let i = 0; i < 3 && i < wrong.length; i++) options.push(wrong[i]);
        options.sort(() => Math.random() - 0.5);

        optionsContainer.innerHTML = '';
        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'btn';
            btn.style.textAlign = 'left';
            btn.style.textTransform = 'none';
            btn.innerText = opt;
            btn.onclick = () => submitAnswer(opt);
            optionsContainer.appendChild(btn);
        });
    }

    async function submitAnswer(answer) {
        if (!gameActive || !matchId) return;
        const buttons = optionsContainer.querySelectorAll('button');
        buttons.forEach(b => b.disabled = true);
        try {
            const res = await fetch('pvp_api.php?action=answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `match_id=${encodeURIComponent(matchId)}&answer=${encodeURIComponent(answer)}`
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Answer failed');
            questions = data.match.questions ? JSON.parse(data.match.questions) : questions;
            currentIdx = data.match.current_idx;
            updateUI(data.match);
            if (data.match.status === 'playing') {
                if (data.correct) {
                    matchStatusEl.textContent = 'Correct! Point scored.';
                } else {
                    matchStatusEl.textContent = 'Incorrect. Try the next question.';
                }
            }
        } catch (err) {
            console.error(err);
            setError('Answer submission failed.');
        } finally {
            if (gameActive) buttons.forEach(b => b.disabled = false);
        }
    }

    function handleEnd(match) {
        gameActive = false;
        clearTimeout(statusTimer);
        statusTimer = null;
        const winner = match.winner_id === <?php echo json_encode($_SESSION['user_id']); ?>;
        questionText.textContent = winner ? 'Victory! You won the match.' : 'Defeat. The opponent prevailed.';
        optionsContainer.innerHTML = '';
        matchStatusEl.textContent = winner ? 'Return to the dashboard to climb the ranks.' : 'Try again to improve your PvP score.';
        if (winner) {
            try {
                fetch('award_xp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'xp=50'
                });
            } catch (e) {
                console.warn(e);
            }
        }
        setTimeout(() => { window.location.href = 'dashboard.php'; }, 3000);
    }

    leaveBtn.addEventListener('click', () => {
        window.location.href = 'dashboard.php';
    });

    joinArena();
});
</script>

</body>
</html>
