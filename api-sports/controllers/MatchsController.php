<?php
/**
 * Contrôleur des matchs
 *
 * Ce fichier gère les différentes actions liées à la gestion des matchs, 
 * telles que l'affichage de la liste des matchs, l'ajout, la modification, 
 * la suppression, et la consultation des détails d'un match.
 *
 * Actions disponibles :
 * - `liste` : Affiche tous les matchs enregistrés.
 * - `ajouter` : Permet d'ajouter un nouveau match.
 * - `modifier` : Permet de modifier les détails d'un match existant. Si le match est dans le passé, certains champs peuvent être gelés.
 * - `matches_a_venir` : Affiche uniquement les matchs à venir.
 * - `matches_passes` : Affiche uniquement les matchs déjà terminés.
 * - `details` : Affiche les informations détaillées pour un match spécifique.
 * - `supprimer` : Supprime un match spécifique.
 *
 * Les actions utilisent les méthodes du modèle `GameMatch` pour interagir avec la base de données.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../models/Match.php';

$database = new Database();
$db = $database->getConnection();
$gameMatch = new GameMatch($db);

$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'liste':
        $matchs = $gameMatch->obtenirTousLesMatchs();
        require '../views/matchs/index.php';
        break;

    case 'ajouter':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'date_match' => $_POST['date_match'],
                'heure_match' => $_POST['heure_match'],
                'nom_equipe_adverse' => $_POST['nom_equipe_adverse'],
                'lieu_de_rencontre' => $_POST['lieu_de_rencontre'],
            ];
            $gameMatch->ajouterMatch($data);
            header("Location: MatchsController.php?action=liste");
            exit();
        }
        require '../views/matchs/ajouter.php';
        break;

    case 'modifier':
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo "ID du match non spécifié.";
            exit;
        }
        
        // Obtenir les détails du match
        $match = $gameMatch->obtenirMatch($id_match);
        
        // Déterminer si le match est dans le passé
        $isMatchInThePast = $gameMatch->estMatchDansLePasse($id_match);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_match' => $id_match,
                'date_match' => $_POST['date_match'],
                'heure_match' => $_POST['heure_match'],
                'nom_equipe_adverse' => $_POST['nom_equipe_adverse'],
                'lieu_de_rencontre' => $_POST['lieu_de_rencontre'],
            ];

            if ($isMatchInThePast) {
                $data['resultat'] = $_POST['resultat'] ?? null; // Ajouter le résultat si disponible
            }
        
            $gameMatch->mettreAJourMatch($data);
            header("Location: MatchsController.php?action=liste");
            exit();
        }
        
        // Inclure la vue
        require '../views/matchs/modifier.php';
        break;
        

    case 'matches_a_venir':
        $matchs = $gameMatch->obtenirMatchsParStatut('À venir');
        require '../views/matchs/index.php';
        break;

    case 'matches_passes':
        $matchs = $gameMatch->obtenirMatchsParStatut('Terminé');
        require '../views/matchs/index.php';
        break;

    case 'details':
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo "ID du match non spécifié.";
            exit;
        }
        $match = $gameMatch->obtenirMatch($id_match);
        require '../views/matchs/details.php';
        break;

    case 'supprimer':
        $id_match = $_GET['id_match'];
        $gameMatch->supprimerMatch($id_match);
        header("Location: MatchsController.php?action=liste");
        exit();
        break;

    default:
        $matchs = $gameMatch->obtenirTousLesMatchs();
        require '../views/matchs/index.php';
        break;
}
?>
