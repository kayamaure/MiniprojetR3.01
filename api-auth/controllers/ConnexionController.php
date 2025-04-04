<?php
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

// Récupération des données POST
$input = json_decode(file_get_contents("php://input"), true);

// Vérification des champs requis
if (!isset($input['nom_utilisateur']) || !isset($input['mot_de_passe'])) {
    http_response_code(400);
    echo json_encode(["error" => "Nom d'utilisateur et mot de passe requis"]);
    exit();
}

$nom_utilisateur = trim($input['nom_utilisateur']);
$mot_de_passe = $input['mot_de_passe'];

// Création de la connexion à la base de données
try {
    $database = new Database();
    $db = $database->getConnection();
    $utilisateur = new Utilisateur($db);

    // Vérification des identifiants
    $user_info = $utilisateur->verifierUtilisateur($nom_utilisateur, $mot_de_passe);

    if ($user_info) {
        // Création du token
        $token = $utilisateur->creerToken($user_info['id_utilisateur']);

        if ($token) {
            // Mise à jour de la dernière connexion
            $utilisateur->mettreAJourDerniereConnexion($user_info['id_utilisateur']);

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Connexion réussie",
                "token" => $token,
                "user" => [
                    "id" => $user_info['id_utilisateur'],
                    "username" => $user_info['nom_utilisateur']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la création du token"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Identifiants invalides"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur: " . $e->getMessage()]);
}
?>
