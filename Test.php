<?php
require('vendor/autoload.php');
use JShrink\Minifier as JShrink;


$jsFilePath = 'script.js';

// Đọc và minify JS
$jsContent = file_get_contents($jsFilePath);
$minifiedJs = JShrink::minify($jsContent);
echo '<textarea readonly rows="10" style="width: 100%">' . $minifiedJs . '</textarea>';
