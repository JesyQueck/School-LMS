<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$factory = app('view');
$fileViewFinder = $factory->getFinder();

$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

$errors = [];

// Iterate over all blade files in views directory
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('resources/views', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    
    $srcPath = $file->getRealPath();
    $content = file_get_contents($srcPath);
    
    try {
        $compiled = $compiler->compileString($content);
        
        // Write to temp and check PHP syntax
        $tmpFile = tempnam(sys_get_temp_dir(), 'blade_lint_') . '.php';
        file_put_contents($tmpFile, $compiled);
        
        $cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
        $output = shell_exec($cmd);
        
        if (strpos($output, 'No syntax errors') === false) {
            $errors[] = [
                'file' => $srcPath,
                'error' => trim($output),
            ];
        }
        
        unlink($tmpFile);
    } catch (\Throwable $e) {
        $errors[] = [
            'file' => $srcPath,
            'error' => 'EXCEPTION: ' . $e->getMessage(),
        ];
    }
}

echo "=== Total files checked ===\n";
echo "Total errors found: " . count($errors) . "\n\n";

foreach ($errors as $error) {
    echo $error['file'] . ":\n";
    echo "  " . $error['error'] . "\n\n";
}
