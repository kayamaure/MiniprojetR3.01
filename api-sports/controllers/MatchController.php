<?php
require_once __DIR__ . '/../models/Match.php';
require_once __DIR__ . '/../config/database.php';

class MatchController {
    private $match;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->match = new GameMatch($this->db);
    }

    // GET - Obtenir tous les matchs
    public function index() {
        try {
            if (isset($_GET['statut'])) {
                $matchs = $this->match->obtenirMatchsParStatut($_GET['statut']);
            } else {
                $matchs = $this->match->obtenirTousLesMatchs();
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $matchs]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des matchs']);
        }
    }

    // GET - Obtenir un match par son ID
    public function show($id_match) {
        try {
            $match = $this->match->obtenirMatch($id_match);
            if ($match) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $match]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Match non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération du match']);
        }
    }

    // POST - Créer un nouveau match
    public function create() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validation des données
            if (!$this->validateMatchData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
                return;
            }

            if ($this->match->ajouterMatch($data)) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Match créé avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du match']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du match']);
        }
    }

    // PUT - Mettre à jour un match
    public function update($id_match) {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $data['id_match'] = $id_match;

            // Validation des données
            if (!$this->validateMatchData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
                return;
            }

            if ($this->match->mettreAJourMatch($data)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Match mis à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du match']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du match']);
        }
    }

    // DELETE - Supprimer un match
    public function delete($id_match) {
        try {
            if ($this->match->supprimerMatch($id_match)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Match supprimé avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du match']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du match']);
        }
    }

    // Validation des données d'un match
    private function validateMatchData($data) {
        $required_fields = ['date_match', 'heure_match', 'nom_equipe_adverse', 'lieu_de_rencontre'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        // Validation de la date (format : YYYY-MM-DD)
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['date_match'])) {
            return false;
        }

        // Validation de l'heure (format : HH:MM:SS)
        if (!preg_match("/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/", $data['heure_match'])) {
            return false;
        }

        // Validation du lieu de rencontre
        if (!in_array($data['lieu_de_rencontre'], ['Domicile', 'Extérieur'])) {
            return false;
        }

        // Validation du résultat si présent
        if (isset($data['resultat']) && !in_array($data['resultat'], ['Victoire', 'Défaite', 'Match Nul'])) {
            return false;
        }

        return true;
    }
}