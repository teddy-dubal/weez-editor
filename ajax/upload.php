<?php

$rootDir   = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir    = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';

$ds = DIRECTORY_SEPARATOR;  //1
$rpath         = '/data/img/';
$bgrpath       = '/data/bg/';
$storeFolder   = $rootDir . $rpath;  //2  /weez-editor/data/img/
$storeBgFolder = $rootDir . $bgrpath; //   /weez-editor/data/bg/
$width         = (isset($_POST['width']))?$_POST['width']:'';
$height        = (isset($_POST['height']))?$_POST['height']:'';

if (!empty($_FILES)) {
    foreach ($_FILES["files"]["error"] as $key => $error) { //Processing Image file
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["files"]["tmp_name"][$key]; // /weez-editor/data/img/imageFile
            $name     = basename($_FILES["files"]["name"][$key]); // imageFile
            move_uploaded_file($tmp_name, $storeFolder . $name); // /weez-editor/data/img/imageFile
            echo json_encode(['status' => 1, 'file' => $rpath . $name]); // /weez-editor/data/img/imageFile
        }
    }
    foreach ($_FILES["bgFiles"]["error"] as $key => $error) { //Processing background file
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["bgFiles"]["tmp_name"][$key];
            $name     = basename($_FILES["bgFiles"]["name"][$key]);
            $image    = new Imagick($tmp_name);
            $image   -> setImageFormat("png");
            $image   -> setImageBackgroundColor('white');
            $image   -> setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $image   -> mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $image   -> setImageOpacity(0.5);
            $image   -> scaleImage($width, $height);
            $image   -> writeImage($tmp_name, true);
            file_put_contents($tmp_name, $image);
            move_uploaded_file($tmp_name, $storeBgFolder . $name);
            echo json_encode(['status' => 1, 'file' => $bgrpath . $name]);
        }
    }
}


