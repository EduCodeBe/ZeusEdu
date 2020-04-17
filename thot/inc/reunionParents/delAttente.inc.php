<?php
require_once("../../../config.inc.php");

session_start();

$matricule = isset($_POST['matricule'])?$_POST['matricule']:Null;
$acronyme = isset($_POST['acronyme'])?$_POST['acronyme']:Null;
$date = isset($_POST['date'])?$_POST['date']:Null;
$periode = isset($_POST['periode'])?$_POST['periode']:Null;

require_once(INSTALL_DIR.'/inc/classes/classApplication.inc.php');
$Application = new Application();

require_once(INSTALL_DIR.'/inc/classes/classThot.inc.php');
$thot = new Thot();
$nb = $thot->delListeAttenteProf($matricule, $acronyme, $date, $periode);

// récupérer la liste d'attente restante
$listeAttente = $thot->getListeAttenteProf($date, $acronyme);

require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = "../templates";
$smarty->compile_dir = "../templates_c";

$smarty->assign('listeAttente',$listeAttente);
$smarty->assign('acronyme',$acronyme);
$smarty->assign('date',$date);
$smarty->assign('periode',$periode);
$smarty->display('../../templates/reunionParents/listeAttenteAdmin.tpl');
