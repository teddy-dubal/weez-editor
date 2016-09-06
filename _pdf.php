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
$format      = $inputData['format'];
$format_name = $format['name'];
$dimension   = $format['dimension'];
$pageWidth   = $dimension['mm']['width'];
$pageHeight  = $dimension['mm']['height'];
$rate        = $dimension['px']['width'] / $dimension['mm']['width'];
$fontFamily  = array();

$m          = current($mock);
$attributes = [
    'position'     => 'absolute',
    'top'          => '',
    'left'         => '',
    'width'        => '',
    'height'       => '',
    'color'        => 'black',
    'text-align'   => 'right',
    'fill-opacity' => '1',
    'font-size'    => '4mm',
    'font-style'   => 'normal',
    'font-family'  => '',
];
$inner      = '';
foreach ($object as $o) {
    $w = pixelToMm($o['width'], $rate) * $o['scaleX'];
    $h = pixelToMm($o['height'], $rate) * $o['scaleY'];
    $l = pixelToMm($o['bx'], $rate);
    $t = pixelToMm($o['by'], $rate);

    $elt_attributes = [
        'top'          => $t . 'mm',
        'left'         => $l . 'mm',
        'width'        => $w . 'mm',
        'height'       => $h . 'mm',
        'rotate'       => -$o['angle'],
        'fill-opacity' => $o['opacity'],
        'font-family'  => $o['fontFamily'],
    ];
    switch ($o['type']) {
        case 'textbox':
        case 'i-text':
            if (isset($o['fontFamily'])){
                $font = $o['fontFamily'];
                if ('bold' == $o['fontWeight']) {
                    $font .= 'b';
                }
                if ('italic' == $o['fontStyle']) {
                    $font .= 'i';
                }
                $fontFamily[] = $font;
            }
            $elt_attributes = array_merge($elt_attributes, [
                'color'      => $o['fill'],
                'text-align' => $o['textAlign'],
                'font-size'  => ($o['fontSize'] - 1) . 'px',
                'font-family'=> isset($font) ? $font : '',
            ]);
            $merge          = array_merge($attributes, $elt_attributes);
            $r              = '';
            foreach ($merge as $k => $v) {
                $r .= $k . ':' . $v . ';';
            }
            if (isset($m[$o['tag']])) {
                $o['text'] = $m[$o['tag']];
            }
            /*if ('italic' == $o['fontStyle']) {
                $o['text'] = '<i>' . $o['text'] . '</i>';
            }*/
//            $inner .= '<div style="border: solid 0.5mm red;' . $r . '">' . $o['text'] . '</div>' . PHP_EOL;
            $inner .= '<div style="' . $r . '">' . $o['text'] . '</div>' . PHP_EOL;
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
$html2pdf   = new HTML2PDF('P', array($pageWidth, $pageHeight), 'fr', true, 'UTF-8', [0, 0, 0, 0]);

foreach ($fontFamily as $fontName){
    /*
     * Si le fichier fontName.php n'est pas dans le dossier :
     * - Télécharger la font nécessaire sur fonts.google.com
     * - Déplacer le fichier font.ttf dans le dossier data/fonts du projet
     * - Dans le terminal, depuis la racine du projet : vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i data/fonts/font.ttf
     */
    $html2pdf->addFont($fontName,'','data/fonts/'.str_replace(" ", "", strtolower($fontName)).".php");
}

//$html2pdf->setModeDebug();
$html2pdf->WriteHTML($content);
$html2pdf->Output('exemple.pdf');
$time_end   = microtime(true);

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
