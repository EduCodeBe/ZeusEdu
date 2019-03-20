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

require_once INSTALL_DIR.'/inc/classes/class.Files.php';
$Files = new Files();

$n = isset($_POST['n']) ? $_POST['n'] : 0;
$coursGrp = isset($_POST['coursGrp']) ? $_POST['coursGrp'] : null;

// liste des compétences génériques pour ce cours
$listeCompetences = $Files->getCompetencesCoursGrp($coursGrp);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('n', $n+1);
$smarty->assign('listeCompetences', $listeCompetences);

echo $smarty->fetch('casier/newCompetence.tpl');
