<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\ImportCredential;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

echo "=== Current State ===\n";
echo 'Students (users): '.User::where('role', 'student')->count()."\n";
echo 'Parents (users): '.User::where('role', 'parent')->count()."\n";
echo 'Teachers (users): '.User::where('role', 'teacher')->count()."\n";
echo 'Admins (users): '.User::where('role', 'admin')->count()."\n";
echo 'Total users: '.User::count()."\n";
echo 'Import credentials: '.ImportCredential::count()."\n";
echo 'Classes: '.DB::table('classes')->count()."\n";
echo 'Sessions: '.DB::table('academic_sessions')->count()."\n";
echo 'Subjects: '.DB::table('subjects')->count()."\n";

$classes = DB::table('classes')->pluck('name')->toArray();
$sessions = DB::table('academic_sessions')->pluck('name')->toArray();
echo "\nClasses: ".implode(', ', $classes)."\n";
echo 'Sessions: '.implode(', ', $sessions)."\n";
