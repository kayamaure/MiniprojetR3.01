<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Démarrez la session au début du fichier

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link rel="stylesheet" href="../views/css/style.css">
    <style>
        .btn-stat {
            background-color: #12c2f3;
        }

        .btn-stat:hover {
            background-color: #22a1e6; /* Orange plus foncé */
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="table-container">
        <div class="dashboard-container">
            <h1>Bienvenue sur le Tableau de Bord</h1>
            <?php if (isset($_SESSION['utilisateur'])) : ?>
                <p>Bonjour, <?= htmlspecialchars($_SESSION['utilisateur']); ?> ! Vous êtes connecté.</p>
            <?php else : ?>
                <p>Erreur : Utilisateur non connecté.</p>
            <?php endif; ?>
            <a href="../controllers/JoueursController.php?action=liste" class="btn btn-gestjou">Gestion des Joueurs</a></li>
            <a href="../controllers/MatchsController.php?action=liste" class="btn btn-gestmatch">Gestion des Matchs</a></li>
            <a href="../controllers/StatistiquesController.php?action=index" class="btn btn-stat">Statistiques</a></li> 
            <a href="../controllers/DeconnexionController.php" class="btn btn-deco">Déconnexion</a></li>

        </div>
    </div>
</body>

</html>

<?php include 'footer.php'; ?>