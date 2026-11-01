<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear compiled views to force recompilation
$cachePath = 'storage/framework/views';
foreach (glob($cachePath . '/*') as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        unlink($file);
    }
}

$compiler = app('blade.compiler');
$factory = app('view');
$fileViewFinder = $factory->getFinder();

$views = [
    'admin.report-cards.index',
    'admin.report-cards.show',
    'teacher.report-cards.index',
    'teacher.report-cards.preview',
    'student.report-cards',
    'student.report-cards.preview',
    'pdf.report-card',
    'parent.report-cards',
];

// Check PHP syntax of compiled views
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

foreach ($views as $view) {
    echo "=== $view ===\n";
    try {
        $path = $fileViewFinder->find($view);
        echo "  Compiling: $path\n";
        // Force compilation
        $compiled = $compiler->compileString(file_get_contents($path));
        
        // Write a temporary file and check PHP syntax
        $tmpFile = tempnam(sys_get_temp_dir(), 'blade_check_') . '.php';
        file_put_contents($tmpFile, $compiled);
        
        $cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
        $output = shell_exec($cmd);
        echo "  " . trim($output) . "\n";
        
        unlink($tmpFile);
    } catch (\Throwable $e) {
        echo "  COMPILE EXCEPTION: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
