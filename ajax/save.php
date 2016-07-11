<?php



$rootDir   = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir    = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';

//Data mock
require_once $rootDir . '/data/mock.php';

if (empty($_POST)) {
    die('No Hack');
}


$elt       = $_POST['json'];
$duplicate = isset($_POST['duplicate']) ? $_POST['duplicate'] : false;
$file      = $_POST['file'];
$idclient  = 8300487;
# A4 (Mm = 210x297) : (Px : 596x842 en 72dpi) #
if (!file_exists($files     = $rootDir . '/data/' . $file) && !file_exists($files     = $rootDir . '/data/perso/' . $file)) {
    die('no input data');
    exit;
}
$inputData = json_decode($elt, true);


if ($duplicate) {
    @mkdir($rootDir . '/data/perso');
    $files = $rootDir . '/data/perso/' . date('YmdHis') . '_a4.json';
}
if (file_put_contents($files, json_encode($inputData, JSON_PRETTY_PRINT))) {
    if (file_exists($pdfOuput = $rootDir . '/pdf/poc.pdf')) {
        unlink($pdfOuput);
    }
    $data64Png      = $_POST['img'];
    $imgName        = uniqid() . '.png';
    $tmpImgFilename = sys_get_temp_dir() . '/' . $imgName;
    $data    = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data64Png));
    file_put_contents($tmpImgFilename, $data);
    try {
        $content = "<page><img src='" . $tmpImgFilename . "'/></page>";
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', array(0, 0, 0, 0));
        $html2pdf->writeHTML($content);
        $html2pdf->Output($rootDir . '/tmp/out.pdf', 'F');
    } catch (Html2PdfException $e) {
        $formatter = new ExceptionFormatter($e);
        echo $formatter->getHtmlMessage();
    }
    echo 'OK';
} else {
    echo 'KO';
}