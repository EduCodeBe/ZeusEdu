<?php

require_once '../../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

session_start();
if (!(isset($_SESSION[APPLICATION]))) {
    die("<div class='alert alert-danger'>".RECONNECT.'</div>');
}

$classe = isset($_POST['classe']) ? $_POST['classe'] : null;
$date = isset($_POST['date']) ? $_POST['date'] : null;
$date = Application::dateMysql($date);

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

require_once INSTALL_DIR.'/inc/classes/classThot.inc.php';
$thot = new Thot();

$listeEleves = $Ecole->listeEleves($classe, 'groupe');
$listeRV = $thot->getRVeleve(array_keys($listeEleves), $date);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('listeEleves', $listeEleves);
$smarty->assign('listeRV', $listeRV);
$smarty->display('reunionParents/listeElevesDeployee.tpl');
