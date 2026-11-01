<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function tryCompile($path, $content, $label) {
    $tmpDir = 'resources/views/tmp_test';
    if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
    $tmpPath = $tmpDir . '/' . $path . '.blade.php';
    file_put_contents($tmpPath, $content);
    
    $cachePath = 'storage/framework/views';
    foreach (glob($cachePath . '/*') as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') unlink($file);
    }
    
    $factory = app('view');
    $view = $factory->make('tmp_test.' . $path, []);
    
    try {
        $view->render();
        echo "  $label: SUCCESS\n";
    } catch (\Illuminate\View\ViewException $e) {
        if (str_contains($e->getMessage(), 'syntax error') || str_contains($e->getMessage(), 'unexpected')) {
            echo "  $label: PARSE ERROR - " . $e->getMessage() . "\n";
        } else {
            echo "  $label: RUNTIME ERROR - " . $e->getMessage() . "\n";
        }
    } catch (\Throwable $e) {
        echo "  $label: ERROR - " . get_class($e) . ": " . $e->getMessage() . "\n";
    }
    
    unlink($tmpPath);
    foreach (glob($cachePath . '/*') as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') unlink($file);
    }
}

// Test: forelse with @if inside, in card, in layouts.app
tryCompile('test1', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@if($i)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'forelse+if in card');

// Test: foreach with @if inside, in card, in layouts.app
tryCompile('test2', '<x-layouts.app title="T"><x-ui.card>@foreach($items as $i)@if($i)yes@endif@endforeach</x-ui.card></x-layouts.app>', 'foreach+if in card');

// Test: foreach WITHOUT @if, with @php inside, in card
tryCompile('test3', '<x-layouts.app title="T"><x-ui.card>@foreach($items as $i)@php $x=1; @endphp<span>{{$i}}</span>@endforeach</x-ui.card></x-layouts.app>', 'foreach+php in card');

// Test: foreach with @php and HTML content (no @if), in card
tryCompile('test4', '<x-layouts.app title="T"><x-ui.card>@foreach($items as $i)@php $x=1;@endphp<tr><td>{{$i}}</td></tr>@endforeach</x-ui.card></x-layouts.app>', 'foreach+php+tr in card');

// Test: forelse with @php and HTML content (no @if), in card
tryCompile('test5', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@php $x=1;@endphp<tr><td>{{$i}}</td></tr>@empty no @endforelse</x-ui.card></x-layouts.app>', 'forelse+php+tr in card');

// Test: forelse with @php, @if inside, in card (like test H but simpler)
tryCompile('test6', '<x-layouts.app title="T"><x-ui.card>@forelse($items as $i)@php $x=1;@endphp@if($x)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'forelse+php+if in card');

// Test: WITHOUT component nesting - just @forelse with @php and @if
tryCompile('test7', '@forelse($items as $i)@php $x=1;@endphp@if($x)yes@endif@empty no @endforelse', 'forelse+php+if no component');

// Test: Single @if + component, then @forelse+@php in another component
tryCompile('test8', '<x-layouts.app title="T">@if(true)<x-ui.alert>A</x-ui.alert>@endif<x-ui.card>@forelse($items as $i)@php $x=1;@endphp@if($x)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'alert + forelse+php+if');

// Test: Two @if + component, then @foreach+@php in card (NO @if inside loop)
tryCompile('test9', '<x-layouts.app title="T">@if($x)<x-ui.alert>A</x-ui.alert>@endif@if($y)<x-ui.alert>B</x-ui.alert>@endif<x-ui.card>@foreach($items as $i)@php $g=1;@endphp<span>{{$i}}</span>@endforeach</x-ui.card></x-layouts.app>', 'two ifs + foreach+php');

// Test: @if with @csrf inside + @forelse+@php in card (like show.blade)
tryCompile('test10', '<x-layouts.app title="T">@if(true)<form>@csrf<input></form>@endif<x-ui.card>@forelse($items as $i)@php $x=1;@endphp@if($x)yes@endif@empty no @endforelse</x-ui.card></x-layouts.app>', 'if+csrf + forelse+php+if');
