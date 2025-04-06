<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Match.php';

// Vérification du token JWT
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

$database = new Database();
$db = $database->getConnection();
$matchModel = new GameMatch($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ------------------- GET -------------------------
    case 'GET':
        if (isset($_GET['id_match'])) {
            $match = $matchModel->obtenirMatch($_GET['id_match']);
            if ($match) {
                echo json_encode($match);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Match non trouvé"]);
            }
        } else {
            // Si filtre statut fourni
            if (isset($_GET['statut'])) {
                $matches = $matchModel->obtenirMatchsParStatut($_GET['statut']);
            } else {
                $matches = $matchModel->obtenirTousLesMatchs();
            }
            echo json_encode($matches);
        }
        break;

    // ------------------- POST -------------------------
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Données JSON manquantes ou invalides"]);
            exit;
        }

        $required = ['date_match', 'heure_match', 'nom_equipe_adverse', 'lieu_de_rencontre'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Champ requis manquant : $field"]);
                exit;
            }
        }

        $ok = $matchModel->ajouterMatch($data);
        if ($ok) {
            http_response_code(201);
            echo json_encode(["message" => "Match créé avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la création du match"]);
        }
        break;

    // ------------------- PUT -------------------------
    case 'PUT':
        if (!isset($_GET['id_match'])) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'id_match' requis"]);
            exit;
        }

        $existing = $matchModel->obtenirMatch($_GET['id_match']);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Match introuvable"]);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Données JSON invalides"]);
            exit;
        }

        $data['id_match'] = $_GET['id_match'];

        $ok = $matchModel->mettreAJourMatch($data);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message" => "Match mis à jour avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la mise à jour"]);
        }
        break;

    // ------------------- DELETE -------------------------
    case 'DELETE':
        if (!isset($_GET['id_match'])) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'id_match' requis"]);
            exit;
        }

        $existing = $matchModel->obtenirMatch($_GET['id_match']);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Match introuvable"]);
            exit;
        }

        $ok = $matchModel->supprimerMatch($_GET['id_match']);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message" => "Match supprimé"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la suppression"]);
        }
        break;

    // ------------------- AUTRES -------------------------
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode HTTP non autorisée"]);
        break;
}
