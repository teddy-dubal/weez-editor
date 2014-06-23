<?php

$rootDir = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';
require_once $vendorDir .'blueimp/jquery-file-upload/server/php/UploadHandler.php';

class WeezUploadHandler extends UploadHandler {
    protected function get_unique_filename($file_path, $name, $size, $type, $error, $index, $content_range) {
        return $name;
    }
}

$uploadDir = $rootDir . '/tmp/';

@mkdir($uploadDir);
$options = array("upload_dir" => $uploadDir,"upload_url"=>'/tmp/');
$upload_handler = new WeezUploadHandler($options);

