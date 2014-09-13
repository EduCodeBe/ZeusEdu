<?php
if ($userStatus != 'admin') die('get out of here');
$etape = isset($_POST['etape'])?$_POST['etape']:Null;

$smarty->assign('action',$action);
$smarty->assign('mode',$mode);

switch ($mode) {

	case 'images':
		$listeImages = $Bulletin->imagesPngBranches(200);
		$smarty->assign("listeImages", $listeImages);
		$smarty->assign("corpsPage", "imagesCours");
		$ok = 'ok';
		break;

	case 'resetSituations':
		switch ($etape) {
			case 'confirmer':
				$listeEleves = $Ecole->listeEleves();
				$Bulletin->archiveEleves(ANNEESCOLAIRE, $listeEleves);
				$Bulletin->archiveSituations(ANNEESCOLAIRE);
				$Bulletin->deleteSituations();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Toutes les Situations sont transférées")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','resetSituations');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;

	case 'resetHistorique':
		switch ($etape) {
			case 'confirmer':
				$listeEleves = $Ecole->listeEleves();
				$Bulletin->deleteHistoriques();
				$smarty->assign('message', array(
									'title'=>'Réinitialisation',
									'texte'=>'Tous les historiques sont effacés')
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','resetHistorique');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
			}
		break;
	case 'resetAttitudes':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetAttitudes();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Toutes les 'Attitudes' sont effacées")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','resetAttitudes');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;
	case 'resetCommentTitus':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetCommentTitus();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Tous les commentaires des titulaires sont effacés")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','commentTitus');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;
	case 'resetCoordin':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetCoordinateurs();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Toutes les notices 'Coordinateurs' aux bulletins sont effacées")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','resetCoordinateurs');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}

		break;
	case 'resetCommentProfs':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetCommentProfs();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Tous les commentaires aux bulletins sont effacés")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','commentProfs');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;
	case 'resetDetailsCotes':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetDetailsCotes();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Toutes les cotes du bulletin sont vidées")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','details');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;
	case 'initCarnet':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetCcotes();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Les carnets de cotes sont vidés")
									);
				break;
			default:
				$smarty->assign('etape','confirmer');
				$smarty->assign('type','carnet');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;

	case 'delPonderations':
		switch ($etape) {
			case 'confirmer':
				$Bulletin->resetPonderations();
				$smarty->assign("message", array(
									'title'=>"Réinitialisation",
									'texte'=>"Toutes les pondérations sont effacées")
									);
				break;
			default:
				$smarty->assign('type','ponderations');
				$smarty->assign('etape','confirmer');
				$smarty->assign('corpsPage','confirmReset');
				$ok = 'ok';
				break;
		}
		break;
	case 'ponderations':
		switch ($etape) {
			case 'enregistrer':
				$nbResultats = $Bulletin->enregistrerPonderations($_POST, NBPERIODES);
				$smarty->assign("message", array(
					'title'=>"Enregistrement",
					'texte'=>"$nbResultats ponderations initialisées")
					);
				$smarty->assign('etape',Null);  // pour pouvoir recommencer une pondération sur un autre groupe de cours
				break;
			default:
				$listeCoursGrp = $Ecole->listeCoursGrp($Ecole->listeNiveaux());
				$smarty->assign('etape','enregistrer');
				$smarty->assign("listeCoursGrp", $listeCoursGrp);
				$smarty->assign("nbPeriodes", NBPERIODES);
				$smarty->assign("corpsPage", "initPonderations");
				$ok='ok';
				break;
			}
		break;
	case 'verrous':
		$nb = $Bulletin->renewAllLocks();
		$smarty->assign("message",
						array(
							'title'=>"Initialisation des verrous",
							'texte'=>sprintf("%d verrous initialisés (ouverts)",$nb)
						));
		break;

	default:
		// wtf
		break;
}

if (!(isset($ok)))
	$smarty->assign('corpsPage','choixInit');

?>
