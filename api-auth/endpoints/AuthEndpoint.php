<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';    
require_once __DIR__ . '/../config/jwt_secret.php';  
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../utils/jwt-utils.php';

$method = $_SERVER['REQUEST_METHOD'];

// Instanciation du modèle + contrôleur
$userModel = new Utilisateur($pdo);       
$authController = new AuthController($userModel, $jwt_secret);

switch ($method) {
    case 'POST':
        // récupère le corps JSON
        $data = json_decode(file_get_contents("php://input"), true);

        // vérifie la présence des champs attendus
        if (!isset($data['nom_utilisateur']) || !isset($data['mot_de_passe'])) {
            http_response_code(400);
            echo json_encode(["error" => "Nom d'utilisateur ou mot de passe manquant"]);
            exit;
        }

        // appelle la fonction login() du contrôleur
        $authController->login($data['nom_utilisateur'], $data['mot_de_passe']);
        break;

    case 'GET':
        // récupère le token dans le header Authorization: Bearer ...
        $token = get_bearer_token();
        $authController->verifyToken($token);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}
