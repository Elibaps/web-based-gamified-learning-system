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
let terminalOpen = false;

// ── Elements ─────────────────────────────────────────────────────────────────
const scene         = document.getElementById("worldScene");
const storyGrid     = document.querySelector(".story-grid");
const codePanel     = document.querySelector(".code-panel");
const terminalToggle= document.getElementById("terminalToggle");
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
const rewardTitle = document.getElementById("rewardQuestTitle");
const rewardDesc  = document.getElementById("rewardQuestDetails");
const rewardXP    = document.getElementById("rewardXP");
const rewardUnlock= document.getElementById("rewardUnlock");
const xpCounter   = document.getElementById("xpCounter");
const cpOutput    = document.getElementById("cpOutput");
const cpOutContent= document.getElementById("cpOutContent");
const codeEditor  = document.getElementById("codeEditor");

// ── Boot ─────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  // Initialize loading screen
  initializeLoadingScreen();
  
  // Keep the terminal hidden until the player chooses CODE
  collapseCodePanel();

  // Load first stage but keep hidden until loading screen is dismissed
  loadStage(0);
  scene.addEventListener("click", onSceneClick);
});

function collapseCodePanel() {
  terminalOpen = false;
  if (storyGrid) storyGrid.classList.add("terminal-collapsed");
  if (codePanel) codePanel.classList.add("hidden");
  if (terminalToggle) terminalToggle.textContent = "💻 OPEN TERMINAL";
}

function revealCodePanel() {
  if (!terminalOpen) {
    terminalOpen = true;
    if (storyGrid) storyGrid.classList.remove("terminal-collapsed");
    if (codePanel) codePanel.classList.remove("hidden");
    if (terminalToggle) terminalToggle.textContent = "💻 TERMINAL READY";
    setTimeout(() => codeEditor.focus(), 250);
  }
}

function revealCodePanel() {
  if (!terminalOpen) {
    terminalOpen = true;
    if (storyGrid) storyGrid.classList.remove("terminal-collapsed");
    if (codePanel) codePanel.classList.remove("hidden");
    if (terminalToggle) terminalToggle.textContent = "💻 TERMINAL READY";
    setTimeout(() => codeEditor.focus(), 250);
  }
}

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
  collapseCodePanel();

  const s = STAGES[idx];
  langBadge.textContent = s.lang;
  stageBadge.textContent= s.stage;
  objText.textContent   = s.objective;
  sbChapter.textContent = s.chapter;
  worldStage.textContent= s.sector;

  if (stageIdx < STAGES.length - 1) {
    const next = STAGES[stageIdx + 1];
    rewardTitle.textContent = `Current mission: ${s.sector}`;
    rewardDesc.textContent  = `Solve this challenge to earn ${s.xp} XP and unlock ${next.lang}.`;
    rewardXP.textContent    = `+${s.xp} XP`;
    rewardUnlock.textContent= `Next unlock: ${next.sector}`;
  } else {
    rewardTitle.textContent = `FINAL MISSION: ${s.sector}`;
    rewardDesc.textContent  = `Defeat CYPH-3R and finish the story path with a major XP reward.`;
    rewardXP.textContent    = `+${s.xp} XP`;
    rewardUnlock.textContent= `Unlocks: Victory and endgame rewards`;
  }

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
    worldActions.style.display = "none";
    revealCodePanel();
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
  const s = STAGES[stageIdx];
  
  // Get code from whichever editor is currently active
  const terminalEditor = document.getElementById("terminalEditor");
  const terminalOverlay = document.getElementById("terminalOverlay");
  const activeEditor = (terminalOverlay.style.display !== "none") ? terminalEditor : codeEditor;
  
  const raw = activeEditor.value.trim();
  const given = raw.toLowerCase().replace(/\s+/g, "");
  const expect = s.answer.toLowerCase().replace(/\s+/g, "");
  
  // Show output in appropriate location
  if (terminalOverlay.style.display !== "none") {
    updateTerminalOutput("", false);
  } else {
    cpOutput.style.display = "flex";
    cpOutContent.classList.remove("cp-out-ok", "cp-out-err");
  }

  if (given === expect) {
    const outputText = "✔ Compiled! Output: " + raw;
    if (terminalOverlay.style.display !== "none") {
      updateTerminalOutput(outputText, false);
    } else {
      cpOutContent.textContent = outputText;
      cpOutContent.classList.add("cp-out-ok");
    }

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
    const outputText = "✘ Error. Expected: " + s.answer;
    if (terminalOverlay.style.display !== "none") {
      updateTerminalOutput(outputText, true);
    } else {
      cpOutContent.textContent = outputText;
      cpOutContent.classList.add("cp-out-err");
    }

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
  const terminalBtn = document.getElementById("terminalHintBtn");
  const hintText = "💡 " + STAGES[stageIdx].hint;
  
  if (btn) {
    btn.textContent = hintText;
    btn.classList.add("hint-shown");
  }
  if (terminalBtn) {
    terminalBtn.textContent = hintText;
    terminalBtn.classList.add("hint-shown");
  }
}

function resetHintBtn() {
  const btn = document.getElementById("hintBtn");
  const terminalBtn = document.getElementById("terminalHintBtn");
  
  if (btn) {
    btn.textContent = "💡 HINT";
    btn.classList.remove("hint-shown");
  }
  if (terminalBtn) {
    terminalBtn.textContent = "💡 HINT";
    terminalBtn.classList.remove("hint-shown");
  }
}

// ── Clear ──────────────────────────────────────────────────────────────────────
function clearCode() {
  const terminalEditor = document.getElementById("terminalEditor");
  const terminalOverlay = document.getElementById("terminalOverlay");
  
  if (terminalOverlay.style.display !== "none") {
    terminalEditor.value = "";
    hideTerminalOutput();
    terminalEditor.focus();
  } else {
    codeEditor.value = "";
    hideOutput();
    codeEditor.focus();
  }
  
  // Also clear the other editor
  codeEditor.value = "";
  terminalEditor.value = "";
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
    body: "xp=" + stage.xp +
          "&course=" + encodeURIComponent(stage.lang) +
          "&lesson_slug=practice",
function nextStage() {
  document.getElementById("vOverlay").style.display = "none";
  if (stageIdx + 1 >= STAGES.length) {
    window.location.href = "dashboard.php";
  } else {
    loadStage(stageIdx + 1);
  }
}

// ── Terminal Overlay Functions ──────────────────────────────────────────────────
/**
 * Show the battle terminal overlay (50% screen height at bottom)
 */
function showTerminalOverlay() {
  const terminalOverlay = document.getElementById("terminalOverlay");
  const storyGrid = document.getElementById("storyGrid");
  const terminalEditor = document.getElementById("terminalEditor");
  
  // Show overlay and adjust grid layout
  terminalOverlay.style.display = "flex";
  terminalOverlay.classList.remove("hidden");
  storyGrid.classList.add("full-width");
  
  // Sync the current code to terminal editor
  terminalEditor.value = codeEditor.value;
  
  // Focus on terminal editor and make draggable
  terminalEditor.focus();
  makeDraggable(document.getElementById("terminalDragHandle"), terminalOverlay);
}

/**
 * Close the battle terminal overlay
 */
function closeTerminalOverlay() {
  const terminalOverlay = document.getElementById("terminalOverlay");
  const storyGrid = document.getElementById("storyGrid");
  
  // Hide overlay and restore grid layout
  terminalOverlay.classList.add("hidden");
  setTimeout(() => {
    terminalOverlay.style.display = "none";
    storyGrid.classList.remove("full-width");
  }, 150);
}

/**
 * Sync code between main editor and terminal editor
 */
function syncTerminalCode() {
  const terminalEditor = document.getElementById("terminalEditor");
  const mainEditor = document.getElementById("codeEditor");
  
  // Mirror code changes both ways
  if (document.activeElement === terminalEditor) {
    mainEditor.value = terminalEditor.value;
  } else if (document.activeElement === mainEditor) {
    terminalEditor.value = mainEditor.value;
  }
}

/**
 * Make element draggable by its handle
 */
function makeDraggable(handle, element) {
  let offsetX = 0;
  let offsetY = 0;
  let isDown = false;
  let currentY = 0;
  
  handle.addEventListener("mousedown", (e) => {
    isDown = true;
    offsetY = e.clientY - element.getBoundingClientRect().top;
  });
  
  document.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    
    const viewportHeight = window.innerHeight;
    const maxY = viewportHeight - (viewportHeight * 0.3); // Don't move above 30% from top
    const minY = viewportHeight * 0.2; // Don't move below 20% from top
    
    currentY = Math.max(minY, Math.min(maxY, e.clientY - offsetY));
    element.style.bottom = "auto";
    element.style.top = currentY + "px";
  });
  
  document.addEventListener("mouseup", () => {
    isDown = false;
  });
}

/**
 * Update terminal output display
 */
function updateTerminalOutput(content, isError) {
  const terminalOutput = document.getElementById("terminalOutput");
  const terminalOutContent = document.getElementById("terminalOutContent");
  
  terminalOutput.style.display = "flex";
  terminalOutContent.textContent = content;
  
  if (isError) {
    terminalOutContent.classList.add("err");
    terminalOutContent.classList.remove("ok");
  } else {
    terminalOutContent.classList.add("ok");
    terminalOutContent.classList.remove("err");
  }
}

/**
 * Hide terminal output
 */
function hideTerminalOutput() {
  const terminalOutput = document.getElementById("terminalOutput");
  terminalOutput.style.display = "none";
}

// Add event listeners for code editor synchronization
document.addEventListener("DOMContentLoaded", () => {
  const codeEditorElem = document.getElementById("codeEditor");
  const terminalEditorElem = document.getElementById("terminalEditor");
  
  if (codeEditorElem) {
    codeEditorElem.addEventListener("input", syncTerminalCode);
  }
  if (terminalEditorElem) {
    terminalEditorElem.addEventListener("input", syncTerminalCode);
  }
});

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
