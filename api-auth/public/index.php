<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$route = trim(str_replace($script_name, '', $uri), '/');

switch ($route) {
    case 'auth':
        require_once __DIR__ . '/../endpoints/AuthEndpoint.php';
        break;
    case 'register':
        require_once __DIR__ . '/../endpoints/RegisterEndpoint.php';
        break;
    case 'logout':
        require_once __DIR__ . '/../endpoints/LogoutEndpoint.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route non trouvée"]);
        break;
}
