<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

echo 'Users with student/parent role: '.DB::table('users')->whereIn('role', ['student', 'parent'])->count().PHP_EOL;
echo 'Teachers: '.DB::table('users')->where('role', 'teacher')->count().PHP_EOL;
echo 'Admins: '.DB::table('users')->where('role', 'admin')->count().PHP_EOL;
echo 'Parent profiles: '.DB::table('parent_profiles')->count().PHP_EOL;
echo 'Students: '.DB::table('students')->count().PHP_EOL;
