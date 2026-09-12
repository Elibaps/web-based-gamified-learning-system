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
/* ── House Account Menu (hover via CSS, click/tap via class toggle) ── */
document.addEventListener("DOMContentLoaded", function() {
    const menu = document.getElementById("houseMenu");
    if (!menu) return;
    const trigger = menu.querySelector(".house-trigger");

    function setOpen(open) {
        menu.classList.toggle("is-open", open);
        if (trigger) trigger.setAttribute("aria-expanded", open ? "true" : "false");
    }

    if (trigger) {
        trigger.addEventListener("click", function(e) {
            e.stopPropagation();
            setOpen(menu.classList.contains("is-open") ? false : true);
        });
    }

    document.addEventListener("click", function(e) {
        if (!menu.contains(e.target)) setOpen(false);
    });

    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") setOpen(false);
    });
});
</script>
</body>
</html>
