<?php

$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';
require_once $rootDir . '/data/mock.php';

$json_file = $file      = isset($_GET['file']) ? $_GET['file'] : $defaultFile;
$udata     = isset($_POST['udata']) ? $_POST['udata'] : [];

$fd          = $rootDir . '/data/perso/' . $json_file;
$inputData   = json_decode(file_get_contents($fd), true);
$object      = $inputData['objects'];
$background  = $inputData['backgroundImage'];
$format      = $inputData['format'];
$format_name = $format['name'];
$dimension   = $format['dimension'];
$pageWidth   = $dimension['mm']['width'];
$pageHeight  = $dimension['mm']['height'];
$rate        = $dimension['px']['width'] / $dimension['mm']['width'];

$m          = current($mock);
$attributes = [
    'position'   => 'absolute',
    'top'        => '',
    'left'       => '',
    'width'      => '',
    'height'     => '',
    'color'      => 'black',
    'rotation'   => 0,
    'text-align' => 'right',
    'font-size'  => '4mm',
    'font-style' => 'normal',
];
$inner      = '';
foreach ($object as $o) {
    $w  = pixelToMm($o['width'], $rate) * $o['scaleX'];
    $h  = pixelToMm($o['height'], $rate) * $o['scaleY'];
    $l  = pixelToMm($o['left'], $rate);
    $t  = pixelToMm($o['top'], $rate);
    $cx = pixelToMm($o['cx'], $rate);
    $cy = pixelToMm($o['cy'], $rate);

    if ($o['angle'] % 360) {
        $t = $cy - $h / 2;
        $l = $cx - $w / 2;
    }
    if ($o['angle'] == 90 || $o['angle'] == 270) {
        $t = $cy - $w / 2;
        $l = $cx - $h / 2;
    }
    $elt_attributes = [
        'top'    => $t . 'mm',
        'left'   => $l . 'mm',
        'width'  => $w . 'mm',
        'height' => $h . 'mm',
        'rotate' => -$o['angle'],
    ];
    switch ($o['type']) {
        case 'textbox':
        case 'i-text':
            $elt_attributes = array_merge($elt_attributes, [
                'color'      => $o['fill'],
                'text-align' => $o['textAlign'],
                'font-size'  => $o['fontSize'],
            ]);
            $merge          = array_merge($attributes, $elt_attributes);
            $r              = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            if (isset($m[$o['tag']])) {
                $o['text'] = $m[$o['tag']];
            }
            if ('italic' == $o['fontStyle']) {
                $o['text'] = '<i>' . $o['text'] . '</i>';
            }
            $inner .= '<div style="border: solid 1mm red;' . $r . '">' . $o['text'] . '</div>' . PHP_EOL;
            break;
        case 'image':
            $merge = array_merge($attributes, $elt_attributes, ['color' => $o['fill']]);
            $r     = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            switch ($o['tag']) {
                case "qrcode":
                    $inner .= '<qrcode style="' . $r . '" value="' . $m['barcode_id'] . '" ec="H"></qrcode>' . PHP_EOL;
                    break;
                case "barcode":
//                    var_dump($r);
                    $inner .= '<div style="' . $r . '">' . PHP_EOL;
                    $inner .= '<barcode value="' . $m['barcode_id'] . '" type="EAN13"></barcode>' . PHP_EOL;
                    $inner .= '</div>' . PHP_EOL;
                    break;
                default:
                    $inner .= '<div style="' . $r . '"><img style="width:100%;height:100%" src="' . str_replace("http://localhost:8080/", "", $o['src']) . '"/></div>' . PHP_EOL;
                    break;
            }
            break;
    }
}
//echo '<pre>';
//var_dump(htmlentities($inner));
//echo '</pre>';
//exit;
$time_start = microtime(true);
$orientation = '';
$backimg  = '';
$backimgx = '';
$backimgy = '';
$backimgw = '';

if (isset($background)){
    $backimg  = str_replace("http://localhost:8080/","",$background['src']);
    $backimgx = 'left';
    $backimgy = 'top';
    $backimgw = $background['width'];
}
if ($format_name=='8x3'){
    $orientation = "paysage";
}

$content = "<page orientation='".$orientation."' backimg='".$backimg."' backimgx='".$backimgx."' backimgy='".$backimgy."' backimgw='".$backimgw."'>";
$content .= $inner;
$content .= "</page>";
$html2pdf = new HTML2PDF('P', array($pageWidth, $pageHeight), 'fr', true, 'UTF-8', [0, 0, 0, 0]);
//$html2pdf->setModeDebug();
$html2pdf->WriteHTML($content);
$html2pdf->Output('exemple.pdf');
$time_end = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);

function pixelToMm($value, $rate = 1) {
    return $value / $rate;
}

//function renderInner($s, $d = 1) {
//    if ($d) {
//        $s = '<div style="border: solid 1mm red">' . $s . '</div>';
//    }
//    return $s . PHP_EOL;
//}

echo 'In ' . $execution_time . ' s' . PHP_EOL;
