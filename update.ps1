$css = Get-Content -Raw -Path 'UI.css'

$rootVars = @"

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

"@

if ($css -notmatch '--bg-color') {
    $css = $rootVars + $css
    
    $css = $css -replace '#000000', 'var(--bg-color)'
    $css = $css -replace '(?<![0-9a-fA-F])#000(?![0-9a-fA-F])', 'var(--bg-color)'
    $css = $css -replace '#020617', 'var(--card-bg)'
    $css = $css -replace '#4ade80', 'var(--primary-color)'
    $css = $css -replace '#22c55e', 'var(--primary-color)'
    $css = $css -replace '#0f5127', 'var(--shadow-color)'
    
    $css = $css -replace 'border-radius:\s*0\s*;', 'border-radius: var(--border-radius);'
    $css = $css -replace 'rgba\(0, 0, 0, 0\.25\)', 'rgba(0, 0, 0, 0.15)'
    
    Set-Content -Path 'UI.css' -Value $css
    Write-Host "CSS updated via PowerShell."
} else {
    Write-Host "CSS already updated."
}
