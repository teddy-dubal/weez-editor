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
$format    = isset($_POST['format']) ? $_POST['format'] : 'a4';
$file      = $_POST['file'];

if (!file_exists($files     = $rootDir . '/data/perso/' . $file)) {
    die('no input data');
    exit;
}
$inputData = json_decode($elt, true);


if ($duplicate) {
    @mkdir($rootDir . '/data/perso');
    $files = $rootDir . '/data/perso/' . date('YmdHis') . '_' . $format . '.json';
}
if (file_put_contents($files, json_encode($inputData, JSON_PRETTY_PRINT))) {
    echo 'OK';
} else {
    echo 'KO';
}