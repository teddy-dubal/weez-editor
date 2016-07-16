<?php

use Weez\Zpl\Constant\ZebraFont;
use Weez\Zpl\Constant\ZebraPPP;
use Weez\Zpl\Model\Element\ZebraBarCode39;
use Weez\Zpl\Model\Element\ZebraQrCode;
use Weez\Zpl\Model\Element\ZebraText;
use Weez\Zpl\Model\ZebraLabel;
use Weez\Zpl\Utils\ZplUtils;

$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';
require_once $rootDir . '/data/mock.php';

if (empty($_POST)) {
    die('No Hack');
}

$time_start = microtime(true);
$json_file  = isset($_POST['file']) ? $_POST['file'] : false;
$udata     = isset($_POST['udata']) ? $_POST['udata'] : [];
$format    = isset($_POST['format']) ? $_POST['format'] : '8x3';
$dimension = []; //Dimension en dot pour 203 dot per inch
switch ($format) {
    case '8x3':
        $dimension = ['width' => 1624, 'height' => 609.6];
        break;
    case '3x3':
        $dimension = ['width' => 632, 'height' => 696];
        break;
    default:
        break;
}
$fd         = $rootDir . '/data/perso/' . $json_file;
$inputData  = json_decode(file_get_contents($fd), true);
$object     = $inputData['objects'];
$m          = current($mock);
$attributes = [
    'position'   => 'absolute',
    'top'        => '',
    'left'       => '',
    'width'      => '',
    'height'     => '',
    'color'      => '',
    'rotation'   => 0,
    'text-align' => 'right',
    'font-size'  => '4mm',
    'font-style' => 'normal',
];
$inner      = '';
$zebraLabel = new ZebraLabel($dimension['width'], $dimension['height']);
$zebraLabel->setDefaultZebraFont(new ZebraFont(ZebraFont::ZEBRA_ZERO));
foreach ($object as $o) {
    $elt_attributes = [
        'top'    => $o['top'],
        'left'   => $o['left'],
        'width'  => $o['width'] * $o['scaleX'],
        'height' => $o['height'] * $o['scaleY'],
        'rotate' => -$o['angle'],
    ];
    switch ($o['type']) {
        case 'textbox':
        case 'i-text':
            $elt_attributes = array_merge($elt_attributes, [
                'font-size' => $o['fontSize'],
            ]);
            $r              = array_merge($attributes, $elt_attributes);
            if (isset($m[$o['tag']])) {
                $o['text'] = $m[$o['tag']];
            }
            $x = ZplUtils::convertPixelInDot($r['left'], ZebraPPP::DPI_300);
            $y = ZplUtils::convertPixelInDot($r['top'], ZebraPPP::DPI_300);
            $zebraLabel->addElement(new ZebraText($x, $y, $o['text'], $r['font-size']));
            break;
        case 'image':
            $r = array_merge($attributes, $elt_attributes);
            $x = ZplUtils::convertPixelInDot($r['left'], ZebraPPP::DPI_300);
            $y = ZplUtils::convertPixelInDot($r['top'], ZebraPPP::DPI_300);
            switch ($o['tag']) {
                case "qrcode":
                    $zebraLabel->addElement(new ZebraQrCode($x, $y, $m['barcode_id']));
                    //$inner .= '<qrcode style="' . $r . '" value="' . $m['barcode_id'] . '" ec="H"></qrcode>' . PHP_EOL;
                    break;
                case "barcode":
                    $w = ZplUtils::convertPixelInDot($r['width'], ZebraPPP::DPI_300);
                    $h = ZplUtils::convertPixelInDot($r['height'], ZebraPPP::DPI_300);
                    $zebraLabel->addElement(new ZebraBarCode39($x, $y, $m['barcode_id'], $h, $w, 2));
                    //$inner .= '<barcode style="' . $r . '" value="' . $m['barcode_id'] . '" type="EAN13"></barcode>' . PHP_EOL;
                    break;
                default:
                    //$inner .= '<div style="' . $r . '"><img src="' . $o['src'] . '"/></div>' . PHP_EOL;
                    break;
            }
            break;
        default:
            break;
    }
}
$time_end = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);
$result         = ['zpl' => $zebraLabel->getZplCode(), 'excTime' => $execution_time];
echo json_encode($result);
