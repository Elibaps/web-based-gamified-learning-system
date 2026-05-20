/**
 * storylogic.js
 * Drives the Pokémon-style story mode:
 * - Typewriter dialogue system
 * - Code challenge stages
 * - NPC HP bar depletes on correct answers
 * - Victory overlay + XP award
 */

// ── Stage definitions ────────────────────────────────────────────────────────
const STAGES = [
  {
    chapter   : "CHAPTER 1: THE BREACH",
    sector    : "SECTOR 01 — MAINFRAME CORE",
    lang      : "HTML",
    difficulty: "⭐ STAGE 1",
    objective : 'Output a valid HTML heading: <h1>Hello, World!</h1>',
    hint      : 'Type exactly: <h1>Hello, World!</h1>',
    answer    : '<h1>hello, world!</h1>',
    xp        : 20,
    dialogues : [
      { speaker:"CYPH-3R", text:"Intruder detected... You dare enter my mainframe?" },
      { speaker:"CYPH-3R", text:"I have corrupted every sector. You cannot fix what you don't understand." },
      { speaker:"SYSTEM",  text:"[BRIEFING] Sector 01 firewall is active. Destroy it by writing the correct HTML heading tag." },
      { speaker:"SYSTEM",  text:"[ACTION] Choose CODE to access the terminal. Good luck, hacker." },
    ],
    logEntryId: "logEntry2",
    npcHPAfter: 75,
    successMsg: "HTML firewall neutralised! CYPH-3R is weakening...",
  },
  {
    chapter   : "CHAPTER 1: THE BREACH",
    sector    : "SECTOR 02 — STYLE ENGINE",
    lang      : "CSS",
    difficulty: "⭐ STAGE 2",
    objective : 'Write a CSS rule that sets the body background to black: body { background: black; }',
    hint      : 'Type exactly: body { background: black; }',
    answer    : 'body{background:black;}',
    xp        : 25,
    dialogues : [
      { speaker:"CYPH-3R", text:"Impressive... You broke through Sector 01. But the style engine is mine." },
      { speaker:"SYSTEM",  text:"[BRIEFING] CYPH-3R has corrupted the CSS layer. Restore it by writing a correct CSS rule." },
      { speaker:"SYSTEM",  text:"[ACTION] Enter the correct CSS in the terminal to proceed." },
    ],
    logEntryId: "logEntry3",
    npcHPAfter: 50,
    successMsg: "CSS engine restored! CYPH-3R retreats further...",
  },
  {
    chapter   : "CHAPTER 2: COUNTER-STRIKE",
    sector    : "SECTOR 03 — LOGIC CORE",
    lang      : "JavaScript",
    difficulty: "⭐⭐ STAGE 3",
    objective : 'Complete the function: return the sum of a + b. Write: return a + b;',
    hint      : 'Type: return a + b;',
    answer    : 'return a + b;',
    xp        : 30,
    dialogues : [
      { speaker:"CYPH-3R", text:"You... You cannot crack the Logic Core. It is pure chaos." },
      { speaker:"SYSTEM",  text:"[BRIEFING] The JavaScript Logic Core is broken. Complete the missing function body." },
      { speaker:"SYSTEM",  text:"[ACTION] Write the correct JS expression to restore order." },
    ],
    logEntryId: "logEntry4",
    npcHPAfter: 20,
    successMsg: "Logic Core online! CYPH-3R is critically damaged!",
  },
  {
    chapter   : "CHAPTER 3: FINAL BATTLE",
    sector    : "FINAL — CYPH-3R CORE",
    lang      : "PHP",
    difficulty: "⭐⭐⭐ FINAL",
    objective : 'Echo a message: Write: echo "System Restored";',
    hint      : 'Type: echo "System Restored";',
    answer    : 'echo "system restored";',
    xp        : 50,
    dialogues : [
      { speaker:"CYPH-3R", text:"N-No... Impossible! You've reached my core!" },
      { speaker:"CYPH-3R", text:"I will not let the mainframe be restored! NEVER!" },
      { speaker:"SYSTEM",  text:"[FINAL] Destroy CYPH-3R by outputting the system restore command." },
    ],
    logEntryId: "logEntry5",
    npcHPAfter: 0,
    successMsg: "MAINFRAME RESTORED! CYPH-3R has been defeated!",
  },
];

// ── State ────────────────────────────────────────────────────────────────────
let stageIndex     = 0;
let dialogueIndex  = 0;
let isTyping       = false;
let totalXP        = 0;
let logOpen        = true;

// ── DOM refs ─────────────────────────────────────────────────────────────────
const dialogueBox     = document.getElementById("dialogueBox");
const dialogueSpeaker = document.getElementById("dialogueSpeaker");
const dialogueText    = document.getElementById("dialogueText");
const dialogueCursor  = document.getElementById("dialogueCursor");
const storyActions    = document.getElementById("storyActions");
const codeEditor      = document.getElementById("codeEditor");
const outputContent   = document.getElementById("outputContent");
const codeOutput      = document.getElementById("codeOutput");
const npcHPBar        = document.getElementById("npcHP");
const langBadge       = document.getElementById("langBadge");
const objText         = document.getElementById("objText");
const logChapter      = document.getElementById("logChapter");
const stageLabel      = document.querySelector(".stage-label");

// ── Boot ─────────────────────────────────────────────────────────────────────
window.addEventListener("DOMContentLoaded", () => {
  loadStage(0);
  dialogueBox.addEventListener("click", advanceDialogue);
});

// ── Stage loader ─────────────────────────────────────────────────────────────
function loadStage(idx) {
  stageIndex    = idx;
  dialogueIndex = 0;
  const s = STAGES[idx];

  // Update UI metadata
  langBadge.textContent                                  = s.lang;
  document.getElementById("badge-difficulty") && (document.getElementById("badge-difficulty").textContent = s.difficulty);
  objText.textContent                                    = s.objective;
  logChapter.textContent                                 = s.chapter;
  if (stageLabel) stageLabel.textContent                 = "📡 " + s.sector;
  document.querySelector(".badge-difficulty").textContent = s.difficulty;

  // Activate log entry
  document.querySelectorAll(".log-entry").forEach(el => el.classList.remove("active-entry"));
  const logEl = document.getElementById(s.logEntryId);
  if (logEl) {
    logEl.style.opacity = "1";
    logEl.classList.add("active-entry");
    logEl.querySelector(".log-entry-icon").textContent = "🔴";
  }

  // Hide actions, clear editor
  storyActions.style.display = "none";
  codeEditor.value           = "";
  clearOutput();

  // Start dialogue
  playDialogue(0);
}

// ── Dialogue system ──────────────────────────────────────────────────────────
function playDialogue(idx) {
  const s = STAGES[stageIndex];
  if (idx >= s.dialogues.length) {
    // All dialogues done — show action menu
    dialogueCursor.style.display = "none";
    storyActions.style.display   = "grid";
    return;
  }

  dialogueIndex = idx;
  const d = s.dialogues[idx];
  dialogueSpeaker.textContent  = d.speaker;
  dialogueSpeaker.className    = "dialogue-speaker" + (d.speaker === "CYPH-3R" ? " speaker-npc" : " speaker-system");
  dialogueCursor.style.display = "none";

  typewrite(d.text, () => {
    dialogueCursor.style.display = "inline";
  });

  // NPC idle animation
  const npcSpr = document.getElementById("npcSprite");
  npcSpr.classList.add("npc-talking");
  setTimeout(() => npcSpr.classList.remove("npc-talking"), 800);
}

function advanceDialogue() {
  if (isTyping) {
    // Skip typewriter
    isTyping = false;
    const s = STAGES[stageIndex];
    dialogueText.textContent     = s.dialogues[dialogueIndex].text;
    dialogueCursor.style.display = "inline";
    return;
  }

  const s = STAGES[stageIndex];
  if (dialogueIndex + 1 >= s.dialogues.length) {
    dialogueCursor.style.display = "none";
    storyActions.style.display   = "grid";
  } else {
    playDialogue(dialogueIndex + 1);
  }
}

// ── Typewriter ───────────────────────────────────────────────────────────────
function typewrite(text, onDone) {
  dialogueText.textContent = "";
  isTyping = true;
  let i = 0;

  const tick = setInterval(() => {
    if (!isTyping) { clearInterval(tick); if (onDone) onDone(); return; }
    dialogueText.textContent += text[i++];
    if (i >= text.length) {
      clearInterval(tick);
      isTyping = false;
      if (onDone) onDone();
    }
  }, 30);
}

// ── Action menu ──────────────────────────────────────────────────────────────
function chooseAction(action) {
  storyActions.style.display = "none";

  if (action === "code") {
    // Flash the code panel
    const panel = document.getElementById("storyRight");
    panel.classList.add("panel-flash");
    setTimeout(() => panel.classList.remove("panel-flash"), 600);
    typewrite("CYPH-3R: So you choose to fight with code? Interesting... I'll be watching.", () => {
      dialogueCursor.style.display = "none";
    });
    codeEditor.focus();

  } else if (action === "study") {
    objText.parentElement.classList.add("obj-highlight");
    setTimeout(() => objText.parentElement.classList.remove("obj-highlight"), 1500);
    typewrite("SYSTEM: Review the objective panel on the right. Study before you strike.", () => {
      dialogueCursor.style.display = "none";
      setTimeout(() => { storyActions.style.display = "grid"; }, 1500);
    });

  } else if (action === "hint") {
    showHint();
    typewrite("SYSTEM: [HINT UNLOCKED] Check the hint below the editor.", () => {
      dialogueCursor.style.display = "none";
      setTimeout(() => { storyActions.style.display = "grid"; }, 1500);
    });

  } else if (action === "run") {
    typewrite("CYPH-3R: Ha! Running away won't save you. The mainframe stays corrupted!", () => {
      dialogueCursor.style.display = "none";
      setTimeout(() => { storyActions.style.display = "grid"; }, 2000);
    });
  }
}

// ── Run code ─────────────────────────────────────────────────────────────────
function runCode() {
  const s      = STAGES[stageIndex];
  const raw    = codeEditor.value.trim();
  const answer = raw.toLowerCase().replace(/\s+/g, "");
  const expect = s.answer.toLowerCase().replace(/\s+/g, "");

  codeOutput.style.display = "flex";
  outputContent.classList.remove("output-success", "output-error");

  if (answer === expect) {
    // ── Correct ──
    outputContent.textContent = "✔ Compiled successfully. Output: " + raw;
    outputContent.classList.add("output-success");

    // Damage NPC
    npcHPBar.style.width = s.npcHPAfter + "%";
    npcHPBar.classList.add("hp-damage");
    setTimeout(() => npcHPBar.classList.remove("hp-damage"), 600);

    // NPC hurt sprite
    const npcSpr = document.getElementById("npcSprite");
    npcSpr.classList.add("sprite-hit");
    setTimeout(() => npcSpr.classList.remove("sprite-hit"), 500);

    // Player attack animation
    const playerSpr = document.getElementById("playerSprite");
    playerSpr.classList.add("sprite-attack");
    setTimeout(() => playerSpr.classList.remove("sprite-attack"), 500);

    totalXP += s.xp;

    // Dialogue
    setTimeout(() => {
      typewrite("CYPH-3R: AGH! That... that actually worked. I'm... losing control!", () => {
        dialogueCursor.style.display = "none";
        setTimeout(() => showVictoryOverlay(s), 1000);
      });
    }, 800);

  } else {
    // ── Wrong ──
    outputContent.textContent = "✘ Compilation error. Check your syntax. Expected: " + s.answer;
    outputContent.classList.add("output-error");

    const playerSpr = document.getElementById("playerSprite");
    playerSpr.classList.add("sprite-shake");
    setTimeout(() => playerSpr.classList.remove("sprite-shake"), 500);

    // Show actions again
    setTimeout(() => {
      typewrite("CYPH-3R: Heh... Your code is flawed. Try again, hacker!", () => {
        dialogueCursor.style.display = "none";
        storyActions.style.display = "grid";
      });
    }, 400);
  }
}

// ── Show hint ────────────────────────────────────────────────────────────────
function showHint() {
  const s = STAGES[stageIndex];
  const hintBtn = document.getElementById("hintBtn");
  hintBtn.textContent = "💡 " + s.hint;
  hintBtn.classList.add("hint-active");
}

// ── Clear editor ─────────────────────────────────────────────────────────────
function clearEditor() {
  codeEditor.value = "";
  clearOutput();
  codeEditor.focus();
}

function clearOutput() {
  outputContent.textContent = "Waiting for code execution...";
  outputContent.classList.remove("output-success", "output-error");
  codeOutput.style.display  = "none";
}

// ── Victory overlay ──────────────────────────────────────────────────────────
function showVictoryOverlay(stage) {
  const isFinal = stageIndex >= STAGES.length - 1;
  document.getElementById("overlayBigIcon").textContent  = isFinal ? "🏆" : "✅";
  document.getElementById("overlayHeading").textContent  = isFinal ? "MAINFRAME RESTORED!" : "SECTOR CLEARED!";
  document.getElementById("overlaySubtext").textContent  = stage.successMsg;
  document.getElementById("overlayXP").textContent       = "+" + stage.xp + " XP Earned";

  const nextBtn = document.querySelector(".story-overlay-card .tool-btn");
  if (isFinal) nextBtn.textContent = "🏠 RETURN TO BASE";

  document.getElementById("storyOverlay").style.display = "flex";

  // Award XP
  fetch("award_xp.php", {
    method : "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body   : "xp=" + stage.xp
  }).catch(e => console.warn("XP award failed", e));
}

function nextStage() {
  document.getElementById("storyOverlay").style.display = "none";
  if (stageIndex + 1 >= STAGES.length) {
    window.location.href = "dashboard.php";
  } else {
    loadStage(stageIndex + 1);
    // Reset hint button
    const hintBtn = document.getElementById("hintBtn");
    hintBtn.textContent = "💡 HINT";
    hintBtn.classList.remove("hint-active");
  }
}

// ── Story log toggle ─────────────────────────────────────────────────────────
function toggleLog() {
  const content = document.getElementById("storyLog");
  const btn     = document.querySelector(".log-toggle");
  logOpen = !logOpen;
  content.style.display = logOpen ? "flex" : "none";
  btn.textContent       = logOpen ? "▲" : "▼";
}

// ── Editor line numbers ──────────────────────────────────────────────────────
codeEditor && codeEditor.addEventListener("input", () => {
  const lines   = codeEditor.value.split("\n").length;
  const gutter  = document.getElementById("editorGutter");
  gutter.innerHTML = Array.from({ length: Math.max(10, lines) }, (_, i) => `<span>${i + 1}</span>`).join("");
});
