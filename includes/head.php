<?php
// $pageTitle must be set before including this file.
$pageTitle = isset($pageTitle) ? $pageTitle : 'CodeNest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CodeNest — A gamified coding learning platform">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="UI.css?v=3">
    <script>
      (function() {
        var theme = localStorage.getItem('theme');
        if (theme === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      })();
    </script>
</head>
