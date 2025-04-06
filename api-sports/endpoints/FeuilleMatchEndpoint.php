<?php
header('Content-Type: application/json');

// (1) Charger les dépendances
require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeuilleMatch.php';
require_once __DIR__ . '/../models/Match.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Participer.php';

// (2) Vérifier le token, sinon quitter
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// (3) Créer les objets nécessaires
$database = new Database();
$db = $database->getConnection();
$feuilleMatch = new FeuilleMatch($db);
$gameMatch = new GameMatch($db);
$commentaireModel = new Commentaire($db);
$participerModel = new Participer($db);

// (4) Lire la méthode HTTP + input
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id_match = $_GET['id_match'] ?? null;
$input = json_decode(file_get_contents("php://input"), true);

// (5) Routage
switch ($method) {
    // ----- GET : Récupération des données -----
    case 'GET':
        if (!$action) {
            http_response_code(400);
            echo json_encode(["error" => "Action non spécifiée"]);
            break;
        }

        switch ($action) {
            case 'afficher':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $match = $gameMatch->obtenirMatch($id_match);
                $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
                $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match);
                echo json_encode(["match" => $match, "titulaires" => $titulaires, "remplacants" => $remplacants]);
                break;

            case 'ajouter':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $match = $gameMatch->obtenirMatch($id_match);
                $joueurs = $feuilleMatch->obtenirJoueursNonSelectionnes($id_match);
                foreach ($joueurs as &$j) {
                    $j['moyenne_evaluation'] = $feuilleMatch->obtenirMoyenneEvaluation($j['numero_licence']);
                    $j['commentaire'] = $commentaireModel->obtenirDernierCommentaireParJoueur($j['numero_licence']);
                }
                echo json_encode(["match" => $match, "joueursNonSelectionnes" => $joueurs]);
                break;

            case 'evaluer':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $joueurs = $feuilleMatch->obtenirJoueursParMatch($id_match);
                echo json_encode(["joueurs" => $joueurs]);
                break;

            default:
                http_response_code(400);
                echo json_encode(["error" => "Action GET '$action' inconnue"]);
                break;
        }
        break;

    // ----- POST : Ajout ou validation -----
    case 'POST':
        switch ($action) {
            case 'ajouter':
                $required = ['numero_licence', 'id_match', 'role', 'poste'];
                foreach ($required as $field) {
                    if (empty($input[$field])) return erreur("Champ '$field' manquant");
                }
                try {
                    $feuilleMatch->ajouterJoueur($input);
                    http_response_code(201);
                    echo json_encode(["message" => "Joueur ajouté à la feuille de match"]);
                } catch (Exception $e) {
                    return erreur($e->getMessage());
                }
                break;

            case 'valider_evaluation':
                if (!$id_match || empty($input['evaluations'])) {
                    return erreur("ID du match ou évaluations manquants");
                }

                foreach ($input['evaluations'] as $numero_licence => $note) {
                    if (!is_numeric($note) || $note < 1 || $note > 5) {
                        return erreur("Note invalide pour $numero_licence");
                    }

                    $feuilleMatch->mettreAJourEvaluation([
                        'evaluation' => $note,
                        'numero_licence' => $numero_licence,
                        'id_match' => $id_match,
                        'role' => $input['roles'][$numero_licence] ?? null,
                        'poste' => $input['postes'][$numero_licence] ?? null
                    ]);
                }

                echo json_encode(["message" => "Évaluations enregistrées"]);
                break;

            case 'valider_feuille':
                if (!$id_match) return erreur("ID du match manquant");
                $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
                if (count($titulaires) < 11) {
                    return erreur("Vous devez avoir au moins 11 titulaires pour valider");
                }

                $ok = $feuilleMatch->mettreAJourEtatMatch($id_match, 'Validé');
                echo json_encode($ok ? ["message" => "Feuille validée"] : ["error" => "Erreur lors de la validation"]);
                break;

            default:
                http_response_code(400);
                echo json_encode(["error" => "Action POST '$action' inconnue"]);
                break;
        }
        break;

    // ----- Autres méthodes non supportées -----
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}

// (6) Fonction utilitaire
function erreur($message)
{
    http_response_code(400);
    echo json_encode(["error" => $message]);
    exit;
}
