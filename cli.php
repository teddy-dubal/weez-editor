<?php

use Knp\Snappy\Image;

//


$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

$time_start = microtime(true);
$loader     = new Twig_Loader_Filesystem($rootDir . '/templates');
$twig       = new Twig_Environment($loader, array(
    'cache'       => $rootDir . '/cache',
    'auto_reload' => true,
    'debug'       => true
        ));

$imgSrc = 'pdf/img.jpg';
if (file_exists($imgSrc)) {
    unlink($imgSrc);
}

$time_end = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);

echo 'In ' . $execution_time . ' s' . PHP_EOL;
