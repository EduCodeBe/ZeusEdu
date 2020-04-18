<?php

require_once '../../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    die("<div class='alert alert-danger'>".RECONNECT.'</div>');
}

$User = $_SESSION[APPLICATION];
$acronyme = $User->getAcronyme();

require_once INSTALL_DIR.'/inc/classes/class.Files.php';
$Files = new Files();

$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : null;

$form = array();
parse_str($formulaire, $form);

$data = $Files->saveDataTravail($form, $acronyme);

$coursGrp = $data['coursGrp'];
$idTravail = $data['idTravail'];

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$listeEleves = $Ecole->listeElevesCours($coursGrp);
// préparation des casiers pour le dépôt des travaux
$nb = $Files->initTravauxEleves($acronyme, $idTravail, $listeEleves);

echo json_encode($data);
