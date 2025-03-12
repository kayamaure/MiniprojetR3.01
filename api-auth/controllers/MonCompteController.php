<?php
// MonCompteController.php for API authentication (get account info)
header("Content-Type: application/json");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../jwt_helper.php';

// Get headers
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Authorization header missing."]);
    exit();
}

$authHeader = $headers['Authorization'];
// Expected format: Bearer <token>
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} else {
    echo json_encode(["error" => "Invalid Authorization header format."]);
    exit();
}

$secret = "123Top@Bruh"; // Use the same secret as in ConnexionController.php
$payload = verify_jwt($token, $secret);
if (!$payload) {
    echo json_encode(["error" => "Invalid or expired token."]);
    exit();
}

$userId = $payload['id_utilisateur'];

$database = new Database();
$db = $database->getConnection();

$utilisateur = new Utilisateur($db);
$userInfo = $utilisateur->getUtilisateurParId($userId);

if ($userInfo) {
    echo json_encode(["success" => true, "user" => $userInfo]);
} else {
    echo json_encode(["error" => "User not found."]);
}
?>
