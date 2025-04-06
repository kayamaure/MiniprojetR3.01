<?php
// CommentaireController.php for API endpoint

header("Content-Type: application/json");

// Allow only POST requests for adding a comment
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method."]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Commentaire.php';

// Create database connection and instantiate Commentaire model
$database = new Database();
$db = $database->getConnection();
$commentaire = new Commentaire($db);

// Retrieve JSON input from the request body
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["error" => "Missing or invalid JSON input."]);
    exit();
}

// Ensure required fields are present
if (!isset($input['sujet_commentaire'], $input['texte_commentaire'], $input['numero_licence'])) {
    echo json_encode(["error" => "Missing required fields: sujet_commentaire, texte_commentaire, numero_licence."]);
    exit();
}

// Prepare the data array for insertion
$data = [
    "sujet_commentaire" => $input['sujet_commentaire'],
    "texte_commentaire" => $input['texte_commentaire'],
    "numero_licence"   => $input['numero_licence']
];

// Attempt to add the comment to the database
if ($commentaire->ajouterCommentaire($data)) {
    echo json_encode(["success" => "Comment added successfully."]);
} else {
    echo json_encode(["error" => "Failed to add comment."]);
}

exit();
?>
