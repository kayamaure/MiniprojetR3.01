<?php
header('Content-Type: application/json');

// (1) Charger les dépendances
require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Statistiques.php';
require_once __DIR__ . '/../models/Joueur.php';

// (2) Vérifier le token JWT
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// (3) Instancier les objets
$database = new Database();
$db = $database->getConnection();
$statsModel = new Statistiques($db);
$joueurModel = new Joueur($db);

// (4) Vérifier méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée. Seul GET est permis."]);
    exit;
}

// (5) Récupérer les paramètres
$type = $_GET['type'] ?? null; // 'matchs' ou 'joueur'
$numeroLicence = $_GET['numero_licence'] ?? null;

// (6) Routage selon le type demandé
switch ($type) {
    case 'matchs':
        $totaux = $statsModel->obtenirStatistiquesMatchs();
        $pourcentages = $statsModel->obtenirPourcentagesMatchs();
        echo json_encode([
            "totaux" => $totaux,
            "pourcentages" => $pourcentages
        ]);
        break;

    case 'joueur':
        if (!$numeroLicence) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'numero_licence' requis"]);
            exit;
        }

        $joueur = $joueurModel->getById($numeroLicence);
        if (!$joueur) {
            http_response_code(404);
            echo json_encode(["error" => "Joueur introuvable"]);
            exit;
        }

        $data = [
            "statut" => $statsModel->obtenirStatutJoueur($numeroLicence),
            "poste_prefere" => $statsModel->obtenirPostePrefere($numeroLicence),
            "titularisations" => $statsModel->obtenirNombreTitularisations($numeroLicence),
            "remplacements" => $statsModel->obtenirNombreRemplacements($numeroLicence),
            "moyenne_evaluations" => $statsModel->obtenirMoyenneEvaluations($numeroLicence),
            "pourcentage_matchs_gagnes" => $statsModel->obtenirPourcentageMatchsGagnes($numeroLicence),
            "selections_consecutives" => $statsModel->obtenirSelectionsConsecutives($numeroLicence)
        ];

        echo json_encode($data);
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Paramètre 'type' invalide ou manquant. Utilisez 'type=matchs' ou 'type=joueur'."]);
        break;
}
