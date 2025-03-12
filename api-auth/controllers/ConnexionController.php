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

if ($utilisateur->verifierUtilisateur($nom_utilisateur, $mot_de_passe)) {
    // Retrieve the user ID
    $query = "SELECT id_utilisateur FROM utilisateur WHERE nom_utilisateur = :nom_utilisateur LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && isset($row['id_utilisateur'])) {
        $userId = $row['id_utilisateur'];
        // Create JWT payload
        $payload = [
            "id_utilisateur" => $userId,
            "nom_utilisateur" => $nom_utilisateur,
            "iat" => time(),
            "exp" => time() + (60 * 60) // Token valid for 1 hour
        ];
        $secret = "123Top@Bruh"; // Change this to a secure secret key
        $token = create_jwt($payload, $secret);
        echo json_encode(["success" => true, "token" => $token]);
        exit();
    }
} else {
    echo json_encode(["error" => "Invalid credentials."]);
    exit();
}
?>
