<?php

require_once '../../config.inc.php';

session_start();

// définition de la class Application
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

require_once INSTALL_DIR.'/inc/classes/classEleve.inc.php';

$fragment = isset($_REQUEST['query']) ? $_REQUEST['query'] : null;

$fragment = trim($fragment);

$listeEleves = Eleve::searchEleve2($fragment);

echo json_encode($listeEleves);
