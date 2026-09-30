<?php
// Draws a neat grey product tile when a product has no image yet (works offline).
$b = mb_substr(strip_tags($_GET['b'] ?? ''), 0, 30);
$n = mb_substr(strip_tags($_GET['n'] ?? ''), 0, 60);
$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
header('Content-Type: image/svg+xml'); header('Cache-Control: public, max-age=86400');
$words = explode(' ', $n); $lines = []; $cur = '';
foreach ($words as $w) { if (mb_strlen($cur . ' ' . $w) > 22 && $cur) { $lines[] = trim($cur); $cur = $w; } else { $cur .= ' ' . $w; } } $lines[] = trim($cur);
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 750"><rect width="600" height="750" fill="#ececec"/>';
echo '<circle cx="300" cy="330" r="150" fill="none" stroke="#d6d6d6" stroke-width="2"/><circle cx="300" cy="330" r="100" fill="none" stroke="#d6d6d6" stroke-width="2"/>';
echo '<text x="300" y="352" text-anchor="middle" font-family="Georgia,serif" font-style="italic" font-size="' . (mb_strlen($b) > 10 ? 44 : 60) . '" fill="#111">' . $h($b) . '</text>';
$y = 560; foreach (array_slice($lines, 0, 3) as $l) { echo '<text x="300" y="' . $y . '" text-anchor="middle" font-family="Arial,sans-serif" font-size="26" fill="#555">' . $h($l) . '</text>'; $y += 34; }
echo '<text x="300" y="715" text-anchor="middle" font-family="Arial,sans-serif" font-size="14" letter-spacing="4" fill="#aaa">SEXY SONNET SPORT</text></svg>';
