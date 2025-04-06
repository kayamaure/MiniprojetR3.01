<?php
/**
 * Contrôleur de gestion des joueurs
 * Gère toutes les opérations liées aux joueurs via l'API JSON
 */

header("Content-Type: application/json");

// Inclusion des fichiers de configuration et des modèles nécessaires
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Joueur.php';

// Création de la connexion à la base de données et instanciation du modèle Joueur
$database = new Database();
$db = $database->getConnection();
$joueur = new Joueur($db);

// Récupération de l'action demandée depuis les paramètres GET (par défaut: 'liste')
$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'liste':
        // Récupération de la liste complète des joueurs
        $joueurs = $joueur->getAll();
        echo json_encode(["success" => true, "joueurs" => $joueurs]);
        exit;
        break;

    case 'ajouter':
        // Ajout d'un nouveau joueur : nécessite une requête POST avec des données JSON
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["error" => "Méthode de requête invalide. Utilisez POST."]);
            exit;
        }

        // Récupération et validation des données JSON
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["error" => "Données JSON manquantes ou invalides."]);
            exit;
        }

        // Vérification des champs requis : numero_licence, nom, prenom, date_naissance, taille, poids, statut
        if (!isset($input['numero_licence'], $input['nom'], $input['prenom'], $input['date_naissance'], $input['taille'], $input['poids'], $input['statut'])) {
            echo json_encode(["error" => "Champs requis manquants."]);
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
        if ($joueur->create($data)) {
            echo json_encode(["success" => "Joueur ajouté avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de l'ajout du joueur."]);
        }
        exit;
        break;

    case 'modifier':
<<<<<<< Updated upstream
        // Modifying a player: expect a POST request with JSON data.
        // The player's numero_licence is passed as a GET parameter.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["error" => "Invalid request method. Use POST for modification."]);
=======
        // Modification d'un joueur : nécessite une requête PUT avec des données JSON
        // Le numéro de licence du joueur est passé en paramètre GET
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            echo json_encode(["error" => "Méthode de requête invalide. Utilisez PUT pour la modification."]);
>>>>>>> Stashed changes
            exit;
        }

        // Vérification du numéro de licence
        $numero_licence = $_GET['numero_licence'] ?? null;
        if (!$numero_licence) {
            echo json_encode(["error" => "Numéro de licence non spécifié."]);
            exit;
        }

        // Récupération et validation des données JSON
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            echo json_encode(["error" => "Données JSON manquantes ou invalides."]);
            exit;
        }

        // Vérification des champs requis pour la modification : nom, prenom, date_naissance, taille, poids, statut
        if (!isset($input['nom'], $input['prenom'], $input['date_naissance'], $input['taille'], $input['poids'], $input['statut'])) {
            echo json_encode(["error" => "Champs requis manquants pour la modification."]);
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
        if ($joueur->update($data)) {
            echo json_encode(["success" => "Joueur mis à jour avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de la mise à jour du joueur."]);
        }
        exit;
        break;

    case 'supprimer':
        // Suppression d'un joueur : le numéro de licence est passé en paramètre GET
        $numero_licence = $_GET['numero_licence'] ?? null;
        if (!$numero_licence) {
            echo json_encode(["error" => "Numéro de licence non spécifié."]);
            exit;
        }

        // Tentative de suppression du joueur
        if ($joueur->delete($numero_licence)) {
            echo json_encode(["success" => "Joueur supprimé avec succès."]);
        } else {
            echo json_encode(["error" => "Échec de la suppression du joueur."]);
        }
        exit;
        break;

    default:
        // Gestion des actions non reconnues
        echo json_encode(["error" => "Action non reconnue."]);
        exit;
        break;
}
