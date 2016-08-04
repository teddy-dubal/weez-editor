<?php



$rootDir   = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir    = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';

if (empty($_POST)) {
    die('No Hack');
}

$elt       = $_POST['json'];
$duplicate = isset($_POST['duplicate']) ? $_POST['duplicate'] : false;
$data_format = isset($_POST['format']) ? json_decode($_POST['format'], true) : ['format' => 'a4'];
$format      = $data_format['format'];
$file      = $_POST['file'];

if (!file_exists($files     = $rootDir . '/data/perso/' . $file)) {
    die('no input data');
    exit;
}
$inputData = array_merge(json_decode($elt, true), $data_format);

if ($duplicate) {
    @mkdir($rootDir . '/data/perso');
    $files = $rootDir . '/data/perso/' . date('YmdHis') . '_' . $format['name'] . '.json';
}
if (file_put_contents($files, json_encode($inputData, JSON_PRETTY_PRINT))) {
    echo 'OK';
} else {
    echo 'KO';
}