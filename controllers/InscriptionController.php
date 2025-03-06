<?php
/**
 * Contrôleur d'inscription
 *
 * Ce fichier gère l'inscription des nouveaux utilisateurs.
 * Il vérifie les données soumises, les insère dans la base de données si elles sont valides,
 * et redirige l'utilisateur en conséquence.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}require_once '../config/database.php';
require_once '../models/Utilisateur.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $utilisateur = new Utilisateur($db);

    // Récupération des données du formulaire
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifier si le nom d'utilisateur existe déjà
    if ($utilisateur->existeUtilisateur($nom_utilisateur)) {
        $erreur = "Le nom d'utilisateur existe déjà. Choisissez un autre nom.";
        require '../views/inscription.php';
    } else {
        // Ajouter un nouvel utilisateur avec un mot de passe haché
        if ($utilisateur->ajouterUtilisateur($nom_utilisateur, $mot_de_passe)) {
            header("Location: ../views/connexion.php?inscription=success"); // Redirection vers la page de connexion avec un message de succès
            exit();
        } else {
            $erreur = "Erreur lors de la création du compte.";
            require '../views/inscription.php';
        }
    }
} else {
    // Afficher le formulaire d'inscription si la méthode n'est pas POST
    require '../views/inscription.php';
}
