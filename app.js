let player = JSON.parse(localStorage.getItem("player")) || {
  name: "Player",
  xp: 0,
  level: 1
};

document.getElementById("playerName").innerText = player.name;
updateUI();

function updateUI() {
  let xpNeeded = player.level * 100;
  let percent = (player.xp / xpNeeded) * 100;

  document.getElementById("level").innerText = "Level: " + player.level;
  document.getElementById("xpBar").style.width = percent + "%";
  document.getElementById("xpText").innerText = player.xp + " / " + xpNeeded;
}

function startBattle() {
  window.location.href = "battle.html";
}