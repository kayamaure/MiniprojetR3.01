<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../utils/jwt-utils.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

$token = get_bearer_token();
if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "Token manquant"]);
    exit;
}

$utilisateur = new Utilisateur($pdo);
$success = $utilisateur->supprimerToken($token);

if ($success) {
    http_response_code(200);
    echo json_encode(["message" => "Déconnexion réussie"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la déconnexion"]);
}
