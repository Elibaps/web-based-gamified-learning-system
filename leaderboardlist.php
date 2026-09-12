<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Identify current user (reuse the session cached user_id like dashboard.php)
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
} else {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $user_id = $row ? (int)$row['user_id'] : 0;
    $_SESSION['user_id'] = $user_id;
}

// ── Pagination (safe integer handling) ───────────────────────────────────
$perPage = 5;
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = (int)$totalResult->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// ── Top players — same ordering as the dashboard mini leaderboard ────────
$stmt = $conn->prepare(
    "SELECT user_id, username, level, exp
       FROM users
      ORDER BY level DESC, exp DESC
      LIMIT ? OFFSET ?"
);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Avatar: reuse the dashboard avatar convention (per-user images/ if one
//    exists) with the universal player.png fallback. ─────────────────────
function leaderboardAvatar($uid) {
    $custom = __DIR__ . "/images/avatar_{$uid}.png";
    if (is_file($custom)) {
        return "images/avatar_{$uid}.png";
    }
    return "assets/player.png";
}

$pageTitle = 'Leaderboard — CodeNest';
include 'includes/head.php';
?>
<body class="dashboard-page">

<?php include 'includes/navbar.php'; ?>

<!-- REUSED DASHBOARD LIVING BACKGROUND (light/dark aware) -->
<div class="dashboard-parallax" aria-hidden="true">
    <div class="dashboard-parallax-layer dashboard-parallax-near"></div>

    <div class="dashboard-particles dashboard-fireflies"></div>
    <div class="dashboard-particles dashboard-motes"></div>
    <div class="dashboard-leaves"></div>

    <div class="dashboard-parallax-vignette"></div>
</div>

<main class="leaderboard-page">
    <h1 class="sr-only">Top Players</h1>

    <section class="leaderboard-board" aria-label="Leaderboard">
        <?php if (empty($players)): ?>
            <p class="lb-empty">No players ranked yet.</p>
        <?php else: ?>
            <div class="leaderboard-list">
                <?php foreach ($players as $i => $player): ?>
                    <?php
                        $globalRank = $offset + $i + 1;
                        $isMe = (int)$player['user_id'] === $user_id;
                        $safeName = htmlspecialchars($player['username'], ENT_QUOTES, 'UTF-8');
                        $placeClass = $globalRank <= 3 ? ' rank-' . $globalRank : '';
                    ?>
                    <div class="lb-row<?php echo $isMe ? ' is-me' : ''; ?><?php echo $placeClass; ?>">
                        <div class="lb-rank">
                            <?php if ($globalRank <= 3): ?>
                                <img
                                    src="assets/leaderboard-top-<?php echo $globalRank; ?>.png"
                                    alt="Rank <?php echo $globalRank; ?>"
                                >
                            <?php else: ?>
                                <span>#<?php echo $globalRank; ?></span>
                            <?php endif; ?>
                        </div>

                        <img
                            class="lb-avatar"
                            src="<?php echo leaderboardAvatar($player['user_id']); ?>"
                            alt="Avatar of <?php echo $safeName; ?>"
                        >

                        <span class="lb-name">
                            <?php echo $safeName; ?>
                            <?php if ($isMe): ?><span class="lb-you">YOU</span><?php endif; ?>
                        </span>

                        <span class="lb-level">LV. <?php echo (int)$player['level']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalUsers > $perPage): ?>
                <nav class="lb-pagination" aria-label="Leaderboard pages">
                    <?php if ($page > 1): ?>
                        <a class="lb-btn" href="leaderboardlist.php?page=<?php echo $page - 1; ?>">PREVIOUS</a>
                    <?php else: ?>
                        <span class="lb-btn is-disabled" aria-disabled="true">PREVIOUS</span>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a class="lb-btn" href="leaderboardlist.php?page=<?php echo $page + 1; ?>">NEXT</a>
                    <?php else: ?>
                        <span class="lb-btn is-disabled" aria-disabled="true">NEXT</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <a class="lb-btn lb-back" href="dashboard.php">BACK TO DASHBOARD</a>
</main>

<script>
/* ── Dashboard Ambient Particles (reused from dashboard.php) ── */
(function () {
    var page = document.body;
    if (!page || !page.classList.contains("dashboard-page")) return;

    var fireflyHost = document.querySelector(".dashboard-fireflies");
    var moteHost    = document.querySelector(".dashboard-motes");
    var leafHost    = document.querySelector(".dashboard-leaves");

    function makeParticles(host, count, className) {
        if (!host || host.children.length) return;

        for (var i = 0; i < count; i++) {
            var particle = document.createElement("span");
            particle.className = className;

            particle.style.setProperty("--x",       (Math.random() * 100).toFixed(2) + "%");
            particle.style.setProperty("--y",       (Math.random() * 100).toFixed(2) + "%");
            particle.style.setProperty("--delay",   (-Math.random() * 8).toFixed(2)  + "s");
            particle.style.setProperty("--duration",(5.2 + Math.random() * 5.8).toFixed(2) + "s");
            particle.style.setProperty("--drift",   (-38 + Math.random() * 76).toFixed(1) + "px");
            particle.style.setProperty("--size",    (2 + Math.random() * 5).toFixed(1) + "px");

            host.appendChild(particle);
        }
    }

    function makeLeaves(host, count) {
        if (!host || host.children.length) return;

        for (var i = 0; i < count; i++) {
            var leaf = document.createElement("span");
            leaf.className = "dashboard-leaf";

            leaf.style.setProperty("--x",        (Math.random() * 100).toFixed(2) + "%");
            leaf.style.setProperty("--delay",    (-Math.random() * 15).toFixed(2) + "s");
            leaf.style.setProperty("--duration", (10 + Math.random() * 8).toFixed(2) + "s");
            leaf.style.setProperty("--sway",     (50 + Math.random() * 85).toFixed(1) + "px");

            host.appendChild(leaf);
        }
    }

    makeParticles(fireflyHost, 40, "dashboard-firefly");
    makeParticles(moteHost,    44, "dashboard-mote");
    makeLeaves(leafHost, 12);
})();
</script>

<?php include 'includes/footer.php'; ?>