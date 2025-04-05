<?php
require_once __DIR__ . '/../models/Joueur.php';
require_once __DIR__ . '/../config/database.php';

class JoueursController {
    private $db;
    private $joueur;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->joueur = new Joueur($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $this->getJoueur($_GET['id']);
                } else {
                    $this->getJoueurs();
                }
                break;

            case 'POST':
                $this->createJoueur();
                break;

            case 'PUT':
                $this->updateJoueur();
                break;

            case 'DELETE':
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID du joueur non fourni"]);
                    return;
                }
                $this->deleteJoueur($_GET['id']);
                break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Méthode non autorisée"]);
                break;
        }
    }

    private function getJoueurs() {
        $result = $this->joueur->getAll();
        if ($result) {
            echo json_encode(["success" => true, "data" => $result]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Aucun joueur trouvé"]);
        }
    }

    private function getJoueur($id) {
        $result = $this->joueur->getById($id);
        if ($result) {
            echo json_encode(["success" => true, "data" => $result]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Joueur non trouvé"]);
        }
    }

    private function createJoueur() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Vérification des champs requis
        if (!$this->validateJoueurData($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes ou invalides"]);
            return;
        }

        if ($this->joueur->create($data)) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "Joueur créé avec succès"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la création du joueur"]);
        }
    }

    private function updateJoueur() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID du joueur non fourni"]);
            return;
        }

        // Vérification des champs requis
        if (!$this->validateJoueurData($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes ou invalides"]);
            return;
        }

        if ($this->joueur->update($data)) {
            echo json_encode([
                "success" => true,
                "message" => "Joueur mis à jour avec succès"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la mise à jour du joueur"]);
        }
    }

    private function deleteJoueur($id) {
        if ($this->joueur->delete($id)) {
            echo json_encode([
                "success" => true,
                "message" => "Joueur supprimé avec succès"
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Joueur non trouvé ou erreur lors de la suppression"]);
        }
    }

    private function validateJoueurData($data) {
        $required_fields = ['nom', 'prenom', 'numero', 'position'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }
        return true;
    }
}

// Instanciation et traitement de la requête
$controller = new JoueursController();
$controller->handleRequest();
