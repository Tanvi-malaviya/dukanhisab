<?php
// Script to show the last few lines of laravel.log
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lastChars = substr($content, -8000);
    echo "<h3>Last 8000 characters of laravel.log:</h3>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; overflow:auto;'>" . htmlspecialchars($lastChars) . "</pre>";
} else {
    echo "laravel.log file not found at " . htmlspecialchars($logPath);
}
