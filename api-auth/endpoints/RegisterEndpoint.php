<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Nom d'utilisateur ou mot de passe manquant"]);
    exit;
}

$username = $data['username'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

$utilisateur = new Utilisateur($pdo);
if ($utilisateur->trouverParNomUtilisateur($username)) {
    http_response_code(409); // Conflit
    echo json_encode(["error" => "Nom d'utilisateur déjà utilisé"]);
    exit;
}

$success = $utilisateur->creerUtilisateur($username, $password);

if ($success) {
    http_response_code(201);
    echo json_encode(["message" => "Utilisateur enregistré avec succès"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la création de l'utilisateur"]);
}
