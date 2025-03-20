<?php
// FeuilleMatchController.php for API (api-sports)
// This controller handles actions for match sheets (feuilles de match) via JSON API

header("Content-Type: application/json");

// Use JSON input for POST methods
$inputData = json_decode(file_get_contents("php://input"), true);

// Include required model and configuration files using absolute paths
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeuilleMatch.php';
require_once __DIR__ . '/../models/Match.php';      // Assume GameMatch is defined here
require_once __DIR__ . '/../models/Joueur.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Participer.php';

// Create database connection and instantiate the models
$database = new Database();
$db = $database->getConnection();
$feuilleMatch = new FeuilleMatch($db);
$commentaireModel = new Commentaire($db);

// Get the requested action from the GET parameters; default to "afficher"
$action = $_GET['action'] ?? 'afficher';

switch ($action) {

    case 'afficher':
        // Display match sheet details (match, titulaires and remplacants)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        // Assuming GameMatch is defined in Match.php
        $gameMatch = new GameMatch($db);
        $match = $gameMatch->obtenirMatch($id_match);
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
        $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match);

        echo json_encode([
            "success" => true,
            "match" => $match,
            "titulaires" => $titulaires,
            "remplacants" => $remplacants
        ]);
        exit;
        break;

    case 'ajouter':
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        // If GET, return match info with unselected players
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $gameMatch = new GameMatch($db);
            $match = $gameMatch->obtenirMatch($id_match);
            $joueursNonSelectionnes = $feuilleMatch->obtenirJoueursNonSelectionnes($id_match);
            foreach ($joueursNonSelectionnes as &$joueur) {
                $joueur['moyenne_evaluation'] = $feuilleMatch->obtenirMoyenneEvaluation($joueur['numero_licence']);
                $joueur['commentaire'] = $commentaireModel->obtenirDernierCommentaireParJoueur($joueur['numero_licence']);
            }
            unset($joueur);
            echo json_encode([
                "success" => true,
                "match" => $match,
                "joueursNonSelectionnes" => $joueursNonSelectionnes
            ]);
            exit;
        }
        // If POST, add a player to the match sheet
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($inputData['numero_licence'], $inputData['id_match'], $inputData['role'], $inputData['poste'])) {
                echo json_encode(["error" => "Champs requis manquants."]);
                exit;
            }
            $data = [
                'numero_licence' => $inputData['numero_licence'],
                'id_match'        => $inputData['id_match'],
                'role'            => $inputData['role'],
                'poste'           => $inputData['poste']
            ];
            try {
                $feuilleMatch->ajouterJoueur($data);
                echo json_encode(["success" => "Joueur ajouté avec succès."]);
                exit;
            } catch (Exception $e) {
                echo json_encode(["error" => "Erreur: " . $e->getMessage()]);
                exit;
            }
        }
        break;

    case 'evaluer':
        // Retrieve players to be evaluated for a given match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $participe = $feuilleMatch->obtenirJoueursParMatch($id_match);
        echo json_encode(["success" => true, "joueurs" => $participe]);
        exit;
        break;

    case 'valider_evaluation':
        // Validate and update evaluations for players in a match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        if (empty($inputData['evaluations'])) {
            echo json_encode(["error" => "Aucune évaluation soumise."]);
            exit;
        }
        $evaluations = $inputData['evaluations'];
        $roles = $inputData['roles'] ?? [];
        $postes = $inputData['postes'] ?? [];
        try {
            foreach ($evaluations as $numero_licence => $evaluation) {
                if (!is_numeric($evaluation) || $evaluation < 1 || $evaluation > 5) {
                    echo json_encode(["error" => "Les évaluations doivent être des nombres entre 1 et 5."]);
                    exit;
                }
                $data = [
                    'evaluation'      => $evaluation,
                    'numero_licence'  => $numero_licence,
                    'id_match'        => $id_match,
                    'role'            => $roles[$numero_licence] ?? null,
                    'poste'           => $postes[$numero_licence] ?? null
                ];
                $feuilleMatch->mettreAJourEvaluation($data);
            }
            echo json_encode(["success" => "Évaluations enregistrées avec succès."]);
            exit;
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur lors de l'enregistrement: " . $e->getMessage()]);
            exit;
        }
        break;

    case 'modifier':
        // Return current players (titulaires and remplacants) for modification
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match) ?? [];
        $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match) ?? [];
        echo json_encode([
            "success" => true,
            "titulaires" => $titulaires,
            "remplacants" => $remplacants
        ]);
        exit;
        break;

    case 'supprimer':
        // Delete selected players from a match sheet
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        $joueursASupprimer = $input['joueur_a_supprimer'] ?? [];
        if (empty($joueursASupprimer)) {
            echo json_encode(["error" => "Aucun joueur sélectionné pour suppression."]);
            exit;
        }
        try {
            foreach ($joueursASupprimer as $joueur) {
                $numeroLicence = $joueur['numero_licence'];
                $feuilleMatch->supprimerJoueurParLicenceEtMatch($numeroLicence, $id_match);
            }
            echo json_encode(["success" => "Les joueurs sélectionnés ont été supprimés avec succès."]);
            exit;
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur lors de la suppression: " . $e->getMessage()]);
            exit;
        }
        break;

    case 'valider_selection':
        // Validate the player selection for a match (ensure at least 11 titulaires)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        $selections = $input['joueur_selectionnes'] ?? [];
        $titularCount = 0;
        $joueursValides = array_filter($selections, function ($selection) use (&$titularCount) {
            if (!empty($selection['numero_licence']) && !empty($selection['role']) && !empty($selection['poste'])) {
                if ($selection['role'] === 'Titulaire') {
                    $titularCount++;
                }
                return true;
            }
            return false;
        });
        if ($titularCount < 11) {
            echo json_encode(["error" => "Vous devez sélectionner au moins 11 titulaires."]);
            exit;
        }
        $participerModel = new Participer($db);
        try {
            foreach ($joueursValides as $selection) {
                $data = [
                    'numero_licence' => $selection['numero_licence'],
                    'id_match'       => $id_match,
                    'role'           => $selection['role'],
                    'poste'          => $selection['poste'],
                    'evaluation'     => null
                ];
                $participerModel->ajouterSelection($data);
            }
            echo json_encode(["success" => "Sélection validée avec succès."]);
            exit;
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur: " . $e->getMessage()]);
            exit;
        }
        break;

    case 'valider_modification':
        // Validate modifications to the player selection
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $input = json_decode(file_get_contents("php://input"), true);
        $selections = $input['joueur_selectionnes'] ?? [];
        try {
            foreach ($selections as $selection) {
                $data = [
                    'numero_licence' => $selection['numero_licence'],
                    'id_match'       => $id_match,
                    'role'           => $selection['role'],
                    'poste'          => $selection['poste']
                ];
                $feuilleMatch->modifierSelection($data);
            }
            echo json_encode(["success" => "La sélection a été modifiée avec succès."]);
            exit;
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur lors de la modification: " . $e->getMessage()]);
            exit;
        }
        break;

    case 'valider_feuille':
        // Validate the match sheet by ensuring there are at least 11 titulaires and updating its state
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
        if (count($titulaires) < 11) {
            echo json_encode(["error" => "La feuille de match ne peut pas être validée : moins de 11 titulaires."]);
            exit;
        }
        $result = $feuilleMatch->mettreAJourEtatMatch($id_match, 'Validé');
        if ($result) {
            echo json_encode(["success" => "La feuille de match a été validée avec succès."]);
        } else {
            echo json_encode(["error" => "Erreur lors de la validation de la feuille de match."]);
        }
        exit;
        break;

    default:
        echo json_encode(["error" => "Action non reconnue."]);
        exit;
        break;
}
?>
