<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Vérification du token JWT pour toutes les requêtes sauf OPTIONS
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    $auth_info = AuthMiddleware::verifyToken();
}

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