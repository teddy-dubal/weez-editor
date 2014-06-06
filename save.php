<?php

use Intervention\Image\ImageManagerStatic as Image;




$rootDir = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

if (empty($_POST))
    die ('No Hack');

if (!file_exists($files = $rootDir.'/data/a4.json')) {
    die('no input data');
    exit;
}
$elt = $_POST['elt'];
$inputData = json_decode(file_get_contents($files),true);
foreach ($elt as $key => $value) {
    $inputData[$key] = array_merge($inputData[$key],$value);
}
if (file_put_contents($files, json_encode($inputData))){
    echo 'OK';
    $pdf = new Zend_Pdf();
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    $pdfPage = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
    $pdfWidth = $pdfPage->getWidth();
    $pdfHeight = $pdfPage->getHeight();
    
    foreach ($inputData as $key => $value) {
        switch ($value['type']) {
            case 'img':
                $imgPic = Image::make($value['type'])->save('tmp/bar.jpg');
//                var_dump($imgPic);
                //$image = Zend_Pdf_Image::imageWithPath($value['src']);
                //$pdfPage->drawImage($image, 100, 100, 400, 300);
                break;
            case 'txt':
                $color = isset($value['color']) ? $value['color'] : '#000000';
                $size = isset($value['size']) ? $value['size'] : 12;
                $x = isset($value['x']) ? $value['x'] : 0;
                $y = isset($value['y']) ? $value['y'] : 0;
                $z = isset($value['z']) ? $value['z'] : 0;
                $pdfPage->setFillColor(Zend_Pdf_Color_Html::color($color))
                    ->setFont($font, $size)
                    ->drawText($value['txt'], $x, $y);
                break;
            default:
                break;
        }
    }
    
    $pdf->pages[] = $pdfPage;
    $pdf->save('poc_zend.pdf');
} else {
    echo 'KO';
}
