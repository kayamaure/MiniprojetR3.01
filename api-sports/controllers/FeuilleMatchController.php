<?php
/**
 * Contrôleur de gestion des feuilles de match
 * Gère toutes les opérations liées aux feuilles de match via l'API JSON
 */

header("Content-Type: application/json");

// Récupération des données JSON pour les méthodes POST
$inputData = json_decode(file_get_contents("php://input"), true);

// Inclusion des modèles et fichiers de configuration nécessaires
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeuilleMatch.php';
require_once __DIR__ . '/../models/Match.php';        // Définition de la classe GameMatch
require_once __DIR__ . '/../models/Joueur.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Participer.php';

// Création de la connexion à la base de données et instanciation des modèles
$database = new Database();
$db = $database->getConnection();
$feuilleMatch = new FeuilleMatch($db);
$commentaireModel = new Commentaire($db);

// Récupération de l'action demandée depuis les paramètres GET (par défaut: 'afficher')
$action = $_GET['action'] ?? 'afficher';

switch ($action) {

    case 'afficher':
        // Affichage des détails de la feuille de match (match, titulaires et remplaçants)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Récupération des informations du match et des joueurs
        $gameMatch = new GameMatch($db);
        $match = $gameMatch->obtenirMatch($id_match);
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
        $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match);

        // Envoi de la réponse avec toutes les informations
        echo json_encode([
            "success" => true,
            "match" => $match,
            "titulaires" => $titulaires,
            "remplacants" => $remplacants
        ]);
        exit;
        break;

    case 'ajouter':
        // Vérification de l'ID du match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Méthode GET : Retourne les informations du match et la liste des joueurs non sélectionnés
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Récupération des données du match
            $gameMatch = new GameMatch($db);
            $match = $gameMatch->obtenirMatch($id_match);

            // Récupération et enrichissement des données des joueurs
            $joueursNonSelectionnes = $feuilleMatch->obtenirJoueursNonSelectionnes($id_match);
            foreach ($joueursNonSelectionnes as &$joueur) {
                // Ajout des évaluations moyennes et du dernier commentaire
                $joueur['moyenne_evaluation'] = $feuilleMatch->obtenirMoyenneEvaluation($joueur['numero_licence']);
                $joueur['commentaire'] = $commentaireModel->obtenirDernierCommentaireParJoueur($joueur['numero_licence']);
            }
            unset($joueur);

            // Envoi de la réponse
            echo json_encode([
                "success" => true,
                "match" => $match,
                "joueursNonSelectionnes" => $joueursNonSelectionnes
            ]);
            exit;
        }

        // Méthode POST : Ajout d'un joueur à la feuille de match
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification des champs requis
            if (!isset($inputData['numero_licence'], $inputData['id_match'], $inputData['role'], $inputData['poste'])) {
                echo json_encode(["error" => "Champs requis manquants."]);
                exit;
            }

            // Préparation des données du joueur
            $data = [
                'numero_licence' => $inputData['numero_licence'],
                'id_match'        => $inputData['id_match'],
                'role'            => $inputData['role'],
                'poste'           => $inputData['poste']
            ];

            // Tentative d'ajout du joueur
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
        // Récupération de la liste des joueurs à évaluer pour un match donné
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Récupération des joueurs ayant participé au match
        $participe = $feuilleMatch->obtenirJoueursParMatch($id_match);
        echo json_encode(["success" => true, "joueurs" => $participe]);
        exit;
        break;

    case 'valider_evaluation':
        // Validation et mise à jour des évaluations des joueurs pour un match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Vérification de la présence d'évaluations
        if (empty($inputData['evaluations'])) {
            echo json_encode(["error" => "Aucune évaluation soumise."]);
            exit;
        }

        // Récupération des données d'évaluation
        $evaluations = $inputData['evaluations'];
        $roles = $inputData['roles'] ?? [];
        $postes = $inputData['postes'] ?? [];

        try {
            // Vérification de la validité des notes d'évaluation
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
        // Récupération des joueurs actuels (titulaires et remplaçants) pour modification
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Récupération des listes de joueurs
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match) ?? [];
        $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match) ?? [];

        // Envoi de la réponse avec les listes de joueurs
        echo json_encode([
            "success" => true,
            "titulaires" => $titulaires,
            "remplacants" => $remplacants
        ]);
        exit;
        break;

    case 'supprimer':
        // Suppression des joueurs sélectionnés de la feuille de match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Récupération des données de la requête
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
        // Validation de la sélection des joueurs pour un match (vérification du minimum de 11 titulaires)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        // Récupération des données de sélection
        $input = json_decode(file_get_contents("php://input"), true);
        $selections = $input['joueur_selectionnes'] ?? [];

        // Comptage des titulaires et validation des données des joueurs
        $titularCount = 0;
        $joueursValides = array_filter($selections, function ($selection) use (&$titularCount) {
            // Vérification des champs obligatoires
            if (!empty($selection['numero_licence']) && !empty($selection['role']) && !empty($selection['poste'])) {
                // Incrémentation du compteur pour les titulaires
                if ($selection['role'] === 'Titulaire') {
                    $titularCount++;
                }
                return true;
            }
            return false;
        });

        // Vérification du nombre minimum de titulaires requis
        if ($titularCount < 11) {
            echo json_encode(["error" => "Vous devez sélectionner au moins 11 titulaires."]);
            exit;
        }

        // Initialisation du modèle de participation
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
