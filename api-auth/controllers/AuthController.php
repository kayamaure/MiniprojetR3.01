<?php
require_once __DIR__ . '/../utils/jwt-utils.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class AuthController {
    private $userModel;
    private $secret;

    public function __construct($userModel, $secret) {
        $this->userModel = $userModel;
        $this->secret = $secret;
    }

    /**
     * Création d'un jeton (login).
     * - Vérifie le mot de passe via password_verify()
     * - Génère un JWT si OK
     */
    public function login($nom_utilisateur, $mot_de_passe) {
        // 1. Trouver l'utilisateur en base
        $user = $this->userModel->trouverParNomUtilisateur($nom_utilisateur);
        if (!$user) {
            http_response_code(401);
            echo json_encode(["error" => "Utilisateur introuvable"]);
            return;
        }
        // 2. Vérifier le mot de passe haché
        if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(["error" => "Mot de passe incorrect"]);
            return;
        }

        // 3. Construire le token JWT
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'id_utilisateur' => $user['id_utilisateur'],
            'nom_utilisateur' => $user['nom_utilisateur'],
            'exp' => time() + 3600  // Expire dans 1h
        ];
        $jwt = generate_jwt($headers, $payload, $this->secret);

        // 4. Enregistrer le jeton en base (table "tokens")
        $this->userModel->enregistrerJeton($user['id_utilisateur'], $jwt, date('Y-m-d H:i:s', $payload['exp']));

        // 5. Mettre à jour la dernière connexion
        $this->userModel->mettreAJourDerniereConnexion($user['id_utilisateur']);

        // 6. Retourner le jeton en JSON
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Connexion réussie",
            "token" => $jwt,
            "user" => [
                "id" => $user['id_utilisateur'],
                "username" => $user['nom_utilisateur']
            ]
        ]);
    }

    /**
     * Vérification de la validité d'un jeton (GET)
     * - Si token invalide → 403
     * - Sinon → message "Token valide"
     */
    public function verifyToken($jwt) {
        if (!$jwt || !is_jwt_valid($jwt, $this->secret)) {
            http_response_code(403);
            echo json_encode(["error" => "Token invalide"]);
            return;
        }
        echo json_encode(["message" => "Token valide"]);
    }
}
