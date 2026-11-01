<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

function checkBlade($compiler, $content, $label, $php) {
    $compiled = $compiler->compileString($content);
    $tmpFile = tempnam(sys_get_temp_dir(), 'blade_min2_') . '.php';
    file_put_contents($tmpFile, $compiled);
    $cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
    $result = trim(shell_exec($cmd));
    echo "$label: $result\n";
    unlink($tmpFile);
}

// Test A: @forelse with @if inside, NO component wrapper
checkBlade($compiler, '@forelse($items as $i)@if($i)yes@endif@empty no @endforelse', 'A: forelse+if no component', $php);

// Test B: @forelse with @if inside, WITH component
checkBlade($compiler, '<x-ui.card>@forelse($items as $i)@if($i)yes@endif@empty no @endforelse</x-ui.card>', 'B: forelse+if in ui.card', $php);

// Test C: @foreach with @if inside, WITH component
checkBlade($compiler, '<x-ui.card>@foreach($items as $i)@if($i)yes@endif@endforeach</x-ui.card>', 'C: foreach+if in ui.card', $php);

// Test D: @forelse without @if inside, WITH component
checkBlade($compiler, '<x-ui.card>@forelse($items as $i)yes@if($i)extra@endif@empty no @endforelse</x-ui.card>', 'D: forelse+if AFTER content in ui.card', $php);

// Test E: @forelse with @if as first thing, WITH component
checkBlade($compiler, '<x-ui.card>@forelse($items as $i)@if($i)yes@endif content @empty no @endforelse</x-ui.card>', 'E: forelse+if first in ui.card', $php);

// Test F: Two @if inside @forelse, WITH component
checkBlade($compiler, '<x-ui.card>@forelse($items as $i)@if($i)@if($i==1)yes@endif@endif@empty no @endforelse</x-ui.card>', 'F: nested if in forelse in card', $php);

// Test G: layouts.app + card with forelse+if
checkBlade($compiler, '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@if($i)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'G: forelse+if in card in layouts', $php);
