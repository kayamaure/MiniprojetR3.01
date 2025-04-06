<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Joueur.php';

$database = new Database();
$db = $database->getConnection();
$joueur = new Joueur($db);

$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'liste':
        // Liste de tous les joueurs        
        $joueurs = $joueur->obtenirTousLesJoueurs();
        echo json_encode(["success" => true, "joueurs" => $joueurs]);
        exit;
        break;

    case 'ajouter':
        // Ajout d'un lecteur : attente d'une requête POST avec des données JSON        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["error" => "Invalid request method. Use POST."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["error" => "Missing or invalid JSON input."]);
            exit;
        }
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

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            echo json_encode(["error" => "Invalid request method. Use PUT for modification."]);
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
        // Supprimer joueur
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