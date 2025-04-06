<?php
/**
 * Vérifie la présence et la validité d'un jeton auprès de l'API d'authentification.
 * En cas de problème, envoie un code HTTP 401 ou 403 et stoppe l'exécution.
 *
 * @param string $urlApiAuth L'URL de l'API d'authentification
 * @return void
 */

// utils/verifierJeton.php

function verifierJetonOuQuitter(string $urlApiAuth): void {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Jeton d'authentification non transmis"]);
        exit;
    }

    // Extraire le token
    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "Format de jeton invalide"]);
        exit;
    }
    $token = $matches[1];

    // Appel à l’API Auth en GET
    $ch = curl_init($urlApiAuth);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode === 200) {
        $json = json_decode($response, true);
        if (!empty($json['message']) && $json['message'] === "Token valide") {
            return; // on continue
        }
    }

    http_response_code(403);
    echo json_encode(["error" => "Jeton invalide ou expiré"]);
    exit;
}
