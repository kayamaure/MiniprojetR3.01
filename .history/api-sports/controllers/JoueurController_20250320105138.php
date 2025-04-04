<?php
header("Content-Type: application/json");

// Include required configuration and model files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Joueur.php';

// Create database connection and instantiate the Joueur model
$database = new Database();
$db = $database->getConnection();
$joueur = new Joueur($db);

// Get the requested action from the GET parameters; default to "liste"
$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'liste':
        // List all players
        $joueurs = $joueur->obtenirTousLesJoueurs();
        echo json_encode(["success" => true, "joueurs" => $joueurs]);
        exit;
        break;

    case 'ajouter':
        // Adding a player: expect a POST request with JSON data
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["error" => "Invalid request method. Use POST."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["error" => "Missing or invalid JSON input."]);
            exit;
        }
        // Required fields: numero_licence, nom, prenom, date_naissance, taille, poids, statut
        if (!isset($input['numero_licence'], $input['nom'], $input['prenom'], $input['date_naissance'], $input['taille'], $input['poids'], $input['statut'])) {
            echo json_encode(["error" => "Missing required fields."]);
            exit;
        }
        $data = [
            'numero_licence' => $input['numero_licence'],
            'nom'            => $input['nom'],
            'prenom'         => $input['prenom'],
            'date_naissance' => $input['date_naissance'],
            'taille'         => $input['taille'],
            'poids'          => $input['poids'],
            'statut'         => $input['statut']
        ];
        if ($joueur->ajouterJoueur($data)) {
            echo json_encode(["success" => "Joueur ajouté avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de l'ajout du joueur."]);
        }
        exit;
        break;

    case 'modifier':
        // Modifying a player: expect a POST request with JSON data.
        // The player's numero_licence is passed as a GET parameter.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["error" => "Invalid request method. Use POST for modification."]);
            exit;
        }
        $numero_licence = $_GET['numero_licence'] ?? null;
        if (!$numero_licence) {
            echo json_encode(["error" => "Numero de licence non spécifié."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["error" => "Missing or invalid JSON input."]);
            exit;
        }
        // Required fields for modification: nom, prenom, date_naissance, taille, poids, statut
        if (!isset($input['nom'], $input['prenom'], $input['date_naissance'], $input['taille'], $input['poids'], $input['statut'])) {
            echo json_encode(["error" => "Missing required fields for modification."]);
            exit;
        }
        $data = [
            'numero_licence' => $numero_licence,
            'nom'            => $input['nom'],
            'prenom'         => $input['prenom'],
            'date_naissance' => $input['date_naissance'],
            'taille'         => $input['taille'],
            'poids'          => $input['poids'],
            'statut'         => $input['statut']
        ];
        if ($joueur->mettreAJourJoueur($data)) {
            echo json_encode(["success" => "Joueur mis à jour avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de la mise à jour du joueur."]);
        }
        exit;
        break;

    case 'supprimer':
        // Deleting a player: the player's numero_licence is passed as a GET parameter.
        $numero_licence = $_GET['numero_licence'] ?? null;
        if (!$numero_licence) {
            echo json_encode(["error" => "Numero de licence non spécifié."]);
            exit;
        }
        if ($joueur->supprimerJoueur($numero_licence)) {
            echo json_encode(["success" => "Joueur supprimé avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de la suppression du joueur."]);
        }
        exit;
        break;

    default:
        echo json_encode(["error" => "Action non reconnue."]);
        exit;
        break;
}
?>
