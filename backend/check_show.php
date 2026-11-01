<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

$srcPath = 'resources/views/admin/report-cards/show.blade.php';
$content = file_get_contents($srcPath);

echo "=== Direct compile check ===\n";
$compiled = $compiler->compileString($content);

// Check specific lines around line 130
$lines = explode("\n", $compiled);
echo "Compiled PHP line count: " . count($lines) . "\n";
echo "\n--- Compiled lines 120-135 ---\n";
for ($i = 125; $i <= min(140, count($lines)); $i++) {
    echo ($i+1) . ": " . $lines[$i] . "\n";
}

echo "\n--- PHP Lint ---\n";
$tmpFile = tempnam(sys_get_temp_dir(), 'blade_show_') . '.php';
file_put_contents($tmpFile, $compiled);
$cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
$output = shell_exec($cmd);
echo trim($output) . "\n";
unlink($tmpFile);

// Also check from the original source
echo "\n=== Original Blade lines 68-93 ===\n";
$origLines = explode("\n", $content);
for ($i = 67; $i <= min(92, count($origLines)); $i++) {
    echo ($i+1) . ": " . $origLines[$i] . "\n";
}
