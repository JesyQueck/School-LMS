<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$compiler = app('blade.compiler');
$content = file_get_contents('resources/views/admin/report-cards/show.blade.php');
$compiled = $compiler->compileString($content);
file_put_contents('C:\Users\USER\Desktop\School-LMS\backend\check_show_compiled.php', $compiled);
echo "Written. Length: " . strlen($compiled) . "\n";

// Also lint it
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';
$cmd = $php . ' -l C:\Users\USER\Desktop\School-LMS\backend\check_show_compiled.php 2>&1';
echo shell_exec($cmd);
