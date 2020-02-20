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

$notifId = isset($_POST['notifId']) ? $_POST['notifId'] : Null;

require_once INSTALL_DIR."/inc/classes/classThot.inc.php";
$Thot = new Thot();
$notification = $Thot->getNotification($notifId, $acronyme);

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$module = $Application->getModule(3);

// s'il s'agit d'une notification à une classe, un cours ou un élève isole (!), on cherche la liste des élèves
$type = $notification['type'];

$listeTypes = $Thot->getTypes();
// tous les types de destinataires sauf les élèves isolés
$selectTypes = $Thot->getTypes(true);

$listeNiveaux = $Ecole->listeNiveaux();
$listeClasses = $Ecole->listeClasses();
$listeCours = $User->getListeCours();

$userStatus = $User->userStatus($module);

// liste des élèves, pour mémoire...
$listeEleves = Null;

// est-ce une notification destinée à l'élève dont le matricule est le destinataire?
// if (preg_match('/^[\d\s]*$/', $notification['destinataire'])) {
if ($notification['matricule'] != '') {
    $listeEleves = $Ecole->detailsDeListeEleves($notification['matricule']);
    }
    else {
        // pour les entités 'coursGrp', 'classes' et 'eleves', on a besoin des élèves qui figurent dans ces groupes
        if (in_array($type, array('coursGrp', 'classes', 'groupe'))) {
            require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
            $Ecole = new Ecole();
            switch ($type) {
                case 'coursGrp':
                    $listeEleves = $Ecole->listeElevesCours($notification['destinataire']);
                    break;
                case 'classes':
                    $listeEleves = $Ecole->listeElevesClasse($notification['destinataire']);
                    break;
                case 'groupe':
                    // prévoir la liste des élèves du groupe
                    break;
            }
        }
    }

$stringDestinataire = Null;
switch ($type) {
    case 'ecole':
        $stringDestinataire = 'Tous';
        break;
    case 'niveau':
        $stringDestinataire = sprintf('%de année', $notification['destinataire']);
        break;
    case 'cours':
        $stringDestinataire = $notification['destinataire'];
        break;
    case 'coursGrp':
        // le destinataire est-il un matricule (numérique entier, donc) ?
        if (preg_match('#^[\d\s]*$#', $notification['destinataire'])){
            $stringDestinataire = sprintf('%s %s', current($listeEleves)['prenom'], current($listeEleves)['nom']);
            }
            else $stringDestinataire = $notification['destinataire'];
        break;
    case 'groupe':
        // le destinataire est-il un matricule (numérique entier, donc) ?
        if (preg_match('#^[\d\s]*$#', $notification['destinataire'])){
            $stringDestinataire = sprintf('%s %s', current($listeEleves)['prenom'], current($listeEleves)['nom']);
            }
            else $stringDestinataire = $notification['destinataire'];
        break;
    case 'classes':
        // le destinataire est-il un matricule (numérique entier, donc) ?
        if (preg_match('#^[\d\s]*$#', $notification['destinataire'])){
            $stringDestinataire = sprintf('%s %s', current($listeEleves)['prenom'], current($listeEleves)['nom']);
            }
            else $stringDestinataire = $notification['destinataire'];
        break;
    default:
        $stringDestinataire = 'wtf';
        break;
    }

// pièces jointes à la notification $notifId
$pjFiles = $Thot->getPj4Notifs($notifId, $acronyme);
// il suffit de prendre les PJ de la première notification (les autres sont les mêmes)
if ($pjFiles != Null)
    $pjFiles = current($pjFiles);

$selectedFiles = array();
foreach ($pjFiles as $oneFile) {
    $selectedFiles[] = $oneFile['path'].$oneFile['fileName'];
}

// ------------------------------------------------------------------------------
require_once INSTALL_DIR."/smarty/Smarty.class.php";
$smarty = new Smarty();
$smarty->template_dir = "../../templates";
$smarty->compile_dir = "../../templates_c";

$smarty->assign('type', $type);
$smarty->assign('edition', true);
$smarty->assign('destinataire', $notification['destinataire']);
$smarty->assign('stringDestinataire', $stringDestinataire);
$smarty->assign('niveau', $notification['niveau']);
$smarty->assign('notification', $notification);
$smarty->assign('listeEleves', $listeEleves);
$smarty->assign('notifId', $notifId);
// à destination du widget fileTree
$smarty->assign('pjFiles', $pjFiles);

$smarty->assign('selectedFiles', $selectedFiles);
$smarty->assign('selectTypes', $selectTypes);
$smarty->assign('listeNiveaux', $listeNiveaux);
$smarty->assign('listeCours', $listeCours);
$smarty->assign('userStatus', $userStatus);

echo $smarty->display('notification/edit/tabEdit.tpl');
