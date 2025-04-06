<?php
header('Content-Type: application/json');

// (1) Charger les dépendances
require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Commentaire.php';

// (2) Vérifier le token, sinon quitter
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// (3) Créer les objets nécessaires
$database = new Database();
$db = $database->getConnection();
$commentaireModel = new Commentaire($db);

// (4) Lire la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

/**
 * EXPLICATION :
 * - GET : Liste ou détails des commentaires d’un joueur (via ?numero_licence=)
 * - POST : Ajouter un commentaire (JSON dans le corps)
 */
switch ($method) {
    // ---------- GET (consultation) ----------
    case 'GET':
        if (!isset($_GET['numero_licence'])) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'numero_licence' requis"]);
            exit;
        }

        $numeroLicence = $_GET['numero_licence'];
        $commentaires = $commentaireModel->obtenirCommentairesParJoueur($numeroLicence);

        if (empty($commentaires)) {
            http_response_code(404);
            echo json_encode(["error" => "Aucun commentaire trouvé pour ce joueur"]);
        } else {
            echo json_encode($commentaires);
        }
        break;

    // ---------- POST (création) ----------
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Corps JSON invalide ou manquant"]);
            exit;
        }

        // Champs obligatoires
        $requiredFields = ['sujet_commentaire', 'texte_commentaire', 'numero_licence'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Champ '$field' requis"]);
                exit;
            }
        }

        // Enregistrement en base
        $ok = $commentaireModel->ajouterCommentaire($data);
        if ($ok) {
            http_response_code(201);
            echo json_encode(["message" => "Commentaire ajouté avec succès"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de l'ajout du commentaire"]);
        }
        break;

    // ---------- Méthodes non autorisées ----------
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}
