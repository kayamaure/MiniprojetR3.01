<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../utils/verifierJeton.php';

// Vérification du token
$payload = verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// Connexion DB
$database = new Database();
$pdo = $database->getConnection();
$utilisateur = new Utilisateur($pdo);

// Vérification méthode
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

// Récupération des données JSON
$donnees = json_decode(file_get_contents("php://input"), true);

if (
    empty($donnees['ancien_mot_de_passe']) ||
    empty($donnees['nouveau_mot_de_passe'])
) {
    http_response_code(400);
    echo json_encode(["error" => "Les champs requis sont manquants"]);
    exit;
}

$id_utilisateur = $payload['id_utilisateur'];
$ancien = $donnees['ancien_mot_de_passe'];
$nouveau = $donnees['nouveau_mot_de_passe'];

// Vérifier ancien mot de passe
$infos = $utilisateur->getById($id_utilisateur);
if (!$infos || !password_verify($ancien, $infos['mot_de_passe'])) {
    http_response_code(401);
    echo json_encode(["error" => "Mot de passe actuel incorrect"]);
    exit;
}

// Mise à jour
$nouveauHash = password_hash($nouveau, PASSWORD_DEFAULT);
if ($utilisateur->changerMotDePasse($id_utilisateur, $nouveauHash)) {
    echo json_encode(["success" => "Mot de passe mis à jour"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la mise à jour"]);
}

?>
