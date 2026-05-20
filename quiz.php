<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$topic = $_GET['topic'] ?? 'HTML';
$safeTopic = htmlspecialchars($topic, ENT_QUOTES, 'UTF-8');

$pageTitle = $safeTopic . ' Quiz — CodeNest';
include 'includes/head.php';
?>
<body class="battle-page">
<?php include 'includes/navbar.php'; ?>

<div class="battle-container" style="max-width: 600px; text-align: center;">
    <h1 style="color: var(--primary-color); text-shadow: 0 0 5px rgba(74, 222, 128, 0.6); font-size: 2.5rem; margin-bottom: 20px;">Quiz: <?php echo $safeTopic; ?></h1>
    
    <div class="battle-box" style="width: 100%; text-align: left; margin-bottom: 20px;">
        <h3 id="questionText" style="color: var(--primary-color); margin-bottom: 20px; font-size: 1.5rem;">Loading questions...</h3>
        
        <div id="optionsContainer" style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Options will be generated here -->
        </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; border: 4px solid var(--primary-color); padding: 10px; background: var(--card-bg);">
        <span style="color: inherit; font-weight: bold;">Score: <span id="scoreDisplay">0</span></span>
        <span style="color: inherit; font-weight: bold;">Question: <span id="questionCount">1</span>/10</span>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    const topic = "<?php echo addslashes($topic); ?>";
    let questions = [];
    
    try {
        const res = await fetch(`get_questions.php?topic=${encodeURIComponent(topic)}`);
        if (!res.ok) throw new Error("Network response was not ok");
        questions = await res.json();
    } catch (err) {
        console.error(err);
        questions = [{ question_text: "What does HTML stand for?", answer: "hypertext markup language" }];
    }
    
    if (!questions.length) {
        document.getElementById("questionText").innerText = "No questions available.";
        return;
    }
    
    // Collect all answers to use as distractors
    const allAnswers = questions.map(q => q.answer);
    
    let currentIdx = 0;
    let score = 0;
    
    function loadQuestion() {
        if (currentIdx >= questions.length) {
            endQuiz();
            return;
        }
        
        document.getElementById("questionCount").innerText = (currentIdx + 1);
        const q = questions[currentIdx];
        document.getElementById("questionText").innerText = q.question_text;
        
        // Generate options (1 correct, 3 wrong)
        let options = [q.answer];
        let availableWrong = allAnswers.filter(a => a.toLowerCase() !== q.answer.toLowerCase());
        
        // Shuffle available wrong answers
        availableWrong.sort(() => Math.random() - 0.5);
        for(let i=0; i<3 && i<availableWrong.length; i++) {
            options.push(availableWrong[i]);
        }
        
        // If not enough unique wrong answers, add some dummy ones
        if (options.length < 4) {
            const dummies = ["Option A", "Option B", "Option C"];
            for (let d of dummies) {
                if (options.length < 4) options.push(d);
            }
        }
        
        options.sort(() => Math.random() - 0.5);
        
        const container = document.getElementById("optionsContainer");
        container.innerHTML = "";
        
        options.forEach(opt => {
            let btn = document.createElement("button");
            btn.className = "btn";
            btn.style.textAlign = "left";
            btn.style.textTransform = "none";
            btn.innerText = opt;
            btn.onclick = () => selectOption(opt, q.answer);
            container.appendChild(btn);
        });
    }
    
    function selectOption(selected, correct) {
        if (selected.toLowerCase() === correct.toLowerCase()) {
            score += 10;
            document.getElementById("scoreDisplay").innerText = score;
        }
        currentIdx++;
        loadQuestion();
    }
    
    async function endQuiz() {
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
        alert(`Quiz finished! You earned ${score} XP.`);
        window.location.href = "dashboard.php";
    }
    
    loadQuestion();
});
</script>

<?php include 'includes/footer.php'; ?>
