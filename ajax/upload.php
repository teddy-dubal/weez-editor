<?php

$rootDir   = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir    = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';

$ds = DIRECTORY_SEPARATOR;  //1
$rpath       = '/data/img/';
$storeFolder = $rootDir . $rpath;  //2

if (!empty($_FILES)) {
    foreach ($_FILES["files"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["files"]["tmp_name"][$key];
            $name     = basename($_FILES["files"]["name"][$key]);
            move_uploaded_file($tmp_name, $storeFolder . $name);
            echo json_encode(['status' => 1, 'file' => $rpath . $name]);
        }
    }
}


