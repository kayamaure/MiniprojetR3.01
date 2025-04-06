<?php
header('Content-Type: application/json');

// (1) Charger les dépendances
require_once __DIR__ . '/../utils/verifierJeton.php';   // ← Fichier qui vérifie le token auprès de l'API Auth
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Joueur.php';

// (2) Vérifier le token, sinon quitter (401 ou 403)
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth'); 
// Adaptation de l’URL au besoin

// (3) Créer les objets nécessaires
$database = new Database();
$db = $database->getConnection();
$joueurModel = new Joueur($db);

$method = $_SERVER['REQUEST_METHOD'];

/**
 * EXPLICATION :
 * - GET : liste (ou détail si param GET['numero_licence'])
 * - POST : création (on attend un JSON dans le corps)
 * - PUT : modification (on attend un JSON + param GET['numero_licence'])
 * - DELETE : suppression (on attend param GET['numero_licence'])
 */
switch ($method) {
    // ------------- GET (consultation) ------------------
    case 'GET':
        if (isset($_GET['numero_licence'])) {
            // Détail d'un joueur
            $numeroLicence = $_GET['numero_licence'];
            $joueur = $joueurModel->getById($numeroLicence);
            if (!$joueur) {
                http_response_code(404);
                echo json_encode(["error" => "Joueur introuvable"]);
                exit;
            }
            echo json_encode($joueur);
        } else {
            // Liste de tous les joueurs
            $liste = $joueurModel->getAll();
            echo json_encode($liste);
        }
        break;

    // ------------- POST (création) --------------------
    case 'POST':
        // On récupère les données JSON envoyées
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Corps de requête JSON invalide ou manquant"]);
            exit;
        }

        // Vérifier les champs obligatoires
        $requiredFields = ['numero_licence','nom','prenom','date_naissance','taille','poids','statut'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Champ '$field' manquant ou vide"]);
                exit;
            }
        }

        // Insérer en base
        $ok = $joueurModel->create($data);
        if ($ok) {
            http_response_code(201);
            echo json_encode(["message"=>"Joueur créé avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error"=>"Erreur lors de la création du joueur"]);
        }
        break;

    // ------------- PUT (modification) ------------------
    case 'PUT':
        if (!isset($_GET['numero_licence'])) {
            http_response_code(400);
            echo json_encode(["error"=>"Paramètre 'numero_licence' manquant dans l'URL"]);
            exit;
        }
        $numeroLicence = $_GET['numero_licence'];

        // Vérifier que ce joueur existe
        $existing = $joueurModel->getById($numeroLicence);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error"=>"Joueur introuvable"]);
            exit;
        }

        // Lire le corps JSON
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Corps de requête JSON invalide ou manquant"]);
            exit;
        }

        // À minima, on veut ces champs (tu peux en exiger moins si tu veux)
        $requiredFields = ['nom','prenom','date_naissance','taille','poids','statut'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Champ '$field' manquant pour la modification"]);
                exit;
            }
        }

        // Injecter 'numero_licence' dans $data pour la requête
        $data['numero_licence'] = $numeroLicence;

        // Mise à jour
        $ok = $joueurModel->update($data);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message"=>"Joueur mis à jour"]);
        } else {
            http_response_code(500);
            echo json_encode(["error"=>"Erreur lors de la mise à jour"]);
        }
        break;

    // ------------- DELETE (suppression) ----------------
    case 'DELETE':
        if (!isset($_GET['numero_licence'])) {
            http_response_code(400);
            echo json_encode(["error"=>"Paramètre 'numero_licence' manquant"]);
            exit;
        }
        $numeroLicence = $_GET['numero_licence'];

        // Vérifier que le joueur existe
        $existing = $joueurModel->getById($numeroLicence);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error"=>"Joueur introuvable"]);
            exit;
        }

        // Supprimer
        $ok = $joueurModel->delete($numeroLicence);
        if ($ok) {
            http_response_code(200);
            echo json_encode(["message"=>"Joueur supprimé"]);
        } else {
            http_response_code(500);
            echo json_encode(["error"=>"Erreur lors de la suppression"]);
        }
        break;

    // ------------- Sinon : 405 Method Not Allowed ------
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}
