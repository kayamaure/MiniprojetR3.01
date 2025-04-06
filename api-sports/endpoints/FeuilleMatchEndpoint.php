<?php
header('Content-Type: application/json');

// (1) Dépendances
require_once __DIR__ . '/../utils/verifierJeton.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeuilleMatch.php';
require_once __DIR__ . '/../models/Match.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Participer.php';

// (2) Vérification JWT
verifierJetonOuQuitter('http://localhost/MiniprojetR3.01/api-auth/public/auth');

// (3) Initialisation
$database = new Database();
$db = $database->getConnection();
$feuilleMatch = new FeuilleMatch($db);
$gameMatch = new GameMatch($db);
$commentaireModel = new Commentaire($db);
$participerModel = new Participer($db);

$method = $_SERVER['REQUEST_METHOD'];
$subAction = $_GET['sub_action'] ?? null;
$id_match = $_GET['id_match'] ?? null;
$input = json_decode(file_get_contents("php://input"), true);

// (4) Routage
switch ($method) {
    // ------------- GET -------------
    case 'GET':
        if (!$subAction) return erreur("Sous-action GET non spécifiée");

        switch ($subAction) {
            case 'afficher':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $match = $gameMatch->obtenirMatch($id_match);
                $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
                $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match);
                echo json_encode([
                    "match" => $match,
                    "titulaires" => $titulaires,
                    "remplacants" => $remplacants
                ]);
                break;

            case 'ajouter':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $match = $gameMatch->obtenirMatch($id_match);
                $joueurs = $feuilleMatch->obtenirJoueursNonSelectionnes($id_match);
                foreach ($joueurs as &$j) {
                    $j['moyenne_evaluation'] = $feuilleMatch->obtenirMoyenneEvaluation($j['numero_licence']);
                    $j['commentaire'] = $commentaireModel->obtenirDernierCommentaireParJoueur($j['numero_licence']);
                }
                echo json_encode([
                    "match" => $match,
                    "joueursNonSelectionnes" => $joueurs
                ]);
                break;

            case 'evaluer':
                if (!$id_match) return erreur("Paramètre id_match manquant");
                $joueurs = $feuilleMatch->obtenirJoueursParMatch($id_match);
                echo json_encode(["joueurs" => $joueurs]);
                break;

                case 'modifier':
                    // Return current players (titulaires and remplacants) for modification
                    $id_match = $_GET['id_match'] ?? null;
                    if (!$id_match) {
                        echo json_encode(["error" => "ID du match non spécifié."]);
                        exit;
                    }
                    $joueursSelectionnes = $feuilleMatch->obtenirJoueursSelectionnes($id_match) ?? [];

                    echo json_encode([
                        "success" => true,
                        "match" => $gameMatch->obtenirMatch($id_match),
                        "joueursSelectionnes" => $joueursSelectionnes
                    ]);
                    exit;
                    break;

                    case 'supprimer':
                        if (!$id_match) return erreur("Paramètre id_match manquant");
                    
                        $match = $gameMatch->obtenirMatch($id_match);
                        $joueursSelectionnes = $feuilleMatch->obtenirJoueursSelectionnes($id_match);
                    
                        echo json_encode([
                            "match" => $match,
                            "joueursSelectionnes" => $joueursSelectionnes
                        ]);
                        break;



                    
                    
                
                

            default:
                http_response_code(400);
                echo json_encode(["error" => "Sous-action GET '$subAction' inconnue"]);
                break;
        }
        break;


    // ------------- PUT -------------
case 'PUT':
    if (!$subAction) return erreur("Sous-action PUT non spécifiée");

    switch ($subAction) {
        case 'valider_modification':
            if (!$id_match || empty($input['numero_licence']) || empty($input['role']) || empty($input['poste'])) {
                return erreur("ID du match ou données du joueur manquantes");
            }

            try {
                $feuilleMatch->modifierSelection([
                    'numero_licence' => $input['numero_licence'],
                    'id_match' => $id_match,
                    'role' => $input['role'],
                    'poste' => $input['poste']
                ]);

                echo json_encode(["success" => true, "message" => "Modification enregistrée avec succès."]);
            } catch (Exception $e) {
                return erreur("Erreur lors de la modification : " . $e->getMessage());
            }
            break;


            case 'valider_evaluation':
                if (!$id_match || empty($input['numero_licence']) || empty($input['evaluation'])) {
                    return erreur("ID du match, numéro de licence ou note manquants.");
                }
    
                if (!is_numeric($input['evaluation']) || $input['evaluation'] < 1 || $input['evaluation'] > 5) {
                    return erreur("La note doit être un nombre entre 1 et 5.");
                }
    
                try {
                    $ok = $feuilleMatch->mettreAJourEvaluation([
                        'numero_licence' => $input['numero_licence'],
                        'evaluation' => (int) $input['evaluation'],
                        'id_match' => $id_match
                    ]);
    
                    echo json_encode(["success" => "Évaluation enregistrée avec succès."]);
                } catch (Exception $e) {
                    return erreur("Erreur lors de l'enregistrement : " . $e->getMessage());
                }
    
                break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Sous-action PUT '$subAction' inconnue"]);
            break;
    }
    break;


    // ------------- POST -------------
    case 'POST':
        if (!$subAction) return erreur("Sous-action POST non spécifiée");

        switch ($subAction) {
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

 

            case 'valider_feuille':
                if (!$id_match) return erreur("ID du match manquant");
                $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
                if (count($titulaires) < 11) {
                    return erreur("Vous devez avoir au moins 11 titulaires pour valider");
                }

                $ok = $feuilleMatch->mettreAJourEtatMatch($id_match, 'Validé');
                echo json_encode($ok
                    ? ["message" => "Feuille validée"]
                    : ["error" => "Erreur lors de la validation"]
                );
                break;

                case 'valider_modification':
                    if (!$id_match || empty($input['numero_licence']) || empty($input['role']) || empty($input['poste'])) {
                        return erreur("Informations du joueur incomplètes");
                    }
                    
                    try {
                        $data = [
                            'numero_licence' => $input['numero_licence'],
                            'id_match'       => $id_match,
                            'role'           => $input['role'],
                            'poste'          => $input['poste']
                        ];
                        $feuilleMatch->modifierSelection($data);
                    
                        echo json_encode(["success" => true, "message" => "Joueur modifié avec succès."]);
                    } catch (Exception $e) {
                        return erreur("Erreur lors de la modification : " . $e->getMessage());
                    }

            default:
                http_response_code(400);
                echo json_encode(["error" => "Sous-action POST '$subAction' inconnue"]);
                break;
        }
        break;


        case 'DELETE':
            if (!$subAction) return erreur("Sous-action DELETE non spécifiée");

    switch ($subAction) {

case 'supprimer':
    $id_match = $_GET['id_match'] ?? null;
    $id_selection = $_GET['id_selection'] ?? null;

    if (!$id_match || !$id_selection) {
        echo json_encode(["error" => "ID du match ou ID du joueur manquant."]);
        exit;
    }

    try {
        $feuilleMatch->supprimerJoueurDuMatch($id_selection);
        echo json_encode(["success" => "Joueur supprimé avec succès."]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Erreur lors de la suppression : " . $e->getMessage()]);
    }

    exit;

    }
    break;

    // ------------- AUTRES MÉTHODES -------------
    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode HTTP non autorisée"]);
        break;
}

// (5) Fonction utilitaire
function erreur($message)
{
    http_response_code(400);
    echo json_encode(["error" => $message]);
    exit;
}
