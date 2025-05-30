<?php
header("Content-Type: application/json");

// Vérifier l'authentification par JWT (à ajouter ultérieurement si nécessaire)
// ...

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case "liste":
    case "ajouter":
    case "modifier":
    case "supprimer":
        require_once "controllers/JoueursController.php";
        break;
        
    case "commentaires":
    case "ajouter_commentaire":
    case "modifier_commentaire":
    case "supprimer_commentaire":
        require_once "controllers/CommentaireController.php";
        break;
        
    case "matchs":
    case "ajouter_match":
    case "modifier_match":
    case "supprimer_match":
    case "feuille_match": 
    case "valider_feuille": 
    case "evaluer": 
    case "match": // Ajout de cette action pour récupérer un match spécifique
        require_once "controllers/MatchsController.php";
        break;
        
    case "ajouter_feuille":
    case "modifier_feuille":
    case "supprimer_feuille":
        require_once "controllers/FeuilleMatchController.php";
        break;
        
    case "statistiques":
        require_once "controllers/StatistiquesController.php";
        break;
        
    default:
        echo json_encode(["error" => "Action non reconnue"]);
        break;
}
?>