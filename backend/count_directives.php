<?php

$code = file_get_contents(__DIR__.'/storage/framework/views/d735bfabf8b18dc42f992bd3a239c6bf.php');
$lines = explode("\n", $code);

$openIf = 0;
$closeIf = 0;
$push = 0;
$pop = 0;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    if (preg_match('/if\s*\(/', $line)) {
        $openIf++;
        echo 'IF at line '.($i + 1).': '.trim($line)."\n";
    }
    if (preg_match('/endif/', $line)) {
        $closeIf++;
    }
    if (preg_match('/startPush/', $line)) {
        $push++;
    }
    if (preg_match('/stopPush/', $line)) {
        $pop++;
    }
}

echo "\nTotal if(): $openIf\n";
echo "Total endif: $closeIf\n";
echo "Total startPush: $push\n";
echo "Total stopPush: $pop\n";
echo 'Net if: '.($openIf - $closeIf)."\n";
