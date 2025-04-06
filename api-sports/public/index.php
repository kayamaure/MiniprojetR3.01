<?php
require_once __DIR__ . '/../utils/verifierJeton.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
// (2) Vérification token
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');


// (3) Récupérer l'action
$route = $_GET['action'] ?? null;

// (4) Routage vers les endpoints
switch ($route) {
    case 'joueurs':
        require_once __DIR__ . '/../endpoints/JoueurEndpoint.php';
        break;

    case 'matchs':
        require_once __DIR__ . '/../endpoints/MatchEndpoint.php';
        break;

    case 'feuille_match':
        require_once __DIR__ . '/../endpoints/FeuilleMatchEndpoint.php';
        break;

    case 'commentaires':
        require_once __DIR__ . '/../endpoints/CommentaireEndpoint.php';
        break;

    case 'statistiques':
        require_once __DIR__ . '/../endpoints/StatistiquesEndpoint.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Action inconnue"]);
        break;
}
