<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\ImportCredential;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0');

Student::truncate();
ParentProfile::truncate();
User::whereIn('role', ['student', 'parent'])->delete();
ImportCredential::where('role', 'student')->orWhere('role', 'parent')->delete();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Students cleared, Parents cleared, student/parent users deleted\n";
echo 'Remaining users: '.User::count().PHP_EOL;
echo 'Remaining credentials: '.ImportCredential::count().PHP_EOL;

$classes = DB::table('classes')->count();
$sessions = DB::table('academic_sessions')->count();
echo "Classes: {$classes}, Sessions: {$sessions}\n";

$firstClass = DB::table('classes')->first();
$firstSession = DB::table('academic_sessions')->first();
echo "First class: {$firstClass->name}, First session: {$firstSession->name}\n";

$subjects = DB::table('subjects')->count();
echo "Subjects: {$subjects}\n";
