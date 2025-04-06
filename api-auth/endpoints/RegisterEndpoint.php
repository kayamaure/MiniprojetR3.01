    <?php
    /**
     * Point d'entrée pour l'inscription des utilisateurs
     * Gère la création de nouveaux comptes utilisateurs
     */

    header('Content-Type: application/json');

    // Inclusion des dépendances nécessaires
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Utilisateur.php';

    // Récupération de la méthode HTTP
    $method = $_SERVER['REQUEST_METHOD'];

    // Vérification que la méthode est bien POST
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        exit;
    }

    // Récupération et validation des données du formulaire
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nom d'utilisateur ou mot de passe manquant"]);
        exit;
    }

    // Préparation des données utilisateur
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hashage du mot de passe

    // Vérification de l'unicité du nom d'utilisateur
    $utilisateur = new Utilisateur($pdo);
    if ($utilisateur->trouverParNomUtilisateur($username)) {
        http_response_code(409); // Conflit
        echo json_encode(["error" => "Nom d'utilisateur déjà utilisé"]);
        exit;
    }

    // Tentative de création du nouvel utilisateur
    $success = $utilisateur->creerUtilisateur($username, $password);

    // Envoi de la réponse selon le résultat
    if ($success) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Utilisateur enregistré avec succès"]);
    } else {
        http_response_code(500); // Erreur serveur
        echo json_encode(["error" => "Erreur lors de la création de l'utilisateur"]);
    }
