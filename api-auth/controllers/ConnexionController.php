<?php
// ConnexionController.php for API authentication (login)
header("Content-Type: application/json");

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method."]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../jwt_helper.php';

// Get POST data (assuming JSON input)
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['nom_utilisateur']) || !isset($input['mot_de_passe'])) {
    echo json_encode(["error" => "Missing required fields."]);
    exit();
}

$nom_utilisateur = trim($input['nom_utilisateur']);
$mot_de_passe = $input['mot_de_passe'];

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Create the user object
$utilisateur = new Utilisateur($db);

// Tenter de vérifier l'utilisateur
$user_info = $utilisateur->verifierUtilisateur($nom_utilisateur, $mot_de_passe);

if ($user_info) {
    // Créer le payload JWT avec les informations utilisateur
    $payload = [
        "id_utilisateur" => $user_info['id_utilisateur'],
        "nom_utilisateur" => $user_info['nom_utilisateur'],
        "iat" => time(),
        "exp" => time() + (60 * 60) // Token valid for 1 hour
    ];
    
    $secret = "123Top@Bruh"; // Change this to a secure secret key
    $token = create_jwt($payload, $secret);
    echo json_encode(["success" => true, "token" => $token]);
    exit();
} else {
    echo json_encode(["error" => "Invalid credentials."]);
    exit();
}
?>
