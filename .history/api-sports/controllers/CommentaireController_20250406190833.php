<?php

header("Content-Type: application/json");

// Autoriser uniquement les requêtes POST pour l'ajout de commentaires
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Méthode de requête invalide."]);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Commentaire.php';

// Création de la connexion à la base de données et instanciation du modèle Commentaire
$database = new Database();
$db = $database->getConnection();
$commentaire = new Commentaire($db);

// Récupération des données JSON envoyées dans le corps de la requête
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["error" => "Entrée JSON manquante ou invalide."]);
    exit();
}

// Vérifier que les champs obligatoires sont présents
if (!isset($input['sujet_commentaire'], $input['texte_commentaire'], $input['numero_licence'])) {
    echo json_encode([
        "error" => "Champs requis manquants : sujet_commentaire, texte_commentaire, numero_licence."
    ]);
    exit();
}

// Préparer les données à insérer
$data = [
    "sujet_commentaire"   => $input['sujet_commentaire'],
    "texte_commentaire"   => $input['texte_commentaire'],
    "numero_licence"      => $input['numero_licence']
];

// Tenter d'ajouter le commentaire dans la base de données
if ($commentaire->ajouterCommentaire($data)) {
    echo json_encode(["success" => "Commentaire ajouté avec succès."]);
} else {
    echo json_encode(["error" => "Échec de l'ajout du commentaire."]);
}

exit();
?>
