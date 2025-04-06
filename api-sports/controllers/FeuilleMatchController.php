<?php
// Contrôleur FeuilleMatchController.php (API pour la gestion des feuilles de match)

header("Content-Type: application/json");

// Récupération des données envoyées en JSON (pour les méthodes POST/PUT)
$inputData = json_decode(file_get_contents("php://input"), true);

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeuilleMatch.php';
require_once __DIR__ . '/../models/Match.php';
require_once __DIR__ . '/../models/Joueur.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Participer.php';

// Connexion à la base de données + instanciation des modèles
$database = new Database();
$db = $database->getConnection();

$feuilleMatch = new FeuilleMatch($db);
$commentaireModel = new Commentaire($db);

// Récupération de l'action depuis l'URL (par défaut "afficher")
$action = $_GET['action'] ?? 'afficher';

switch ($action) {

    case 'afficher':
        // Affiche les infos d'un match + les titulaires et remplaçants
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

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

    case 'ajouter':
        // Soit on récupère les joueurs dispo (GET), soit on ajoute un joueur (POST)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $gameMatch = new GameMatch($db);
            $match = $gameMatch->obtenirMatch($id_match);
            $joueurs = $feuilleMatch->obtenirJoueursNonSelectionnes($id_match);

            foreach ($joueurs as &$j) {
                $j['moyenne_evaluation'] = $feuilleMatch->obtenirMoyenneEvaluation($j['numero_licence']);
                $j['commentaire'] = $commentaireModel->obtenirDernierCommentaireParJoueur($j['numero_licence']);
            }

            echo json_encode([
                "success" => true,
                "match" => $match,
                "joueursNonSelectionnes" => $joueurs
            ]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($inputData['numero_licence'], $inputData['id_match'], $inputData['role'], $inputData['poste'])) {
                echo json_encode(["error" => "Champs requis manquants."]);
                exit;
            }

            $data = [
                'numero_licence' => $inputData['numero_licence'],
                'id_match'       => $inputData['id_match'],
                'role'           => $inputData['role'],
                'poste'          => $inputData['poste']
            ];

            try {
                $feuilleMatch->ajouterJoueur($data);
                echo json_encode(["success" => "Joueur ajouté avec succès."]);
            } catch (Exception $e) {
                echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
            }
            exit;
        }
        break;

    case 'evaluer':
        // Récupère les joueurs d’un match pour les évaluer
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        $joueurs = $feuilleMatch->obtenirJoueursParMatch($id_match);
        echo json_encode(["success" => true, "joueurs" => $joueurs]);
        exit;

    case 'valider_evaluation':
        // Valide les évaluations des joueurs après un match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match || empty($inputData['evaluations'])) {
            echo json_encode(["error" => "ID ou évaluations manquants."]);
            exit;
        }

        $evaluations = $inputData['evaluations'];
        $roles = $inputData['roles'] ?? [];
        $postes = $inputData['postes'] ?? [];

        try {
            foreach ($evaluations as $licence => $note) {
                if (!is_numeric($note) || $note < 1 || $note > 5) {
                    echo json_encode(["error" => "Note invalide pour $licence."]);
                    exit;
                }

                $data = [
                    'evaluation'     => $note,
                    'numero_licence' => $licence,
                    'id_match'       => $id_match,
                    'role'           => $roles[$licence] ?? null,
                    'poste'          => $postes[$licence] ?? null
                ];
                $feuilleMatch->mettreAJourEvaluation($data);
            }

            echo json_encode(["success" => "Évaluations enregistrées avec succès."]);
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
        }
        exit;

    case 'modifier':
        // Récupère les joueurs sélectionnés pour modification
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

    case 'supprimer':
        // Supprime des joueurs de la sélection
        $id_match = $_GET['id_match'] ?? null;
        $ids = $inputData['joueurs_a_supprimer'] ?? [];

        if (!$id_match || empty($ids)) {
            echo json_encode(["error" => "ID du match ou joueurs à supprimer manquants."]);
            exit;
        }

        try {
            foreach ($ids as $id) {
                $feuilleMatch->supprimerJoueurDuMatch($id);
            }
            echo json_encode(["success" => "Joueurs supprimés avec succès."]);
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
        }
        exit;

    case 'valider_selection':
        // Ajoute une sélection complète de joueurs (11 titulaires min)
        $id_match = $_GET['id_match'] ?? null;
        $selections = $inputData['joueur_selectionnes'] ?? [];

        if (!$id_match || empty($selections)) {
            echo json_encode(["error" => "Paramètres manquants."]);
            exit;
        }

        $titularCount = 0;
        $joueursValides = array_filter($selections, function ($s) use (&$titularCount) {
            if (!empty($s['numero_licence']) && !empty($s['role']) && !empty($s['poste'])) {
                if ($s['role'] === 'Titulaire') $titularCount++;
                return true;
            }
            return false;
        });

        if ($titularCount < 11) {
            echo json_encode(["error" => "Au moins 11 titulaires requis."]);
            exit;
        }

        $participerModel = new Participer($db);
        try {
            foreach ($joueursValides as $s) {
                $participerModel->ajouterSelection([
                    'numero_licence' => $s['numero_licence'],
                    'id_match'       => $id_match,
                    'role'           => $s['role'],
                    'poste'          => $s['poste'],
                    'evaluation'     => null
                ]);
            }
            echo json_encode(["success" => "Sélection enregistrée."]);
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
        }
        exit;

    case 'valider_modification':
        // Enregistre la modification d'une sélection déjà faite
        $id_match = $_GET['id_match'] ?? null;
        $selections = $inputData['joueur_selectionnes'] ?? [];

        if (!$id_match || empty($selections)) {
            echo json_encode(["error" => "Paramètres manquants."]);
            exit;
        }

        try {
            foreach ($selections as $s) {
                $feuilleMatch->modifierSelection([
                    'numero_licence' => $s['numero_licence'],
                    'id_match'       => $id_match,
                    'role'           => $s['role'],
                    'poste'          => $s['poste']
                ]);
            }
            echo json_encode(["success" => "Sélection mise à jour."]);
        } catch (Exception $e) {
            echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
        }
        exit;

    case 'valider_feuille':
        // Valide définitivement une feuille de match (s'il y a assez de titulaires)
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(["error" => "ID du match non spécifié."]);
            exit;
        }

        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
        if (count($titulaires) < 11) {
            echo json_encode(["error" => "Moins de 11 titulaires, validation impossible."]);
            exit;
        }

        $ok = $feuilleMatch->mettreAJourEtatMatch($id_match, 'Validé');
        echo json_encode($ok
            ? ["success" => "Feuille validée."]
            : ["error" => "Erreur lors de la validation."]
        );
        exit;

    default:
        echo json_encode(["error" => "Action non reconnue."]);
        exit;
}
