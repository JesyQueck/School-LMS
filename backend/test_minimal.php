<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
$php = '"C:\\laragon\\Bin\\php\\php-8.5.11-Win32-vs17-x64\\php.exe"';

function checkBlade($compiler, $content, $label, $php) {
    $compiled = $compiler->compileString($content);
    $tmpFile = tempnam(sys_get_temp_dir(), 'blade_min_') . '.php';
    file_put_contents($tmpFile, $compiled);
    $cmd = $php . ' -l ' . escapeshellarg($tmpFile) . ' 2>&1';
    $result = trim(shell_exec($cmd));
    echo "$label: $result\n";
    
    $ifCount = preg_match_all('/\(\$.+->shouldRender\(\)\):/', $compiled);
    $endifCount = substr_count($compiled, 'renderComponent()');
    echo "  shouldRender if: $ifCount, renderComponent: $endifCount\n";
    
    unlink($tmpFile);
}

// Test 1: Simple x-layouts.app + x-ui.card
checkBlade($compiler, '<x-layouts.app title="Test"><x-ui.card>Content</x-ui.card></x-layouts.app>', 'Test 1: layouts.app + ui.card', $php);

// Test 2: With @if around alert
checkBlade($compiler, '<x-layouts.app title="Test">@if(session("status"))<x-ui.alert>A</x-ui.alert>@endif<x-ui.card>Content</x-ui.card></x-layouts.app>', 'Test 2: + alert in @if', $php);

// Test 3: With @forelse inside card
checkBlade($compiler, '<x-layouts.app title="Test"><x-ui.card>@forelse($items as $i)@if($i)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'Test 3: + forelse in card', $php);

// Test 4: With @if inside card + @forelse
checkBlade($compiler, '<x-layouts.app title="Test"><x-ui.card><div>@foreach($items as $i)<span>{{$i}}</span>@endforeach</div></x-ui.card></x-layouts.app>', 'Test 4: + foreach in card', $php);

// Test 5: Minimal show.blade.php structure
checkBlade($compiler, '<x-layouts.app title="Test">@if(true)<x-ui.alert>A</x-ui.alert>@endif<x-ui.card><div>Test</div></x-ui.card></x-layouts.app>', 'Test 5: if+alert+card', $php);

// Test 6: @foreach with @php inside card (like original)
checkBlade($compiler, '<x-layouts.app title="Test"><x-ui.card><div>@foreach($items as $i)@php $x = 1; @endphp<span>{{$i}}</span>@endforeach</div></x-ui.card></x-layouts.app>', 'Test 6: foreach+php in card', $php);

// Test 7: @forelse with @php inside card
checkBlade($compiler, '<x-layouts.app title="Test"><x-ui.card><div>@forelse($items as $i)@php $x = 1; @endphp<span>{{$i}}</span>@empty none @endforelse</div></x-ui.card></x-layouts.app>', 'Test 7: forelse+php in card', $php);

// Test 8: The actual structure with two if blocks
checkBlade($compiler, '<x-layouts.app title="Test"><div>@if(true)<a>Link</a>@endif<div>@if(true)<x-ui.alert>A</x-ui.alert>@endif</div></div><x-ui.card><div>Content</div></x-ui.card></x-layouts.app>', 'Test 8: nested ifs + alert + card', $php);
