<?php

use Intervention\Image\ImageManagerStatic as Image;

$rootDir = __DIR__ . '/..';
$vendorDir = $rootDir . '/vendor/';
$tmpDir = sys_get_temp_dir() . '/';

require_once $vendorDir . 'autoload.php';
//Data mock
require_once $rootDir . '/data/mock.php';

if (empty($_POST)) {
    die('No Hack');
}


$elt = $_POST['elt'];
$format = $_POST['format'];
$container = $_POST['container'];
$duplicate = $_POST['duplicate'];
$file = $_POST['file'];
$idclient = 8300487;
# A4 (Mm = 210x297) : (Px : 596x842 en 72dpi) #
if (!file_exists($files = $rootDir . '/data/' . $file) && !file_exists($files = $rootDir . '/data/perso/' . $file)) {
    die('no input data');
    exit;
}
$inputData = json_decode(file_get_contents($files), true);
foreach ($elt as $key => $value) {
//Unit [mm]
    $inputData[$key] = isset($inputData[$key]) ? array_merge($inputData[$key], $value) : $value;
}

if ($duplicate) {
    @mkdir($rootDir . '/data/perso');
    $files = $rootDir . '/data/perso/' . date('YmdHis') . '_a4.json';
}
if (file_put_contents($files, json_encode($inputData))) {
    if (file_exists($pdfOuput = $rootDir . '/pdf/poc.pdf')) {
        unlink($pdfOuput);
    }
    $pdf = new TCPDF($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false);
// remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage();
    $baseAlign = array('left' => 'L', 'center' => 'C', 'right' => 'R', 'justify' => 'J');
    $baseFontStyle = array('bold' => 'B', 'italic' => 'C', 'bold_italic' => 'BI');
    foreach ($inputData as $key => $value) {
        $x = isset($value['x']) ? $value['x'] : 0;
        $y = isset($value['y']) ? $value['y'] : 0;
        $z = isset($value['z']) ? $value['z'] : 0;
        $w = isset($value['w']) ? $value['w'] : 0;
        $h = isset($value['h']) ? $value['h'] : 0;
        $color = isset($value['style']['color']) ? $value['style']['color'] : '#000000';
        $color = hex2RGB($color);
        $align = isset($value['style']['align']) ? $baseAlign[$value['style']['align']] : $baseAlign['left'];
        $data_src = isset($mock['data'][$idclient][$value['tag']]) ? $mock['data'][$idclient][$value['tag']] : $value['default'];
// ROTATION SYNTAXE $pdf->Rotate($this->getRotate(), $this->getRotateOriX(), $this->getRotateOriY());
        switch ($value['type']) {
            case 'img':
                $ext = pathinfo($data_src, PATHINFO_EXTENSION);
                $imgPath = $tmpDir . uniqid() . '.' . $ext;
                $imgPic = Image::make($data_src)->save($imgPath);
                $pdf->Image($imgPath, $x, $y, $w, $h, $type = '', $link = '', $align = $align, $resize = false, $dpi = 300, $palign = 'T', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
                break;
            case 'qrcode':
                $barcodeobj = new TCPDF2DBarcode($data_src, 'QRCODE,H');
                $imgPath = $tmpDir . uniqid() . '.png';
                $imgPic = Image::make($barcodeobj->getBarcodePngData(10, 10))->save($imgPath);
                $pdf->Image($imgPath, $x, $y, $w, $h, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = 'T', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
                break;
            case 'barcode':
                $barcodeobj = new TCPDFBarcode($data_src, 'C128');
                $imgPath = $tmpDir . uniqid() . '.png';
                $imgPic = Image::make($barcodeobj->getBarcodePngData(10, 10))->save($imgPath);
                $pdf->Image($imgPath, $x, $y, $w, $h, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = 'T', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
                break;
            case 'shape':
                $pdf->Line($x, $y, $x + $w, $y + $h, $style = array('color' => array($color['r'], $color['g'], $color['b'])));
                break;
            case 'txt':
                $size = isset($value['style']['size']) ? $value['style']['size'] : 12;
                $style = isset($baseFontStyle[$value['style']['font-style']]) ? $baseFontStyle[$value['style']['font-style']] : '';
                $pdf->setFont($value['style']['font'], $style = $style, $size = $size, $fontfile = '', $subset = 'default', $out = true);
                $pdf->SetTextColor($color['r'], $color['g'], $color['b']);
                $html = setFilter($data_src, $value['filter']);
                $pdf->MultiCell($w, $h, $html = $html, $border = 0, $align = $align, $fill = false, $ln = 1, $x, $y, $reseth = true, 0, false, $autopadding = true, $h, 'T', true);
                break;
            default:
                break;
        }
    }
    $pdf->Output($pdfOuput, 'F');
    echo 'OK';
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
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
{
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

function setFilter($src, $filters = array())
{
    $result = $src;
    foreach ($filters as $filter) {
        switch ($filter) {
            case 'upper':
                $result = strtoupper($result);
                break;
            default:
                break;
        }
    }
    return $result;
}

/*
  $params = array();
  $client = new Client();
  $tickets = $client->getClienEvenementTransactionsBillets(array('id_client' => array(8300487, 8300488)));
  foreach ($tickets as $data) {
  $ticket = new ModelTicket();
  $params[] = $ticket->getContentBilletPDF(true, $data['id_evenement'], $data['id_billet'], $data['id_transaction'], $data['id_client']);
  }
  $cfg['data'] = $params;
 */

/**
 * Get elements from table ClienEvenementTransactionsBillets according to criteria
 * Ex : $this->getClienEvenementTransactionsBillets(array('id_client' => array(8300487, 8300488)))
 * @param array $criteria
 * @return array
 * @throws \Exception
 */
/*
  public function getClienEvenementTransactionsBillets($criteria = array())
  {
  if (!is_array($criteria))
  throw new \Exception('You must supply an array');

  $sql = 'SELECT *
  FROM clients_evenement_transactions_billets
  WHERE ';
  $c = count($criteria);
  foreach ($criteria as $k => $v) {
  $sql .= $k . ' IN ( %s ) ';
  $sql = sprintf($sql, implode(',', $v));
  if ($c > 1)
  $sql .= ' OR ';
  }
  $sql = trim($sql, "OR ") . ';';
  if ($c)
  return $GLOBALS['CON']->fetchAll($GLOBALS['CON']->executeRequeteSM($sql));
  return array();
  }

  /**
 * return the data we need for rendering a PDF ticket
 * @param type $real_data
 * @param type $current_ev
 * @param type $id_billet
 * @param type $id_transaction
 * @param type $id_client
 * @param type $forceLang
 * @return type
 */
/*
function getContentBilletPDF($real_data, $current_ev, $id_billet = 0, $id_transaction = 0, $id_client = 0, $forceLang = false)
{
$transaction = new Transaction();
$transaction->load($id_transaction);

$lang = false;
if ($forceLang && is_numeric($forceLang)) {
$lang = $forceLang;
} elseif ($id_transaction > 0) {
$lang = $transaction->getIdLanguage();
}

if (is_numeric($current_ev)) {
$current_ev = new Evenement($current_ev);
}
$id_ev = $current_ev->getId();

$participant = new Participant();
$participant->load($id_client);
$participant->getBillet();

$acheteur = new Acheteur();
$acheteur->load($participant->getIdAcheteur());

$ticket = new Ticket();
$ticket->setId($id_billet);

$data_contact = $current_ev->getContactOrga();
$data_billeterie = $current_ev->getInfosEvenementBilleterie();
$data_evenement = $current_ev->getInfos();

$orga = new Organisateur($current_ev->getOrgaId());
$data_orga = $orga->getInfos();

$member_data = array();
if ($orga->isUsingMemberSubscriptions()) {
$member = new Member();
// this ticket represents an abonnement or a member
$member_data = $member->loadByClient($participant->getId());
if (!$member_data['id_member']) {
// this ticket belongs to a member
$member_data = $member->belongsToMember($participant->getIdBillet());

if (!$member_data['id_member']) {
// this ticket belongs to a member
$member_data = $member->abonnementBelongsToMember($participant->getId());
}
}
}

// TODO check if still in use with new guichet and replace with model
$data_billet = ($id_billet) ? $this->db->executeRequeteS(
(!isset($GLOBALS['guichet']) ||!$GLOBALS['guichet']->id_guichet) ?
'SELECT * FROM evenement_billet WHERE id_code='
. (int) $id_billet :
'SELECT t1.prix, t2.type_billet, t2.nb_billet_multiple, t2.nom, t2.description, t2.visuel_color, t2.visuel_image, t2.perso_all, t2.perso_couleur_texte, t2.perso_fichier_bg FROM guichet_billets AS t1 INNER JOIN evenement_billet AS t2 ON t1.id_billet=t2.id_code WHERE t1.id_guichet='
. (int) $GLOBALS['guichet']->id_guichet
. ' AND t1.id_billet='
. (int) $id_billet
) : array();

if ($real_data && $id_ev != $transaction->getEventId()) {
return;
}

// clean the data
foreach (array('data_billeterie', 'data_evenement', 'data_orga', 'data_billet', 'data_contact') as $data) {
if (is_array(${$data})) {
foreach (${$data} as &$v) {
$v = stripslashes((String) $v);
}
} else {
error_log('Not an array : ' . $data . '.Id_evenement: ' . $id_ev);
}
}
unset($v);

//-- Ticket price
$price = $participant->getAmountBeforeReduction() - $participant->getReduction($transaction);
if (!$real_data) {
$price = $ticket->getTicketPrice();
} else if ($participant->isGroupMaster()) {
$price /= $participant->getGroupSize();
} else if ($participant->isGroupSlave()) {
$participant_ref = new Participant();
$participant_ref->load($participant->findGroupMaster());
$price = ($participant_ref->getAmountBeforeReduction() - $participant_ref->getReduction($transaction)) / $participant_ref->getGroupSize();
}

$billet_frais = 0;
if ($real_data && $transaction->isWithCommission() && $price > 0) {
$CONF_COMMISSIONS = $current_ev->getCommissionInfo($transaction->data['type_carte'], $current_ev->getId(), $current_ev->getOrgaId(), $ticket->getId());
$billet_frais = Math::commission($CONF_COMMISSIONS, $price);
if ($price < $billet_frais) {
$price = $billet_frais;
}
}

$tva_rate = $transaction->getTaxRate($transaction->getData(), $current_ev->getTaxRate());
$billet_tva = Currency::formatDefault(Tax::calculate($price, $tva_rate, 1), $current_ev->getCurrencyId());
$billet_prix = Currency::formatDefault($price, $current_ev->getCurrencyId());
if (!$price) {
$billet_frais = $billet_tva = Currency::formatDefault(0, $current_ev->getCurrencyId());
$billet_prix = Language::get('off_widget_gratuit', $lang);
}

$billet_frais = Currency::formatDefault($billet_frais, $current_ev->getCurrencyId());

//-- Placement
if ($participant->getPlacementId() > 0) {
$placement = new Placement();
$seatLabel = $placement->getSeatLabel($participant->getPlacementId());
}

//-- Date commande
$date_commande = Date::formatDate($transaction->data['transaction_date'], false, $current_ev->getTimezoneOrga());

//-- Acheteur / Client : Prénom, Nom
$prenom_client = $participant->getFormattedFirstName();
$nom_client = $participant->getFormattedName();
$prenom_acheteur = $acheteur->getFormattedFirstName();
$nom_acheteur = $acheteur->getFormattedName();

if (empty($prenom_client) && empty($nom_client) && ($transaction->data['type_paiement'] != 7)) {
$prenom_client = $prenom_acheteur;
$nom_client = $nom_acheteur;
}

//-- if no data client or buyer get data member : Prénom, Nom
if ($member_data['identification_number']) {
$participant_member = new Participant();
$participant_member->load($member_data['id_client']);
$prenom_member = $member_data['identification_number'] . ' - ' . $participant_member->getFormattedFirstName();
$nom_member = $participant_member->getFormattedName();

$prenom_client = ($prenom_client == "" ? $prenom_member : $prenom_client);
$nom_client = ($nom_client == "" ? $nom_member : $nom_client);
}

//-- Guichet
$guichet_nom = '';
if ($transaction->data['guichet_id'] > 0) {
$pos = new Pos();
$pos->load($transaction->data['guichet_id']);
$guichet_nom = $pos->getName();
}

$lieu_ville = trim($data_evenement['cp'] . ' ' . $data_evenement['ville']);
if ($current_ev->getRegion() == 'canada') {
$lieu_ville = trim($data_evenement['ville'] . ' ' . $data_evenement['province'] . ' ' . $data_evenement['cp']);
}

if ($real_data) {
$conf = array(
'barcode_id' => $participant->billet['barcode_id'],
 'client_id' => $id_client,
 'acheteur_id' => $acheteur->getId(),
 'ev_id' => $id_ev,
 'ev_nom' => $current_ev->getNom(),
 'lieu_nom' => $data_evenement['lieu'],
 'lieu_adresse' => $data_evenement['adresse'],
 'lieu_ville' => $lieu_ville,
 'billet_nom' => $data_billet['nom'],
 'billet_categorie' => $current_ev->getInfosTicketCategory($id_billet),
 'cmd_client' => $prenom_client . ' ' . $nom_client,
 'cmd_client_prenom' => $prenom_client,
 'cmd_client_nom' => $nom_client,
 'billet_prix' => $billet_prix,
 'billet_frais' => $billet_frais,
 'billet_num' => $participant->billet['id_weez_ticket'],
 'id_orga' => $data_orga['id_organisateur'],
 'ev_orga' => $current_ev->getNomOrga(0, $data_orga),
 'orga_license' => $current_ev->getLicenseOrga($data_orga),
 'cmd_acheteur' => $prenom_acheteur . ' ' . $nom_acheteur,
 'cmd_acheteur_prenom' => $prenom_acheteur,
 'cmd_acheteur_nom' => $nom_acheteur,
 'cmd_date' => $date_commande,
 'cmd_num' => $transaction->getNumber(),
 'currency_id' => $transaction->getCurrencyId(),
 'billet_desc' => String::clean_pdf($data_billet['description']),
 'ev_msg' => $data_billeterie['text_confirmation_inscription'],
 'ev_url' => empty($data_evenement['site_web']) ? $current_ev->makeURL() : String::uniformUrl($data_evenement['site_web']),
 'billet_place' => $seatLabel,
 'paye' => $transaction->isPayed(),
 'guichet_nom' => $guichet_nom,
 'answers' => $participant->getAllAnswersByParticipant($id_client, $acheteur->getId()),
 'member_number' => $member_data['identification_number'],
 'id_transaction_billet' => $participant->getIdBillet(),
);
if (isset($GLOBALS['guichet']) && $GLOBALS['guichet']->id_guichet) {
$data_guichet = $this->db->executeRequeteS('SELECT mess_confirmation FROM guichet WHERE id_guichet=' . (int) $GLOBALS['guichet']->id_guichet);
if ($data_guichet['mess_confirmation']) {
$conf['ev_msg'] = stripslashes($data_guichet['mess_confirmation']);
}
}
$conf['ev_email'] = $data_contact['email'];
} else {
$conf = array(
'barcode_id' => '133700',
 'client_id' => '0',
 'acheteur_id' => '0',
 'ev_nom' => $current_ev->getNom(),
 'lieu_nom' => $data_evenement['lieu'],
 'lieu_adresse' => $data_evenement['adresse'],
 'lieu_ville' => $lieu_ville,
 'billet_nom' => $data_billet['nom'],
 'billet_categorie' => $current_ev->getInfosTicketCategory($id_billet),
 'cmd_client' => 'Prénom NOM',
 'billet_prix' => $billet_prix,
 'billet_frais' => 'x' . Currency::getCurrencySymbol($current_ev->getCurrencyId()),
 'billet_desc' => String::clean_pdf($data_billet['description']),
 'ev_msg' => $data_billeterie['text_confirmation_inscription'],
 'ev_url' => empty($data_evenement['site_web']) ? $current_ev->makeURL() : String::uniformUrl($data_evenement['site_web']),
 'ev_email' => $data_contact['email'],
 'billet_num' => 'T0E' . $current_ev->getId() . 'O' . $data_evenement['id_organisateur'],
 'ev_orga' => $current_ev->getNomOrga(0, $data_orga),
 'orga_license' => $current_ev->getLicenseOrga($data_orga),
 'cmd_acheteur' => 'Prénom NOM',
 'cmd_date' => Date::formatDate(Date::currentIso(), false, $current_ev->getTimezoneOrga()),
 'cmd_num' => 'C0E' . $id_ev . 'O' . $data_evenement['id_organisateur'],
 'currency_id' => $current_ev->getCurrencyId(),
 'guichet_nom' => $guichet_nom,
 'answers' => array(),
 'member_number' => 'ABC01',
 'id_transaction_billet' => $participant->getIdBillet(),
);
}

$conf['address'] = $conf['lieu_nom'] . ($conf['lieu_adresse'] ? ' | ' . $conf['lieu_adresse'] : '') . ($conf['lieu_ville'] ? ' | ' . $conf['lieu_ville'] : '');
$conf['ev_orga_txt'] = Language::get('pdf_billet_orga', $lang) . ' ' . $conf['ev_orga'] . ($conf['orga_license'] ? ' - ' . $conf['orga_license'] : '');
$conf['ev_tva'] = array($tva_rate, $billet_tva, Tax::getTitleShortById($current_ev->getTaxId()));
$conf['ev_tva_txt'] = !$tva_rate ? '' : sprintf(Language::get('pdf_billet_tva_info', $lang), $conf['ev_tva'][2], $conf['ev_tva'][0], $conf['ev_tva'][1]);
$conf['cmd_date_txt'] = Language::get('pdf_billet_txn_date', $lang) . ' ' . $conf['cmd_date'];
$conf['cmd_num_txt'] = Language::get('pdf_billet_txn_id', $lang) . ' ' . $conf['cmd_num'];
$conf['billet_num_txt'] = Language::get('pdf_billet_numero', $lang) . ' ' . $conf['billet_num'];
$conf['billet_prix_txt'] = $current_ev->showCommissions() ? sprintf(Language::get('pdf_billet_prix_comm', $lang), $conf['billet_frais'], $conf['billet_prix']) : sprintf(Language::get('pdf_billet_prix', $lang), $conf['billet_prix']);
$conf['billet_place_txt'] = $conf['billet_place'] ? 'Place : ' . $conf['billet_place'] : '';

// Visuel des billets
// Rétro-compatibilité
$conf['default_color'] = $conf['default_image'] = false;
if ($data_billet['visuel_color'] === '' && $data_billet['visuel_image'] === '' && $data_billet['perso_all'] != '2') {
$conf['default_color'] = $conf['default_image'] = true;
if ($data_billeterie['visuel_color']) {
$conf['visuel_color'] = $data_billeterie['visuel_color'];
}
if ($data_billeterie['visuel_image']) {
$conf['visuel_image'] = bo . '../uploads/evenement_billet/' . $data_billeterie['visuel_image'];
}
}

if (!isset($conf['visuel_color']) && $data_billet['visuel_color'] === '' && $data_billet['perso_couleur_texte'] != 0) {
$conf['visuel_color'] = ($data_billet['perso_couleur_texte'] == 1) ? 'ffffff' : '000000';
}
if (!isset($conf['visuel_image']) && $data_billet['visuel_image'] === '' && $data_billet['perso_fichier_bg'] != '') {
$conf['visuel_image'] = bo . '../uploads/temp_up/' . (substr($data_billet['perso_fichier_bg'], 0, 1) == '/' ? substr($data_billet['perso_fichier_bg'], 1) : $data_billet['perso_fichier_bg']);
}

// NEW system starting here

if (!isset($conf['visuel_color'])) {
if ($data_billet['visuel_color']) {
$conf['visuel_color'] = $data_billet['visuel_color'];
} else {
$conf['default_color'] = true;
$conf['visuel_color'] = ($data_billeterie['visuel_color']) ? $data_billeterie['visuel_color'] : '#000';
}
}
if (!isset($conf['visuel_image'])) {
if ($data_billet['visuel_image']) {
$conf['visuel_image'] = bo . '../uploads/evenement_billet/' . $data_billet['visuel_image'];
} else {
$conf['default_image'] = true;
$conf['visuel_image'] = ($data_billeterie['visuel_image']) ? bo . '../uploads/evenement_billet/' . $data_billeterie['visuel_image'] : ($real_data ? '' : bo . 'images/PDF_Billet/front_billet.jpg');
}
}

if (!file_exists($conf['visuel_image'])) {
$conf['visuel_image'] = ($real_data ? '' : bo . 'images/PDF_Billet/front_billet.jpg');
}

if ($data_billet['visuel_date'] > 0) {
$conf['visuel_date'] = $data_billet['visuel_date'];
} else {
$conf['default_date'] = true;
$conf['visuel_date'] = $data_billeterie['visuel_date'] > 0 ? $data_billeterie['visuel_date'] : 1;
}

// which date do we show and how?
// by default, take the event date info
$data_date = $data_evenement;
if ($participant->billet['id_date']) {
// overwrite with seance date info, take the same format
$seance = new EvenementDates();
$seanceData = $seance->getDateById($participant->billet['id_date']);
$data_date = array(
'date_debut' => $seanceData['start_date'],
 'heure_debut' => Date::extractHour($seanceData['start_date']),
 'minute_debut' => Date::extractMinute($seanceData['start_date'])
);
}
$conf['ev_dates'] = Date::formatPeriod($data_date, (int) $conf['visuel_date']);
$conf['region'] = $current_ev->getRegion();
$conf['cgu'] = ($current_ev->infosEvenement['config_marque_blanche']['use_cgv_weez_in_pdf']) ? str_replace(
'Weezevent', $current_ev->infosEvenement['config_marque_blanche']['text_propulse_par'], Language::get('pdf_billet_cgu', $lang)
) : $current_ev->infosEvenement['config_marque_blanche']['cgv_in_pdf'];

if (empty($conf['cgu'])) {
$conf['cgu'] = Language::get('pdf_billet_cgu', $lang);
}
$conf['id_lang'] = (int) $lang;
// TODO : remove perso stuff here, use generic functions instead (getMailData for example)
// Perso data
//--Get society in invitation table
if (in_array($id_ev, array(28210))) {
$id_trans_billet = $participant->billet['id_transaction_billet'];
$data_perso = $GLOBALS['CON']->executeRequeteS('SELECT societe FROM agence_commande_invitation WHERE id_transaction_billet=' . (int) $id_trans_billet);
$conf['answers'][9] = $data_perso['societe'];
}

// omnivore 2014
if (in_array($id_ev, array(49852))) {
// the custom quesion is the most important
$soc = $conf['answers'][279529];
// if empty, get invitation platform societe
if (!$soc) {
$id_trans_billet = $participant->billet['id_transaction_billet'];
$data_perso = $GLOBALS['CON']->executeRequeteS('SELECT societe FROM agence_commande_invitation WHERE id_transaction_billet=' . (int) $id_trans_billet);
$soc = $data_perso['societe'];
}
if (!$soc) {
$soc = $conf['answers'][9];
}
$conf['answers'][9] = $soc;
}

//--Omnivore 2013
if (in_array($id_ev, array(28205, 28210))) {
// Get society in personnalized answers
if ($conf['answers'][103933]) {
$conf['answers'][9] = $conf['answers'][103933];
} elseif ($conf['answers'][103985]) {
$conf['answers'][9] = $conf['answers'][103985];
}
}

// Trim spaces
$conf = String::trim_array($conf);

return $conf;
}

*/
