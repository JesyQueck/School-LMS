<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$classes = DB::table('classes')->pluck('name')->toArray();
$subjects = DB::table('subjects')->pluck('name')->toArray();
$sessions = DB::table('academic_sessions')->pluck('name')->toArray();

echo 'Classes: '.implode(', ', $classes).PHP_EOL;
echo 'Subjects: '.implode(', ', $subjects).PHP_EOL;
echo 'Sessions: '.implode(', ', $sessions).PHP_EOL;
