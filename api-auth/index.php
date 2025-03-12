<?php
header("Content-Type: application/json");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case "login":
        require_once "controllers/ConnexionController.php";
        break;
    case "register":
        require_once "controllers/InscriptionController.php";
        break;
    case "logout":
        require_once "controllers/DeconnexionController.php";
        break;
    case "moncompte":
        require_once "controllers/MonCompteController.php";
        break;
    default:
        echo json_encode(["error" => "Invalid action."]);
        break;
}
?>
