<?php
$files = glob("*.php");
$replacements = [
    'color: #4ade80;' => 'color: var(--primary-color);',
    'color: white;' => 'color: inherit;',
    'color:white;' => 'color: inherit;',
    'background: #000;' => 'background: var(--bg-color);',
    'background: #020617;' => 'background: var(--card-bg);',
    'border: 4px solid #4ade80;' => 'border: 4px solid var(--primary-color);',
    'border: 2px solid #4ade80;' => 'border: 2px solid var(--primary-color);',
    'box-shadow: 6px 6px 0 #0f5127;' => 'box-shadow: 6px 6px 0 var(--shadow-color);',
    'box-shadow: 4px 4px 0 #0f5127;' => 'box-shadow: 4px 4px 0 var(--shadow-color);',
    'border-color: #ff0000;' => 'border-color: var(--danger-color);',
    'background: #ff0000;' => 'background: var(--danger-color);',
    'text-shadow: 0 0 5px rgba(74, 222, 128, 0.6);' => 'text-shadow: var(--text-shadow-glow);',
    'color:#4ade80;' => 'color: var(--primary-color);'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = strtr($content, $replacements);
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated: $file\n";
    }
}
echo "All PHP files updated with CSS variables.";
