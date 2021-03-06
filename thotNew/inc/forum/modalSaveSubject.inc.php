<?php

require_once '../../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

$User = $_SESSION[APPLICATION];
$acronyme = $User->getAcronyme();

$module = $Application->getModule(3);

$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : Null;
$form = array();
parse_str($formulaire, $form);

$idCategorie = isset($form['modalIdCategorie']) ? $form['modalIdCategorie'] : Null;
$idSujet = isset($form['modalIdSujet']) ? $form['modalIdSujet'] : Null;
$sujet = isset($form['modalSubject']) ? $form['modalSubject'] : Null;

$ds = DIRECTORY_SEPARATOR;
require_once INSTALL_DIR.$ds.$module.$ds.'inc/classes/class.thotForum.php';
$Forum = new thotForum();

$categorie = $Forum->getInfoCategorie($idCategorie);

if ($idSujet == Null) {
    // enregistrement du nouveau sujet
    $idSujet = $Forum->saveSubject($idCategorie, $sujet, $acronyme);
    }
    else {
        // modification du sujet existant
        $idSujet = $Forum->saveSubject($idCategorie, $sujet, $acronyme, $idSujet);
    }

// enregistrement des accès pour ce sujet
if ($idSujet != 0) {
    $listeProfs = isset($form['acronyme']) ? $form['acronyme'] : Null;
    if ($categorie['userStatus'] == 'profs')
        $tous = Null;
        else $tous = ($form['choixCibleEleve'] == 'tous') ? 'TOUS' : Null;
    $listeNiveaux = isset($form['niveau']) ? $form['niveau'] : Null;
    $listeClasses = isset($form['classe']) ? $form['classe'] : Null;
    $listeCoursGrp = isset($form['coursGrp']) ? $form['coursGrp'] : Null;
    $nb = $Forum->saveForumAcces($idSujet, $idCategorie, $tous, $listeProfs, $listeNiveaux, $listeClasses, $listeCoursGrp);
}

echo $idSujet;
