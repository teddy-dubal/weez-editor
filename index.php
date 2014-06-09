<?php

//

use Knp\Snappy\Pdf;

$rootDir = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

$time_start = microtime(true);
$loader = new Twig_Loader_Filesystem($rootDir . '/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => $rootDir . '/cache',
    'auto_reload' => true,
    'debug' => true
        ));

if (file_exists('pdf/poc.pdf')) {
    unlink('pdf/poc.pdf');
}

$snappy = new Pdf($vendorDir . 'bin/wkhtmltopdf-amd64');
$snappy->setOption('dpi', 300);
$snappy->setOption('margin-left', 0);
$snappy->setOption('margin-right', 0);
$snappy->setOption('margin-top', 0);
$snappy->setOption('margin-bottom', 0);
//$snappy->setOption('zoom', 2.09);
//$snappy->generateFromHtml($twig->render('index.twig', array('ev_nom' => 'Fabien')), 'poc.pdf');
//$snappy->generateFromHtml($twig->render('editor.twig', array('ev_nom' => 'Fabien')), 'poc.pdf');
$info = $snappy->generate('http://localhost:8888/editor.php?mode=cli', 'pdf/poc.pdf');
var_dump($info);
//$snappy->generate('http://fr.wikipedia.org/wiki/Rihanna', 'poc.pdf');
//echo $twig->display('index.twig', array('ev_nom' => 'Fabien'));
$time_end = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);

echo 'In ' . $execution_time . ' s' . PHP_EOL;
