<?php

use Knp\Snappy\Pdf;
/**
 * lanch server 
 * php -S 0.0.0.0:8888
 */
$rootDir = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'web';
$time_start = microtime(true);
$loader = new Twig_Loader_Filesystem($rootDir . '/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => $rootDir . '/cache',
    'debug' => true
        ));

if (!file_exists($files = $rootDir.'/data/a4.json')) {
    die('no input data');
    exit;
}
$inputData = json_decode(file_get_contents($files),true);
//$snappy->generateFromHtml($twig->render('index.twig', array('ev_nom' => 'Fabien')), 'poc.pdf');
$modeToInclude = 'cli' == $mode ? 'core_a4.twig' : 'editor.twig';
echo $twig->render($modeToInclude, array(
    'mode' => $mode,
    'inputData' => $inputData,
));
