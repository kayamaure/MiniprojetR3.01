<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/jwt_secret.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../utils/jwt-utils.php';

$method = $_SERVER['REQUEST_METHOD'];

$userModel = new Utilisateur($pdo);
$authController = new AuthController($userModel, $jwt_secret);

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Nom d'utilisateur ou mot de passe manquant"]);
            exit;
        }
        $authController->login($data['username'], $data['password']);
        break;

    case 'GET':
        $token = get_bearer_token();
        $authController->verifyToken($token);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}
?>
