<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

// Clear all compiled views
$cachePath = 'storage/framework/views';
foreach (glob($cachePath . '/*') as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        unlink($file);
    }
}

function tryCompile($path, $content, $label) {
    // Write a temp blade file
    $tmpDir = 'resources/views/tmp_test';
    if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
    $tmpPath = $tmpDir . '/' . $path . '.blade.php';
    file_put_contents($tmpPath, $content);
    
    $cachePath = 'storage/framework/views';
    // Clear cache for this specific file (different hash each time)
    foreach (glob($cachePath . '/*') as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            unlink($file);
        }
    }
    
    // Compile using the framework
    $factory = app('view');
    $view = $factory->make('tmp_test.' . $path, []);
    
    try {
        $view->render();
        echo "  $label: SUCCESS\n";
    } catch (\Throwable $e) {
        echo "  $label: ERROR - " . $e->getMessage() . "\n";
    }
    
    // Clean up temp file
    unlink($tmpPath);
    
    // Clear cache
    foreach (glob($cachePath . '/*') as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            unlink($file);
        }
    }
}

// Test various patterns
// A: Just components, no directives
tryCompile('test_a', '<x-layouts.app title="T"><x-ui.card>Content</x-ui.card></x-layouts.app>', $php, 'A: layouts+card');

// B: + @if around alert
tryCompile('test_b', '<x-layouts.app title="T">@if(true)<x-ui.alert>V</x-ui.alert>@endif<x-ui.card>Content</x-ui.card></x-layouts.app>', $php, 'B: +alert in if');

// C: + @forelse inside card
tryCompile('test_c', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i){{$i}}@empty no @endforelse</x-ui.card></x-layouts.app>', $php, 'C: forelse in card');

// D: + @if inside @forelse inside card
tryCompile('test_d', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@if($i)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', $php, 'D: if in forelse in card');

// E: + @php inside @forelse inside card
tryCompile('test_e', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@php $x=1; @endphp{{$i}}@empty no @endforelse</x-ui.card></x-layouts.app>', $php, 'E: php in forelse in card');

// F: @foreach with @if inside card
tryCompile('test_f', '<x-layouts.app title="T"><x-ui.card>@foreach($items as $i)@if($i)yes@endif@endforeach</x-ui.card></x-layouts.app>', $php, 'F: foreach+if in card');

// G: Multiple forms with @csrf and @method inside card
tryCompile('test_g', '<x-layouts.app title="T"><x-ui.card><form>@csrf @method("PUT")<input></form></x-ui.card></x-layouts.app>', $php, 'G: form+csrf+method in card');

// H: @forelse + @php + @if inside card
tryCompile('test_h', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@php $x=match($i){\'a\'=>1,default=>2};@endphp@if($x)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', $php, 'H: forelse+php+if in card');
