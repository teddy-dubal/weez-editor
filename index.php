<?php

/**
 * lanch server
 * php -S 0.0.0.0:8888
 */
$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';
@mkdir($rootDir . '/data/perso');
$time_start  = microtime(true);
$loader      = new Twig_Loader_Filesystem($rootDir . '/templates');
$twig        = new Twig_Environment($loader, array(
//    'cache' => $rootDir . '/cache',
    'debug' => true
        ));
$defaultFile = 'default_a4.json';
$json_file   = $file        = isset($_GET['file']) ? $_GET['file'] : $defaultFile;
$udata       = isset($_POST['udata']) ? $_POST['udata'] : [];
$persofiles  = array_merge(array($defaultFile), array_diff(scandir($rootDir . '/data/perso'), array(
    '..', '.')));
if (!file_exists($files       = $rootDir . '/data/' . $defaultFile) && !file_exists($files       = $rootDir . '/data/perso/' . $file)) {
    die('no input data');
    exit;
}
$fd = $rootDir . '/data/';
if (isset($_GET['file'])) {
    if ($_GET['file'] != $defaultFile) {
        $fd = $rootDir . '/data/perso/';
    }
} else {
    if (count($persofiles) > 1) {
        $fd = $rootDir . '/data/perso/';
    }
}
$mode          = isset($_GET['mode']) ? $_GET['mode'] : 'web';
$files         = $fd . $persofiles[count($persofiles) - 1];
$inputData     = json_decode(file_get_contents($files), true);
$modeToInclude = 'cli' == $mode ? 'cli.twig' : 'editor.twig';
echo $twig->render($modeToInclude, array(
    'persoFiles' => $persofiles,
    'json_file'  => $json_file,
    'udata'      => $udata,
));
