document.addEventListener("DOMContentLoaded", function () {

let bossHP = 100;
let playerHP = 100;
let timeLeft = 10;
let timer;

let questions = [
  { q: "What is HTML?", answer: "markup" },
  { q: "CSS is for?", answer: "design" }
];

let current = 0;
let isAnswering = true;

startTurn();

function startTurn() {
  if (current >= questions.length) return win();

  timeLeft = 10;
  isAnswering = true;

  updateTimerBar();
  showQuestion();

  timer = setInterval(() => {
    timeLeft -= 0.1;
    updateTimerBar();

    if (timeLeft <= 0) {
      clearInterval(timer);
      isAnswering = false;

      playerDamage(10);

      setTimeout(() => {
        current++;
        startTurn();
      }, 800);
    }
  }, 100);
}

function showQuestion() {
  let q = questions[current];
  document.getElementById("question").innerText = q.q;

  let input = document.getElementById("answerInput");
  input.value = "";
  input.focus();
}

function updateTimerBar() {
  let percent = (timeLeft / 10) * 100;
  document.getElementById("timerFill").style.width = percent + "%";
}

function submitAnswer() {
  if (!isAnswering) return;

  isAnswering = false;
  clearInterval(timer);

  let userAnswer = document.getElementById("answerInput").value.toLowerCase().trim();
  let correct = questions[current].answer;

  if (userAnswer === correct) {
    let damage = Math.floor(timeLeft * 8);
    bossHP -= damage;

    document.querySelector(".player").classList.add("attack");
    setTimeout(() => document.querySelector(".player").classList.remove("attack"), 300);

    document.querySelector(".enemy").classList.add("hit");
    setTimeout(() => document.querySelector(".enemy").classList.remove("hit"), 300);

  } else {
    playerDamage(15);
  }

  updateHP();
  current++;

  setTimeout(() => {
    if (bossHP <= 0) return win();
    if (playerHP <= 0) return lose();
    startTurn();
  }, 800);
}

function playerDamage(dmg) {
  playerHP -= dmg;
  document.querySelector(".player").classList.add("hit");
  setTimeout(() => document.querySelector(".player").classList.remove("hit"), 300);
  updateHP();
}

function updateHP() {
  document.getElementById("bossHP").style.width = bossHP + "%";
  document.getElementById("playerHP").style.width = playerHP + "%";
}

function win() {
  alert("Victory!");
  window.location.href = "dashboard.php";
}

function lose() {
  alert("Defeated!");
  window.location.href = "dashboard.php";
}

document.getElementById("submitBtn").addEventListener("click", submitAnswer);

document.getElementById("answerInput").addEventListener("keypress", function(e){
  if(e.key === "Enter") submitAnswer();
});

});