<?php
class AuthMiddleware {
    private static $auth_api_url = 'http://localhost/MiniprojetR3.01/api-auth/index.php?action=verify-token';

    public static function verifyToken() {
        // Récupération du token depuis le header Authorization
        $headers = apache_request_headers();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (empty($auth_header) || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            http_response_code(401);
            echo json_encode(["error" => "Token non fourni"]);
            exit();
        }

        $token = $matches[1];

        // Vérification du token auprès de l'API d'authentification
        $ch = curl_init(self::$auth_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            http_response_code(401);
            echo json_encode(["error" => "Token invalide"]);
            exit();
        }

        $response_data = json_decode($response, true);
        return $response_data;
    }
}
