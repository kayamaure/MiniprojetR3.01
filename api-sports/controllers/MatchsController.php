<?php
/**
 * Contrôleur des matchs
 *
 * Ce fichier gère les différentes actions liées à la gestion des matchs, 
 * telles que l'affichage de la liste des matchs, l'ajout, la modification, 
 * la suppression, et la consultation des détails d'un match.
 *
 * Actions disponibles :
 * - `liste` : Affiche tous les matchs enregistrés.
 * - `ajouter` : Permet d'ajouter un nouveau match.
 * - `modifier` : Permet de modifier les détails d'un match existant. Si le match est dans le passé, certains champs peuvent être gelés.
 * - `matches_a_venir` : Affiche uniquement les matchs à venir.
 * - `matches_passes` : Affiche uniquement les matchs déjà terminés.
 * - `details` : Affiche les informations détaillées pour un match spécifique.
 * - `supprimer` : Supprime un match spécifique.
 *
 * Les actions utilisent les méthodes du modèle `GameMatch` pour interagir avec la base de données.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détection si l'appel est fait depuis index.php ou directement
$isIncludedFromIndex = (strpos($_SERVER['SCRIPT_FILENAME'], 'index.php') !== false);

// Adaptation des chemins selon la source de l'appel
if ($isIncludedFromIndex) {
    require_once 'config/database.php';
    require_once 'models/Match.php';
    require_once 'models/FeuilleMatch.php';
    require_once 'models/Joueur.php';
} else {
    require_once '../config/database.php';
    require_once '../models/Match.php';
    require_once '../models/FeuilleMatch.php';
    require_once '../models/Joueur.php';
}

$database = new Database();
$db = $database->getConnection();
$gameMatch = new GameMatch($db);
$feuilleMatch = new FeuilleMatch($db);
$joueur = new Joueur($db);

// Vérifier si la requête est faite via l'API REST
$isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Si la requête vient de index.php, c'est toujours une requête API
if (basename($_SERVER['SCRIPT_FILENAME']) === 'index.php') {
    $isApiRequest = true;
}

$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'matchs': // Nouvelle action pour l'API REST
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        
        if ($filter === 'a_venir') {
            $matchs = $gameMatch->obtenirMatchsParStatut('À venir');
        } else if ($filter === 'passes') {
            $matchs = $gameMatch->obtenirMatchsParStatut('Terminé');
        } else {
            $matchs = $gameMatch->obtenirTousLesMatchs();
        }
        
        // Retourner les données au format JSON
        echo json_encode($matchs);
        break;

    case 'match':
        // Action pour récupérer les détails d'un match spécifique
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['error' => 'ID du match non spécifié']);
            break;
        }
        
        // Récupérer les détails du match
        $match = $gameMatch->obtenirMatch($id_match);
        
        if (!$match) {
            echo json_encode(['error' => 'Match non trouvé']);
            break;
        }
        
        // Si on a trouvé le match, renvoyer ses informations
        echo json_encode($match);
        break;

    case 'feuille_match':
        // Gestion de l'affichage des détails d'une feuille de match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        // Récupérer les détails du match
        $match = $gameMatch->obtenirMatch($id_match);
        
        // Récupérer les titulaires et les remplaçants
        $titulaires = $feuilleMatch->obtenirTitulairesParMatch($id_match);
        $remplacants = $feuilleMatch->obtenirRemplacantsParMatch($id_match);
        
        // Retourner le tout au format JSON
        echo json_encode([
            'success' => true,
            'match' => $match,
            'titulaires' => $titulaires,
            'remplacants' => $remplacants
        ]);
        break;

    case 'ajouter_match':
        // Gestion de l'ajout via API REST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données JSON envoyées
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$data) {
                // Si les données ne sont pas en JSON, utiliser $_POST
                $data = $_POST;
            }
            
            if (!empty($data)) {
                $result = $gameMatch->ajouterMatch($data);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Match ajouté avec succès', 'id_match' => $result]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'ajout du match']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Données invalides']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'modifier_match':
        // Gestion de la modification via API REST
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Obtenir les détails du match pour l'édition
            $match = $gameMatch->obtenirMatch($id_match);
            echo json_encode(['success' => true, 'match' => $match]);
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Récupérer les données JSON envoyées
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$data) {
                // Si les données ne sont pas en JSON, utiliser $_POST
                $data = $_POST;
            }
            
            $data['id_match'] = $id_match; // Assurer que l'ID est inclus
            
            $result = $gameMatch->mettreAJourMatch($data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Match mis à jour avec succès']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du match']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'supprimer_match':
        // Gestion de la suppression via API REST
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        $result = $gameMatch->supprimerMatch($id_match);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Match supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression du match']);
        }
        break;

    // Actions spécifiques pour les feuilles de match
    case 'modifier':
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        // Récupérer les détails du match
        $match = $gameMatch->obtenirMatch($id_match);
        
        // Récupérer les joueurs déjà sélectionnés pour ce match
        $joueursSelectionnes = $feuilleMatch->obtenirJoueursSelectionnes($id_match);
        
        // Retourner le tout au format JSON
        echo json_encode([
            'success' => true,
            'match' => $match,
            'joueursSelectionnes' => $joueursSelectionnes
        ]);
        break;

    case 'update_player':
        // Mise à jour du rôle et poste d'un joueur dans la feuille de match
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données JSON envoyées
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$data) {
                // Si les données ne sont pas en JSON, utiliser $_POST
                $data = $_POST;
            }
            
            if (empty($data['id_selection']) || empty($data['id_match'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                break;
            }
            
            $result = $feuilleMatch->mettreAJourJoueurSelection(
                $data['id_selection'],
                $data['role'] ?? null,
                $data['poste'] ?? null
            );
            
            if ($result) {
                // Récupérer la liste mise à jour des joueurs
                $joueursSelectionnes = $feuilleMatch->obtenirJoueursSelectionnes($data['id_match']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Joueur mis à jour avec succès',
                    'joueursSelectionnes' => $joueursSelectionnes
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du joueur']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'valider_feuille':
        // Validation de la feuille de match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        // Mettre à jour l'état de la feuille de match
        $result = $feuilleMatch->mettreAJourEtatMatch($id_match, 'Validé');
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Feuille de match validée avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la validation de la feuille de match']);
        }
        break;

    case 'evaluer':
        // Gestion de l'évaluation des joueurs d'un match
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            break;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Récupérer les détails du match
            $match = $gameMatch->obtenirMatch($id_match);
            
            // Récupérer tous les joueurs sélectionnés pour le match
            $joueursSelectionnes = $feuilleMatch->obtenirJoueursSelectionnes($id_match);
            
            // Retourner le tout au format JSON
            echo json_encode([
                'success' => true,
                'match' => $match,
                'joueursSelectionnes' => $joueursSelectionnes
            ]);
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Mise à jour de l'évaluation d'un joueur
            // Récupérer les données JSON envoyées
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$data) {
                // Si les données ne sont pas en JSON, utiliser $_POST
                $data = $_POST;
            }
            
            if (empty($data['id_selection']) || !isset($data['note'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                break;
            }
            
            // Mettre à jour l'évaluation
            $result = $feuilleMatch->mettreAJourEvaluation([
                'id_selection' => $data['id_selection'],
                'numero_licence' => null, // On n'a pas cette donnée, mais on peut l'obtenir par l'id_selection
                'id_match' => $data['id_match'],
                'evaluation' => $data['note']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Évaluation enregistrée avec succès']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement de l\'évaluation']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    // Les cas suivants sont pour l'interface directe, pas pour l'API REST
    case 'liste':
        if ($isApiRequest) {
            $matchs = $gameMatch->obtenirTousLesMatchs();
            echo json_encode($matchs);
        } else {
            $matchs = $gameMatch->obtenirTousLesMatchs();
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/index.php' : '../views/matchs/index.php';
            require $viewPath;
        }
        break;

    case 'ajouter':
        if ($isApiRequest) {
            // Cette section est gérée par ajouter_match pour l'API
            echo json_encode(['success' => false, 'error' => 'Utilisez ajouter_match pour l\'API REST']);
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'date_match' => $_POST['date_match'],
                    'heure_match' => $_POST['heure_match'],
                    'nom_equipe_adverse' => $_POST['nom_equipe_adverse'],
                    'lieu_de_rencontre' => $_POST['lieu_de_rencontre'],
                ];
                $gameMatch->ajouterMatch($data);
                header("Location: MatchsController.php?action=liste");
                exit();
            }
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/ajouter.php' : '../views/matchs/ajouter.php';
            require $viewPath;
        }
        break;

    case 'matches_a_venir':
        if ($isApiRequest) {
            $matchs = $gameMatch->obtenirMatchsParStatut('À venir');
            echo json_encode($matchs);
        } else {
            $matchs = $gameMatch->obtenirMatchsParStatut('À venir');
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/index.php' : '../views/matchs/index.php';
            require $viewPath;
        }
        break;

    case 'matches_passes':
        if ($isApiRequest) {
            $matchs = $gameMatch->obtenirMatchsParStatut('Terminé');
            echo json_encode($matchs);
        } else {
            $matchs = $gameMatch->obtenirMatchsParStatut('Terminé');
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/index.php' : '../views/matchs/index.php';
            require $viewPath;
        }
        break;

    case 'details':
        $id_match = $_GET['id_match'] ?? null;
        if (!$id_match) {
            if ($isApiRequest) {
                echo json_encode(['success' => false, 'error' => 'ID du match non spécifié']);
            } else {
                echo "ID du match non spécifié.";
            }
            exit;
        }
        
        $match = $gameMatch->obtenirMatch($id_match);
        
        if ($isApiRequest) {
            echo json_encode(['success' => true, 'match' => $match]);
        } else {
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/details.php' : '../views/matchs/details.php';
            require $viewPath;
        }
        break;

    case 'supprimer':
        if ($isApiRequest) {
            // Cette section est gérée par supprimer_match pour l'API
            echo json_encode(['success' => false, 'error' => 'Utilisez supprimer_match pour l\'API REST']);
        } else {
            $id_match = $_GET['id_match'];
            $gameMatch->supprimerMatch($id_match);
            header("Location: MatchsController.php?action=liste");
            exit();
        }
        break;

    default:
        if ($isApiRequest) {
            echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
        } else {
            $matchs = $gameMatch->obtenirTousLesMatchs();
            // Adapter le chemin si appelé depuis index.php
            $viewPath = $isIncludedFromIndex ? 'views/matchs/index.php' : '../views/matchs/index.php';
            require $viewPath;
        }
        break;
}
?>
