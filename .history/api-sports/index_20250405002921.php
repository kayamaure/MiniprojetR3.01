<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérification du token JWT
require_once __DIR__ . '/../api-auth/config/jwt_utils.php';

$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token d'authentification manquant"]);
    exit();
}

$token = $matches[1];
if (!JWTUtils::verifyToken($token)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token d'authentification invalide ou expiré"]);
    exit();
}

// Récupération de la méthode HTTP et de la ressource
$method = $_SERVER['REQUEST_METHOD'];
$resource = isset($_GET['resource']) ? $_GET['resource'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Routage des requêtes
switch ($resource) {
    case 'joueurs':
        require_once "controllers/JoueurController.php";
        $controller = new JoueurController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $controller->show($id);
                } else {
                    if (isset($_GET['active'])) {
                        $controller->getActiveJoueurs();
                    } else {
                        $controller->index();
                    }
                }
                break;
            case 'POST':
                $controller->create();
                break;
            case 'PUT':
                if ($id) {
                    $controller->update($id);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID manquant"]);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $controller->delete($id);
                } else {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID manquant"]);
                }
                break;
        }
        break;

    case 'matchs':
        require_once "controllers/MatchController.php";
        // TODO: Implémenter le contrôleur des matchs
        break;

    case 'feuilles':
        require_once "controllers/FeuilleMatchController.php";
        // TODO: Implémenter le contrôleur des feuilles de match
        break;

    case 'statistiques':
        require_once "controllers/StatistiqueController.php";
        // TODO: Implémenter le contrôleur des statistiques
        break;

    default:
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Ressource non trouvée"]);
        break;
}
?>