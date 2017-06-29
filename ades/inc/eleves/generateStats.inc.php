<?php

require_once '../../../config.inc.php';

// définition de la class Application
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
// require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';

session_start();
if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

require_once INSTALL_DIR.'/inc/classes/classEleve.inc.php';

$module = $Application->getModule(3);
require_once INSTALL_DIR."/$module/inc/classes/classAdes.inc.php";
$Ades = new Ades();

// durée de validité pour les Cookies
$unAn = time() + 365 * 24 * 3600;
// $debut = isset($_POST['debut']) ? $_POST['debut'] : null;
$debut = Application::postOrCookie('debut', $unAn);
// $fin = isset($_POST['fin']) ? $_POST['fin'] : null;
$fin = Application::postOrCookie('fin', $unAn);
// $niveau = isset($_POST['niveau']) ? $_POST['niveau'] : null;
$niveau = Application::postOrCookie('niveau', $unAn);

// $classe = isset($_POST['classe']) ? $_POST['classe'] : null;
$classe = Application::postOrCookie('classe', $unAn);

$matricule = isset($_POST['matricule']) ? $_POST['matricule'] : null;

// génération pour un élève isolé, une classe ou le niveau d'étude
if ($matricule == Null) {
    if ($classe == Null) {
        $listeEleves = $listeEleves = $Ecole->listeElevesNiveaux($niveau);
        $groupe = sprintf('Niveau %d e', $niveau);
        }
        else {
            $listeEleves = $Ecole->listeEleves($classe, 'groupe');
            $groupe = 'Classe '.$classe;
        }
    }
    else {
        $eleve = Eleve::staticGetDetailsEleve($matricule);
        $listeEleves = array($matricule => $eleve);
        $groupe = $eleve['nom'].' '.$eleve['prenom'];
    }

$statistiques = $Ades->statistiques($listeEleves, $debut, $fin);

require_once INSTALL_DIR."/smarty/Smarty.class.php";
$smarty = new Smarty();
$smarty->template_dir = "../../templates";
$smarty->compile_dir = "../../templates_c";

$smarty->assign('debut', $debut);
$smarty->assign('fin', $fin);
$smarty->assign('niveau', $niveau);
$smarty->assign('classe', $classe);
$smarty->assign('matricule', $matricule);
$smarty->assign('groupe', $groupe);
$smarty->assign('statistiques', $statistiques);

echo $smarty->fetch('eleve/syntheseStats.tpl');
