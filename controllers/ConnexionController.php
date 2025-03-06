<?php
/**
 * Contrôleur de connexion
 *
 * Ce fichier gère le processus d'authentification des utilisateurs.
 * - Si la méthode est POST : vérifie les informations de connexion fournies.
 * - Si la méthode est GET ou autre : affiche le formulaire de connexion.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../models/Utilisateur.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Création d'une instance de connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();
    
    // Création de l'objet utilisateur
    $utilisateur = new Utilisateur($db);

    // Récupération des données du formulaire
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérification des informations de connexion
    if ($utilisateur->verifierUtilisateur($nom_utilisateur, $mot_de_passe)) {
        // Authentification réussie : on stocke l'ID de l'utilisateur dans la session
        // (On fait une petite requête pour récupérer l'id_utilisateur, 
        //  puisque verifierUtilisateur() renvoie juste true/false)
        $query = "SELECT id_utilisateur 
                  FROM utilisateur 
                  WHERE nom_utilisateur = :nom_utilisateur 
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si on a trouvé un id_utilisateur, on le met en session
        if ($row && isset($row['id_utilisateur'])) {
            $_SESSION['id_utilisateur'] = $row['id_utilisateur'];
        }

        // Vous pouvez garder l’ancien usage si vous souhaitez aussi conserver le nom utilisateur :
        $_SESSION['utilisateur'] = $nom_utilisateur;

        // Redirection vers le tableau de bord
        header("Location: ../views/dashboard.php");
        exit();
    } else {
        // Authentification échouée : message d'erreur
        $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
        require '../views/connexion.php';
    }
} else {
    // Si la requête n'est pas en méthode POST, afficher le formulaire de connexion
    require '../views/connexion.php';
}
