<?php

require_once '../../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

$module = $Application->getModule(3);

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();
if (!(isset($_SESSION[APPLICATION]))) {
    die("<div class='alert alert-danger'>".RECONNECT.'</div>');
}

$User = $_SESSION[APPLICATION];
$acronyme = $User->getAcronyme();

$shareId = isset($_POST['shareId'])?$_POST['shareId']:null;

require_once INSTALL_DIR.'/inc/classes/class.Files.php';
$Files = new Files();

$commentaire = $Files->getCommentaire($shareId, $acronyme);

echo $commentaire;