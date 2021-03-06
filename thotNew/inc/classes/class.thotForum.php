<?php

/**
 *
 */
class ThotForum
{

    function __construct()
    {
        // code...
    }

    /**
     * constuit l'arbre correspondant à la structure linéaire extraite de la  BD
     * @param  array   $elements tableau linéaire des catégories
     * @param  integer $parentId identifiant du parent de l'élément actuellement examiné
     * @return array    arborescence des catégories
     */
    private function buildTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['parentId'] == $parentId) {
            $children = $this->buildTree($elements, $element['idCategorie']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
            }
        }

    return $branch;
    }

    /**
     * recherche la liste des catégories pour le niveau d'utilisateur précisé
     *
     * @param string $userStatus
     *
     * @return array
     */
    public function getListeCategories($userStatus = Null) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        if ($userStatus != Null)
            $sql .= 'WHERE userStatus = :userStatus ';
        $sql .= 'ORDER BY userStatus ';

        $requete = $connexion->prepare($sql);

        if ($userStatus != Null)
            $requete->bindParam(':userStatus', $userStatus, PDO::PARAM_STR, 7);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $liste[$idCategorie] = $ligne;
            }
        }
        $tree = $this->buildTree($liste);

        Application::deconnexionPDO($connexion);

        return $tree;
    }

    /**
     * renvoie un tableau de toutes les catégories existantes
     *
     * @param void
     *
     * @return array
     */
    public function getAllCategories(){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();

        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idCategorie = $ligne['idCategorie'];
                $liste[$idCategorie] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }


    /**
     * renvoie le nombre de sujets pour chaque catégorie
     *
     * @param void | $idCategorie
     *
     * @return array
     */
    public function countSubjects4categories($idCategorie = Null){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, COUNT(*) AS nb ';
        $sql .= 'FROM '.PFX.'thotForumsSujets ';
        if ($idCategorie != Null) {
            $sql .= 'WHERE idCategorie = :idCategorie ';
        }
        $sql .= 'GROUP BY idCategorie ';
        $requete = $connexion->prepare($sql);

        if ($idCategorie != Null) {
            $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        }

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $liste[$idCategorie] = $ligne['nb'];
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les informations sur la catégorie $idCategorie
     *
     * @param int $idCategorie
     *
     * @return array
     */
    public function getInfoCategorie($idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $categorie = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $categorie = $requete->fetch();
        }

        Application::deconnexionPDO($connexion);

        return $categorie;
    }

    /**
     * renvoie les informations générales sur le sujet dont on fournit le $idSujet
     * pour la catégorie $idCategorie
     *
     * @param int $idSujet
     *
     * @return array
     */
    public function getInfoSujet($idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT sujets.idCategorie, idsujet, sujet, sujets.acronyme, dateCreation, libelle, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%d/%m/%Y") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure, ';
        $sql .= 'sexe, nom, prenom ';
        $sql .= 'FROM '.PFX.'thotForumsSujets AS sujets ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = sujets.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = sujets.acronyme ';
        $sql .= 'WHERE idSujet = :idSujet AND sujets.idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $sujet = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $sujet = $requete->fetch();
            $sujet['nomProf'] = $this->nomProf($sujet['sexe'], $sujet['prenom'], $sujet['nom']);
        }

        Application::deconnexionPDO($connexion);

        return $sujet;
    }

    /**
     * renvoie les caractéristiques d'un post dont on fournit $postId, $idCategorie, $idSujet
     *
     * @param int $idCategorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return array
     */
    public function getInfoPost($idCategorie, $idSujet, $postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, posts.idCategorie, libelle, posts.idSujet, sujet, posts.parentId, ';
        $sql .= 'DATE_FORMAT(date, "%d/%m/%Y") AS ladate, DATE_FORMAT(date, "%H:%i") AS heure, ';
        $sql .= 'auteur, posts.userStatus, post, modifie, ';
        $sql .= 'profs.sexe AS sexeProf, profs.nom AS nomProf, profs.prenom AS prenomProf, ';
        $sql .= 'de.groupe, de.nom AS nomEleve, de.prenom AS prenomEleve ';
        $sql .= 'FROM '.PFX.'thotForumsPosts AS posts ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = posts.idCategorie ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idCategorie = posts.idCategorie AND sujets.idSujet = posts.idSujet ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = auteur ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = auteur ';
        $sql .= 'WHERE postId = :postId AND posts.idSujet = :idSujet AND posts.idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            if ($ligne) {
                $ligne['post'] = strip_tags($ligne['post'],'<http>');
                $userStatus = $ligne['userStatus'];
                if ($ligne['userStatus'] == 'prof') {
                    $appel = ($ligne['sexeProf'] == 'M') ? 'M.' : 'Mme';
                    $user = sprintf('%s %s. %s', $appel, mb_substr($ligne['prenomProf'], 0, 1), $ligne['nomProf']);
                    }
                    else {
                        $user = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                    }
                $ligne['from'] = $user;
            }
        }

        Application::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * Vérifie si une catégorie du nom $libelle existe déjà comme fils direct de la catégorie $parentId
     *
     * @param int $parentId
     * @param int $libelle
     *
     * @return int|Null
     */
    public function categorieAlreadyExists ($parentId, $libelle, $userStatus){
        $libelle = strtolower(trim($libelle));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(*) AS nb ';
        $sql .= 'FROM '.PFX.'thotForums ';
        $sql .= 'WHERE LOWER(libelle) = :libelle ';
        $sql .= 'AND parentId = :parentId AND userStatus = :userStatus ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':libelle', $libelle, PDO::PARAM_STR, 40);
        $requete->bindParam(':parentId', $parentId, PDO::PARAM_INT);
        $requete->bindParam(':userStatus', $userStatus, PDO::PARAM_STR, 6);

        $resultat = $requete->execute();
        $requete->setFetchMode(PDO::FETCH_ASSOC);

        $ligne = $requete->fetch();

        Application::deconnexionPDO($connexion);

        return $ligne['nb'];
    }

    /**
     * enregistre les informations relatives à une Catégorie
     *
     * @param int $parentId
     * @param string $libelle
     * @param string users : all ou profs only
     * @param int $idCategorie (seulement s'il s'agit d'une édition)
     *
     * @return int : 0 ou l'identifiant de l'enregistrement
     */
    public function saveCategorie($parentId, $libelle, $userStatus, $idCategorie = Null) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        if ($idCategorie == Null) {
            $sql = 'INSERT INTO '.PFX.'thotForums ';
            $sql .= 'SET libelle = :libelle, parentId = :parentId, userStatus = :userStatus ';
            }
            else {
                $sql = 'UPDATE '.PFX.'thotForums ';
                $sql .= 'SET libelle = :libelle, parentId = :parentId, userStatus = :userStatus ';
                $sql .= 'WHERE idCategorie = :idCategorie ';
            }
        $requete = $connexion->prepare($sql);

        $libelle = trim($libelle);
        $requete->bindParam(':userStatus', $userStatus, PDO::PARAM_STR, 7);
        $requete->bindParam(':libelle', $libelle, PDO::PARAM_STR, 40);
        $requete->bindParam(':parentId', $parentId, PDO::PARAM_INT);
        if ($idCategorie != Null) {
            $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        }

        $resultat = $requete->execute();

        if ($idCategorie == Null)
            $resultat = $connexion->lastInsertId();
            else $resultat = $idCategorie;


        Application::deconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie la liste des ascendants jusqu'à la racine pour une catégorie donnée
     *
     * @param int $idCategorie
     *
     * @return array
     */
    public function ancestors4categorie($idCategorie) {
        $listeCategories = $this->getAllCategories();
        $ancestors = array();
        do {
            $parentId = $listeCategories[$idCategorie]['parentId'];
            array_push($ancestors, $parentId);
            $idCategorie = (int)$parentId;
            }
            while ($parentId != 0);

        return array_reverse($ancestors);
    }

    /**
     * vérifie si une catégorie a des fils (but: empêcher la suppression d'un parent)
     *
     * @param int $idCategorie
     *
     * @return int : le nombre de fils trouvés
     */
    public function getNbChildren($idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(*) AS nb ';
        $sql .= 'FROM '.PFX.'thotForums WHERE parentId = :parentId ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':parentId', $idCategorie, PDO::PARAM_INT);

        $nb = 0;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $nb = $ligne['nb'];
        }

        Application::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * Effacement de tous les posts, de tous les sujets et de la catégorie $idCategorie
     *
     * @param int $idCategorie
     *
     * @return array array(nbSujets, nbposts) effacés
     */
    public function delCategorie($idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);

        // suppression de tous les posts liés à cette catégorie
        $sql = 'DELETE FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $nbPosts = $requete->rowCount();

        // suppression de tous les sujets liés à cette catégorie
        $sql = 'DELETE FROM '.PFX.'thotForumsSujets ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $nbSujets = $requete->rowCount();

        // suppression de tous les accès liés à cette catégorie
        $sql = 'DELETE FROM '.PFX.'thotForumsAccess ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $nb = $requete->execute();

        // suppression de la catégorie
        $sql = 'DELETE FROM '.PFX.'thotForums ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $nb = $requete->execute();

        Application::deconnexionPDO($connexion);

        return array('nbSujets' => $nbSujets, 'nbPosts' => $nbPosts);
    }

    /**
     * supprime tous les posts liés au sujet $idSujet dans la catégorie $idCategorie
     *
     * @param int $idCatgorie
     * @param int $idSujet
     *
     * @return int : nombre de suppressions
     */
    public function delSubjectPosts($idSujet, $idCategorie) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $nb = $requete->execute();

        Application::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * supprimee les informations d'accès au sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return void
     */
    public function delSubjectAccess($idSujet, $idCategorie) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsAccess ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $nb = $requete->execute();

        Application::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * Supprime toutes les références à un sujet dans la table des likes
     *
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return void
     */
    public function delSubjectLikes($idSujet, $idCategorie) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsLikes ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $nb = $requete->execute();

        Application::deconnexionPDO($connexion);
    }

    /**
     * Supprime le sujet $idSujet de la catégorie $idCategorei
     *
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return void
     */
    public function delSubject($idSujet, $idCategorie){
        // suppression de tous les accès liés au sujet $idSujet de la catégorie $idCategorie
        $this->delSubjectAccess($idSujet, $idCategorie);
        // suppression de Likes aux posts de ce sujet
        $this->delSubjectLikes($idSujet, $idCategorie);
        // suppression des contributions au sujet
        $this->delSubjectPosts($idSujet, $idCategorie);

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // suppression effective du sujet $idSujet dans la catégorie $idCategorie
        $sql = 'DELETE FROM '.PFX.'thotForumsSujets ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);
        echo $sql;
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        Application::afficher(array($idCategorie, $idSujet), true);
        $nb = $requete->execute();

        Application::deconnexionPDO($connexion);
    }

    /**
     * renvoie un tableau de tous les posts qui font partie du même sujet le post $postId
     *
     * @param int $postId
     *
     * @return array
     */
    private function getFamily4postId($postId) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, parentId ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE idSujet = (SELECT idSujet FROM '.PFX.'thotForumsPosts WHERE postId = :postId) ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $postId = $ligne['postId'];
                $liste[$postId] = $ligne['parentId'];
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des ascendants jusqu'à la racine pour un $postId donné
     *
     * @param int $postId
     *
     * @return array
     */
    public function getAncestors4post($postId){
        $ancestors = array();
        $liste = $this->getFamily4postId($postId);
        $ancestors = array();
        do {
            $parentId = $liste[$postId];
            array_push($ancestors, $parentId);
            $postId = (int)$parentId;
        } while ($parentId != 0);

        return array_reverse($ancestors);
    }

    /**
     * Vérifie si l'utilisateur $acronyme actuel est propriétaire du sujet $idSujet dans
     * la categorie $idCategorie
     *
     * @param int $idSujet
     * @param int $idCategorie
     * @param string $acronyme
     */
    public function verfiProprio($acronyme, $idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, idSujet, acronyme, sujet ';
        $sql .= 'FROM '.PFX.'thotForumsSujets ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND acronyme = :acronyme ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * renvoie la liste des sujets pour une catégorie donnée
     *
     * @param int $categorie
     *
     * @return array
     */
    public function getSubjects4category($idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idSujet, sujet, sujets.acronyme, DATE_FORMAT(dateCreation, "%d/%m") AS ladate, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%H:%i") AS heure, dateCreation, sexe, nom, prenom, userStatus ';
        $sql .= 'FROM '.PFX.'thotForumsSujets AS sujets ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = sujets.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = sujets.acronyme ';
        $sql .= 'WHERE sujets.idCategorie = :idCategorie ';
        $sql .= 'ORDER BY dateCreation ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idSujet = $ligne['idSujet'];
                $ligne['date'] = explode(' ', $ligne['dateCreation'])[0];
                if ($ligne['nom'] != Null) {
                    $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                    $ligne['nomProf'] = sprintf('%s %s. %s', $formule, $ligne['prenom'][0], $ligne['nom']);
                }
                else $ligne['nomProf'] = $ligne['acronyme'];

                $liste[$idSujet] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement d'un nouveau sujet dans une catégorie pour l'utilisateur $acronyme
     *
     * @param int $idCategorie
     * @param string $sujet
     * @param string $acronyme
     * @param int $idSujet (en cas d'édition)
     *
     * @return int idSujet
     */
    public function saveSubject($idCategorie, $sujet, $acronyme, $idSujet = Null){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        if ($idSujet == Null) {
            $sql = 'INSERT INTO '.PFX.'thotForumsSujets ';
            $sql .= 'SET idCategorie = :idCategorie, sujet = :sujet, acronyme = :acronyme, dateCreation = Now() ';
            $requete = $connexion->prepare($sql);
            }
            else {
                $sql = 'UPDATE '.PFX.'thotForumsSujets ';
                $sql .= 'SET sujet = :sujet ';
                $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND acronyme = :acronyme ';
                $requete = $connexion->prepare($sql);

                $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
            }

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':sujet', $sujet, PDO::PARAM_STR, 80);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $resultat = $requete->execute();

        if ($resultat != 0) {
            if ($idSujet == Null)
                $resultat = $connexion->lastInsertId();
                else $resultat = $idSujet;
        }

        Application::deconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie une liste ordonnée des abonnés au sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return array
     */
    public function listeAbonnesSujet($idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idSujet, idCategorie, type, cible ';
        $sql .= 'FROM '.PFX.'thotForumsAccess ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $sql .= 'ORDER BY type ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $type = $ligne['type'];
                $cible = $ligne['cible'];
                $liste[$type][$cible] = $ligne['cible'];
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement d'un nouveau post sur le sujet $idSujet dans la catégorie $idCategorie
     * avec le parent $parentId avec le texte $reponse
     *
     * @param string $reponse
     * @param int $idSujet
     * @param int $parentId
     * @param int $idCategorie
     * @param string $auteur ($acronyme ou $matricule)
     * @param bool $modifie (1 si le post a été modifié, sinon 0)
     *
     * @return int le $id du nouveau post
     */
    public function saveNewPost($post, $idSujet, $idCategorie, $parentId, $auteur){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotForumsPosts ';
        $sql .= 'SET idCategorie = :idCategorie, idSujet = :idSujet, parentId = :parentId, ';
        $sql .= 'date = NOW(), auteur = :auteur, userStatus = "prof", post = :post, modifie = 0 ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':parentId', $parentId, PDO::PARAM_INT);
        $requete->bindParam(':auteur', $auteur, PDO::PARAM_STR, 7);
        $requete->bindParam(':post', $post, PDO::PARAM_STR);

        $postId = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $postId = $connexion->lastInsertId();
        }

        Application::deconnexionPDO($connexion);

        return $postId;
    }

    /**
     * enregistrement d'un post MODIFIE sur le sujet $idSujet dans la catégorie $idCategorie
     * avec le parent $parentId avec le texte $reponse
     *
     * @param string $post
     * @param int $postId : identifiant du post
     * @param string $acronyme
     *
     * @return int le $postId du post
     */
    public function saveEditedPost($post, $postId, $auteur){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotForumsPosts ';
        $sql .= 'SET date = NOW(), post = :post, modifie = 1 ';
        $sql .= 'WHERE postId = :postId AND auteur = :auteur ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':post', $post, PDO::PARAM_STR);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        // l'auteur peut être un élève (cas de l'édition par le propriétaire du sujet)

        // $auteur = (string)$auteur;
        $requete->bindParam(':auteur', $auteur, PDO::PARAM_STR, 7);

        $resultat = $requete->execute();

        if ($resultat)
            $retour = $postId;
            else $retour = Null;

        Application::deconnexionPDO($connexion);

        return $retour;
    }

    /**
     * constuit l'arbre correspondant à la structure linéaire extraite de la  BD
     * @param  array   $elements tableau linéaire des catégories
     * @param  int $parentId identifiant du parent de l'élément actuellement examiné
     *
     * @return array    arborescence des catégories
     */
    private function buildPostTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['parentId'] == $parentId) {
            $children = $this->buildPostTree($elements, $element['postId']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
            }
        }

    return $branch;
    }

    /**
     * retourne la liste de tous les posts de la catégorie $idCategorie pour le sujet $idSujet
     *
     * @param int $idCategorie
     * @param int $idSujet
     *
     * @return array
     */
    public function getPosts4subject($idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, parentId, DATE_FORMAT(date, "%d/%m") AS ladate, DATE_FORMAT(date, "%H:%i") AS heure, ';
        $sql .= 'auteur, userStatus, post, modifie, posts.idCategorie, posts.idSujet, ';
        $sql .= 'sujets.acronyme, profs.sexe AS sexeProf, profs.nom AS nomProf, profs.prenom AS prenomProf, ';
        $sql .= 'eleves.sexe AS sexeEleve, eleves.nom AS nomEleve, eleves.prenom AS prenomEleve, eleves.groupe ';
        $sql .= 'FROM '.PFX.'thotForumsPosts AS posts ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idSujet = posts.idSujet AND sujets.idCategorie = posts.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = auteur ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS eleves ON eleves.matricule = auteur ';
        $sql .= 'WHERE posts.idCategorie = :idCategorie AND posts.idSujet = :idSujet ';
        $sql .= 'ORDER BY postId ASC ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $postId = $ligne['postId'];
                // $ligne['post'] = strip_tags($ligne['post'],'<http>,<https>');
                $ligne['post'] = nl2br($ligne['post']);
                if ($ligne['userStatus'] == 'prof') {
                    $appel = ($ligne['sexeProf'] == 'M') ? 'M.' : 'Mme';
                    $user = sprintf('%s %s. %s', $appel, mb_substr($ligne['prenomProf'], 0, 1), $ligne['nomProf']);
                    }
                    else {
                        $user = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                    }
                $ligne['user'] = $user;

                $liste[$postId] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        $tree = $this->buildPostTree($liste);

        return $tree;
    }

    /**
     * renvoie le post dont le postId est passé en argument
     *
     * @param int $postId
     *
     * @return array
     */
    public function getPostById($postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, posts.idCategorie, libelle, posts.idSujet, sujets.acronyme, sujet, posts.parentId, ';
        $sql .= 'DATE_FORMAT(date, "%d/%m/%Y") AS ladate, DATE_FORMAT(date, "%H:%i") AS heure, ';
        $sql .= 'auteur, posts.userStatus, post, modifie, ';
        $sql .= 'profs.sexe AS sexeProf, profs.nom AS nomProf, profs.prenom AS prenomProf, ';
        $sql .= 'de.groupe, de.nom AS nomEleve, de.prenom AS prenomEleve ';
        $sql .= 'FROM '.PFX.'thotForumsPosts AS posts ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = posts.idCategorie ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idCategorie = posts.idCategorie AND sujets.idSujet = posts.idSujet ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = auteur ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = auteur ';
        $sql .= 'WHERE postId = :postId ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            if ($ligne) {
                $ligne['post'] = strip_tags($ligne['post'],'<http>');
                $userStatus = $ligne['userStatus'];
                if ($ligne['userStatus'] == 'prof') {
                    $appel = ($ligne['sexeProf'] == 'M') ? 'M.' : 'Mme';
                    $user = sprintf('%s %s. %s', $appel, mb_substr($ligne['prenomProf'], 0, 1), $ligne['nomProf']);
                    }
                    else {
                        $user = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                    }
                $ligne['from'] = $user;
            }
        }

        Application::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * Enregistrement des accès aux sujets du forum
     *
     * @param int $idSujet
     * @param string $tous : la chaîne vaut "TOUS" si tous les élèves de l'école sont visés
     * @param array $listeProfs
     * @param array $listeNiveaux
     * @param array $listeClasses
     * @param array $listeCoursGrp
     *
     * @return int nombre d'accès enregistrés
     */
    public function saveForumAcces($idSujet, $idCategorie, $tous, $listeProfs, $listeNiveaux, $listeClasses, $listeCoursGrp){
        // suppression de tous les accès existants
        $this->cleanForumSubject($idCategorie, $idSujet);

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $nbAcces = 0;

        // accès pour les profs
        if ($listeProfs != Null) {
            $sql = 'INSERT INTO '.PFX.'thotForumsAccess ';
            $sql .= 'SET type = "prof", cible = :cible, idSujet = :idSujet, idCategorie = :idCategorie ';
            $requete = $connexion->prepare($sql);
            $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
            $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
            foreach ($listeProfs as $unAcronyme){
                $requete->bindParam(':cible', $unAcronyme, PDO::PARAM_STR, 13);
                $resultat = $requete->execute();
                $nbAcces += $requete->rowCount();
            }
        }

        if ($tous != 'TOUS') {
            // accès par niveau
            if ($listeNiveaux != Null){
                $sql = 'INSERT INTO '.PFX.'thotForumsAccess ';
                $sql .= 'SET type = "niveau", cible = :cible, idSujet = :idSujet, idCategorie = :idCategorie ';
                $requete = $connexion->prepare($sql);
                $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
                $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
                foreach ($listeNiveaux as $unNiveau){
                    $requete->bindParam(':cible', $unNiveau, PDO::PARAM_STR, 13);
                    $resultat = $requete->execute();
                    $nbAcces += $requete->rowCount();
                }
            }

            // accès par classe
            if ($listeClasses != Null) {
                $sql = 'INSERT INTO '.PFX.'thotForumsAccess ';
                $sql .= 'SET type = "classe", cible = :cible, idSujet = :idSujet, idCategorie = :idCategorie ';
                $requete = $connexion->prepare($sql);
                $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
                $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
                foreach ($listeClasses as $uneClasse){
                    $requete->bindParam(':cible', $uneClasse, PDO::PARAM_STR, 13);
                    $resultat = $requete->execute();
                    $nbAcces += $requete->rowCount();
                }
            }

            // accès par coursGrp
            if ($listeCoursGrp != Null) {
                $sql = 'INSERT INTO '.PFX.'thotForumsAccess ';
                $sql .= 'SET type = "coursGrp", cible = :cible, idSujet = :idSujet, idCategorie = :idCategorie ';
                $requete = $connexion->prepare($sql);
                $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
                $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
                foreach ($listeCoursGrp as $unCoursGrp){
                    $requete->bindParam(':cible', $unCoursGrp, PDO::PARAM_STR, 13);
                    $resultat = $requete->execute();
                    $nbAcces += $requete->rowCount();
                }
            }
        }
        else {
            // accès à tous les élèves sans distinction
            $sql = 'INSERT INTO '.PFX.'thotForumsAccess ';
            $sql .= 'SET type = "ecole", cible = "all", idSujet = :idSujet, idCategorie = :idCategorie ';
            $requete = $connexion->prepare($sql);
            $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
            $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
            $resultat = $requete->execute();
            $nbAcces += $requete->rowCount();
        }

        Application::deconnexionPDO($connexion);

        return $nbAcces;
    }

    /**
     * Suppression de tous les accès au sujet $idSujet du forum
     *
     * @param int $idSujet
     *
     * @return void
     */
    public function cleanForumSubject($idCategorie, $idSujet) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsAccess ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $resultat = $requete->execute();

        Application::deconnexionPDO($connexion);
    }

    /**
     * liste des sujets pour lequel l'utilisateur $acronyme a été invité
     *
     * @param string $acronyme
     *
     * @return array
     */
    public function getSubjects4user($acronyme){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT access.idCategorie, access.idSujet, cible AS abonne, sujets.sujet, ';
        $sql .= 'forums.libelle, dateCreation, userStatus, nom, prenom, sexe, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%d/%m") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure ';
        $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON (sujets.idSujet = access.idSujet AND sujets.idCategorie = access.idCategorie) ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = access.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = :acronyme ';
        $sql .= 'WHERE cible = :acronyme ';
        $sql .= 'ORDER BY dateCreation, libelle, sujet ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $idSujet = $ligne['idSujet'];

                if ($ligne['nom'] != Null) {
                    $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                    $ligne['nomProf'] = sprintf('%s %s. %s', $formule, $ligne['prenom'][0], $ligne['nom']);
                }
                else $ligne['nomProf'] = $ligne['acronyme'];
                $liste[$idCategorie][$idSujet] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des sujets auxquels l'utilisateur $acronyme est abonné
     *
     * @param string $acronyme
     *
     * @return array
     */
    public function getSubjects4Abonne($acronyme){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT access.idSujet, access.idCategorie, libelle, sujet, cible, userStatus, ';
        $sql .= 'sujets.acronyme, dateCreation, nom, prenom, sexe, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%d/%m") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure ';
        $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON (sujets.idSujet = access.idSujet AND sujets.idCategorie = access.idCategorie) ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = access.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = sujets.acronyme ';
        $sql .= 'WHERE cible = :acronyme ';
        $sql .= 'ORDER BY dateCreation, libelle, sujet ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $idSujet = $ligne['idSujet'];

                if ($ligne['nom'] != Null) {
                    $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                    $ligne['nomProf'] = sprintf('%s %s. %s', $formule, $ligne['prenom'][0], $ligne['nom']);
                }
                else $ligne['nomProf'] = $ligne['acronyme'];
                $liste[$idCategorie][$idSujet] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des sujets dont l'utilisateur $acronyme est propriétaire
     *
     * @param string $acronyme
     *
     * @return array
     */
    public function getSubject4proprio($acronyme) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT sujets.idCategorie, sujets.idSujet, sujets.acronyme, sujets.sujet, ';
        $sql .= 'libelle, userStatus, dateCreation, sexe, nom, prenom, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%d/%m") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure ';
        $sql .= 'FROM '.PFX.'thotForumsSujets AS sujets ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = sujets.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = :acronyme ';
        $sql .= 'WHERE sujets.acronyme = :acronyme ';
        $sql .= 'ORDER BY dateCreation, libelle, sujet ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $idSujet = $ligne['idSujet'];
                if ($ligne['nom'] != Null) {
                    $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                    $ligne['nomProf'] = sprintf('%s %s. %s', $formule, $ligne['prenom'][0], $ligne['nom']);
                }
                else $ligne['nomProf'] = $ligne['acronyme'];
                $liste[$idCategorie][$idSujet] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * vérifie que l'utilisateur $acronyme est l'auteur du post $postId
     * du sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $acronyme
     * @param int $postId
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return bool
     */
    public function verifAuteur($acronyme, $postId, $idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, idCategorie, idSujet, auteur ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet AND auteur = :acronyme ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne != Null;
    }

    /**
     * vérifie que l'utilisateur $acronyme est bien le propriétaire du sujet
     * $idSujet de la catégorie $idCategorie
     *
     * @param string $acronyme
     * @param int $idCategorie
     * @param int $idSujet
     *
     * @return array
     */
    public function verifProprio($acronyme, $idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, idSujet, acronyme ';
        $sql .= 'FROM '.PFX.'thotForumsSujets ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND acronyme = :acronyme ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne != Null;
    }

        /**
         * Efface le contenu du post $postId de l'utilisateur $matricule pour le sujet
         * $idSujet de la catégorie $idCategorie
         *
         * @param int $matricule
         * @param int $postId
         * @param int $idSujet
         * @param int $idCategorie
         *
         * @return int : nombre d'effacements (0 ou 1)
         */
        public function delPost($acronyme, $postId, $idSujet, $idCategorie){
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'UPDATE '.PFX.'thotForumsPosts ';
            $sql .= 'SET post = NULL ';
            $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet AND auteur = :acronyme ';
            $requete = $connexion->prepare($sql);

            $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
            $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
            $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
            $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

            $ligne = Null;
            $resultat = $requete->execute();

            $nb = $requete->rowcount();

            Application::DeconnexionPDO($connexion);

            return $nb;
        }

        /**
         * renvoie le nom du prof avec la formule d'appel qui convient
         *
         * @param string $sexe
         * @param string $prenom
         * @param string $nom
         *
         * @return string : Mme J. Dupont
         */
        public function nomProf($sexe, $prenom, $nom){
            $appel = ($sexe == 'F') ? 'Mme' : 'M.';
            $nom = sprintf('%s %s. %s', $appel, mb_substr($prenom, 0, 1, 'UTF-8'), $nom);
            return $nom;
        }

}
