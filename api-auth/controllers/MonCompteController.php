<?php
// Contrôleur de gestion du compte utilisateur
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vérification de la méthode HTTP
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

// Récupération du token dans l'en-tête Authorization
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "En-tête d'autorisation manquant"]);
    exit();
}

$auth_header = $headers['Authorization'];
if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Format de l'en-tête d'autorisation invalide"]);
    exit();
}

$token = $matches[1];

try {
    $database = new Database();
    $db = $database->getConnection();
    $utilisateur = new Utilisateur($db);

    // Vérification du token
    $token_info = $utilisateur->verifierToken($token);
    if (!$token_info) {
        http_response_code(401);
        echo json_encode(["error" => "Token invalide ou expiré"]);
        exit();
    }

    // Récupération des informations de l'utilisateur
    $user_info = $utilisateur->getUtilisateurParId($token_info['id_utilisateur']);
    if ($user_info) {
        // On ne renvoie pas le mot de passe
        unset($user_info['mot_de_passe']);
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "user" => $user_info
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Utilisateur non trouvé"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur: " . $e->getMessage()]);
}
?>
