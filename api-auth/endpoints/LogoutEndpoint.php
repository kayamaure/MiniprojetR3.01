<?php
/**
 * Point d'entrée pour la déconnexion des utilisateurs
 * Gère la révocation des tokens JWT
 */

header('Content-Type: application/json');

// Inclusion des dépendances nécessaires
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../utils/jwt-utils.php';

// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Vérification que la méthode est bien POST
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

// Récupération et vérification du token d'authentification
$token = get_bearer_token();
if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "Token manquant"]);
    exit;
}

// Tentative de suppression du token de la base de données
$utilisateur = new Utilisateur($pdo);
$success = $utilisateur->supprimerToken($token);

// Envoi de la réponse appropriée selon le résultat
if ($success) {
    http_response_code(200);
    echo json_encode(["message" => "Déconnexion réussie"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la déconnexion"]);
}
