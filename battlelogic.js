/**
 * battlelogic.js
 * Enhanced battle logic with damage popups, feedback bar, XP counter,
 * timer color changes, animated overlay, and particle background.
 */
document.addEventListener("DOMContentLoaded", async function () {

  const topic = (typeof BATTLE_TOPIC !== "undefined") ? BATTLE_TOPIC : "HTML";

  // ── Spawn background particles ──────────────────────────────────────────
  (function spawnParticles() {
    const container = document.getElementById("battleParticles");
    if (!container) return;
    for (let i = 0; i < 30; i++) {
      const p = document.createElement("div");
      p.className = "particle";
      p.style.left     = Math.random() * 100 + "vw";
      p.style.top      = Math.random() * 100 + "vh";
      p.style.width    = (Math.random() * 3 + 2) + "px";
      p.style.height   = p.style.width;
      p.style.animationDuration  = (Math.random() * 8 + 4) + "s";
      p.style.animationDelay     = (Math.random() * 6) + "s";
      p.style.opacity  = Math.random() * 0.2;
      container.appendChild(p);
    }
  })();

  // ── Fetch questions from server ─────────────────────────────────────────
  let questions = [];
  try {
    const res = await fetch(`get_questions.php?topic=${encodeURIComponent(topic)}`);
    if (!res.ok) throw new Error(`Server responded with ${res.status}`);
    questions = await res.json();
  } catch (err) {
    console.error("Could not load questions:", err);
    questions = [{ question_text: "What does HTML stand for?", answer: "hypertext markup language" }];
  }

  // Update question total
  const qTotalEl = document.getElementById("qTotal");
  if (qTotalEl) qTotalEl.textContent = questions.length;

  if (!questions.length) {
    document.getElementById("question").innerText = "No questions available for this topic.";
    document.getElementById("submitBtn").disabled = true;
    return;
  }

  // ── State ───────────────────────────────────────────────────────────────
  let bossHP    = 100;
  let playerHP  = 100;
  let timeLeft  = 10;
  let timer     = null;
  let current   = 0;
  let totalXP   = 0;
  let isAnswering = true;

  updateHP();
  startTurn();

  // ── Turn management ─────────────────────────────────────────────────────
  function startTurn() {
    if (current >= questions.length) return endBattle(true);

    timeLeft    = 10;
    isAnswering = true;
    clearFeedback();
    updateTimerBar();
    updateQuestionCounter();
    showQuestion();

    timer = setInterval(() => {
      timeLeft -= 0.1;
      updateTimerBar();

      if (timeLeft <= 0) {
        clearInterval(timer);
        isAnswering = false;
        showFeedback("⏱ Time's up! -10 HP", "timeout");
        playerDamage(10);
        setTimeout(() => {
          current++;
          // Check if player died from timeout damage before next turn
          if (playerHP <= 0) return endBattle(false);
          startTurn();
        }, 1200);
      }
    }, 100);
  }

  function showQuestion() {
    const el = document.getElementById("question");
    el.style.opacity = "0";
    el.innerText = questions[current].question_text;
    setTimeout(() => { el.style.transition = "opacity 0.3s"; el.style.opacity = "1"; }, 50);

    const input = document.getElementById("answerInput");
    input.value = "";
    input.focus();
  }

  function updateTimerBar() {
    const pct = Math.max(0, (timeLeft / 10) * 100);
    const fill = document.getElementById("timerFill");
    fill.style.width = pct + "%";

    // Color change based on urgency
    fill.classList.remove("warning", "danger");
    if (pct <= 30) fill.classList.add("danger");
    else if (pct <= 60) fill.classList.add("warning");

    const timeText = document.getElementById("timerText");
    if (timeText) timeText.textContent = Math.ceil(timeLeft) + "s";
  }

  function updateQuestionCounter() {
    const el = document.getElementById("qNum");
    if (el) el.textContent = current + 1;
  }

  function updateXP() {
    const el = document.getElementById("xpEarned");
    if (el) el.textContent = totalXP;
  }

  // ── Answer submission ───────────────────────────────────────────────────
  function submitAnswer() {
    if (!isAnswering) return;
    isAnswering = false;
    clearInterval(timer);

    const userAnswer = document.getElementById("answerInput").value.toLowerCase().trim();
    const correct    = questions[current].answer.toLowerCase().trim();

    if (userAnswer === correct) {
      // Damage scales with time left: faster answer = more damage (max 25, min 5)
      const damage = Math.max(5, Math.round((timeLeft / 10) * 25));
      bossHP   = Math.max(0, bossHP - damage);
      totalXP += damage;

      showFeedback(`✔ Correct! Boss takes ${damage} damage!`, "correct");
      showDamagePopup("bossDmgPopup", `-${damage}`, "#ff4444");
      flashHPBar("boss");

      document.querySelector(".player").classList.add("attack");
      setTimeout(() => document.querySelector(".player").classList.remove("attack"), 400);
      document.querySelector(".enemy").classList.add("hit");
      setTimeout(() => document.querySelector(".enemy").classList.remove("hit"), 400);

      // Update boss bar directly (no double call)
      document.getElementById("bossHP").style.width = bossHP + "%";
      const bossText = document.getElementById("bossHPText");
      if (bossText) bossText.textContent = bossHP;

      updateXP();
    } else {
      const dmg = 15;
      playerHP = Math.max(0, playerHP - dmg);
      showFeedback(`✘ Wrong! The answer was: "${questions[current].answer}"`, "wrong");
      showDamagePopup("playerDmgPopup", `-${dmg}`, "#4ade80");
      flashHPBar("player");

      document.querySelector(".player").classList.add("hit");
      setTimeout(() => document.querySelector(".player").classList.remove("hit"), 400);

      // Update player bar directly (no double call)
      document.getElementById("playerHP").style.width = playerHP + "%";
      const playerText = document.getElementById("playerHPText");
      if (playerText) playerText.textContent = playerHP;
    }

    current++;

    setTimeout(() => {
      if (bossHP   <= 0) return endBattle(true);
      if (playerHP <= 0) return endBattle(false);
      startTurn();
    }, 1200);
  }

  // ── Damage helpers ──────────────────────────────────────────────────────
  // Used only for timeout penalty — updates player HP bar directly
  function playerDamage(dmg) {
    playerHP = Math.max(0, playerHP - dmg);
    document.querySelector(".player").classList.add("hit");
    setTimeout(() => document.querySelector(".player").classList.remove("hit"), 400);

    document.getElementById("playerHP").style.width = playerHP + "%";
    const playerText = document.getElementById("playerHPText");
    if (playerText) playerText.textContent = playerHP;
    flashHPBar("player");

    showDamagePopup("playerDmgPopup", "-10", "#fbbf24");
  }

  // Flash only the bar that took damage
  function flashHPBar(side) {
    const bar = document.querySelector(side === "boss" ? ".boss-hp-bar" : ".player-hp-bar");
    if (!bar) return;
    bar.classList.add("hp-flash");
    setTimeout(() => bar.classList.remove("hp-flash"), 600);
  }

  // ── Damage popup ────────────────────────────────────────────────────────
  function showDamagePopup(id, text, color) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = text;
    el.style.color = color;
    el.classList.remove("show");
    void el.offsetWidth; // reflow
    el.classList.add("show");
    setTimeout(() => el.classList.remove("show"), 900);
  }

  // ── Feedback bar ────────────────────────────────────────────────────────
  function showFeedback(msg, type) {
    const bar = document.getElementById("feedbackBar");
    if (!bar) return;
    bar.textContent = msg;
    bar.className = "feedback-bar feedback-" + type;
  }
  function clearFeedback() {
    const bar = document.getElementById("feedbackBar");
    if (bar) { bar.textContent = ""; bar.className = "feedback-bar"; }
  }

  // ── End battle ──────────────────────────────────────────────────────────
  async function endBattle(won) {
    clearInterval(timer);

    if (won && totalXP > 0) {
      try {
        const resp = await fetch("award_xp.php", {
          method:  "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body:    `xp=${totalXP}`,
        });
        const data = await resp.json();
        if (data.leveledUp) {
          setTimeout(() => showOverlay(true, data.newLevel), 600);
          return;
        }
      } catch (e) {
        console.error("XP award failed:", e);
      }
    }

    showOverlay(won, null);
  }

  function showOverlay(won, newLevel) {
    const overlay = document.getElementById("battleOverlay");
    const icon    = document.getElementById("overlayIcon");
    const title   = document.getElementById("overlayTitle");
    const msg     = document.getElementById("overlayMsg");
    const xpEl    = document.getElementById("overlayXP");

    if (!overlay) {
      alert(won ? `🏆 Victory! +${totalXP} XP` : "💀 Defeated!");
      window.location.href = "dashboard.php";
      return;
    }

    icon.textContent  = won ? "🏆" : "💀";
    title.textContent = won ? "VICTORY!" : "DEFEATED";
    title.style.color = won ? "var(--primary-color)" : "#ff4444";

    if (won) {
      msg.textContent  = newLevel ? `🎉 LEVEL UP! You are now Level ${newLevel}!` : "You defeated Nisha!";
      xpEl.textContent = `+ ${totalXP} XP Earned`;
    } else {
      msg.textContent  = "Better luck next time, coder!";
      xpEl.textContent = "";
    }

    overlay.style.display = "flex";
  }

  // ── Event listeners ─────────────────────────────────────────────────────
  document.getElementById("submitBtn").addEventListener("click", submitAnswer);
  document.getElementById("answerInput").addEventListener("keypress", function (e) {
    if (e.key === "Enter") submitAnswer();
  });
});