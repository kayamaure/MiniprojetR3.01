<?php
/**
 * Point d'entrée de l'API pour la gestion des matchs
 * Gère les opérations CRUD sur les matchs via des requêtes HTTP
 */

header('Content-Type: application/json');

// Inclusion des dépendances nécessaires
require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Match.php';

// Vérification de l'authentification via token JWT
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// Initialisation de la connexion à la base de données
$database = new Database();
$db = $database->getConnection();
$matchModel = new GameMatch($db);

// Récupération de la méthode HTTP utilisée
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ------------------- GET : Récupération des matchs -------------------------
    case 'GET':
        // Récupération d'un match spécifique par son ID
        if (isset($_GET['id_match'])) {
            $match = $matchModel->obtenirMatch($_GET['id_match']);
            if ($match) {
                echo json_encode($match);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Match non trouvé"]);
            }
        }
        // Filtrage des matchs selon leur statut
        elseif (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
    
            // Sélection des matchs selon le filtre demandé
            if ($filter === 'a_venir') {
                $matches = $matchModel->obtenirMatchsParStatut('À venir');
            } elseif ($filter === 'passes') {
                $matches = $matchModel->obtenirMatchsParStatut('Terminé');
            } else {
                // Par défaut, retourne tous les matchs
                $matches = $matchModel->obtenirTousLesMatchs();
            }
    
            echo json_encode($matches);
        }
        // Sans filtre, retourne tous les matchs
        else {
            $matches = $matchModel->obtenirTousLesMatchs();
            echo json_encode($matches);
        }
        break;
    

    // ------------------- POST : Création d'un nouveau match -------------------------
    case 'POST':
        // Récupération et validation des données JSON de la requête
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Données JSON manquantes ou invalides"]);
            exit;
        }

        // Vérification de la présence des champs obligatoires
        $required = ['date_match', 'heure_match', 'nom_equipe_adverse', 'lieu_de_rencontre'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Champ requis manquant : $field"]);
                exit;
            }
        }

        // Tentative de création du match dans la base de données
        $ok = $matchModel->ajouterMatch($data);
        if ($ok) {
            http_response_code(201);
            echo json_encode(["message" => "Match créé avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la création du match"]);
        }
        break;

    // ------------------- PUT : Mise à jour d'un match existant -------------------------
    case 'PUT':
        // Vérification de la présence de l'ID du match
        if (!isset($_GET['id_match'])) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'id_match' requis"]);
            exit;
        }

        // Vérification de l'existence du match
        $existing = $matchModel->obtenirMatch($_GET['id_match']);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Match introuvable"]);
            exit;
        }

        // Récupération et validation des données de mise à jour
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Données JSON invalides"]);
            exit;
        }

        // Ajout de l'ID du match aux données
        $data['id_match'] = $_GET['id_match'];

        // Tentative de mise à jour du match
        $ok = $matchModel->mettreAJourMatch($data);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message" => "Match mis à jour avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la mise à jour"]);
        }
        break;

    // ------------------- DELETE : Suppression d'un match -------------------------
    case 'DELETE':
        // Vérification de la présence de l'ID du match
        if (!isset($_GET['id_match'])) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'id_match' requis"]);
            exit;
        }

        // Vérification de l'existence du match
        $existing = $matchModel->obtenirMatch($_GET['id_match']);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Match introuvable"]);
            exit;
        }

        // Tentative de suppression du match
        $ok = $matchModel->supprimerMatch($_GET['id_match']);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message" => "Match supprimé avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la suppression du match"]);
        }
        break;

    // ------------------- Gestion des méthodes HTTP non supportées -------------------------
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode HTTP non autorisée"]);
        break;
}
