<?php
/**
 * Fichier : MonCompteController.php
 * 
 * Description :
 * Ce fichier contient le contrôleur `MonCompteController` qui gère les actions liées à la page 
 * "Mon Compte". Il permet de vérifier si un utilisateur est connecté, de récupérer ses informations 
 * depuis la base de données et de les afficher dans une vue dédiée.
 *  */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../models/Statistiques.php';
require_once '../models/Joueur.php';


$database = new Database();
$db = $database->getConnection();
$Statistiques = new Statistiques($db);

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        // Afficher la page principale des statistiques
        require '../views/statistiques/statistiques.php';
        break;

    case 'matchs':
        $statistiquesModel = new Statistiques($db);

        // Obtenir les statistiques des matchs
        $totaux = $statistiquesModel->obtenirStatistiquesMatchs();
        $pourcentages = $statistiquesModel->obtenirPourcentagesMatchs();

        // Charger la vue statistiques_matchs.php avec les données
        require '../views/statistiques/statistiques_matchs.php';
        break;

    case 'joueurs':
        $joueurModel = new Joueur($db);
        $statistiquesModel = new Statistiques($db);

        // Obtenir tous les joueurs
        $joueurs = $joueurModel->obtenirTousLesJoueurs();

        // Si un joueur est sélectionné
        $selectedPlayerStats = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['numero_licence'])) {
            $numeroLicence = $_POST['numero_licence'];

            //Récupérer les statistiques du joueur
            $selectedPlayerStats = [
                'statut' => $statistiquesModel->obtenirStatutJoueur($numeroLicence),
                'poste_prefere' => $statistiquesModel->obtenirPostePrefere($numeroLicence),
                'titularisations' => $statistiquesModel->obtenirNombreTitularisations($numeroLicence),
                'remplacements' => $statistiquesModel->obtenirNombreRemplacements($numeroLicence),
                'moyenne_evaluations' => $statistiquesModel->obtenirMoyenneEvaluations($numeroLicence),
                'pourcentage_gagnes' => $statistiquesModel->obtenirPourcentageMatchsGagnes($numeroLicence),
                'selections_consecutives' => $statistiquesModel->obtenirSelectionsConsecutives($numeroLicence)
            ];
        }

        // Charger la vue
        require '../views/statistiques/statistiques_joueurs.php';
        break;


    default:
        echo "Action non reconnue.";
        break;
}
