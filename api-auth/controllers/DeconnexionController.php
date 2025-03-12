<?php
// DeconnexionController.php for API authentication (logout)
header("Content-Type: application/json");

// In a stateless JWT system, logout is managed client-side by deleting the token.
// Optionally, implement token invalidation if needed.
echo json_encode(["success" => "Logout successful."]);
?>
