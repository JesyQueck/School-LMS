<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$admin = User::where('role', 'admin')->first();
echo 'Admin user: '.($admin ? $admin->email : 'NONE').PHP_EOL;
echo 'Admin needs_password_change: '.($admin ? $admin->needs_password_change : 'N/A').PHP_EOL;
echo 'Total users: '.User::count().PHP_EOL;

$users = User::all();
foreach ($users as $u) {
    echo "  - {$u->email} ({$u->role}) needs_change=".($u->needs_password_change ? 'true' : 'false').PHP_EOL;
}
