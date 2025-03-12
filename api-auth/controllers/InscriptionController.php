<?php
// InscriptionController.php for API authentication (registration)
header("Content-Type: application/json");

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method."]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['nom_utilisateur']) || !isset($input['mot_de_passe'])) {
    echo json_encode(["error" => "Missing required fields."]);
    exit();
}

$nom_utilisateur = trim($input['nom_utilisateur']);
$mot_de_passe = $input['mot_de_passe'];

$database = new Database();
$db = $database->getConnection();

$utilisateur = new Utilisateur($db);

// Check if the username already exists
if ($utilisateur->existeUtilisateur($nom_utilisateur)) {
    echo json_encode(["error" => "Username already exists."]);
    exit();
}

// Add the new user
if ($utilisateur->ajouterUtilisateur($nom_utilisateur, $mot_de_passe)) {
    echo json_encode(["success" => "User registered successfully."]);
} else {
    echo json_encode(["error" => "User registration failed."]);
}
?>
