<?php
// A simple JWT helper implementation

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function create_jwt($payload, $secret) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode($payload);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);

    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    return $jwt;
}

function verify_jwt($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
    $header = json_decode(base64_decode($base64UrlHeader), true);
    if ($header === null || $header['alg'] !== 'HS256') {
        return false;
    }
    $payload = json_decode(base64_decode($base64UrlPayload), true);
    if ($payload === null) {
        return false;
    }
    // Check token expiration
    if (isset($payload['exp']) && time() >= $payload['exp']) {
        return false;
    }
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignatureCheck = base64UrlEncode($signature);
    if ($base64UrlSignature !== $base64UrlSignatureCheck) {
        return false;
    }
    return $payload;
}
?>
