<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Récupération du corps de la requête pour les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $_POST = json_decode($input, true) ?? [];
}

switch ($action) {
    case "login":
        require_once "controllers/ConnexionController.php";
        break;
    case "register":
        require_once "controllers/InscriptionController.php";
        break;
    case "logout":
        require_once "controllers/DeconnexionController.php";
        break;
    case "moncompte":
        require_once "controllers/MonCompteController.php";
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Action non valide"]);
        break;
}
?>
