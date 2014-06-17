<?php

use Intervention\Image\ImageManagerStatic as Image;

$rootDir = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';

if (empty($_POST))
    die ('No Hack');

if (!file_exists($files = $rootDir.'/data/default_a4.json')) {
    die('no input data');
    exit;
}
$elt = $_POST['elt'];
$format = $_POST['format'];
$container = $_POST['container'];
$inputData = json_decode(file_get_contents($files),true);
# A4 (Mm = 210x297) : (Px : 596x842 en 72dpi) #
foreach ($elt as $key => $value) {
    //Unit [mm]
    $inputData[$key] = isset($inputData[$key]) ? array_merge($inputData[$key],$value) : $value;
}
if (file_put_contents($files, json_encode($inputData))){
    echo 'OK';
    $pdf = new TCPDF($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);
    // remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage();
    foreach ($inputData as $key => $value) {
        $x = isset($value['x']) ? $value['x'] : 0;
        $y = isset($value['y']) ? $value['y'] : 0;
        $z = isset($value['z']) ? $value['z'] : 0;
        $w = isset($value['w']) ? $value['w'] : 0;
        $h = isset($value['h']) ? $value['h'] : 0;
        // ROTATION SYNTAXE $pdf->Rotate($this->getRotate(), $this->getRotateOriX(), $this->getRotateOriY());
        switch ($value['type']) {
            case 'img':
                $imgPath = 'tmp/bar.jpg';
                $imgPic = Image::make($value['default'])->save($imgPath);
                $pdf->Image($imgPath,$x,$y,$w,$h,$type='', $link='', $align='', $resize=false, $dpi=300, $palign='T', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array());
                break;
            case 'qrcode':
                $imgPath = 'tmp/qrcode.jpg';
                $imgPic = Image::make($value['default'])->save($imgPath);
                $pdf->Image($imgPath,$x,$y,$w,$h,$type='', $link='', $align='', $resize=false, $dpi=300, $palign='T', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array());
                break;
            case 'txt':
                $color = isset($value['style']['color']) ? $value['style']['color'] : '#000000';
                $color = hex2RGB($color);
                $size = isset($value['style']['size']) ? $value['size'] : 12;
                $baseAlign = array('left'=> 'L','center'=>'C','right'=>'R','justify'=>'J');
                $pdf->SetFont('helvetica', $style='', $size=$size, $fontfile='', $subset='default', $out=true);
                $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
                $pdf->Text($x, $y, $value['default'], $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align=$baseAlign[$value['style']['align']], $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false) ;
                break;
            default:
                break;
        }
    }
    $pdf->Output('pdf/poc.pdf', 'F');
    
} else {
    echo 'KO';
}


/**
 * Convert a hexa decimal color code to its RGB equivalent
 *
 * @param string $hexStr (hexadecimal color value)
 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
 */                                                                                                 
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
    $rgbArray = array();
    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
        $colorVal = hexdec($hexStr);
        $rgbArray['r'] = 0xFF & ($colorVal >> 0x10);
        $rgbArray['g'] = 0xFF & ($colorVal >> 0x8);
        $rgbArray['b'] = 0xFF & $colorVal;
    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
        $rgbArray['r'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
        $rgbArray['g'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
        $rgbArray['b'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
    } else {
        return false; //Invalid hex color code
    }
    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}

