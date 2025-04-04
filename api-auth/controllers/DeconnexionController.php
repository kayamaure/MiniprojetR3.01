<?php
// Contrôleur de déconnexion pour l'API d'authentification
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vérification de la méthode HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

// Récupération du token dans l'en-tête Authorization
$headers = apache_request_headers();
$token = null;

if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "Token non fourni"]);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $utilisateur = new Utilisateur($db);

    // Invalider le token dans la base de données
    if ($utilisateur->invaliderToken($token)) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Déconnexion réussie"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de la déconnexion"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur: " . $e->getMessage()]);
}
?>
