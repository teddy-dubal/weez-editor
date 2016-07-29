<?php

$rootDir   = __DIR__;
$vendorDir = $rootDir . '/vendor/';

require_once $vendorDir . 'autoload.php';
require_once $rootDir . '/data/mock.php';

$json_file = $file      = isset($_GET['file']) ? $_GET['file'] : $defaultFile;
$udata     = isset($_POST['udata']) ? $_POST['udata'] : [];

$fd         = $rootDir . '/data/perso/' . $json_file;
$inputData  = json_decode(file_get_contents($fd), true);
$object     = $inputData['objects'];
$format      = $inputData['format'];
$format_name = $format['name'];
$dimension   = $format['dimension'];
$rate        = $dimension['px']['width'] / $dimension['mm']['width'];

$m           = current($mock);
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
foreach ($object as $o) {
    $elt_attributes = [
        'top'    => pixelToMm($o['top'], $rate) . 'mm',
        'left'   => pixelToMm($o['left'], $rate) . 'mm',
        'width'  => pixelToMm($o['width'], $rate) * $o['scaleX'] . 'mm',
        'height' => pixelToMm($o['height'], $rate) * $o['scaleY'] . 'mm',
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
            $inner .= '<div style="' . $r . '">' . $o['text'] . '</div>' . PHP_EOL;
            break;
        case 'image':
            $merge = array_merge($attributes, $elt_attributes);
            $r     = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            switch ($o['tag']) {
                case "qrcode":
                    $inner .= '<qrcode style="' . $r . '" value="' . $m['barcode_id'] . '" ec="H"></qrcode>' . PHP_EOL;
                    break;
                case "barcode":
                    $inner .= '<barcode style="' . $r . '" value="' . $m['barcode_id'] . '" type="EAN13"></barcode>' . PHP_EOL;
                    break;
                default:
                    $inner .= '<div style="' . $r . '"><img src="' . str_replace("http://localhost:8080/","",$o['src']) . '"/></div>' . PHP_EOL;
                    break;
            }
            break;
        default:
            break;
    }
}
//echo '<pre>';
//var_dump(htmlentities($inner));
//echo '</pre>';
//exit;
$time_start = microtime(true);
$content    = "<page>";
$content .= $inner;
$content .= "</page>";
$html2pdf   = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', [0, 0, 0, 0]);
//$html2pdf->setModeDebug();
$html2pdf->WriteHTML($content);
$html2pdf->Output('exemple.pdf');
$time_end   = microtime(true);

$execution_time = number_format(($time_end - $time_start), 2);

function pixelToMm($value, $rate = 1) {
    return $value / $rate;
}

echo 'In ' . $execution_time . ' s' . PHP_EOL;
