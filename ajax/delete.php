<?php

$rootDir = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';

if (empty($_POST)) {
    die('No Hack');
}

$file = $_POST['file'];

if (strpos($file, 'default') !== false) {
    echo json_encode(array('status' => 0, 'msg' => 'delete.default.ko'));
    exit();
}
unlink($rootDir . '/data/perso/' . $file);

echo json_encode(array('status' => 1, 'msg' => 'delete.file.ok'));
