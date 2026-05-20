/**
 * app.js — DEPRECATED
 *
 * This file contained an old localStorage-based player system that has been
 * superseded by the PHP session + MySQL backend.
 *
 * Player state (XP, level) is now stored in the `users` database table and
 * managed through:
 *   - dashboard.php  (reads and displays user stats)
 *   - award_xp.php   (POST endpoint that updates XP + handles level-up)
 *
 * This file is kept to avoid 404 errors from any lingering references,
 * but it is safe to delete.
 */