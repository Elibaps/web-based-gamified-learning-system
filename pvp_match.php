<?php
/**
 * pvp_match.php
 * Live PvP match page - shows both players competing in real-time
 */
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['match_id'])) {
    header('Location: pvp.php');
    exit();
}

$match_id = (int)$_GET['match_id'];
$username = $_SESSION['username'];

// Get user ID
$user_result = $conn->query("SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];

// Get match data
$match = $conn->query(
    "SELECT * FROM pvp_matches WHERE match_id = $match_id AND (player1_id = $user_id OR player2_id = $user_id)"
)->fetch_assoc();

if (!$match) {
    header('Location: pvp.php');
    exit();
}

// Get questions for this match
$questions = $conn->query(
    "SELECT * FROM match_questions WHERE match_id = $match_id ORDER BY question_order ASC"
)->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'PvP Match — CodeNest';
include 'includes/head.php';

$is_player1 = ($match['player1_id'] === $user_id);
$my_name = $is_player1 ? $match['player1_username'] : $match['player2_username'];
$opp_name = $is_player1 ? $match['player2_username'] : $match['player1_username'];
?>
<body class="battle-page" style="overflow: hidden;">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 900px;">
    <!-- Score Display -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px;">
        <div style="flex: 1; border: 3px solid var(--primary-color); background: var(--bg-color); padding: 15px; border-radius: 8px; text-align: center;">
            <h3 style="color: var(--primary-color); margin-bottom: 10px;">You</h3>
            <div style="font-size: 2.5rem; color: var(--primary-color); font-weight: bold;" id="myScore">0</div>
            <div style="font-size: 0.9rem; color: #94a3b8;">/ 5 Correct</div>
            <div class="hp-bar" style="width: 100%; margin-top: 10px;">
                <div id="myProgress" style="width: 0%; background: var(--primary-color);"></div>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; flex: 0.5;">
            <div style="font-size: 2rem;">⚔️</div>
            <div style="color: #94a3b8; margin-top: 10px; font-size: 0.9rem;" id="matchStatus">Active</div>
        </div>
        
        <div style="flex: 1; border: 3px solid #ff6b6b; background: var(--bg-color); padding: 15px; border-radius: 8px; text-align: center;">
            <h3 style="color: #ff6b6b; margin-bottom: 10px;" id="oppName">Opponent</h3>
            <div style="font-size: 2.5rem; color: #ff6b6b; font-weight: bold;" id="oppScore">0</div>
            <div style="font-size: 0.9rem; color: #94a3b8;">/ 5 Correct</div>
            <div class="hp-bar" style="width: 100%; margin-top: 10px; border-color: #ff6b6b;">
                <div id="oppProgress" style="width: 0%; background: #ff6b6b;"></div>
            </div>
            <div id="oppStatus" style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px; font-style: italic;">Thinking...</div>
        </div>
    </div>

    <!-- Question Display -->
    <div class="battle-box" style="width: 100%; text-align: left; margin-bottom: 20px;">
        <h3 id="questionText" style="color: var(--primary-color); margin-bottom: 20px; font-size: 1.3rem;">Loading question...</h3>
        <div id="optionsContainer" style="display: flex; flex-direction: column; gap: 10px;"></div>
    </div>

    <!-- Match End Modal -->
    <div id="matchEndModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: var(--bg-color); border: 3px solid var(--primary-color); padding: 40px; border-radius: 12px; text-align: center; max-width: 400px;">
            <h2 id="resultTitle" style="color: var(--primary-color); margin-bottom: 20px; font-size: 2rem;">Match Complete!</h2>
            <p id="resultMessage" style="color: #cbd5e1; margin-bottom: 10px; font-size: 1.1rem;"></p>
            <p id="xpMessage" style="color: var(--primary-color); margin-bottom: 20px; font-size: 1rem;">⚡ +50 XP</p>
            <button class="btn" onclick="window.location.href='dashboard.php'" style="width: 100%;">Back to Dashboard</button>
        </div>
    </div>
</div>

<script>
const MATCH_ID = <?php echo $match_id; ?>;
const USER_ID = <?php echo $user_id; ?>;
const IS_PLAYER1 = <?php echo $is_player1 ? 'true' : 'false'; ?>;

let matchData = {};
let questions = <?php echo json_encode($questions); ?>;
let currentQuestionIdx = 0;
let myAnswers = {};
let oppAnswers = {};
let gameActive = true;
let updateInterval;
let allQuestionsAnswered = false;

// Shuffle question options
function shuffleOptions(correct, allAnswers) {
    let options = [correct];
    let wrong = allAnswers.filter(a => a !== correct).sort(() => Math.random() - 0.5);
    for (let i = 0; i < 3 && i < wrong.length; i++) {
        options.push(wrong[i]);
    }
    return options.sort(() => Math.random() - 0.5);
}

// Load and display a question
function loadQuestion() {
    if (!gameActive || currentQuestionIdx >= questions.length) return;
    
    const q = questions[currentQuestionIdx];
    document.getElementById('questionText').innerText = q.question_text;
    
    // Get all answers for shuffling
    const allAnswers = questions.map(qst => qst.answer);
    const options = shuffleOptions(q.answer, allAnswers);
    
    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    
    options.forEach(opt => {
        const btn = document.createElement('button');
        btn.className = 'btn';
        btn.style.textAlign = 'left';
        btn.style.textTransform = 'none';
        btn.style.padding = '12px 15px';
        btn.innerText = opt;
        btn.onclick = () => handleAnswer(opt === q.answer, opt, q.question_id);
        container.appendChild(btn);
    });
}

// Handle answer submission
async function handleAnswer(isCorrect, selectedAnswer, questionId) {
    if (!gameActive) return;
    
    // Disable all buttons
    document.querySelectorAll('#optionsContainer button').forEach(b => b.disabled = true);
    
    // Record answer locally
    myAnswers[questionId] = isCorrect;
    
    try {
        // Send answer to server
        const response = await fetch('pvp_match_api.php?action=submit_answer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                match_id: MATCH_ID,
                player_id: USER_ID,
                question_id: questionId,
                is_correct: isCorrect
            })
        });
        
        const data = await response.json();
        
        if (data.match_complete) {
            // Match is over
            endMatch(data.winner_id === USER_ID);
            return;
        }
    } catch (error) {
        console.error('Error submitting answer:', error);
    }
    
    // Move to next question after a short delay
    setTimeout(() => {
        if (gameActive) {
            currentQuestionIdx++;
            if (currentQuestionIdx < questions.length) {
                loadQuestion();
            } else {
                allQuestionsAnswered = true;
                document.getElementById('questionText').innerText = 'Waiting for opponent to finish...';
                document.getElementById('optionsContainer').innerHTML = '<p style="color: #94a3b8;">Both players have answered all questions. Match will end shortly...</p>';
            }
        }
    }, 1000);
}

// Poll for opponent progress
async function updateOpponentProgress() {
    if (!gameActive) return;
    
    try {
        const response = await fetch(`pvp_match_api.php?action=get_progress&match_id=${MATCH_ID}&player_id=${USER_ID}`);
        const data = await response.json();
        
        // Update opponent score and progress
        const oppScoreField = IS_PLAYER1 ? 'player2_score' : 'player1_score';
        document.getElementById('oppScore').innerText = data.opponent_score || 0;
        document.getElementById('oppProgress').style.width = (data.opponent_score / 5 * 100) + '%';
        
        // Update opponent status
        const questionsAnswered = Object.keys(data.answers).length;
        if (questionsAnswered === questions.length) {
            document.getElementById('oppStatus').innerText = 'Waiting for match end...';
        } else {
            document.getElementById('oppStatus').innerText = questionsAnswered > 0 ? 'Answering...' : 'Thinking...';
        }
        
        // Store opponent answers for reference
        oppAnswers = data.answers;
        
        // Check if match is complete
        if (data.status === 'completed' && data.winner_id) {
            gameActive = false;
            endMatch(data.winner_id === USER_ID);
        }
    } catch (error) {
        console.error('Error updating opponent progress:', error);
    }
}

// End the match
async function endMatch(won) {
    gameActive = false;
    clearInterval(updateInterval);
    
    const modal = document.getElementById('matchEndModal');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const xpMessage = document.getElementById('xpMessage');
    
    if (won) {
        resultTitle.innerText = '🏆 Victory!';
        resultTitle.style.color = 'var(--primary-color)';
        resultMessage.innerText = 'You defeated your opponent!';
        xpMessage.style.display = 'block';
        
        // Award XP
        try {
            await fetch('award_xp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'xp=50'
            });
        } catch (e) {}
    } else {
        resultTitle.innerText = '⚔️ Defeat';
        resultTitle.style.color = '#ff6b6b';
        resultMessage.innerText = 'Your opponent won this round.';
        xpMessage.style.display = 'none';
    }
    
    modal.style.display = 'flex';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('oppName').innerText = '<?php echo htmlspecialchars($opp_name); ?>';
    document.getElementById('myScore').innerText = '0';
    document.getElementById('oppScore').innerText = '0';
    
    // Load first question
    setTimeout(loadQuestion, 500);
    
    // Start polling for opponent updates every 1 second
    updateInterval = setInterval(updateOpponentProgress, 1000);
    
    // Update opponent progress immediately
    updateOpponentProgress();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    clearInterval(updateInterval);
    if (gameActive) {
        fetch('pvp_match_api.php?action=end_match', {
            method: 'POST',
            keepalive: true,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ match_id: MATCH_ID })
        });
    }
});
</script>

</body>
</html>
