/**
 * storylogic.js — Story Mode v2
 * RPG pixel world with dialogue, code challenges, and stage progression.
 */

const STAGES = [
  {
    chapter   : "CHAPTER 1: THE BREACH",
    sector    : "SECTOR 01 — MAINFRAME CORE",
    lang      : "HTML", stage: "⭐ STAGE 1",
    objective : 'Type the correct HTML heading: <h1>Hello, World!</h1>',
    hint      : '<h1>Hello, World!</h1>',
    answer    : '<h1>hello, world!</h1>',
    xp        : 20, logId: "se1",
    npcLine   : "You dare enter my mainframe?",
    sysLine   : "Write the HTML heading tag to break through Sector 01.",
    successMsg: "HTML firewall neutralised!",
  },
  {
    chapter   : "CHAPTER 1: THE BREACH",
    sector    : "SECTOR 02 — STYLE ENGINE",
    lang      : "CSS", stage: "⭐ STAGE 2",
    objective : 'Write a CSS rule: body { background: black; }',
    hint      : 'body { background: black; }',
    answer    : 'body{background:black;}',
    xp        : 25, logId: "se2",
    npcLine   : "The style engine is mine! You can't fix it!",
    sysLine   : "Write the correct CSS rule to restore the style engine.",
    successMsg: "CSS engine restored!",
  },
  {
    chapter   : "CHAPTER 2: COUNTER-STRIKE",
    sector    : "SECTOR 03 — LOGIC CORE",
    lang      : "JavaScript", stage: "⭐⭐ STAGE 3",
    objective : 'Complete the function body: return a + b;',
    hint      : 'return a + b;',
    answer    : 'return a + b;',
    xp        : 30, logId: "se3",
    npcLine   : "The logic core is pure chaos. No one can fix it!",
    sysLine   : "Write the correct JS expression to restore logic.",
    successMsg: "Logic core online!",
  },
  {
    chapter   : "CHAPTER 3: FINAL BATTLE",
    sector    : "FINAL — CYPH-3R CORE",
    lang      : "PHP", stage: "⭐⭐⭐ FINAL",
    objective : 'Echo the restore command: echo "System Restored";',
    hint      : 'echo "System Restored";',
    answer    : 'echo "system restored";',
    xp        : 50, logId: "se4",
    npcLine   : "N-No... you've reached my core! IMPOSSIBLE!",
    sysLine   : "Execute the final restore command to eject CYPH-3R.",
    successMsg: "MAINFRAME RESTORED! CYPH-3R defeated!",
  },
];

// ── State ───────────────────────────────────────────────────────────────────
let stageIdx  = 0;
let dlgIdx    = 0;
let isTyping  = false;
let dlgQueue  = [];
let totalXP   = 0;
let storyOpen = true;

// ── Elements ─────────────────────────────────────────────────────────────────
const scene       = document.getElementById("worldScene");
const dlgBox      = document.getElementById("rpgDialogue");
const dlgSpeaker  = document.getElementById("rpgSpeaker");
const dlgText     = document.getElementById("rpgText");
const walkPrompt  = document.getElementById("walkPrompt");
const worldActions= document.getElementById("worldActions");
const langBadge   = document.getElementById("langBadge");
const stageBadge  = document.getElementById("stageBadge");
const objText     = document.getElementById("objText");
const sbChapter   = document.getElementById("sbChapter");
const worldStage  = document.getElementById("worldStage");
const xpCounter   = document.getElementById("xpCounter");
const cpOutput    = document.getElementById("cpOutput");
const cpOutContent= document.getElementById("cpOutContent");
const codeEditor  = document.getElementById("codeEditor");

// ── Boot ─────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  // Initialize loading screen
  initializeLoadingScreen();
  
  // Load first stage but keep hidden until loading screen is dismissed
  loadStage(0);
  scene.addEventListener("click", onSceneClick);
});

// ── Loading Screen Handler ───────────────────────────────────────────────────
function initializeLoadingScreen() {
  const loadingScreen = document.getElementById("adventureLoadingScreen");
  const startBtn = document.getElementById("startAdventureBtn");
  const progressBar = document.getElementById("progressBar");
  const progressText = document.getElementById("progressText");
  
  if (!loadingScreen) return;
  
  // Animate progress bar over time
  let progress = 0;
  const progressInterval = setInterval(() => {
    progress += Math.random() * 30;
    if (progress > 95) progress = 95;
    progressBar.style.width = progress + "%";
    progressText.textContent = `Loading adventure... ${Math.floor(progress)}%`;
  }, 400);
  
  // Handle start button click
  startBtn.addEventListener("click", () => {
    clearInterval(progressInterval);
    progressBar.style.width = "100%";
    progressText.textContent = "Loading adventure... 100%";
    
    // Hide loading screen with fade out
    setTimeout(() => {
      loadingScreen.classList.add("hidden");
      
      // Remove loading screen from DOM after animation completes
      setTimeout(() => {
        loadingScreen.style.display = "none";
      }, 800);
    }, 300);
  });
}

// ── Load stage ───────────────────────────────────────────────────────────────
function loadStage(idx) {
  stageIdx = idx;
  dlgIdx   = 0;

  const s = STAGES[idx];
  langBadge.textContent = s.lang;
  stageBadge.textContent= s.stage;
  objText.textContent   = s.objective;
  sbChapter.textContent = s.chapter;
  worldStage.textContent= s.sector;

  // Activate mission log entry
  document.querySelectorAll(".story-entry").forEach(el => el.classList.remove("active-story"));
  const logEl = document.getElementById(s.logId);
  if (logEl) {
    logEl.style.opacity = "1";
    logEl.classList.add("active-story");
    logEl.querySelector(".se-dot").textContent = "🔴";
  }

  // Reset editor & output
  codeEditor.value = "";
  hideOutput();
  resetHintBtn();

  // Build dialogue queue: NPC line → system briefing
  dlgQueue = [
    { speaker: "CYPH-3R", cls: "spk-npc",    text: s.npcLine },
    { speaker: "SYSTEM",  cls: "spk-system",  text: "[BRIEFING] " + s.sysLine },
    { speaker: "SYSTEM",  cls: "spk-system",  text: "[ACTION] Use CODE to open the terminal. Choose your action below." },
  ];

  worldActions.style.display = "none";
  walkPrompt.style.display   = "block";
  dlgBox.style.display       = "none";

  // Auto-start first dialogue after brief pause
  setTimeout(() => showDialogue(0), 600);
}

// ── Scene click handler ───────────────────────────────────────────────────────
function onSceneClick() {
  if (dlgBox.style.display === "none" && worldActions.style.display === "none") return;

  if (isTyping) {
    // Skip typewriter — show full text
    isTyping = false;
    dlgText.textContent = dlgQueue[dlgIdx].text;
    return;
  }

  if (dlgBox.style.display !== "none") {
    // Advance to next dialogue
    if (dlgIdx + 1 < dlgQueue.length) {
      showDialogue(dlgIdx + 1);
    } else {
      // End of dialogue — show action menu
      dlgBox.style.display       = "none";
      walkPrompt.style.display   = "none";
      worldActions.style.display = "grid";
    }
  }
}

// ── Show dialogue ─────────────────────────────────────────────────────────────
function showDialogue(idx) {
  dlgIdx = idx;
  const d = dlgQueue[idx];

  dlgSpeaker.textContent = d.speaker;
  dlgSpeaker.className   = "rpg-speaker " + d.cls;
  dlgBox.style.display   = "block";
  walkPrompt.style.display= "block";

  // NPC talking animation
  if (d.cls === "spk-npc") {
    const npc = document.getElementById("npc1");
    npc.classList.add("npc-talk");
    setTimeout(() => npc.classList.remove("npc-talk"), 1000);
  }

  typewrite(d.text);
}

// ── Typewriter ─────────────────────────────────────────────────────────────────
function typewrite(text) {
  dlgText.textContent = "";
  isTyping = true;
  let i = 0;
  const tick = setInterval(() => {
    if (!isTyping) { clearInterval(tick); dlgText.textContent = text; return; }
    dlgText.textContent += text[i++];
    if (i >= text.length) { clearInterval(tick); isTyping = false; }
  }, 25);
}

// ── Action buttons ─────────────────────────────────────────────────────────────
function doAction(action) {
  worldActions.style.display = "none";

  if (action === "code") {
    flashCodePanel();
    queueMsg("CYPH-3R", "spk-npc", "So you choose to fight with code? I look forward to your failure...");
  } else if (action === "study") {
    document.getElementById("cpObjective").classList.add("obj-pulse");
    setTimeout(() => document.getElementById("cpObjective").classList.remove("obj-pulse"), 1500);
    queueMsg("SYSTEM", "spk-system", "Review the OBJECTIVE on the right panel, then choose CODE to proceed.");
  } else if (action === "hint") {
    showHint();
    queueMsg("SYSTEM", "spk-system", "[HINT UNLOCKED] The hint is now shown on the HINT button.");
  } else if (action === "wait") {
    queueMsg("CYPH-3R", "spk-npc", "HA! Stalling only delays your defeat. The mainframe stays corrupted!");
    setTimeout(() => { worldActions.style.display = "grid"; }, 2500);
  }
}

function queueMsg(speaker, cls, text) {
  dlgQueue = [{ speaker, cls, text }];
  dlgIdx   = 0;
  showDialogue(0);
  walkPrompt.style.display = "block";
}

function flashCodePanel() {
  const cp = document.querySelector(".code-panel");
  cp.style.boxShadow = "inset 0 0 40px rgba(74,222,128,0.3)";
  setTimeout(() => { cp.style.boxShadow = ""; }, 700);
  codeEditor.focus();
}

// ── Run code ───────────────────────────────────────────────────────────────────
function runCode() {
  const s      = STAGES[stageIdx];
  const raw    = codeEditor.value.trim();
  const given  = raw.toLowerCase().replace(/\s+/g, "");
  const expect = s.answer.toLowerCase().replace(/\s+/g, "");

  cpOutput.style.display = "flex";
  cpOutContent.classList.remove("cp-out-ok", "cp-out-err");

  if (given === expect) {
    cpOutContent.textContent = "✔ Compiled! Output: " + raw;
    cpOutContent.classList.add("cp-out-ok");

    totalXP += s.xp;
    xpCounter.textContent = totalXP;

    // Hit NPC
    const npc1 = document.getElementById("npc1");
    npc1.classList.add("char-hit");
    setTimeout(() => npc1.classList.remove("char-hit"), 500);

    // Player attack animation
    const player = document.getElementById("playerChar");
    player.classList.add("char-attack");
    setTimeout(() => player.classList.remove("char-attack"), 500);

    // Show success dialogue then overlay
    setTimeout(() => {
      queueMsg("CYPH-3R", "spk-npc", "AGH! That... that actually worked!");
      walkPrompt.style.display = "block";
      setTimeout(() => showVictory(s), 2000);
    }, 500);

  } else {
    cpOutContent.textContent = "✘ Error. Expected: " + s.answer;
    cpOutContent.classList.add("cp-out-err");

    const player = document.getElementById("playerChar");
    player.classList.add("char-shake");
    setTimeout(() => player.classList.remove("char-shake"), 500);

    setTimeout(() => {
      queueMsg("CYPH-3R", "spk-npc", "Heh... your code is flawed. Try again, hacker!");
      walkPrompt.style.display = "block";
      setTimeout(() => { worldActions.style.display = "grid"; }, 2500);
    }, 400);
  }
}

// ── Show hint ──────────────────────────────────────────────────────────────────
function showHint() {
  const btn = document.getElementById("hintBtn");
  btn.textContent = "💡 " + STAGES[stageIdx].hint;
  btn.classList.add("hint-shown");
}

function resetHintBtn() {
  const btn = document.getElementById("hintBtn");
  btn.textContent = "💡 HINT";
  btn.classList.remove("hint-shown");
}

// ── Clear ──────────────────────────────────────────────────────────────────────
function clearCode() {
  codeEditor.value = "";
  hideOutput();
  codeEditor.focus();
}

function hideOutput() {
  cpOutput.style.display = "none";
  cpOutContent.textContent = "";
  cpOutContent.classList.remove("cp-out-ok", "cp-out-err");
}

// ── Victory ────────────────────────────────────────────────────────────────────
function showVictory(stage) {
  const isFinal = stageIdx >= STAGES.length - 1;
  document.getElementById("vIcon").textContent  = isFinal ? "🏆" : "✅";
  document.getElementById("vTitle").textContent = isFinal ? "MAINFRAME RESTORED!" : "SECTOR CLEARED!";
  document.getElementById("vMsg").textContent   = stage.successMsg;
  document.getElementById("vXP").textContent    = "+" + stage.xp + " XP Earned";
  const nxtBtn = document.querySelector(".v-btns .cp-btn");
  if (nxtBtn) nxtBtn.textContent = isFinal ? "🏠 RETURN TO BASE" : "NEXT SECTOR ▶";
  document.getElementById("vOverlay").style.display = "flex";

  fetch("award_xp.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "xp=" + stage.xp,
  }).catch(e => console.warn("XP award failed", e));
}

function nextStage() {
  document.getElementById("vOverlay").style.display = "none";
  if (stageIdx + 1 >= STAGES.length) {
    window.location.href = "dashboard.php";
  } else {
    loadStage(stageIdx + 1);
  }
}

// ── Story bar toggle ───────────────────────────────────────────────────────────
function toggleStoryBar() {
  const body = document.getElementById("storyBarBody");
  const btn  = document.querySelector(".sb-toggle");
  storyOpen  = !storyOpen;
  body.style.display = storyOpen ? "flex" : "none";
  btn.textContent    = storyOpen ? "▲" : "▼";
}

// ── Gutter line numbers ────────────────────────────────────────────────────────
codeEditor && codeEditor.addEventListener("input", () => {
  const lines  = codeEditor.value.split("\n").length;
  const gutter = document.getElementById("cpGutter");
  gutter.innerHTML = Array.from(
    { length: Math.max(10, lines) }, (_, i) => `<span>${i + 1}</span>`
  ).join("");
});

// ── NPC/char animations (CSS class helpers) ────────────────────────────────────
// Add to CSS via injected style tag
const style = document.createElement("style");
style.textContent = `
  .npc-talk   { animation: npcTalkAnim 0.12s 6 alternate !important; }
  @keyframes npcTalkAnim { from{transform:translateY(0)} to{transform:translateY(-5px)} }
  .char-hit   { animation: charHitAnim 0.4s ease !important; }
  @keyframes charHitAnim {
    0%  { filter: brightness(5); transform:translateX(0); }
    50% { transform:translateX(-14px); }
    100%{ filter:brightness(1); transform:translateX(0); }
  }
  .char-attack { animation: charAttackAnim 0.4s ease-out !important; }
  @keyframes charAttackAnim {
    30% { transform:translateX(-40px) scale(1.1); }
    60% { transform:translateX(0); }
  }
  .char-shake { animation: charShakeAnim 0.4s !important; }
  @keyframes charShakeAnim {
    20%{transform:translateX(-8px)} 40%{transform:translateX(8px)}
    60%{transform:translateX(-6px)} 80%{transform:translateX(6px)} 100%{transform:translateX(0)}
  }
  .obj-pulse { background: rgba(74,222,128,0.25) !important; transition: background 0.3s; }
`;
document.head.appendChild(style);
