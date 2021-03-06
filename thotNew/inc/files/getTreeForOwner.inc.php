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

// on récupère l'arborescence à partir du fichier partagé $shareId
$shareId = isset($_POST['shareId']) ? $_POST['shareId'] : null;

$infos = $Files->getFileInfoByShareId($shareId, $acronyme);

$ds = DIRECTORY_SEPARATOR;
$proprio = $acronyme;
$path = $infos['path'];
$fileName = $infos['fileName'];
$root = INSTALL_DIR.$ds.'upload'.$ds.$proprio;
$originalPath = $path.$ds.$fileName;

// récupération de l'arborescence à partir des infos précédentes
$treeview = $Files->treeview($root, $path, $fileName, $originalPath);

require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = "../../templates";
$smarty->compile_dir = "../../templates_c";

$smarty->assign('tree', $treeview);
$smarty->assign('root', $root);
$smarty->assign('path', $path);

echo $smarty->fetch('files/treeview4Owner.tpl');
