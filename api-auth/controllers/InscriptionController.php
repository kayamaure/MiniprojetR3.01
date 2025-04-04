<?php
// Contrôleur d'inscription pour l'API d'authentification
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cas d'erreur 1 : Vérification de la méthode HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "success" => false,
        "error" => "Méthode non autorisée",
        "message" => "Seule la méthode POST est autorisée pour l'inscription"
    ]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

// Récupération et validation des données POST
$input = json_decode(file_get_contents("php://input"), true);

// Cas d'erreur 2 : Vérification des champs requis
if (!isset($input['nom_utilisateur']) || !isset($input['mot_de_passe']) ||
    empty(trim($input['nom_utilisateur'])) || empty(trim($input['mot_de_passe']))) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "success" => false,
        "error" => "Données manquantes ou invalides",
        "message" => "Le nom d'utilisateur et le mot de passe sont requis et ne peuvent pas être vides"
    ]);
    exit();
}

$nom_utilisateur = trim($input['nom_utilisateur']);
$mot_de_passe = trim($input['mot_de_passe']);

// Validation supplémentaire des données
if (strlen($nom_utilisateur) < 3 || strlen($nom_utilisateur) > 50) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Nom d'utilisateur invalide",
        "message" => "Le nom d'utilisateur doit contenir entre 3 et 50 caractères"
    ]);
    exit();
}

if (strlen($mot_de_passe) < 6) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Mot de passe invalide",
        "message" => "Le mot de passe doit contenir au moins 6 caractères"
    ]);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    $utilisateur = new Utilisateur($db);

    // Cas d'erreur 3 : Vérification si le nom d'utilisateur existe déjà
    if ($utilisateur->existeUtilisateur($nom_utilisateur)) {
        http_response_code(409); // Conflict
        echo json_encode([
            "success" => false,
            "error" => "Utilisateur existant",
            "message" => "Ce nom d'utilisateur est déjà utilisé"
        ]);
        exit();
    }

    // Ajout du nouvel utilisateur
    if ($utilisateur->ajouterUtilisateur($nom_utilisateur, $mot_de_passe)) {
        http_response_code(201); // Created
        echo json_encode([
            "success" => true,
            "message" => "Inscription réussie"
        ]);
    } else {
        throw new Exception("Erreur lors de l'ajout de l'utilisateur");
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erreur de base de données",
        "message" => "Une erreur est survenue lors de l'accès à la base de données"
    ]);
    error_log($e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erreur serveur",
        "message" => $e->getMessage()
    ]);
    error_log($e->getMessage());
}
?>
