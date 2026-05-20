<script>
/* ── Shared navigation utilities (available on every page) ── */
function startBattle(language) {
    window.location.href = "battle.php?topic=" + encodeURIComponent(language);
}
function openLesson(language) {
    window.location.href = "lesson.php?course=" + encodeURIComponent(language);
}
function startQuiz(language) {
    window.location.href = "quiz.php?topic=" + encodeURIComponent(language);
}

/* ── Theme Toggler ── */
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("themeToggleBtn");
    if (!btn) return;

    // Set initial icon
    const currentTheme = document.documentElement.getAttribute("data-theme");
    btn.innerText = currentTheme === "light" ? "🌙" : "🌞";

    btn.addEventListener("click", function() {
        let theme = document.documentElement.getAttribute("data-theme");
        if (theme === "light") {
            document.documentElement.removeAttribute("data-theme");
            localStorage.setItem("theme", "dark");
            btn.innerText = "🌞";
        } else {
            document.documentElement.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
            btn.innerText = "🌙";
        }
    });
});
</script>
</body>
</html>
