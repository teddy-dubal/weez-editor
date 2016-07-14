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
];
$inner      = '';
foreach ($object as $o) {
    $elt_attributes = [
        'top'        => pixelToMm($o['top']) . 'mm',
        'left'       => pixelToMm($o['left']) . 'mm',
        'width'  => pixelToMm($o['width']) . 'mm',
        'height' => pixelToMm($o['height']) . 'mm',
        'rotate' => -$o['angle'],
    ];
    var_dump($o['type']);
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
            $inner .= '<div style="' . $r . '">' . $o['text'] . '</div>' . PHP_EOL;
            break;
        case 'image':
            $merge = array_merge($attributes, $elt_attributes);
            $r              = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            $inner .= '<div style="' . $r . '"><img src="' . $o['src'] . '"/></div>' . PHP_EOL;
            break;
        case 'path-group':
            $merge = array_merge($attributes, $elt_attributes);
            $r     = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            $inner .= '<div style="' . $r . '"><img src="' . $o['src'] . '"/></div>' . PHP_EOL;
            break;
        default:
            break;
    }
}
echo '<pre>';
var_dump(htmlentities($inner));
echo '</pre>';
exit;
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

function pixelToMm($value) {
    $DPI  = 96;
    $rate = $DPI / 25.4;
    return $value / $rate;
}

echo 'In ' . $execution_time . ' s' . PHP_EOL;
