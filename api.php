<?php

$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

use PHPHtmlParser\Dom;

$time_start = microtime(true);

$dom       = new Dom;
$dom->loadFromUrl('http://localhost?mode=cli&file=20160710210128_a4.json');
$domBase64 = $dom->find('#base64')[0];
$inner     = $domBase64->text;
//var_dump($domBase64);
$time_end  = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);

echo 'In ' . $execution_time . ' s' . PHP_EOL;
