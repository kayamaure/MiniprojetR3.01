<?php
session_start(); // Démarrage de la session

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    // Redirige vers la page de connexion si non connecté
    header("Location: connexion.php");
    exit();
}

// Inclure la vue dashboard
include '../views/dashboard.php';
?>
