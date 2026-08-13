<?php

$code = file_get_contents(__DIR__.'/storage/framework/views/d735bfabf8b18dc42f992bd3a239c6bf.php');

// Count PHP alternative syntax: if(...): and endif;
preg_match_all('/if\s*\(.+?\)\s*:/', $code, $altIf);
preg_match_all('/endif\s*;/', $code, $altEndif);

echo "PHP alt-syntax 'if():' count: ".count($altIf[0])."\n";
echo "PHP alt-syntax 'endif;' count: ".count($altEndif[0])."\n";
echo 'Net: '.(count($altIf[0]) - count($altEndif[0]))."\n";

// Also count braces-style ifs that use { }
preg_match_all('/if\s*\(.+?\)\s*\{/', $code, $braceIf);
preg_match_all('/\}/', $code, $braceClose);
echo "\nBrace-style 'if(){' count: ".count($braceIf[0])."\n";
echo "Total '}' count: ".count($braceClose[0])."\n";

// Find the startSection/endsection
preg_match_all('/startSection/', $code, $startSection);
preg_match_all('/stopSection|endsection|stopSection/', $code, $stopSection);
preg_match_all('/startSection|endsection/', $code, $bothSection);
echo "\nstartSection/endsection: ".count($startSection[0]).'/'.count($stopSection[0])."\n";

// Count startComponent/endComponent
preg_match_all('/startComponent/', $code, $startComp);
preg_match_all('/renderComponent/', $code, $endComp);
echo "\nstartComponent:renderComponent = ".count($startComp[0]).':'.count($endComp[0])."\n";

// Check for the layouts.app - component open/close
preg_match_all('/resolve.*?components\.layouts\.app/', $code, $layoutOpen);
preg_match_all('/resolve.*?components\.layout\.footer/', $code, $layoutClose);
echo "\nLayout open: ".count($layoutOpen[0])."\n";
echo 'Layout close (footer): '.count($layoutClose[0])."\n";
