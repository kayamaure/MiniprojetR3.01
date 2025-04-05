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

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $this->getMatch($_GET['id']);
                } else {
                    $this->getMatches();
                }
                break;

            case 'POST':
                $this->createMatch();
                break;

            case 'PUT':
                $this->updateMatch();
                break;

            case 'DELETE':
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID du match non fourni"]);
                    return;
                }
                $this->deleteMatch($_GET['id']);
                break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Méthode non autorisée"]);
                break;
        }
    }

    private function getMatches() {
        try {
            if (isset($_GET['statut'])) {
                $matches = $this->match->getByStatus($_GET['statut']);
            } else {
                $matches = $this->match->getAll();
            }
            
            if ($matches) {
                echo json_encode(["success" => true, "data" => $matches]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Aucun match trouvé"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la récupération des matchs"]);
        }
    }

    private function getMatch($id) {
        try {
            $match = $this->match->getById($id);
            if ($match) {
                echo json_encode(["success" => true, "data" => $match]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Match non trouvé"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la récupération du match"]);
        }
    }

    private function createMatch() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->validateMatchData($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes ou invalides"]);
            return;
        }

        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validation des données
            if (!$this->validateMatchData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
                return;
            }

            if ($this->match->create($data)) {
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

    private function updateMatch() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID du match non fourni"]);
            return;
        }

        if (!$this->validateMatchData($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes ou invalides"]);
            return;
        }

        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $id_match = $data['id'];

            // Validation des données
            if (!$this->validateMatchData($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
                return;
            }

            if ($this->match->update($data)) {
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

    private function deleteMatch($id) {
        try {
            if ($this->match->delete($id)) {
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