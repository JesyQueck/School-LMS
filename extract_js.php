<?php
// Extract the script content from the Blade template
$blade = file_get_contents('backend/resources/views/admin/timetable/index.blade.php');

// Find the script tag content
preg_match('/<script>\s*\(function\(\)\s*\{.*?\}\)\(\);\s*<\/script>/s', $blade, $matches);
if (preg_match('/<script>\s*\(function\(\) \{\s*[\s\S]*?\}\)\(\);\s*<\/script>/', $blade, $matches)) {
    $js = $matches[0];
    // Remove HTML tags to get pure JS
    $js = str_replace(['<script>', '</script>'], '', $js);
    file_put_contents('check_drag.js', $js);
    echo "Extracted JS to check_drag.js\n";
} else {
    echo "Could not extract JS\n";
}
