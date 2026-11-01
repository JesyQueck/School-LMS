<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

$files = [
    'resources/views/admin/report-cards/show.blade.php' => 'admin.report-cards.show',
    'resources/views/student/report-cards.blade.php' => 'student.report-cards',
    'resources/views/admin/report-cards/index.blade.php' => 'admin.report-cards.index',
];

foreach ($files as $path => $view) {
    echo "=== $view ($path) ===\n";
    $content = file_get_contents($path);
    $compiled = $compiler->compileString($content);
    
    // Count if: vs endif
    $ifCount = preg_match_all('/if\s*\(.*\)\s*:/', $compiled);
    $endifCount = substr_count($compiled, 'endif;');
    $renderCompCount = substr_count($compiled, 'renderComponent()');
    
    echo "  if(): $ifCount, endif: $endifCount, renderComponent: $renderCompCount\n";
    
    // PHP lint
    $tmpFile = tempnam(sys_get_temp_dir(), 'blade_check_') . '.php';
    file_put_contents($tmpFile, $compiled);
    $cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
    $output = shell_exec($cmd);
    echo "  " . trim($output) . "\n\n";
    
    unlink($tmpFile);
}
