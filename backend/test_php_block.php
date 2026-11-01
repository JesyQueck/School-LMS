<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

// Read the original
$content = file_get_contents('resources/views/admin/report-cards/show.blade.php');

// Test 1: Original content
echo "=== Test 1: Original ===\n";
$compiled = $compiler->compileString($content);
$tmpFile = tempnam(sys_get_temp_dir(), 'blade_t1_') . '.php';
file_put_contents($tmpFile, $compiled);
$cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
echo trim(shell_exec($cmd)) . "\n";
unlink($tmpFile);

// Test 2: Remove the @php/@endphp block inside the loop
$content2 = preg_replace(
    '/\s*@php\s*\$gradeClass.*?@endphp\s*/s',
    '',
    $content,
    1,
    $count
);
echo "=== Test 2: Without @php block (removed $count matches) ===\n";
$compiled2 = $compiler->compileString($content2);
$tmpFile2 = tempnam(sys_get_temp_dir(), 'blade_t2_') . '.php';
file_put_contents($tmpFile2, $compiled2);
$cmd2 = $php . ' -l ' . escapeshellarg($tmpFile2) . ' 2>&1';
echo trim(shell_exec($cmd2)) . "\n";
unlink($tmpFile2);

// Test 3: Replace @php with inline PHP
$content3 = str_replace(
    "                            @php\n                                    \$gradeClass = match(\$result->grade) {\n                                        'A1', 'B2', 'B3' => 'text-success-600 dark:text-success-400',\n                                        'C4', 'C5', 'C6' => 'text-warning-600 dark:text-warning-400',\n                                        default => 'text-danger-600 dark:text-danger-400',\n                                    };\n                                @endphp\n",
    "                                <?php \$gradeClass = match(\$result->grade) {\n                                        'A1', 'B2', 'B3' => 'text-success-600 dark:text-success-400',\n                                        'C4', 'C5', 'C6' => 'text-warning-600 dark:text-warning-400',\n                                        default => 'text-danger-600 dark:text-danger-400',\n                                    }; ?>\n",
    $content
);
echo "=== Test 3: With inline PHP instead of @php ===\n";
$compiled3 = $compiler->compileString($content3);
$tmpFile3 = tempnam(sys_get_temp_dir(), 'blade_t3_') . '.php';
file_put_contents($tmpFile3, $compiled3);
$cmd3 = $php . ' -l ' . escapeshellarg($tmpFile3) . ' 2>&1';
echo trim(shell_exec($cmd3)) . "\n";
unlink($tmpFile3);

// Count if:/endif
$ifCount = preg_match_all('/if\s*\(.*\)\s*:/', $compiled);
$endifCount = substr_count($compiled, 'endif;');
echo "\n=== Counters ===\n";
echo "if(): $ifCount, endif: $endifCount, renderComponent: " . substr_count($compiled, 'renderComponent()') . "\n";
