<?php
require_once __DIR__ . '/../utils/jwt-utils.php';

class AuthController {
    private $userModel;
    private $secret;

    public function __construct($userModel, $secret) {
        $this->userModel = $userModel;
        $this->secret = $secret;
    }

    public function login($username, $password) {
        $user = $this->userModel->findByUsername($username);
        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(["error" => "Identifiants invalides"]);
            return;
        }

        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'id_utilisateur' => $user['id_utilisateur'],
            'nom_utilisateur' => $user['nom_utilisateur'],
            'exp' => time() + 3600
        ];

        $jwt = generate_jwt($headers, $payload, $this->secret);
        $this->userModel->insertToken($user['id_utilisateur'], $jwt, date('Y-m-d H:i:s', $payload['exp']));
        $this->userModel->updateLastLogin($user['id_utilisateur']);

        echo json_encode(["token" => $jwt]);
    }

    public function verifyToken($jwt) {
        if (!$jwt || !is_jwt_valid($jwt, $this->secret)) {
            http_response_code(403);
            echo json_encode(["error" => "Token invalide"]);
        } else {
            echo json_encode(["message" => "Token valide"]);
        }
    }
}
?>
