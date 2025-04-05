<?php
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generate_jwt(array $headers, array $payload, string $secret): string {
    $headers_encoded = base64url_encode(json_encode($headers));
    $payload_encoded = base64url_encode(json_encode($payload));

    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);

    return "$headers_encoded.$payload_encoded.$signature_encoded";
}

function is_jwt_valid(string $jwt, string $secret): bool {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) return false;

    list($header_encoded, $payload_encoded, $signature_provided) = $tokenParts;

    $signature = base64url_encode(hash_hmac('SHA256', "$header_encoded.$payload_encoded", $secret, true));

    return hash_equals($signature, $signature_provided);
}

function get_bearer_token(): ?string {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) return null;

    if (preg_match('/Bearer\\s(\S+)/', $headers['Authorization'], $matches)) {
        return $matches[1];
    }
    return null;
}
?>
