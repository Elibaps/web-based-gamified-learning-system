<?php
$css = file_get_contents('UI.css');

// Ensure root variables aren't already there
if (strpos($css, '--bg-color') === false) {
    $rootVars = "
:root {
  --bg-color: #000000;
  --card-bg: #020617;
  --primary-color: #4ade80;
  --shadow-color: #0f5127;
  --danger-color: #ff0000;
  --warning-color: #ffb000;
  --text-shadow-glow: 0 0 3px rgba(74, 222, 128, 0.4);
  --border-radius: 6px;
}

[data-theme='light'] {
  --bg-color: #f0fdf4;
  --card-bg: #dcfce7;
  --primary-color: #14532d;
  --shadow-color: #15803d;
  --danger-color: #b91c1c;
  --warning-color: #b45309;
  --text-shadow-glow: none;
}
";
    $css = $rootVars . $css;

    // Perform replacements globally
    $css = str_replace('#000000', 'var(--bg-color)', $css);
    // Be careful with short hex #000
    $css = preg_replace('/#000([^0-9a-fA-F])/', 'var(--bg-color)$1', $css);
    $css = str_replace('black', 'var(--bg-color)', $css);
    
    $css = str_replace('#020617', 'var(--card-bg)', $css);
    
    $css = str_replace('#4ade80', 'var(--primary-color)', $css);
    $css = str_replace('#22c55e', 'var(--primary-color)', $css); // Some hover states use this
    
    $css = str_replace('#0f5127', 'var(--shadow-color)', $css);
    
    // Replace border-radius: 0 with 6px
    $css = preg_replace('/border-radius:\s*0\s*;/', 'border-radius: var(--border-radius);', $css);
    
    // Soften scanlines
    $css = str_replace('rgba(0, 0, 0, 0.25)', 'rgba(0, 0, 0, 0.15)', $css);
    
    // Fix text-shadow
    $css = str_replace('0 0 3px rgba(74, 222, 128, 0.4)', 'var(--text-shadow-glow)', $css);
    $css = str_replace('0 0 3px rgba(74, 222, 128, 0.5)', 'var(--text-shadow-glow)', $css);
    $css = str_replace('0 0 5px rgba(74, 222, 128, 0.6)', 'var(--text-shadow-glow)', $css);

    file_put_contents('UI.css', $css);
    echo "Success";
} else {
    echo "Already updated";
}
