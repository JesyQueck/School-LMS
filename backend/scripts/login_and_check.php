<?php

$baseDir = dirname(__DIR__);
require $baseDir.'/vendor/autoload.php';
$app = require $baseDir.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;

// Step 1: Get the login page to extract CSRF token
$response = Http::withHeaders([
    'Accept' => 'text/html',
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
])->get('http://localhost:8001/login');

$cookie = $response->header('Set-Cookie');
$body = $response->body();

// Parse CSRF token
preg_match('/_token" value="([^"]+)"/', $body, $matches);
$token = $matches[1] ?? '';
echo 'CSRF token: '.substr($token, 0, 20)."...\n";

// Extract cookie
$cookieStr = '';
if (is_array($cookie)) {
    $cookieStr = implode('; ', $cookie);
} else {
    $cookieStr = $cookie;
}
echo 'Cookie: '.$cookieStr."\n";

// Step 2: Login
$loginResp = Http::withHeaders([
    'Accept' => 'text/html',
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Cookie' => $cookieStr,
    'Referer' => 'http://localhost:8001/login',
    'Content-Type' => 'application/x-www-form-urlencoded',
])->asForm()->withSession([
    'X-CSRF-TOKEN' => $token,
])->post('http://localhost:8001/login', [
    '_token' => $token,
    'email' => 'admin@demo.school',
    'password' => 'AdminTest123!',
])->body();

// Check if login succeeded
if (str_contains($loginResp, 'Invalid credentials')) {
    echo "Login failed: Invalid credentials\n";
    exit(1);
}

$loginCookie = '';
foreach (Http::lastTransfer()['response']->getHeaders()['Set-Cookie'] ?? [] as $c) {
    $loginCookie .= $c.'; ';
}
echo "Login done. Checking dashboard...\n";

// Step 3: Access admin dashboard
$dashResp = Http::withHeaders([
    'Accept' => 'text/html',
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Cookie' => $cookieStr.$loginCookie,
    'Referer' => 'http://localhost:8001/login',
])->get('http://localhost:8001/admin');

echo 'Dashboard status: '.$dashResp->status()."\n";
echo 'Dashboard URL matches: '.($dashResp->effectiveUrl() ?? '')."\n";
